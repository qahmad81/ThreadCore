<?php

namespace App\Services\Ai;

use App\Models\Provider;
use App\Models\ProviderModel;
use Illuminate\Support\Facades\Http;

class OllamaProviderClient implements ProviderClient
{
    public function chat(Provider $provider, ProviderModel $model, array $messages, array $options = []): AiResponse
    {
        $response = Http::acceptJson()
            ->timeout(config('threadcore.gateway.timeout_seconds', 1200))
            ->post(rtrim((string) $provider->base_url, '/').'/api/chat', [
                'model' => $model->model_key,
                'messages' => $messages,
                'stream' => false,
            ])
            ->throw()
            ->json();
        $usage = $this->normalizeUsage($response);

        return new AiResponse(
            content: (string) data_get($response, 'message.content', ''),
            inputTokens: $usage['prompt_tokens'],
            outputTokens: $usage['completion_tokens'],
            usage: $usage,
            cost: $model->costForUsage($usage),
            finishReason: isset($response['done']) && $response['done'] ? 'stop' : null,
            metadata: $response,
        );
    }

    /**
     * @param array<string, mixed> $response
     * @return array{prompt_tokens: int, completion_tokens: int, prompt_cache_hit_tokens: int, prompt_cache_miss_tokens: int, reasoning_tokens: int}
     */
    private function normalizeUsage(array $response): array
    {
        $promptTokens = (int) ($response['prompt_eval_count'] ?? 0);
        $completionTokens = (int) ($response['eval_count'] ?? 0);
        $hasReasoningBreakdown = array_key_exists('reasoning_tokens', $response);

        return [
            'prompt_tokens' => $promptTokens,
            'completion_tokens' => $completionTokens,
            'prompt_cache_hit_tokens' => 0,
            'prompt_cache_miss_tokens' => $promptTokens,
            'reasoning_tokens' => $hasReasoningBreakdown ? (int) data_get($response, 'reasoning_tokens', 0) : 0,
            'has_prompt_cache_breakdown' => false,
            'has_reasoning_breakdown' => $hasReasoningBreakdown,
        ];
    }
}
