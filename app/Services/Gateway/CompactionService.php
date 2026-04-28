<?php

namespace App\Services\Gateway;

use App\Models\FamilyAgent;
use App\Models\Provider;
use App\Models\ProviderModel;
use App\Models\Thread;
use App\Services\Ai\ProviderClientManager;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class CompactionService
{
    public function __construct(
        private readonly ProviderClientManager $clients,
        private readonly TokenEstimator $tokens,
    ) {
    }

    public function compact(Thread $thread, bool $force = false, array $overrides = []): CompactionResult
    {
        $family = $thread->familyAgent;

        $pendingMemoryMessages = $thread->messages()
            ->where('is_compacted', false)
            ->where('is_memory', true)
            ->where('is_forgotten', false)
            ->oldest()
            ->get();

        $rawMessages = $thread->messages()
            ->where('is_compacted', false)
            ->where('is_memory', false)
            ->where('is_forgotten', false)
            ->oldest()
            ->get();

        $messagesForCompaction = $pendingMemoryMessages->concat($rawMessages);

        if ($rawMessages->isEmpty() || $messagesForCompaction->count() < 2) {
            return new CompactionResult();
        }

        $activeContextTokens = $messagesForCompaction
            ->sum(fn ($message) => $this->tokens->estimate($message->content));

        if (filled($family->system_prompt)) {
            $activeContextTokens += $this->tokens->estimate($family->system_prompt);
        }

        if (! $force && $activeContextTokens < $family->compaction_threshold_tokens) {
            return new CompactionResult();
        }

        [$compactionProvider, $compactionModel] = $this->resolveCompactionRoute($family, $force ? $overrides : []);
        $compactionPrompt = filled($family->compaction_prompt) ? $family->compaction_prompt : 'Compacted memory';

        $allMessages = $messagesForCompaction
            ->map(fn ($message) => strtoupper($message->role).': '.$message->content)
            ->implode("\n");

        $response = $this->clients->forProvider($compactionProvider)->chat($compactionProvider, $compactionModel, [
            ['role' => 'user', 'content' => $compactionPrompt.":\n".$allMessages],
        ], $overrides);

        if (trim($response->content) === '') {
            throw new RuntimeException("Compaction provider [{$compactionProvider->slug}] returned empty content.");
        }

        $compactedContent = $response->content;
        $inputTokens = $response->inputTokens ?: $this->tokens->estimate($allMessages);
        $outputTokens = $response->outputTokens ?: $this->tokens->estimate($compactedContent);

        DB::transaction(function () use ($thread, $messagesForCompaction, $pendingMemoryMessages, $rawMessages, $compactionProvider, $compactionModel, $response, $allMessages, $compactedContent, $inputTokens, $outputTokens): void {
            $thread->messages()
                ->whereIn('id', $messagesForCompaction->pluck('id'))
                ->update(['is_compacted' => true]);

            $thread->messages()->create([
                'role' => 'system',
                'content' => $compactedContent,
                'input_tokens' => $inputTokens,
                'output_tokens' => $outputTokens,
                'cost' => $response->cost,
                'is_compacted' => false,
                'is_memory' => true,
                'metadata' => [
                    'generated_by' => 'ai_compaction_v1',
                    'raw_message_count' => $rawMessages->count(),
                    'pending_memory_count' => $pendingMemoryMessages->count(),
                    'usage' => $response->usage,
                    'compaction_provider_id' => $compactionProvider?->id,
                    'compaction_provider' => $compactionProvider?->slug,
                    'compaction_provider_model_id' => $compactionModel?->id,
                    'compaction_model' => $compactionModel?->model_key,
                    'finish_reason' => $response->finishReason,
                ],
            ]);

            $thread->forceFill(['compacted_at' => now()])->save();
        });

        return new CompactionResult(
            true,
            $inputTokens,
            $outputTokens,
            $compactionProvider?->id,
            $compactionModel?->id,
            $compactionProvider?->slug,
            $compactionModel?->model_key,
        );
    }

    /**
     * @return array{0: Provider, 1: ProviderModel}
     */
    private function resolveCompactionRoute(FamilyAgent $family, array $overrides = []): array
    {
        $provider = null;
        $model = null;

        if (! empty($overrides['provider'])) {
            $provider = Provider::query()->where('slug', $overrides['provider'])->where('is_enabled', true)->first();
        }

        $provider ??= $family->compactionProvider;
        $provider ??= $family->defaultProvider;
        $provider ??= Provider::query()->where('is_default', true)->where('is_enabled', true)->first();
        $provider ??= Provider::query()->where('is_enabled', true)->first();

        if (! $provider) {
            throw new RuntimeException('No enabled provider is available for compaction.');
        }

        if (! empty($overrides['model'])) {
            $model = ProviderModel::query()
                ->where('provider_id', $provider->id)
                ->where('model_key', $overrides['model'])
                ->where('is_enabled', true)
                ->first();
        }

        if ($family->compaction_provider_model_id && $family->compactionProviderModel?->provider_id === $provider->id) {
            $model ??= $family->compactionProviderModel;
        }

        if ($family->default_provider_model_id && $family->defaultProviderModel?->provider_id === $provider->id) {
            $model ??= $family->defaultProviderModel;
        }

        $model ??= $provider->models()->where('is_default', true)->where('is_enabled', true)->first();
        $model ??= $provider->models()->where('is_enabled', true)->first();

        if (! $model) {
            throw new RuntimeException("Provider [{$provider->slug}] has no enabled compaction model.");
        }

        return [$provider, $model];
    }

    public function forget(Thread $thread, string $needle): int
    {
        if ($needle === '') {
            return 0;
        }

        return $thread->messages()
            ->where('content', 'like', '%'.$needle.'%')
            ->update(['is_forgotten' => true]);
    }
}
