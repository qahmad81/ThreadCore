<?php

namespace Tests\Feature;

use App\Models\Thread;
use App\Models\User;
use App\Models\ApiKey;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AdminThreadsTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_threads_index_shows_view_conversation_action(): void
    {
        $this->seed();

        $user = User::query()->where('email', config('threadcore.admin.email'))->firstOrFail();
        $account = \App\Models\CustomerAccount::query()->firstOrFail();
        $family = \App\Models\FamilyAgent::query()->firstOrFail();
        $provider = \App\Models\Provider::query()->firstOrFail();
        $model = \App\Models\ProviderModel::query()->firstOrFail();
        $plainToken = ApiKey::makePlainToken();
        $apiKey = $account->apiKeys()->create([
            'name' => 'Admin test key',
            'prefix' => substr($plainToken, 0, 12),
            'token_hash' => ApiKey::hashToken($plainToken),
        ]);

        Thread::query()->create([
            'public_id' => (string) Str::uuid(),
            'customer_account_id' => $account->id,
            'api_key_id' => $apiKey->id,
            'family_agent_id' => $family->id,
            'provider_id' => $provider->id,
            'provider_model_id' => $model->id,
            'title' => 'Demo thread',
            'input_tokens' => 0,
            'output_tokens' => 0,
            'max_context_tokens' => $family->max_context_tokens,
            'metadata' => [],
        ]);

        $this->actingAs($user)
            ->get(route('admin.threads.index'))
            ->assertOk()
            ->assertSee('View conversation');
    }

    public function test_admin_can_view_thread_conversation(): void
    {
        $this->seed();
        $admin = User::query()->where('email', config('threadcore.admin.email'))->firstOrFail();
        $plainToken = $this->createApiKey();

        Http::fake([
            'openrouter.ai/*' => Http::response([
                'choices' => [
                    ['message' => ['content' => 'Hello from OpenRouter'], 'finish_reason' => 'stop'],
                ],
                'usage' => ['prompt_tokens' => 9, 'completion_tokens' => 4],
            ]),
        ]);

        $threadId = $this->withToken($plainToken)->postJson('/api/v1/threads', [
            'family_agent' => 'default',
            'content' => 'Hello',
        ])->json('thread_id');

        $thread = Thread::query()->where('public_id', $threadId)->firstOrFail();

        $this->actingAs($admin)
            ->get(route('admin.threads.show', $thread))
            ->assertOk()
            ->assertSee('Thread Conversation')
            ->assertSee($thread->public_id)
            ->assertSee('Hello')
            ->assertSee('Hello from OpenRouter');
    }

    private function createApiKey(): string
    {
        $account = \App\Models\CustomerAccount::query()->firstOrFail();
        $plainToken = \App\Models\ApiKey::makePlainToken();
        $account->apiKeys()->create([
            'name' => 'Thread viewer key',
            'prefix' => substr($plainToken, 0, 12),
            'token_hash' => \App\Models\ApiKey::hashToken($plainToken),
        ]);

        return $plainToken;
    }
}
