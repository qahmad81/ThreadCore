<?php

namespace Tests\Feature;

use App\Models\ApiKey;
use App\Models\CustomerAccount;
use App\Models\FamilyAgent;
use App\Models\Provider;
use App\Models\ProviderModel;
use App\Models\Thread;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class CustomerDocsTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_docs_show_workspace_summary_and_reply_example(): void
    {
        $this->seed();

        $user = User::query()->where('email', config('threadcore.demo_customer.email'))->firstOrFail();
        $account = $user->customerAccount()->firstOrFail();
        $family = FamilyAgent::query()->firstOrFail();
        $provider = Provider::query()->firstOrFail();
        $model = ProviderModel::query()->firstOrFail();

        for ($i = 1; $i <= 6; $i++) {
            $plainToken = ApiKey::makePlainToken();
            $account->apiKeys()->create([
                'name' => 'Extra key '.$i,
                'prefix' => substr($plainToken, 0, 12),
                'token_hash' => ApiKey::hashToken($plainToken),
            ]);
        }

        for ($i = 1; $i <= 7; $i++) {
            Thread::query()->create([
                'public_id' => (string) Str::uuid(),
                'customer_account_id' => $account->id,
                'api_key_id' => $account->apiKeys()->first()->id,
                'family_agent_id' => $family->id,
                'provider_id' => $provider->id,
                'provider_model_id' => $model->id,
                'title' => 'Doc thread '.$i,
                'input_tokens' => 0,
                'output_tokens' => 0,
                'max_context_tokens' => $family->max_context_tokens,
                'metadata' => [],
            ]);
        }

        $this->actingAs($user)
            ->get(route('customer.docs'))
            ->assertOk()
            ->assertSee('Active customer workspace')
            ->assertSee('Active agents')
            ->assertSee((string) $account->apiKeys()->whereNull('revoked_at')->count())
            ->assertSee((string) $account->threads()->count())
            ->assertSee('Create a thread')
            ->assertSee('Reply to an existing thread')
            ->assertSee('Supported commands')
            ->assertSee('/whisper')
            ->assertSee('/skip')
            ->assertSee('/dayend')
            ->assertSee('/forget')
            ->assertSee('Agent Name')
            ->assertSee('Agent Code')
            ->assertSee('Description')
            ->assertSee('Default (provider & model)')
            ->assertSee('Context Length')
            ->assertSee('family_agent')
            ->assertSee('content');
    }
}
