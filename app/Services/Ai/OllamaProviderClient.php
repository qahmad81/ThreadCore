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

        return new AiResponse(
            content: (string) data_get($response, 'message.content', ''),
            inputTokens: (int) ($response['prompt_eval_count'] ?? 0),
            outputTokens: (int) ($response['eval_count'] ?? 0),
            finishReason: isset($response['done']) && $response['done'] ? 'stop' : null,
            metadata: $response,
        );
    }
}
