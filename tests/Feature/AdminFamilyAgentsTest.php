<?php

namespace Tests\Feature;

use App\Models\FamilyAgent;
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
        $provider = \App\Models\Provider::query()->firstOrFail();
        $model = \App\Models\ProviderModel::query()->firstOrFail();

        $this->actingAs($admin)->post(route('admin.family-agents.store'), [
            'number' => 'assistant-1',
            'name' => 'Assistant One',
            'description' => 'Optional helper description',
            'system_prompt' => 'Be helpful.',
            'default_provider_id' => $provider->id,
            'default_provider_model_id' => $model->id,
            'compaction_provider_id' => $provider->id,
            'compaction_provider_model_id' => $model->id,
            'compaction_prompt' => '',
            'max_context_tokens' => 4096,
            'compaction_threshold_tokens' => 3000,
            'is_enabled' => 1,
        ])->assertRedirect(route('admin.family-agents.index'));

        $family = FamilyAgent::query()->where('number', 'assistant-1')->firstOrFail();

        $this->assertSame('Optional helper description', $family->description);
        $this->assertSame($provider->id, $family->compaction_provider_id);
        $this->assertSame($model->id, $family->compaction_provider_model_id);
        $this->assertSame('Compacted memory', $family->compaction_prompt);

        $this->actingAs($admin)
            ->get(route('admin.family-agents.edit', $family))
            ->assertOk()
            ->assertSee('Optional helper description')
            ->assertSee('Compaction provider')
            ->assertSee('Compacted memory');
    }
}
