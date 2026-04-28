<?php

namespace Tests\Unit;

use App\Models\Provider;
use App\Services\Ai\AnthropicProviderClient;
use App\Services\Ai\GoogleProviderClient;
use App\Services\Ai\LmstudioProviderClient;
use App\Services\Ai\OllamaProviderClient;
use App\Services\Ai\OpenAiProviderClient;
use App\Services\Ai\ProviderClientManager;
use App\Services\Ai\VllmProviderClient;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class ProviderClientManagerTest extends TestCase
{
    #[DataProvider('driverMap')]
    public function test_it_resolves_the_expected_client_for_each_driver(string $driver, string $expectedClass): void
    {
        $provider = new Provider(['driver' => $driver]);

        $client = $this->app->make(ProviderClientManager::class)->forProvider($provider);

        $this->assertInstanceOf($expectedClass, $client);
    }

    public static function driverMap(): array
    {
        return [
            ['openai', OpenAiProviderClient::class],
            ['openrouter', OpenAiProviderClient::class],
            ['google', GoogleProviderClient::class],
            ['anthropic', AnthropicProviderClient::class],
            ['lmstudio', LmstudioProviderClient::class],
            ['vllm', VllmProviderClient::class],
            ['ollama', OllamaProviderClient::class],
        ];
    }
}
