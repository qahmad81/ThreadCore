<?php

namespace App\Services\Ai;

use App\Models\Provider;
use InvalidArgumentException;

class ProviderClientManager
{
    public function __construct(
        private readonly OpenAiProviderClient $openAi,
        private readonly GoogleProviderClient $google,
        private readonly AnthropicProviderClient $anthropic,
        private readonly LmstudioProviderClient $lmstudio,
        private readonly VllmProviderClient $vllm,
        private readonly OllamaProviderClient $ollama,
    ) {
    }

    public function forProvider(Provider $provider): ProviderClient
    {
        return match ($provider->driver) {
            'openai', 'openrouter' => $this->openAi,
            'google' => $this->google,
            'anthropic' => $this->anthropic,
            'lmstudio' => $this->lmstudio,
            'vllm' => $this->vllm,
            'ollama' => $this->ollama,
            default => throw new InvalidArgumentException("Unsupported provider driver [{$provider->driver}]."),
        };
    }
}
