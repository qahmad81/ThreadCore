<?php

namespace App\Services\Ai;

use App\Models\Provider;
use App\Models\ProviderModel;
use Illuminate\Support\Facades\Http;

class OpenRouterProviderClient implements ProviderClient
{
    public function chat(Provider $provider, ProviderModel $model, array $messages, array $options = []): AiResponse
    {
        $response = Http::withToken((string) env($provider->api_key_env))
            ->acceptJson()
            ->post(rtrim((string) $provider->base_url, '/').'/chat/completions', [
                'model' => $model->model_key,
                'messages' => $messages,
                'provider' => $options['inner_provider'] ?? null,
            ])
            ->throw()
            ->json();

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
}
