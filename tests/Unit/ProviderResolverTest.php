<?php

namespace Tests\Unit;

use App\Models\FamilyAgent;
use App\Models\Provider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProviderResolverTest extends TestCase
{
    use RefreshDatabase;

    public function test_request_override_takes_precedence(): void
    {
        $this->seed();
        $family = FamilyAgent::query()->where('number', 'default')->firstOrFail();
        $resolver = app(\App\Services\Gateway\ProviderResolver::class);

        $route = $resolver->resolve($family, null, ['provider' => 'ollama']);

        $this->assertSame('ollama', $route->provider->slug);
        $this->assertSame(Provider::query()->where('slug', 'ollama')->first()->id, $route->provider->id);
    }
}
