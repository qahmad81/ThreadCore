<?php

namespace Tests\Feature;

use App\Models\ApiKey;
use App\Models\CustomerAccount;
use App\Models\FamilyAgent;
use App\Models\Provider;
use App\Models\ProviderModel;
use App\Models\Thread;
use App\Models\ThreadMessage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class CustomerThreadsTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_threads_index_shows_only_their_threads(): void
    {
        $this->seed();

        $user = User::query()->where('email', config('threadcore.demo_customer.email'))->firstOrFail();
        $account = $user->customerAccount()->firstOrFail();
        $ownThread = $this->createThreadForAccount($account, 'Customer thread one');
        $otherAccount = CustomerAccount::query()->create([
            'name' => 'Other Customer',
            'slug' => 'other-customer',
            'status' => 'active',
        ]);
        $this->createThreadForAccount($otherAccount, 'Other customer thread');

        $this->actingAs($user)
            ->get(route('customer.threads.index'))
            ->assertOk()
            ->assertSee('Your conversations')
            ->assertSee($ownThread->title)
            ->assertDontSee('Other customer thread');
    }

    public function test_customer_can_view_own_thread_but_not_another_customers_thread(): void
    {
        $this->seed();

        $user = User::query()->where('email', config('threadcore.demo_customer.email'))->firstOrFail();
        $account = $user->customerAccount()->firstOrFail();
        $ownThread = $this->createThreadForAccount($account, 'Customer thread one');
        $otherAccount = CustomerAccount::query()->create([
            'name' => 'Other Customer',
            'slug' => 'other-customer',
            'status' => 'active',
        ]);
        $otherThread = $this->createThreadForAccount($otherAccount, 'Other customer thread');

        $this->actingAs($user)
            ->get(route('customer.threads.show', $ownThread->public_id))
            ->assertOk()
            ->assertSee('Thread Conversation')
            ->assertSee($ownThread->public_id)
            ->assertSee('Hello from customer')
            ->assertSee('Hello from assistant');

        $this->actingAs($user)
            ->get(route('customer.threads.show', $otherThread->public_id))
            ->assertNotFound();
    }

    public function test_customer_can_export_own_thread_as_markdown(): void
    {
        $this->seed();

        $user = User::query()->where('email', config('threadcore.demo_customer.email'))->firstOrFail();
        $account = $user->customerAccount()->firstOrFail();
        $ownThread = $this->createThreadForAccount($account, 'Customer thread one');

        $response = $this->actingAs($user)->get(route('customer.threads.export', $ownThread->public_id));

        $response->assertOk()
            ->assertDownload('customer-thread-one.md');

        $content = $response->streamedContent();

        $this->assertStringContainsString('# Thread Conversation', $content);
        $this->assertStringContainsString('### User', $content);
        $this->assertStringContainsString($ownThread->public_id, $content);
        $this->assertStringContainsString('Hello from customer', $content);
        $this->assertStringNotContainsString('Metadata', $content);
        $this->assertStringNotContainsString('```', $content);
        $this->assertStringNotContainsString('Created:', $content);
    }

    public function test_customer_export_falls_back_to_public_id_when_title_does_not_slug(): void
    {
        $this->seed();

        $user = User::query()->where('email', config('threadcore.demo_customer.email'))->firstOrFail();
        $account = $user->customerAccount()->firstOrFail();
        $thread = $this->createThreadForAccount($account, '!!!');

        $response = $this->actingAs($user)->get(route('customer.threads.export', $thread->public_id));

        $response->assertOk()
            ->assertDownload($thread->public_id.'.md');
    }

    public function test_customer_threads_index_paginate_beyond_one_hundred_threads(): void
    {
        $this->seed();

        $user = User::query()->where('email', config('threadcore.demo_customer.email'))->firstOrFail();
        $account = $user->customerAccount()->firstOrFail();

        for ($i = 1; $i <= 101; $i++) {
            $this->createThreadForAccount($account, 'Customer thread '.$i);
        }

        $this->actingAs($user)
            ->get(route('customer.threads.index', ['page' => 3]))
            ->assertOk()
            ->assertSee('View conversation')
            ->assertSee('page=2');
    }

    private function createThreadForAccount(CustomerAccount $account, string $title): Thread
    {
        $family = FamilyAgent::query()->firstOrFail();
        $provider = Provider::query()->firstOrFail();
        $model = ProviderModel::query()->firstOrFail();
        $plainToken = ApiKey::makePlainToken();
        $apiKey = $account->apiKeys()->create([
            'name' => 'Thread key',
            'prefix' => substr($plainToken, 0, 12),
            'token_hash' => ApiKey::hashToken($plainToken),
        ]);

        $thread = Thread::query()->create([
            'public_id' => (string) Str::uuid(),
            'customer_account_id' => $account->id,
            'api_key_id' => $apiKey->id,
            'family_agent_id' => $family->id,
            'provider_id' => $provider->id,
            'provider_model_id' => $model->id,
            'title' => $title,
            'input_tokens' => 12,
            'output_tokens' => 8,
            'max_context_tokens' => $family->max_context_tokens,
            'metadata' => [],
        ]);

        $thread->messages()->create([
            'role' => 'user',
            'content' => 'Hello from customer',
            'input_tokens' => 6,
        ]);

        $thread->messages()->create([
            'role' => 'assistant',
            'content' => 'Hello from assistant',
            'output_tokens' => 4,
        ]);

        return $thread;
    }
}
