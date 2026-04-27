<?php

namespace App\Services\Gateway;

use App\Models\FamilyAgent;
use App\Models\Provider;
use App\Models\ProviderModel;
use App\Models\Thread;
use RuntimeException;

class ProviderResolver
{
    public function resolve(FamilyAgent $familyAgent, ?Thread $thread = null, array $overrides = []): ResolvedRoute
    {
        $provider = null;
        $model = null;

        if (! empty($overrides['provider'])) {
            $provider = Provider::query()->where('slug', $overrides['provider'])->where('is_enabled', true)->first();
        }

        if ($thread?->provider_id) {
            $provider ??= $thread->provider;
        }

        $provider ??= $familyAgent->defaultProvider;
        $provider ??= Provider::query()->where('is_default', true)->where('is_enabled', true)->first();
        $provider ??= Provider::query()->where('is_enabled', true)->first();

        if (! $provider) {
            throw new RuntimeException('No enabled provider is available.');
        }

        if (! empty($overrides['model'])) {
            $model = ProviderModel::query()
                ->where('provider_id', $provider->id)
                ->where('model_key', $overrides['model'])
                ->where('is_enabled', true)
                ->first();
        }

        if ($thread?->provider_model_id && $thread->provider_model?->provider_id === $provider->id) {
            $model ??= $thread->providerModel;
        }

        if ($familyAgent->default_provider_model_id && $familyAgent->defaultProviderModel?->provider_id === $provider->id) {
            $model ??= $familyAgent->defaultProviderModel;
        }

        $model ??= $provider->models()->where('is_default', true)->where('is_enabled', true)->first();
        $model ??= $provider->models()->where('is_enabled', true)->first();

        if (! $model) {
            throw new RuntimeException("Provider [{$provider->slug}] has no enabled model.");
        }

        return new ResolvedRoute($provider, $model);
    }
}
