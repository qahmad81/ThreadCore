<?php

namespace Tests\Feature;

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
}
