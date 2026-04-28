<?php

namespace App\Services\Ai;

use App\Models\Provider;
use App\Models\ProviderModel;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class OpenRouterProviderClient implements ProviderClient
{
    public function chat(Provider $provider, ProviderModel $model, array $messages, array $options = []): AiResponse
    {
        [$apiKey, $apiKeySource] = $this->resolveApiKey($provider);

        try {
            $response = Http::withToken($apiKey)
                ->acceptJson()
                ->timeout(config('threadcore.gateway.timeout_seconds', 1200))
                ->post(rtrim((string) $provider->base_url, '/').'/chat/completions', [
                    'model' => $model->model_key,
                    'messages' => $messages,
                    'provider' => $options['inner_provider'] ?? null,
                ])
                ->throw()
                ->json();
        } catch (RequestException $exception) {
            $status = $exception->response?->status();
            $payload = $exception->response?->json() ?? [];
            $remoteMessage = data_get($payload, 'error.message')
                ?? data_get($payload, 'message')
                ?? data_get($payload, 'reason');
            $remoteCode = data_get($payload, 'error.code')
                ?? data_get($payload, 'code');

            if ($status === 401) {
                throw new RuntimeException("Provider [{$provider->slug}] rejected the request because the Authorization header is missing or empty. Check {$apiKeySource}.");
            }

            if ($status === 403) {
                $detail = $remoteCode ? "{$remoteCode}" : 'forbidden';
                $suffix = $remoteMessage ? " {$remoteMessage}" : '';

                throw new RuntimeException("Provider [{$provider->slug}] rejected the configured API key from {$apiKeySource} ({$detail}).{$suffix}");
            }

            throw $exception;
        }

        $choice = $response['choices'][0] ?? [];
        $usage = $response['usage'] ?? [];
        $normalizedUsage = $this->normalizeUsage($usage);

        return new AiResponse(
            content: (string) data_get($choice, 'message.content', ''),
            inputTokens: $normalizedUsage['prompt_tokens'],
            outputTokens: $normalizedUsage['completion_tokens'],
            usage: $normalizedUsage,
            cost: $model->costForUsage($normalizedUsage),
            finishReason: $choice['finish_reason'] ?? null,
            metadata: $response,
        );
    }

    /**
     * @return array{0: string, 1: string}
     */
    private function resolveApiKey(Provider $provider): array
    {
        $configuredValue = is_string($provider->api_key_env) ? trim($provider->api_key_env) : '';

        if ($configuredValue === '') {
            throw new RuntimeException("Provider [{$provider->slug}] is missing its API key configuration.");
        }

        $envValue = env($configuredValue);

        if (is_string($envValue) && trim($envValue) !== '') {
            return [trim($envValue), "env [{$configuredValue}]"];
        }

        return [$configuredValue, 'the provider record'];
    }

    /**
     * @param array<string, mixed> $usage
     * @return array{prompt_tokens: int, completion_tokens: int, prompt_cache_hit_tokens: int, prompt_cache_miss_tokens: int, reasoning_tokens: int}
     */
    private function normalizeUsage(array $usage): array
    {
        $promptTokens = (int) ($usage['prompt_tokens'] ?? 0);
        $completionTokens = (int) ($usage['completion_tokens'] ?? 0);
        $hasPromptCacheBreakdown = array_key_exists('prompt_cache_hit_tokens', $usage)
            || array_key_exists('prompt_cache_miss_tokens', $usage)
            || data_get($usage, 'prompt_tokens_details.cached_tokens') !== null;
        $hasReasoningBreakdown = array_key_exists('reasoning_tokens', $usage)
            || data_get($usage, 'completion_tokens_details.reasoning_tokens') !== null;
        $promptCacheHitTokens = $hasPromptCacheBreakdown
            ? (int) data_get($usage, 'prompt_cache_hit_tokens', data_get($usage, 'prompt_tokens_details.cached_tokens', 0))
            : 0;
        $promptCacheMissTokens = $hasPromptCacheBreakdown
            ? data_get($usage, 'prompt_cache_miss_tokens')
            : null;

        if ($promptCacheMissTokens === null) {
            $promptCacheMissTokens = max(0, $promptTokens - $promptCacheHitTokens);
        }

        return [
            'prompt_tokens' => $promptTokens,
            'completion_tokens' => $completionTokens,
            'prompt_cache_hit_tokens' => $promptCacheHitTokens,
            'prompt_cache_miss_tokens' => (int) $promptCacheMissTokens,
            'reasoning_tokens' => $hasReasoningBreakdown
                ? (int) data_get($usage, 'reasoning_tokens', data_get($usage, 'completion_tokens_details.reasoning_tokens', 0))
                : 0,
            'has_prompt_cache_breakdown' => $hasPromptCacheBreakdown,
            'has_reasoning_breakdown' => $hasReasoningBreakdown,
        ];
    }
}
