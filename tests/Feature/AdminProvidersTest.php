<?php

namespace Tests\Feature;

use App\Models\Provider;
use App\Models\ProviderModel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminProvidersTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('admin.providers.index'))
            ->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_view_seeded_providers(): void
    {
        $this->seed();

        $user = User::query()->where('email', config('threadcore.admin.email'))->firstOrFail();

        $this->actingAs($user)
            ->get(route('admin.providers.index'))
            ->assertOk()
            ->assertSee('OpenRouter')
            ->assertSee('Ollama')
            ->assertSee(config('threadcore.openrouter.main_model'))
            ->assertSee(config('threadcore.ollama.balanced_model'));
    }

    public function test_admin_path_comes_from_configuration(): void
    {
        $this->assertSame('/'.config('threadcore.admin.path').'/providers', parse_url(route('admin.providers.index'), PHP_URL_PATH));
    }

    public function test_authenticated_non_admin_user_is_forbidden(): void
    {
        $this->seed();

        $user = User::factory()->create([
            'email' => 'customer@example.com',
            'is_admin' => false,
        ]);

        $this->actingAs($user)
            ->get(route('admin.providers.index'))
            ->assertForbidden();
    }

    public function test_admin_can_delete_provider_and_its_models(): void
    {
        $this->seed();

        $admin = User::query()->where('email', config('threadcore.admin.email'))->firstOrFail();
        $provider = Provider::query()->create([
            'name' => 'Disposable Provider',
            'slug' => 'disposable-provider',
            'driver' => 'openai',
            'base_url' => 'https://example.com/v1',
            'api_key_env' => null,
            'is_enabled' => true,
            'is_default' => false,
        ]);

        $model = $provider->models()->create([
            'name' => 'Disposable Model',
            'model_key' => 'disposable-model',
            'role' => 'worker',
            'context_window' => 4096,
            'is_enabled' => true,
            'is_default' => false,
        ]);

        $this->actingAs($admin)
            ->from(route('admin.resources.index'))
            ->delete(route('admin.providers.destroy', $provider))
            ->assertRedirect(route('admin.resources.index'));

        $this->assertDatabaseMissing('providers', ['id' => $provider->id]);
        $this->assertDatabaseMissing('provider_models', ['id' => $model->id]);
    }

    public function test_admin_cannot_create_duplicate_provider_model_key_for_same_provider(): void
    {
        $this->seed();

        $admin = User::query()->where('email', config('threadcore.admin.email'))->firstOrFail();
        $provider = Provider::query()->firstOrFail();

        ProviderModel::query()->create([
            'provider_id' => $provider->id,
            'name' => 'Existing Model',
            'model_key' => 'duplicate-model',
            'role' => 'worker',
            'context_window' => 2048,
            'is_enabled' => true,
            'is_default' => false,
        ]);

        $this->actingAs($admin)
            ->from(route('admin.provider-models.create'))
            ->post(route('admin.provider-models.store'), [
                'provider_id' => $provider->id,
                'name' => 'Duplicate Model',
                'model_key' => 'duplicate-model',
                'role' => 'worker',
                'context_window' => 2048,
                'is_enabled' => 1,
                'is_default' => 0,
            ])
            ->assertRedirect(route('admin.provider-models.create'))
            ->assertSessionHasErrors('model_key');

        $this->assertSame(
            1,
            ProviderModel::query()
            ->where('provider_id', $provider->id)
            ->where('model_key', 'duplicate-model')
            ->count()
        );
    }

    public function test_admin_can_reuse_model_key_for_a_different_provider(): void
    {
        $this->seed();

        $admin = User::query()->where('email', config('threadcore.admin.email'))->firstOrFail();
        $sourceProvider = Provider::query()->where('slug', 'openai')->firstOrFail();
        $targetProvider = Provider::query()->where('slug', 'ollama')->firstOrFail();

        ProviderModel::query()->create([
            'provider_id' => $sourceProvider->id,
            'name' => 'Shared Model Name',
            'model_key' => 'shared-model',
            'role' => 'worker',
            'context_window' => 2048,
            'is_enabled' => true,
            'is_default' => false,
        ]);

        $this->actingAs($admin)
            ->from(route('admin.provider-models.create'))
            ->post(route('admin.provider-models.store'), [
                'provider_id' => $targetProvider->id,
                'name' => 'Shared Model Name',
                'model_key' => 'shared-model',
                'role' => 'worker',
                'context_window' => 2048,
                'is_enabled' => 1,
                'is_default' => 0,
            ])
            ->assertRedirect(route('admin.resources.index'))
            ->assertSessionHasNoErrors();

        $this->assertSame(
            1,
            ProviderModel::query()
                ->where('provider_id', $sourceProvider->id)
                ->where('model_key', 'shared-model')
                ->count()
        );

        $this->assertSame(
            1,
            ProviderModel::query()
            ->where('provider_id', $targetProvider->id)
            ->where('model_key', 'shared-model')
            ->count()
        );
    }

    public function test_admin_cannot_create_provider_model_with_duplicate_key_in_same_provider(): void
    {
        $this->seed();

        $admin = User::query()->where('email', config('threadcore.admin.email'))->firstOrFail();
        $provider = Provider::query()->where('slug', 'openai')->firstOrFail();

        ProviderModel::query()->create([
            'provider_id' => $provider->id,
            'name' => 'Existing Model',
            'model_key' => 'duplicate-create-model',
            'role' => 'worker',
            'context_window' => 2048,
            'is_enabled' => true,
            'is_default' => false,
        ]);

        $this->actingAs($admin)
            ->from(route('admin.provider-models.create'))
            ->post(route('admin.provider-models.store'), [
                'provider_id' => $provider->id,
                'name' => 'Duplicate Model',
                'model_key' => 'duplicate-create-model',
                'role' => 'worker',
                'context_window' => 2048,
                'is_enabled' => 1,
                'is_default' => 0,
            ])
            ->assertRedirect(route('admin.provider-models.create'))
            ->assertSessionHasErrors('model_key');
    }

    public function test_admin_cannot_move_model_to_provider_that_already_has_same_key(): void
    {
        $this->seed();

        $admin = User::query()->where('email', config('threadcore.admin.email'))->firstOrFail();
        $sourceProvider = Provider::query()->where('slug', 'openai')->firstOrFail();
        $targetProvider = Provider::query()->where('slug', 'ollama')->firstOrFail();

        $model = ProviderModel::query()->create([
            'provider_id' => $sourceProvider->id,
            'name' => 'Movable Model',
            'model_key' => 'shared-move-model',
            'role' => 'worker',
            'context_window' => 2048,
            'is_enabled' => true,
            'is_default' => false,
        ]);

        ProviderModel::query()->create([
            'provider_id' => $targetProvider->id,
            'name' => 'Blocking Model',
            'model_key' => 'shared-move-model',
            'role' => 'worker',
            'context_window' => 2048,
            'is_enabled' => true,
            'is_default' => false,
        ]);

        $this->actingAs($admin)
            ->from(route('admin.provider-models.edit', $model))
            ->put(route('admin.provider-models.update', $model), [
                'provider_id' => $targetProvider->id,
                'name' => 'Movable Model',
                'model_key' => 'shared-move-model',
                'role' => 'worker',
                'context_window' => 2048,
                'is_enabled' => 1,
                'is_default' => 0,
            ])
            ->assertRedirect(route('admin.provider-models.edit', $model))
            ->assertSessionHasErrors('model_key');
    }
}
