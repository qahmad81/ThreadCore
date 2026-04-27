<?php

namespace Tests\Feature;

use App\Models\ApiKey;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
}
