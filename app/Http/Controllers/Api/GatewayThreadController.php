<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FamilyAgent;
use App\Models\GatewayRequestLog;
use App\Models\Thread;
use App\Services\Ai\ProviderClientManager;
use App\Services\Gateway\CommandParser;
use App\Services\Gateway\CompactionService;
use App\Services\Gateway\HistoryBuilder;
use App\Services\Gateway\LimitService;
use App\Services\Gateway\ProviderResolver;
use App\Services\Gateway\TokenEstimator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Throwable;

class GatewayThreadController extends Controller
{
    public function __construct(
        private readonly ProviderResolver $resolver,
        private readonly ProviderClientManager $clients,
        private readonly TokenEstimator $tokens,
        private readonly CommandParser $commands,
        private readonly HistoryBuilder $history,
        private readonly CompactionService $compaction,
        private readonly LimitService $limits,
    ) {
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'family_agent' => ['required', 'string'],
            'content' => ['required', 'string'],
            'provider' => ['nullable', 'string'],
            'model' => ['nullable', 'string'],
            'inner_provider' => ['nullable'],
            'encryption_key' => ['nullable', 'string'],
        ]);

        $family = FamilyAgent::query()->where('number', $data['family_agent'])->where('is_enabled', true)->firstOrFail();
        $route = $this->resolver->resolve($family, null, $data);
        $account = $request->attributes->get('customer_account');
        $apiKey = $request->attributes->get('api_key');

        $thread = Thread::query()->create([
            'public_id' => (string) Str::uuid(),
            'customer_account_id' => $account->id,
            'api_key_id' => $apiKey->id,
            'family_agent_id' => $family->id,
            'provider_id' => $route->provider->id,
            'provider_model_id' => $route->model->id,
            'title' => Str::limit($data['content'], 80),
            'max_context_tokens' => $family->max_context_tokens,
            'metadata' => ['encryption_key_present' => filled($data['encryption_key'] ?? null)],
        ]);

        return $this->handleMessage($request, $thread, $data);
    }

    public function message(Request $request, string $publicId): JsonResponse
    {
        $data = $request->validate([
            'content' => ['required', 'string'],
            'provider' => ['nullable', 'string'],
            'model' => ['nullable', 'string'],
            'inner_provider' => ['nullable'],
            'encryption_key' => ['nullable', 'string'],
        ]);

        $account = $request->attributes->get('customer_account');
        $thread = Thread::query()
            ->where('public_id', $publicId)
            ->where('customer_account_id', $account->id)
            ->firstOrFail();

        return $this->handleMessage($request, $thread, $data);
    }

    private function handleMessage(Request $request, Thread $thread, array $data): JsonResponse
    {
        $account = $request->attributes->get('customer_account');
        $apiKey = $request->attributes->get('api_key');
        $family = $thread->familyAgent;
        $command = $this->commands->parse($data['content']);
        $content = $this->commands->withoutCommand($data['content']);
        $inputEstimate = $this->tokens->estimate($content);

        $this->limits->assertCanUse($account, $inputEstimate);

        if ($command === 'forget') {
            $forgotten = $this->compaction->forget($thread, $content);
            $this->limits->recordUsage($account, $inputEstimate);

            GatewayRequestLog::query()->create([
                'customer_account_id' => $account->id,
                'api_key_id' => $apiKey->id,
                'thread_id' => $thread->id,
                'provider_id' => $thread->provider_id,
                'provider_model_id' => $thread->provider_model_id,
                'status' => 'ok',
                'input_tokens' => $inputEstimate,
                'output_tokens' => 0,
                'request_payload' => ['command' => $command, 'content' => Str::limit($content, 500)],
                'response_metadata' => ['forgotten' => $forgotten],
            ]);

            return response()->json([
                'thread_id' => $thread->public_id,
                'response' => "Forgot {$forgotten} matching memory item(s).",
                'usage' => ['input_tokens' => $inputEstimate, 'output_tokens' => 0],
                'max_context_tokens' => $thread->max_context_tokens,
                'compaction' => ['triggered' => false],
            ]);
        }

        $route = $this->resolver->resolve($family, $thread, $data);
        $messages = $this->history->build($family, $thread, $command === 'skip');
        $messages[] = ['role' => 'user', 'content' => $content];

        try {
            $response = $this->clients->forProvider($route->provider)->chat($route->provider, $route->model, $messages, $data);
            $inputTokens = $response->inputTokens ?: $inputEstimate;
            $outputTokens = $response->outputTokens ?: $this->tokens->estimate($response->content);

            $userMessage = null;
            if ($command !== 'whisper') {
                $userMessage = $thread->messages()->create([
                    'role' => 'user',
                    'content' => $content,
                    'input_tokens' => $inputEstimate,
                    'command' => $command,
                ]);
            }

            if ($command !== 'whisper') {
                $thread->messages()->create([
                    'role' => 'assistant',
                    'content' => $response->content,
                    'output_tokens' => $outputTokens,
                    'metadata' => ['finish_reason' => $response->finishReason],
                ]);
            }

            $thread->increment('input_tokens', $inputTokens);
            $thread->increment('output_tokens', $outputTokens);
            $this->limits->recordUsage($account, $inputTokens + $outputTokens);

            $compacted = $this->compaction->compact($thread->fresh(), $command === 'dayend');

            GatewayRequestLog::query()->create([
                'customer_account_id' => $account->id,
                'api_key_id' => $apiKey->id,
                'thread_id' => $thread->id,
                'provider_id' => $route->provider->id,
                'provider_model_id' => $route->model->id,
                'status' => 'ok',
                'input_tokens' => $inputTokens,
                'output_tokens' => $outputTokens,
                'request_payload' => ['command' => $command, 'content' => Str::limit($content, 500)],
                'response_metadata' => $response->metadata,
            ]);

            return response()->json([
                'thread_id' => $thread->public_id,
                'message_id' => $userMessage?->id,
                'response' => $response->content,
                'provider' => $route->provider->slug,
                'model' => $route->model->model_key,
                'usage' => ['input_tokens' => $inputTokens, 'output_tokens' => $outputTokens],
                'max_context_tokens' => $thread->max_context_tokens,
                'compaction' => ['triggered' => $compacted],
            ]);
        } catch (Throwable $exception) {
            GatewayRequestLog::query()->create([
                'customer_account_id' => $account->id,
                'api_key_id' => $apiKey->id,
                'thread_id' => $thread->id,
                'provider_id' => $route->provider->id,
                'provider_model_id' => $route->model->id,
                'status' => 'error',
                'input_tokens' => $inputEstimate,
                'request_payload' => ['command' => $command, 'content' => Str::limit($content, 500)],
                'error_message' => $exception->getMessage(),
            ]);

            return response()->json(['message' => 'Provider request failed.', 'error' => $exception->getMessage()], 502);
        }
    }
}
