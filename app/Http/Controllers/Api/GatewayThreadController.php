<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FamilyAgent;
use App\Models\GatewayRequestLog;
use App\Models\Thread;
use App\Services\Ai\ProviderClientManager;
use App\Services\Gateway\CommandParser;
use App\Services\Gateway\CompactionResult;
use App\Services\Gateway\CompactionService;
use App\Services\Gateway\HistoryBuilder;
use App\Services\Gateway\LimitService;
use App\Services\Gateway\ProviderResolver;
use App\Services\Gateway\TokenEstimator;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
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

    public function store(Request $request): SymfonyResponse
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
        $account = $request->attributes->get('customer_account');
        $apiKey = $request->attributes->get('api_key');
        $command = $this->commands->parse($data['content']);
        $content = $this->commands->withoutCommand($data['content']);
        $inputEstimate = $this->tokens->estimate($content);

        $this->limits->assertCanUse($account, $command === 'dayend' ? 0 : $inputEstimate);

        if ($command === 'dayend') {
            $route = $this->resolver->resolve($family, null, $data);
            $this->limits->recordUsage($account, 0);

            GatewayRequestLog::query()->create([
                'customer_account_id' => $account->id,
                'api_key_id' => $apiKey->id,
                'thread_id' => null,
                'provider_id' => $route->provider->id,
                'provider_model_id' => $route->model->id,
                'status' => 'ok',
                'input_tokens' => 0,
                'output_tokens' => 0,
                'request_payload' => ['command' => $command, 'content' => Str::limit($content, 500)],
                'response_metadata' => ['handled' => true, 'compaction_triggered' => false],
            ]);

            return response()->json([
                'thread_id' => null,
                'response' => 'Order complete!! No thread was created because /dayend only manages compaction.',
                'usage' => ['input_tokens' => 0, 'output_tokens' => 0],
                'max_context_tokens' => $family->max_context_tokens,
                'compaction' => ['triggered' => false],
            ]);
        }

        $route = $this->resolver->resolve($family, null, $data);

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

        $response = $this->handleMessage($request, $thread, $data);

        if ($response->getStatusCode() >= 400 && ! $thread->messages()->exists()) {
            $thread->delete();
        }

        return $response;
    }

    public function message(Request $request, string $publicId): SymfonyResponse
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

    private function handleMessage(Request $request, Thread $thread, array $data): SymfonyResponse
    {
        set_time_limit(config('threadcore.gateway.timeout_seconds', 1200));

        $account = $request->attributes->get('customer_account');
        $apiKey = $request->attributes->get('api_key');
        $family = $thread->familyAgent;
        $command = $this->commands->parse($data['content']);
        $content = $this->commands->withoutCommand($data['content']);
        $inputEstimate = $this->tokens->estimate($content);

        $this->limits->assertCanUse($account, $command === 'forget' ? 0 : $inputEstimate);

        if ($command === 'dayend') {
            try {
                $compaction = $this->compaction->compact($thread->fresh(), true, $data);
            } catch (Throwable $exception) {
                GatewayRequestLog::query()->create([
                    'customer_account_id' => $account->id,
                    'api_key_id' => $apiKey->id,
                    'thread_id' => $thread->id,
                    'provider_id' => $thread->provider_id,
                    'provider_model_id' => $thread->provider_model_id,
                    'status' => 'error',
                    'input_tokens' => 0,
                    'output_tokens' => 0,
                    'request_payload' => ['command' => $command, 'content' => Str::limit($content, 500)],
                    'error_message' => $exception->getMessage(),
                ]);

                return response()->json(['message' => 'Provider request failed.', 'error' => $exception->getMessage()], 502);
            }

            if ($compaction->triggered) {
                $this->limits->recordUsage($account, $compaction->inputTokens + $compaction->outputTokens);
            }

            $providerId = $thread->provider_id;
            $providerModelId = $thread->provider_model_id;

            if ($compaction->triggered && $compaction->providerId && $compaction->providerModelId) {
                $providerId = $compaction->providerId;
                $providerModelId = $compaction->providerModelId;
            }

            GatewayRequestLog::query()->create([
                'customer_account_id' => $account->id,
                'api_key_id' => $apiKey->id,
                'thread_id' => $thread->id,
                'provider_id' => $providerId,
                'provider_model_id' => $providerModelId,
                'status' => 'ok',
                'input_tokens' => $compaction->inputTokens,
                'output_tokens' => $compaction->outputTokens,
                'request_payload' => ['command' => $command, 'content' => Str::limit($content, 500)],
                'response_metadata' => ['handled' => true, 'compaction_triggered' => $compaction->triggered],
            ]);

            return response()->json([
                'thread_id' => $thread->public_id,
                'response' => $compaction->triggered
                    ? 'Order complete!! Conversation memory was compacted.'
                    : 'Order complete!! Nothing needed compaction yet.',
                'usage' => ['input_tokens' => $compaction->inputTokens, 'output_tokens' => $compaction->outputTokens],
                'max_context_tokens' => $thread->max_context_tokens,
                'compaction' => ['triggered' => $compaction->triggered],
            ]);
        }

        if ($command === 'forget') {
            $forgotten = $this->compaction->forget($thread, $content);

            $thread->messages()->create([
                'role' => 'user',
                'content' => $content,
                'input_tokens' => 0,
                'output_tokens' => 0,
                'cost' => '0.000000',
                'command' => $command,
                'is_forgotten' => true,
            ]);

            $thread->messages()->create([
                'role' => 'assistant',
                'content' => 'Order done!!',
                'input_tokens' => 0,
                'output_tokens' => 0,
                'cost' => '0.000000',
                'is_forgotten' => true,
                'metadata' => ['forgotten' => $forgotten],
            ]);

            $this->limits->recordUsage($account, 0);

            GatewayRequestLog::query()->create([
                'customer_account_id' => $account->id,
                'api_key_id' => $apiKey->id,
                'thread_id' => $thread->id,
                'provider_id' => $thread->provider_id,
                'provider_model_id' => $thread->provider_model_id,
                'status' => 'ok',
                'input_tokens' => 0,
                'output_tokens' => 0,
                'request_payload' => ['command' => $command, 'content' => Str::limit($content, 500)],
                'response_metadata' => ['forgotten' => $forgotten],
            ]);

            return response()->json([
                'thread_id' => $thread->public_id,
                'response' => 'Order done!!',
                'usage' => ['input_tokens' => 0, 'output_tokens' => 0],
                'max_context_tokens' => $thread->max_context_tokens,
                'compaction' => ['triggered' => false],
            ]);
        }

        if ($command === 'whisper' || $command === 'skip') {
            $route = $this->resolver->resolve($family, $thread, $data);
            $messages = $this->history->build($family, $thread, $command === 'skip');
            $messages[] = ['role' => 'user', 'content' => $content];

            try {
                $response = $this->clients->forProvider($route->provider)->chat($route->provider, $route->model, $messages, $data);
                $inputTokens = $response->inputTokens ?: $inputEstimate;
                $outputTokens = $response->outputTokens ?: $this->tokens->estimate($response->content);

                $thread->messages()->create([
                    'role' => 'user',
                    'content' => $content,
                    'input_tokens' => 0,
                    'output_tokens' => 0,
                    'cost' => '0.000000',
                    'command' => $command,
                    'is_forgotten' => true,
                ]);

                $thread->messages()->create([
                    'role' => 'assistant',
                    'content' => $response->content,
                    'input_tokens' => $inputTokens,
                    'output_tokens' => $outputTokens,
                    'cost' => $response->cost,
                    'is_forgotten' => true,
                    'metadata' => [
                        'finish_reason' => $response->finishReason,
                        'usage' => $response->usage,
                    ],
                ]);

                $thread->increment('input_tokens', $inputTokens);
                $thread->increment('output_tokens', $outputTokens);
                $this->limits->recordUsage($account, $inputTokens + $outputTokens);

                $compaction = new CompactionResult();
                $compactionError = null;

                try {
                    $compaction = $this->compaction->compact($thread->fresh(), false, []);
                } catch (Throwable $compactionException) {
                    $compactionError = $compactionException->getMessage();
                }

                if ($compaction->triggered) {
                    $this->limits->recordUsage($account, $compaction->inputTokens + $compaction->outputTokens);
                }

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
                    'response_metadata' => array_filter(
                        array_merge($response->metadata, [
                            'finish_reason' => $response->finishReason,
                            'normalized_usage' => $response->usage,
                            'compaction_triggered' => $compaction->triggered,
                            'compaction_error' => $compactionError,
                        ]),
                        static fn ($value) => $value !== null,
                    ),
                ]);

                return response()->json([
                    'thread_id' => $thread->public_id,
                    'response' => $response->content,
                    'provider' => $route->provider->slug,
                    'model' => $route->model->model_key,
                    'usage' => ['input_tokens' => $inputTokens, 'output_tokens' => $outputTokens],
                    'max_context_tokens' => $thread->max_context_tokens,
                    'compaction' => ['triggered' => $compaction->triggered],
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

        $route = $this->resolver->resolve($family, $thread, $data);
        $messages = $this->history->build($family, $thread, false);
        $messages[] = ['role' => 'user', 'content' => $content];

        try {
            $response = $this->clients->forProvider($route->provider)->chat($route->provider, $route->model, $messages, $data);
            $inputTokens = $response->inputTokens ?: $inputEstimate;
            $outputTokens = $response->outputTokens ?: $this->tokens->estimate($response->content);

            $userMessage = $thread->messages()->create([
                'role' => 'user',
                'content' => $content,
                'input_tokens' => 0,
                'output_tokens' => 0,
                'cost' => '0.000000',
                'command' => null,
            ]);

            $thread->messages()->create([
                'role' => 'assistant',
                'content' => $response->content,
                'input_tokens' => $inputTokens,
                'output_tokens' => $outputTokens,
                'cost' => $response->cost,
                'metadata' => [
                    'finish_reason' => $response->finishReason,
                    'usage' => $response->usage,
                ],
            ]);

            $thread->increment('input_tokens', $inputTokens);
            $thread->increment('output_tokens', $outputTokens);
            $this->limits->recordUsage($account, $inputTokens + $outputTokens);

                $compaction = new CompactionResult();
                $compactionError = null;

                try {
                    $compaction = $this->compaction->compact($thread->fresh(), false, []);
                } catch (Throwable $compactionException) {
                    $compactionError = $compactionException->getMessage();
                }

                if ($compaction->triggered) {
                    $this->limits->recordUsage($account, $compaction->inputTokens + $compaction->outputTokens);
                }

                GatewayRequestLog::query()->create([
                    'customer_account_id' => $account->id,
                    'api_key_id' => $apiKey->id,
                    'thread_id' => $thread->id,
                    'provider_id' => $route->provider->id,
                    'provider_model_id' => $route->model->id,
                    'status' => 'ok',
                    'input_tokens' => $inputTokens,
                    'output_tokens' => $outputTokens,
                    'request_payload' => ['command' => null, 'content' => Str::limit($content, 500)],
                    'response_metadata' => array_filter(
                        array_merge($response->metadata, [
                            'finish_reason' => $response->finishReason,
                            'normalized_usage' => $response->usage,
                            'compaction_triggered' => $compaction->triggered,
                            'compaction_error' => $compactionError,
                        ]),
                        static fn ($value) => $value !== null,
                    ),
                ]);

            return response()->json([
                'thread_id' => $thread->public_id,
                'message_id' => $userMessage?->id,
                'response' => $response->content,
                'provider' => $route->provider->slug,
                'model' => $route->model->model_key,
                'usage' => ['input_tokens' => $inputTokens, 'output_tokens' => $outputTokens],
                'max_context_tokens' => $thread->max_context_tokens,
                'compaction' => ['triggered' => $compaction->triggered],
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
                'request_payload' => ['command' => null, 'content' => Str::limit($content, 500)],
                'error_message' => $exception->getMessage(),
            ]);

            return response()->json(['message' => 'Provider request failed.', 'error' => $exception->getMessage()], 502);
        }
    }
}
