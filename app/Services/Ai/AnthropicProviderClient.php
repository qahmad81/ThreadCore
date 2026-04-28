<?php

namespace App\Services\Ai;

use App\Models\Provider;
use App\Models\ProviderModel;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class AnthropicProviderClient implements ProviderClient
{
    public function chat(Provider $provider, ProviderModel $model, array $messages, array $options = []): AiResponse
    {
        [$apiKey, $apiKeySource] = $this->resolveApiKey($provider);
        $system = data_get(collect($messages)->firstWhere('role', 'system'), 'content');
        $payloadMessages = collect($messages)
            ->reject(fn (array $message) => $message['role'] === 'system')
            ->map(function (array $message): array {
                return [
                    'role' => $message['role'] === 'assistant' ? 'assistant' : 'user',
                    'content' => $message['content'],
                ];
            })
            ->values()
            ->all();

        try {
            $baseUrl = preg_replace('#/v1/?$#', '', rtrim((string) $provider->base_url, '/'));
            $response = Http::withHeaders([
                'x-api-key' => $apiKey,
                'anthropic-version' => '2023-06-01',
                'content-type' => 'application/json',
            ])
                ->acceptJson()
                ->timeout(config('threadcore.gateway.timeout_seconds', 1200))
                ->post($baseUrl.'/v1/messages', [
                    'model' => $model->model_key,
                    'messages' => $payloadMessages,
                    'max_tokens' => $options['max_tokens'] ?? 4096,
                    'system' => $system,
                ])
                ->throw()
                ->json();
        } catch (RequestException $exception) {
            $status = $exception->response?->status();
            $payload = $exception->response?->json() ?? [];
            $message = data_get($payload, 'error.message') ?? data_get($payload, 'message');

            if ($status === 401 || $status === 403) {
                throw new RuntimeException("Provider [{$provider->slug}] rejected the configured API key from {$apiKeySource}.".($message ? " {$message}" : ''));
            }

            throw $exception;
        }

        $usage = $response['usage'] ?? [];
        $normalizedUsage = [
            'prompt_tokens' => (int) ($usage['input_tokens'] ?? 0),
            'completion_tokens' => (int) ($usage['output_tokens'] ?? 0),
            'prompt_cache_hit_tokens' => (int) ($usage['cache_read_input_tokens'] ?? 0),
            'prompt_cache_miss_tokens' => max(0, (int) ($usage['input_tokens'] ?? 0) - (int) ($usage['cache_read_input_tokens'] ?? 0)),
            'reasoning_tokens' => (int) data_get($usage, 'output_tokens_details.reasoning_tokens', 0),
            'has_prompt_cache_breakdown' => array_key_exists('cache_read_input_tokens', $usage) || array_key_exists('cache_creation_input_tokens', $usage),
            'has_reasoning_breakdown' => data_get($usage, 'output_tokens_details.reasoning_tokens') !== null,
        ];

        return new AiResponse(
            content: $this->responseContent($response),
            inputTokens: $normalizedUsage['prompt_tokens'],
            outputTokens: $normalizedUsage['completion_tokens'],
            usage: $normalizedUsage,
            cost: $model->costForUsage($normalizedUsage),
            finishReason: data_get($response, 'stop_reason'),
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

    private function responseContent(array $response): string
    {
        $content = data_get($response, 'content');

        if (is_string($content) && trim($content) !== '') {
            return trim($content);
        }

        $parts = data_get($response, 'content', []);

        if (is_array($parts)) {
            return collect($parts)
                ->map(fn ($part) => data_get($part, 'text'))
                ->filter(fn ($text) => is_string($text) && trim($text) !== '')
                ->implode("\n");
        }

        return '';
    }
}
