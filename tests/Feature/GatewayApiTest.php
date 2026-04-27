<?php

namespace Tests\Feature;

use App\Models\ApiKey;
use App\Models\Thread;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class GatewayApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_api_key_auth_rejects_missing_token(): void
    {
        $this->postJson('/api/v1/threads', [])->assertUnauthorized();
    }

    public function test_gateway_creates_thread_and_posts_message(): void
    {
        $this->seed();
        [$plainToken] = $this->createApiKey();

        Http::fake([
            'openrouter.ai/*' => Http::response([
                'choices' => [
                    ['message' => ['content' => 'Hello from OpenRouter'], 'finish_reason' => 'stop'],
                ],
                'usage' => ['prompt_tokens' => 9, 'completion_tokens' => 4],
            ]),
        ]);

        $created = $this->withToken($plainToken)->postJson('/api/v1/threads', [
            'family_agent' => 'default',
            'content' => 'Hello',
        ])->assertOk()
            ->assertJsonPath('response', 'Hello from OpenRouter')
            ->assertJsonPath('provider', 'openrouter')
            ->json();

        $this->withToken($plainToken)->postJson('/api/v1/threads/'.$created['thread_id'].'/messages', [
            'content' => 'Continue',
        ])->assertOk()
            ->assertJsonPath('response', 'Hello from OpenRouter');

        $this->assertSame(1, Thread::query()->count());
        $this->assertSame(4, Thread::query()->first()->messages()->count());
    }

    public function test_gateway_commands_skip_whisper_dayend_and_forget(): void
    {
        $this->seed();
        [$plainToken] = $this->createApiKey();

        Http::fake([
            'openrouter.ai/*' => Http::response([
                'choices' => [
                    ['message' => ['content' => 'Command response'], 'finish_reason' => 'stop'],
                ],
                'usage' => ['prompt_tokens' => 9, 'completion_tokens' => 4],
            ]),
        ]);

        $created = $this->withToken($plainToken)->postJson('/api/v1/threads', [
            'family_agent' => 'default',
            'content' => 'Remember alpha',
        ])->json();

        $threadId = $created['thread_id'];

        $this->withToken($plainToken)->postJson("/api/v1/threads/{$threadId}/messages", [
            'content' => '/whisper private question',
        ])->assertOk();

        $thread = Thread::query()->where('public_id', $threadId)->firstOrFail();
        $this->assertSame(2, $thread->messages()->count());

        $this->withToken($plainToken)->postJson("/api/v1/threads/{$threadId}/messages", [
            'content' => '/skip no history',
        ])->assertOk();

        $this->withToken($plainToken)->postJson("/api/v1/threads/{$threadId}/messages", [
            'content' => '/dayend close day',
        ])->assertOk()
            ->assertJsonPath('compaction.triggered', true);

        $this->withToken($plainToken)->postJson("/api/v1/threads/{$threadId}/messages", [
            'content' => '/forget alpha',
        ])->assertOk();

        $this->assertTrue($thread->fresh()->messages()->where('is_memory', true)->exists());
    }

    public function test_failed_provider_call_does_not_persist_user_turn(): void
    {
        $this->seed();
        [$plainToken] = $this->createApiKey();

        Http::fake([
            'openrouter.ai/*' => Http::response([], 502),
        ]);

        $this->withToken($plainToken)->postJson('/api/v1/threads', [
            'family_agent' => 'default',
            'content' => 'This will fail',
        ])->assertStatus(502);

        $thread = Thread::query()->firstOrFail();
        $this->assertSame(0, $thread->messages()->count());
    }

    public function test_forget_command_is_logged_and_consumes_usage(): void
    {
        $this->seed();
        [$plainToken] = $this->createApiKey();

        Http::fake([
            'openrouter.ai/*' => Http::response([
                'choices' => [
                    ['message' => ['content' => 'Command response'], 'finish_reason' => 'stop'],
                ],
                'usage' => ['prompt_tokens' => 9, 'completion_tokens' => 4],
            ]),
        ]);

        $created = $this->withToken($plainToken)->postJson('/api/v1/threads', [
            'family_agent' => 'default',
            'content' => 'Remember alpha',
        ])->json();

        $account = \App\Models\CustomerAccount::query()->firstOrFail();
        $beforeRequests = $account->activeSubscription->requests_used;

        $this->withToken($plainToken)->postJson("/api/v1/threads/{$created['thread_id']}/messages", [
            'content' => '/forget alpha',
        ])->assertOk();

        $account->refresh();
        $this->assertSame($beforeRequests + 1, $account->activeSubscription->requests_used);
        $this->assertDatabaseHas('gateway_request_logs', [
            'status' => 'ok',
            'request_payload->command' => 'forget',
        ]);
    }

    private function createApiKey(): array
    {
        $account = \App\Models\CustomerAccount::query()->firstOrFail();
        $plainToken = ApiKey::makePlainToken();
        $apiKey = $account->apiKeys()->create([
            'name' => 'Test gateway key',
            'prefix' => substr($plainToken, 0, 12),
            'token_hash' => ApiKey::hashToken($plainToken),
        ]);

        return [$plainToken, $apiKey];
    }
}
