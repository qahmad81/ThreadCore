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

        return new AiResponse(
            content: (string) data_get($choice, 'message.content', ''),
            inputTokens: (int) ($usage['prompt_tokens'] ?? 0),
            outputTokens: (int) ($usage['completion_tokens'] ?? 0),
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
}
