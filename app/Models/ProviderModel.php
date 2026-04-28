<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProviderModel extends Model
{
    use HasFactory;

    protected $fillable = [
        'provider_id',
        'name',
        'model_key',
        'role',
        'context_window',
        'is_enabled',
        'is_default',
        'metadata',
        'pricing',
    ];

    protected function casts(): array
    {
        return [
            'context_window' => 'integer',
            'is_enabled' => 'boolean',
            'is_default' => 'boolean',
            'metadata' => 'array',
            'pricing' => 'array',
        ];
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class);
    }

    public function familyAgents(): HasMany
    {
        return $this->hasMany(FamilyAgent::class, 'default_provider_model_id');
    }

    public function pricingRate(string $key): float
    {
        return (float) data_get($this->pricing ?? [], $key, 0);
    }

    /**
     * @param array<string, int|bool> $usage
     */
    public function costForUsage(array $usage): string
    {
        $total = 0.0;

        $promptTokens = (int) ($usage['prompt_tokens'] ?? 0);
        $promptCacheHitTokens = (int) ($usage['prompt_cache_hit_tokens'] ?? 0);
        $promptCacheMissTokens = (int) ($usage['prompt_cache_miss_tokens'] ?? 0);
        $completionTokens = (int) ($usage['completion_tokens'] ?? 0);
        $reasoningTokens = max(0, (int) ($usage['reasoning_tokens'] ?? 0));

        $hasPromptCacheBreakdown = (bool) ($usage['has_prompt_cache_breakdown'] ?? false);
        $hasReasoningBreakdown = (bool) ($usage['has_reasoning_breakdown'] ?? false);

        if ($hasPromptCacheBreakdown) {
            $promptRate = $this->pricingRate('prompt_tokens');
            $promptCacheHitRate = $this->pricingRate('prompt_cache_hit_tokens') ?: $promptRate;
            $promptCacheMissRate = $this->pricingRate('prompt_cache_miss_tokens') ?: $promptRate;

            $total += $promptCacheHitTokens * $promptCacheHitRate;
            $total += $promptCacheMissTokens * $promptCacheMissRate;
        } else {
            $total += $promptTokens * $this->pricingRate('prompt_tokens');
        }

        $completionRate = $this->pricingRate('completion_tokens');
        $reasoningRate = $this->pricingRate('reasoning_tokens');

        if ($hasReasoningBreakdown) {
            $visibleCompletionTokens = max(0, $completionTokens - $reasoningTokens);
            $visibleCompletionRate = $completionRate ?: $reasoningRate;
            if ($reasoningRate <= 0) {
                $reasoningRate = $completionRate;
            }

            $total += $visibleCompletionTokens * $visibleCompletionRate;
            $total += $reasoningTokens * $reasoningRate;
        } else {
            $total += $completionTokens * ($completionRate ?: $reasoningRate);
        }

        return number_format($total, 6, '.', '');
    }
}
