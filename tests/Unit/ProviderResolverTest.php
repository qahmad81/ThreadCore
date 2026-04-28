<?php

namespace Tests\Unit;

use App\Models\FamilyAgent;
use App\Models\Provider;
use App\Models\ProviderModel;
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

    public function test_default_model_can_supply_provider_when_family_provider_is_missing(): void
    {
        $this->seed();

        $model = ProviderModel::query()->whereHas('provider', fn ($query) => $query->where('slug', 'openai'))->firstOrFail();
        $family = FamilyAgent::query()->create([
            'number' => 'model-only',
            'name' => 'Model Only',
            'system_prompt' => null,
            'default_provider_id' => null,
            'default_provider_model_id' => $model->id,
            'compaction_provider_id' => null,
            'compaction_provider_model_id' => null,
            'max_context_tokens' => 4096,
            'compaction_threshold_tokens' => 3000,
            'compaction_prompt' => 'Compacted memory',
            'is_enabled' => true,
        ]);

        $resolver = app(\App\Services\Gateway\ProviderResolver::class);
        $route = $resolver->resolve($family);

        $this->assertSame($model->provider_id, $route->provider->id);
        $this->assertSame($model->id, $route->model->id);
    }
}
