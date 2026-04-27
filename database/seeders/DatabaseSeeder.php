<?php

namespace Database\Seeders;

use App\Models\FamilyAgent;
use App\Models\Provider;
use App\Models\SitePage;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $adminPassword = config('threadcore.admin.password');

        if (! is_string($adminPassword) || $adminPassword === '') {
            throw new RuntimeException('THREADCORE_ADMIN_PASSWORD must be set before running the ThreadCore seeder.');
        }

        $adminUser = User::query()->firstOrNew([
            'email' => config('threadcore.admin.email'),
        ]);

        $adminUser->name = 'ThreadCore Admin';
        $adminUser->is_admin = true;

        if (! $adminUser->exists) {
            $adminUser->password = Hash::make($adminPassword);
        }

        $adminUser->save();

        $openRouter = Provider::query()->updateOrCreate(
            ['slug' => 'openrouter'],
            [
                'name' => 'OpenRouter',
                'driver' => 'openrouter',
                'base_url' => config('threadcore.openrouter.base_url'),
                'api_key_env' => 'OPENROUTER_API_KEY',
                'is_enabled' => true,
                'is_default' => true,
            ],
        );

        $openRouterMain = $openRouter->models()->updateOrCreate(
            ['model_key' => config('threadcore.openrouter.main_model')],
            [
                'name' => 'OpenRouter Main',
                'role' => 'main',
                'is_enabled' => true,
                'is_default' => true,
            ],
        );

        $openRouter->models()->updateOrCreate(
            ['model_key' => config('threadcore.openrouter.worker_model')],
            [
                'name' => 'OpenRouter Worker',
                'role' => 'worker',
                'is_enabled' => true,
                'is_default' => false,
            ],
        );

        $ollama = Provider::query()->updateOrCreate(
            ['slug' => 'ollama'],
            [
                'name' => 'Ollama',
                'driver' => 'ollama',
                'base_url' => config('threadcore.ollama.base_url'),
                'api_key_env' => null,
                'is_enabled' => true,
                'is_default' => false,
            ],
        );

        foreach ([
            'fast' => config('threadcore.ollama.fast_model'),
            'balanced' => config('threadcore.ollama.balanced_model'),
            'reasoning' => config('threadcore.ollama.reasoning_model'),
        ] as $role => $modelKey) {
            $ollama->models()->updateOrCreate(
                ['model_key' => $modelKey],
                [
                    'name' => 'Ollama '.ucfirst($role),
                    'role' => $role,
                    'is_enabled' => true,
                    'is_default' => $role === 'balanced',
                ],
            );
        }

        FamilyAgent::query()->updateOrCreate(
            ['number' => 'default'],
            [
                'name' => 'Default Family Agent',
                'system_prompt' => 'You are a helpful ThreadCore family agent.',
                'default_provider_id' => $openRouter->id,
                'default_provider_model_id' => $openRouterMain->id,
                'max_context_tokens' => 8192,
                'compaction_threshold_tokens' => 7000,
                'is_enabled' => true,
            ],
        );

        SitePage::query()->updateOrCreate(
            ['slug' => 'landing'],
            [
                'title' => 'ThreadCore - AI thread orchestration',
                'headline' => 'Manage AI threads, providers, and agent memory from one gateway.',
                'summary' => 'ThreadCore is a Laravel microsaas for routing model requests, configuring family agents, tracking tokens, and keeping long-running conversations under control.',
                'blocks' => [
                    [
                        'label' => 'Gateway',
                        'title' => 'One API surface',
                        'body' => 'Create threads, post messages, and receive normalized provider responses with token metadata.',
                    ],
                    [
                        'label' => 'Providers',
                        'title' => 'Database-driven routing',
                        'body' => 'Start with OpenRouter and Ollama, then add future providers without hardcoding the orchestration flow.',
                    ],
                    [
                        'label' => 'Memory',
                        'title' => 'Built for compaction',
                        'body' => 'Track context limits and prepare compressed memories instead of resending stale raw history.',
                    ],
                ],
                'is_published' => true,
            ],
        );
    }
}
