<?php

namespace App\Services\Gateway;

use App\Models\FamilyAgent;
use App\Models\GatewayRequestLog;
use App\Models\Provider;
use App\Models\ProviderModel;
use App\Models\ThreadMessage;
use App\Models\Thread;
use RuntimeException;

class ProviderResolver
{
    public function resolve(FamilyAgent $familyAgent, ?Thread $thread = null, array $overrides = []): ResolvedRoute
    {
        $provider = null;
        $model = null;
        $lastMessageRoute = $thread ? $this->lastMessageRoute($thread) : null;
        $lastLogRoute = $thread && ! $lastMessageRoute ? $this->lastGatewayLogRoute($thread) : null;

        if (! empty($overrides['provider'])) {
            $provider = Provider::query()->where('slug', $overrides['provider'])->where('is_enabled', true)->first();
        }

        if ($lastMessageRoute?->provider) {
            $provider ??= $lastMessageRoute->provider;
        }

        if ($lastLogRoute?->provider) {
            $provider ??= $lastLogRoute->provider;
        }

        if ($thread?->provider_id) {
            $provider ??= $thread->provider;
        }

        $provider ??= $familyAgent->defaultProviderModel?->provider;
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

        if ($lastMessageRoute?->model && $lastMessageRoute->model->provider_id === $provider->id) {
            $model ??= $lastMessageRoute->model;
        }

        if ($lastLogRoute?->model && $lastLogRoute->model->provider_id === $provider->id) {
            $model ??= $lastLogRoute->model;
        }

        if ($thread?->provider_model_id && $thread->providerModel?->provider_id === $provider->id) {
            $model ??= $thread->providerModel;
        }

        if ($familyAgent->default_provider_model_id && $familyAgent->defaultProviderModel?->provider_id === $provider->id) {
            $model ??= $familyAgent->defaultProviderModel;
        }

        if ($familyAgent->default_provider_model_id && ! $familyAgent->defaultProviderModel?->provider_id && $familyAgent->defaultProviderModel?->provider) {
            $model ??= $familyAgent->defaultProviderModel;
        }

        $model ??= $provider->models()->where('is_default', true)->where('is_enabled', true)->first();
        $model ??= $provider->models()->where('is_enabled', true)->first();

        if (! $model) {
            throw new RuntimeException("Provider [{$provider->slug}] has no enabled model.");
        }

        return new ResolvedRoute($provider, $model);
    }

    private function lastMessageRoute(Thread $thread): ?ResolvedRoute
    {
        $message = $thread->messages()
            ->where('is_forgotten', false)
            ->latest('id')
            ->get()
            ->first(function (ThreadMessage $message): bool {
                return filled($message->metadata['provider_id'] ?? null)
                    && filled($message->metadata['provider_model_id'] ?? null);
            });

        if (! $message) {
            return null;
        }

        $provider = Provider::query()
            ->whereKey($message->metadata['provider_id'])
            ->where('is_enabled', true)
            ->first();

        if (! $provider) {
            return null;
        }

        $model = ProviderModel::query()
            ->whereKey($message->metadata['provider_model_id'])
            ->where('provider_id', $provider->id)
            ->where('is_enabled', true)
            ->first();

        if (! $model) {
            return null;
        }

        return new ResolvedRoute($provider, $model);
    }

    private function lastGatewayLogRoute(Thread $thread): ?ResolvedRoute
    {
        $log = GatewayRequestLog::query()
            ->where('thread_id', $thread->id)
            ->where('status', 'ok')
            ->latest('id')
            ->take(20)
            ->get()
            ->first(function (GatewayRequestLog $log): bool {
                return filled($log->provider_id)
                    && filled($log->provider_model_id)
                    && (
                        filled($log->response_metadata['normalized_usage'] ?? null)
                        || filled($log->response_metadata['finish_reason'] ?? null)
                    );
            });

        if (! $log) {
            return null;
        }

        $provider = Provider::query()
            ->whereKey($log->provider_id)
            ->where('is_enabled', true)
            ->first();

        if (! $provider) {
            return null;
        }

        $model = ProviderModel::query()
            ->whereKey($log->provider_model_id)
            ->where('provider_id', $provider->id)
            ->where('is_enabled', true)
            ->first();

        if (! $model) {
            return null;
        }

        return new ResolvedRoute($provider, $model);
    }
}
