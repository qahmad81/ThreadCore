<?php

namespace Tests\Feature;

use App\Models\ApiKey;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CustomerApiKeysTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_create_and_revoke_api_key(): void
    {
        $this->seed();
        $user = User::query()->where('email', config('threadcore.demo_customer.email'))->firstOrFail();

        $this->actingAs($user)->post(route('customer.api-keys.store'), [
            'name' => 'Test key',
        ])->assertRedirect(route('customer.api-keys.index'))
            ->assertSessionHas('plain_api_token');

        $key = ApiKey::query()->where('name', 'Test key')->firstOrFail();

        $this->assertNotNull($key->token_hash);
        $this->assertStringStartsWith('tc_live_', session('plain_api_token'));

        $this->actingAs($user)->delete(route('customer.api-keys.destroy', $key))
            ->assertRedirect(route('customer.api-keys.index'));

        $this->assertNotNull($key->fresh()->revoked_at);
    }

    public function test_customer_dashboard_shows_operational_state_and_can_create_api_key(): void
    {
        $this->seed();
        $user = User::query()->where('email', config('threadcore.demo_customer.email'))->firstOrFail();

        $this->actingAs($user)->get(route('customer.dashboard'))
            ->assertOk()
            ->assertSee('Starter')
            ->assertSee('Create your first API key')
            ->assertSee('View docs');

        $this->actingAs($user)->post(route('customer.api-keys.store'), [
            'name' => 'Dashboard key',
        ])->assertRedirect(route('customer.api-keys.index'))
            ->assertSessionHas('plain_api_token');

        $this->assertDatabaseHas('api_keys', [
            'customer_account_id' => $user->customer_account_id,
            'name' => 'Dashboard key',
        ]);
    }

    public function test_customer_login_redirects_to_customer_dashboard_not_admin(): void
    {
        $this->seed();

        $this->post(route('login.store'), [
            'email' => config('threadcore.demo_customer.email'),
            'password' => config('threadcore.demo_customer.password'),
        ])->assertRedirect(route('customer.dashboard'));
    }

    public function test_customer_profile_and_password_can_be_updated(): void
    {
        $this->seed();
        $user = User::query()->where('email', config('threadcore.demo_customer.email'))->firstOrFail();

        $this->actingAs($user)->get(route('customer.profile.edit'))
            ->assertOk()
            ->assertSee('Account Profile')
            ->assertSee('Change Password');

        $this->actingAs($user)->put(route('customer.profile.update'), [
            'name' => 'Updated Customer',
            'email' => 'updated-customer@threadcore.local',
        ])->assertRedirect(route('customer.profile.edit'));

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Updated Customer',
            'email' => 'updated-customer@threadcore.local',
        ]);

        $this->actingAs($user->fresh())->put(route('customer.password.update'), [
            'current_password' => config('threadcore.demo_customer.password'),
            'password' => 'new-customer-password',
            'password_confirmation' => 'new-customer-password',
        ])->assertRedirect(route('customer.profile.edit'));

        $this->assertTrue(Hash::check('new-customer-password', $user->fresh()->password));
    }
}
