<?php

namespace App\Services\Ai;

use App\Models\Provider;
use InvalidArgumentException;

class ProviderClientManager
{
    public function __construct(
        private readonly OpenRouterProviderClient $openRouter,
        private readonly OllamaProviderClient $ollama,
    ) {
    }

    public function forProvider(Provider $provider): ProviderClient
    {
        return match ($provider->driver) {
            'openrouter' => $this->openRouter,
            'ollama' => $this->ollama,
            default => throw new InvalidArgumentException("Unsupported provider driver [{$provider->driver}]."),
        };
    }
}
