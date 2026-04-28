<?php

namespace Tests\Feature;

use App\Models\FamilyAgent;
use App\Models\Provider;
use App\Models\ProviderModel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminFamilyAgentsTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_family_agent_with_optional_description(): void
    {
        $this->seed();

        $admin = User::query()->where('email', config('threadcore.admin.email'))->firstOrFail();
        $model = \App\Models\ProviderModel::query()
            ->where('is_enabled', true)
            ->whereHas('provider', fn ($query) => $query->where('is_enabled', true))
            ->firstOrFail();

        $this->actingAs($admin)->post(route('admin.family-agents.store'), [
            'number' => 'assistant-1',
            'name' => 'Assistant One',
            'description' => 'Optional helper description',
            'system_prompt' => 'Be helpful.',
            'default_provider_model_id' => $model->id,
            'compaction_provider_model_id' => $model->id,
            'compaction_prompt' => '',
            'max_context_tokens' => 4096,
            'compaction_threshold_tokens' => 3000,
            'is_enabled' => 1,
        ])->assertRedirect(route('admin.family-agents.index'));

        $family = FamilyAgent::query()->where('number', 'assistant-1')->firstOrFail();

        $this->assertSame('Optional helper description', $family->description);
        $this->assertSame($model->provider_id, $family->default_provider_id);
        $this->assertSame($model->provider_id, $family->compaction_provider_id);
        $this->assertSame($model->id, $family->compaction_provider_model_id);
        $this->assertSame('Compacted memory', $family->compaction_prompt);

        $this->actingAs($admin)
            ->get(route('admin.family-agents.edit', $family))
            ->assertOk()
            ->assertSee('Optional helper description')
            ->assertDontSee('Default provider')
            ->assertDontSee('Compaction provider')
            ->assertSee('Default model')
            ->assertSee('Compaction model')
            ->assertSee('Compacted memory');
    }

    public function test_family_agent_form_only_lists_enabled_models_from_enabled_providers(): void
    {
        $this->seed();

        $admin = User::query()->where('email', config('threadcore.admin.email'))->firstOrFail();
        $enabledProvider = Provider::query()->where('slug', 'openai')->firstOrFail();
        $disabledProvider = Provider::query()->create([
            'name' => 'Disabled Provider',
            'slug' => 'disabled-provider',
            'driver' => 'openai',
            'base_url' => 'https://example.com/v1',
            'api_key_env' => null,
            'is_enabled' => false,
            'is_default' => false,
        ]);

        $enabledModel = ProviderModel::query()->create([
            'provider_id' => $enabledProvider->id,
            'name' => 'Visible Enabled Model',
            'model_key' => 'visible-enabled-model',
            'role' => 'worker',
            'context_window' => 4096,
            'is_enabled' => true,
            'is_default' => false,
        ]);

        $disabledModel = ProviderModel::query()->create([
            'provider_id' => $enabledProvider->id,
            'name' => 'Hidden Disabled Model',
            'model_key' => 'hidden-disabled-model',
            'role' => 'worker',
            'context_window' => 4096,
            'is_enabled' => false,
            'is_default' => false,
        ]);

        $disabledProviderModel = ProviderModel::query()->create([
            'provider_id' => $disabledProvider->id,
            'name' => 'Hidden Provider Model',
            'model_key' => 'hidden-provider-model',
            'role' => 'worker',
            'context_window' => 4096,
            'is_enabled' => true,
            'is_default' => false,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.family-agents.create'));

        $response->assertOk()
            ->assertSee('openai/gpt-5.4')
            ->assertDontSee('Hidden Disabled Model')
            ->assertDontSee('Hidden Provider Model');

        $response->assertSee((string) $enabledModel->id, false);
    }

    public function test_admin_can_delete_family_agent(): void
    {
        $this->seed();

        $admin = User::query()->where('email', config('threadcore.admin.email'))->firstOrFail();
        $family = FamilyAgent::query()->create([
            'number' => 'delete-me',
            'name' => 'Delete Me',
            'max_context_tokens' => 2048,
            'compaction_threshold_tokens' => 1500,
            'compaction_prompt' => 'Compacted memory',
            'is_enabled' => true,
        ]);

        $this->actingAs($admin)
            ->from(route('admin.family-agents.index'))
            ->delete(route('admin.family-agents.destroy', $family))
            ->assertRedirect(route('admin.family-agents.index'));

        $this->assertDatabaseMissing('family_agents', ['id' => $family->id]);
    }
}
