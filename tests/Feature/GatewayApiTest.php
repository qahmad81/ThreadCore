<?php

namespace Tests\Feature;

use App\Models\ApiKey;
use App\Models\Thread;
use App\Services\Gateway\CompactionService;
use App\Services\Gateway\HistoryBuilder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Tests\TestCase;

class GatewayApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_api_key_auth_rejects_missing_token(): void
    {
        $this->postJson('/api/v1/threads', [])->assertUnauthorized();
    }

    public function test_dayend_on_thread_creation_does_not_create_an_empty_thread(): void
    {
        $this->seed();
        [$plainToken] = $this->createApiKey();

        $account = \App\Models\CustomerAccount::query()->firstOrFail();
        $beforeThreads = Thread::query()->count();
        $beforeRequests = $account->activeSubscription->requests_used;
        $beforeTokens = $account->activeSubscription->tokens_used;

        $this->withToken($plainToken)->postJson('/api/v1/threads', [
            'family_agent' => 'default',
            'content' => '/dayend close day',
        ])->assertOk()
            ->assertJsonPath('thread_id', null)
            ->assertJsonPath('compaction.triggered', false);

        $account->refresh();
        $this->assertSame($beforeRequests + 1, $account->activeSubscription->requests_used);
        $this->assertSame($beforeTokens, $account->activeSubscription->tokens_used);
        $this->assertSame($beforeThreads, Thread::query()->count());
        $this->assertDatabaseHas('gateway_request_logs', [
            'customer_account_id' => $account->id,
            'thread_id' => null,
            'request_payload->command' => 'dayend',
            'response_metadata->compaction_triggered' => false,
        ]);
    }

    public function test_dayend_without_compaction_logs_false_trigger_and_no_extra_usage(): void
    {
        $this->seed();
        [$plainToken] = $this->createApiKey();

        $account = \App\Models\CustomerAccount::query()->firstOrFail();
        $family = \App\Models\FamilyAgent::query()->where('number', 'default')->firstOrFail();
        $thread = Thread::query()->create([
            'public_id' => (string) Str::uuid(),
            'customer_account_id' => $account->id,
            'api_key_id' => $account->apiKeys()->firstOrFail()->id,
            'family_agent_id' => $family->id,
            'provider_id' => $family->default_provider_id,
            'provider_model_id' => $family->default_provider_model_id,
            'title' => 'Short dayend thread',
            'max_context_tokens' => $family->max_context_tokens,
        ]);

        $thread->messages()->create([
            'role' => 'user',
            'content' => 'only raw message',
            'input_tokens' => 3,
        ]);

        $beforeRequests = $account->activeSubscription->requests_used;
        $beforeTokens = $account->activeSubscription->tokens_used;

        $this->withToken($plainToken)->postJson("/api/v1/threads/{$thread->public_id}/messages", [
            'content' => '/dayend close day',
        ])->assertOk()
            ->assertJsonPath('thread_id', $thread->public_id)
            ->assertJsonPath('compaction.triggered', false);

        $account->refresh();
        $this->assertSame($beforeRequests, $account->activeSubscription->requests_used);
        $this->assertSame($beforeTokens, $account->activeSubscription->tokens_used);
        $this->assertDatabaseHas('gateway_request_logs', [
            'request_payload->command' => 'dayend',
            'response_metadata->compaction_triggered' => false,
        ]);
    }

    public function test_dayend_compaction_failure_returns_502_and_keeps_thread_clean(): void
    {
        $this->seed();
        [$plainToken] = $this->createApiKey();

        Http::fake([
            'openrouter.ai/*' => Http::response([], 500),
        ]);

        $account = \App\Models\CustomerAccount::query()->firstOrFail();
        $family = \App\Models\FamilyAgent::query()->where('number', 'default')->firstOrFail();
        $thread = Thread::query()->create([
            'public_id' => (string) Str::uuid(),
            'customer_account_id' => $account->id,
            'api_key_id' => $account->apiKeys()->firstOrFail()->id,
            'family_agent_id' => $family->id,
            'provider_id' => $family->default_provider_id,
            'provider_model_id' => $family->default_provider_model_id,
            'title' => 'Dayend compaction failure',
            'max_context_tokens' => $family->max_context_tokens,
        ]);

        $thread->messages()->create(['role' => 'user', 'content' => 'first raw']);
        $thread->messages()->create(['role' => 'assistant', 'content' => 'first reply']);

        $this->withToken($plainToken)->postJson("/api/v1/threads/{$thread->public_id}/messages", [
            'content' => '/dayend close day',
        ])->assertStatus(502)
            ->assertJsonPath('message', 'Provider request failed.');

        $account->refresh();
        $this->assertSame(0, $account->activeSubscription->tokens_used);

        $fresh = $thread->fresh();
        $this->assertSame(2, $fresh->messages()->count());
        $this->assertSame(0, $fresh->messages()->where('is_memory', true)->count());
        $this->assertNull($fresh->compacted_at);
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
        $account = \App\Models\CustomerAccount::query()->firstOrFail();
        $this->assertSame(26, $account->activeSubscription->tokens_used);

        $thread = Thread::query()->firstOrFail();
        $firstUserMessage = $thread->messages()->where('role', 'user')->orderBy('id')->firstOrFail();
        $firstAssistantMessage = $thread->messages()->where('role', 'assistant')->orderBy('id')->firstOrFail();

        $this->assertSame(0, $firstUserMessage->input_tokens);
        $this->assertSame(0, $firstUserMessage->output_tokens);
        $this->assertSame('0.000000', $firstUserMessage->cost);
        $this->assertSame(9, $firstAssistantMessage->input_tokens);
        $this->assertSame(4, $firstAssistantMessage->output_tokens);
        $this->assertSame('0.000000', $firstAssistantMessage->cost);
    }

    public function test_gateway_commands_skip_whisper_dayend_and_forget(): void
    {
        $this->seed();
        [$plainToken] = $this->createApiKey();

        Http::fake([
            'openrouter.ai/*' => Http::sequence()
                ->push([
                    'choices' => [
                        ['message' => ['content' => 'Hello from OpenRouter'], 'finish_reason' => 'stop'],
                    ],
                    'usage' => ['prompt_tokens' => 9, 'completion_tokens' => 4],
                ])
                ->push([
                    'choices' => [
                        ['message' => ['content' => 'Whisper response'], 'finish_reason' => 'stop'],
                    ],
                    'usage' => ['prompt_tokens' => 7, 'completion_tokens' => 3],
                ])
                ->push([
                    'choices' => [
                        ['message' => ['content' => 'Skip response'], 'finish_reason' => 'stop'],
                    ],
                    'usage' => ['prompt_tokens' => 8, 'completion_tokens' => 2],
                ])
                ->push([
                    'choices' => [
                        ['message' => ['content' => 'Compressed memory result'], 'finish_reason' => 'stop'],
                    ],
                    'usage' => ['prompt_tokens' => 12, 'completion_tokens' => 3],
                ]),
        ]);

        $created = $this->withToken($plainToken)->postJson('/api/v1/threads', [
            'family_agent' => 'default',
            'content' => 'Remember alpha',
        ])->assertOk()
            ->assertJsonPath('response', 'Hello from OpenRouter')
            ->json();

        $threadId = $created['thread_id'];

        $this->withToken($plainToken)->postJson("/api/v1/threads/{$threadId}/messages", [
            'content' => '/whisper private question',
        ])->assertOk()
            ->assertJsonPath('response', 'Whisper response');

        $thread = Thread::query()->where('public_id', $threadId)->firstOrFail();
        $this->assertSame(4, $thread->messages()->count());
        $this->assertSame(2, $thread->messages()->where('is_forgotten', true)->count());

        $this->withToken($plainToken)->postJson("/api/v1/threads/{$threadId}/messages", [
            'content' => '/skip no history',
        ])->assertOk()
            ->assertJsonPath('response', 'Skip response');

        $account = \App\Models\CustomerAccount::query()->firstOrFail();
        $beforeDayendUsage = $account->activeSubscription->requests_used;

        $this->withToken($plainToken)->postJson("/api/v1/threads/{$threadId}/messages", [
            'content' => '/dayend close day',
        ])->assertOk()
            ->assertJsonPath('thread_id', $threadId)
            ->assertJsonPath('compaction.triggered', true);

        $account->refresh();
        $this->assertSame($beforeDayendUsage + 1, $account->activeSubscription->requests_used);

        $thread->refresh();
        $this->assertSame(7, $thread->messages()->count());

        $this->withToken($plainToken)->postJson("/api/v1/threads/{$threadId}/messages", [
            'content' => '/forget alpha',
        ])->assertOk()
            ->assertJsonPath('response', 'Order done!!');

        $this->assertSame(9, $thread->fresh()->messages()->count());
        $this->assertSame(7, $thread->fresh()->messages()->where('is_forgotten', true)->count());
        $this->assertSame(1, $thread->fresh()->messages()->where('command', 'whisper')->count());
        $this->assertSame(1, $thread->fresh()->messages()->where('command', 'skip')->count());
        $this->assertSame(1, $thread->fresh()->messages()->where('command', 'forget')->count());
        $this->assertFalse($thread->fresh()->messages()->where('command', 'dayend')->exists());
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

        $this->assertSame(0, Thread::query()->count());
    }

    public function test_provider_can_use_direct_api_key_from_provider_record(): void
    {
        $this->seed();
        [$plainToken] = $this->createApiKey();

        $provider = \App\Models\Provider::query()->where('slug', 'openrouter')->firstOrFail();
        $provider->update(['api_key_env' => 'sk-direct-provider-token']);

        Http::fake([
            'openrouter.ai/*' => function ($request) {
                if (($request->header('Authorization')[0] ?? null) !== 'Bearer sk-direct-provider-token') {
                    return Http::response([], 401);
                }

                return Http::response([
                    'choices' => [
                        ['message' => ['content' => 'Hello from provider record'], 'finish_reason' => 'stop'],
                    ],
                    'usage' => ['prompt_tokens' => 9, 'completion_tokens' => 4],
                ]);
            },
        ]);

        $this->withToken($plainToken)->postJson('/api/v1/threads', [
            'family_agent' => 'default',
            'content' => 'Hello',
        ])->assertOk()
            ->assertJsonPath('response', 'Hello from provider record');
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
        $beforeTokens = $account->activeSubscription->tokens_used;

        $this->withToken($plainToken)->postJson("/api/v1/threads/{$created['thread_id']}/messages", [
            'content' => '/forget alpha',
        ])->assertOk();

        $account->refresh();
        $this->assertSame($beforeRequests + 1, $account->activeSubscription->requests_used);
        $this->assertSame($beforeTokens, $account->activeSubscription->tokens_used);
        $this->assertDatabaseHas('gateway_request_logs', [
            'status' => 'ok',
            'request_payload->command' => 'forget',
        ]);
    }

    public function test_forced_compaction_includes_all_raw_messages_without_truncation(): void
    {
        $this->seed();

        Http::fake([
            'openrouter.ai/*' => Http::response([
                'choices' => [
                    ['message' => ['content' => 'Compressed memory result'], 'finish_reason' => 'stop'],
                ],
                'usage' => ['prompt_tokens' => 120, 'completion_tokens' => 12, 'cost' => 0.123456],
            ]),
        ]);

        $family = \App\Models\FamilyAgent::query()->where('number', 'default')->firstOrFail();
        $pricingModel = $family->defaultProviderModel;
        $pricingModel->forceFill([
            'pricing' => [
                'prompt_tokens' => 0.000001,
                'completion_tokens' => 0.000002,
            ],
        ])->save();

        $thread = Thread::query()->create([
            'public_id' => (string) Str::uuid(),
            'family_agent_id' => $family->id,
            'provider_id' => $family->default_provider_id,
            'provider_model_id' => $pricingModel->id,
            'title' => 'Compaction coverage',
            'input_tokens' => 1,
            'output_tokens' => 1,
            'max_context_tokens' => $family->max_context_tokens,
        ]);

        foreach (range(1, 13) as $index) {
            $thread->messages()->create([
                'role' => 'user',
                'content' => 'message-'.$index.' '.str_repeat('x', 350),
            ]);
        }

        $this->assertTrue(app(CompactionService::class)->compact($thread, true)->triggered);

        $memory = $thread->messages()->where('is_memory', true)->firstOrFail();

        $this->assertSame('Compressed memory result', $memory->content);
        $this->assertSame(120, $memory->input_tokens);
        $this->assertSame(12, $memory->output_tokens);
        $this->assertSame('0.000144', $memory->cost);
        $this->assertSame([
            'prompt_tokens' => 120,
            'completion_tokens' => 12,
            'prompt_cache_hit_tokens' => 0,
            'prompt_cache_miss_tokens' => 120,
            'reasoning_tokens' => 0,
            'has_prompt_cache_breakdown' => false,
            'has_reasoning_breakdown' => false,
        ], $memory->metadata['usage']);
        $this->assertSame(13, $memory->metadata['raw_message_count']);
        $this->assertSame('ai_compaction_v1', $memory->metadata['generated_by']);

        Http::assertSent(function ($request) {
            $payload = $request->data();
            $content = $payload['messages'][0]['content'] ?? '';

            return str_contains($content, 'Compacted memory:')
                && str_contains($content, 'message-13')
                && str_contains($content, str_repeat('x', 350));
        });
    }

    public function test_failed_compaction_does_not_mark_messages_or_create_memory(): void
    {
        $this->seed();

        Http::fake([
            'openrouter.ai/*' => Http::response([], 500),
        ]);

        $family = \App\Models\FamilyAgent::query()->where('number', 'default')->firstOrFail();
        $thread = Thread::query()->create([
            'public_id' => (string) Str::uuid(),
            'family_agent_id' => $family->id,
            'provider_id' => $family->default_provider_id,
            'provider_model_id' => $family->default_provider_model_id,
            'title' => 'Compaction failure',
            'input_tokens' => 1,
            'output_tokens' => 1,
            'max_context_tokens' => $family->max_context_tokens,
        ]);

        $thread->messages()->create(['role' => 'user', 'content' => 'first raw']);
        $thread->messages()->create(['role' => 'assistant', 'content' => 'first reply']);

        try {
            app(CompactionService::class)->compact($thread, true);
            $this->fail('Expected compaction to throw when provider fails.');
        } catch (\Throwable) {
            // expected
        }

        $fresh = $thread->fresh();

        $this->assertSame(2, $fresh->messages()->count());
        $this->assertSame(0, $fresh->messages()->where('is_compacted', true)->count());
        $this->assertSame(0, $fresh->messages()->where('is_memory', true)->count());
        $this->assertNull($fresh->compacted_at);
    }

    public function test_compaction_rejects_empty_provider_content(): void
    {
        $this->seed();

        Http::fake([
            'openrouter.ai/*' => Http::response([
                'choices' => [
                    ['message' => ['content' => '   '], 'finish_reason' => 'stop'],
                ],
                'usage' => ['prompt_tokens' => 10, 'completion_tokens' => 0],
            ]),
        ]);

        $family = \App\Models\FamilyAgent::query()->where('number', 'default')->firstOrFail();
        $thread = Thread::query()->create([
            'public_id' => (string) Str::uuid(),
            'family_agent_id' => $family->id,
            'provider_id' => $family->default_provider_id,
            'provider_model_id' => $family->default_provider_model_id,
            'title' => 'Compaction empty content',
            'input_tokens' => 1,
            'output_tokens' => 1,
            'max_context_tokens' => $family->max_context_tokens,
        ]);

        $thread->messages()->create(['role' => 'user', 'content' => 'first raw']);
        $thread->messages()->create(['role' => 'assistant', 'content' => 'first reply']);

        try {
            app(CompactionService::class)->compact($thread, true);
            $this->fail('Expected compaction to reject empty provider content.');
        } catch (\RuntimeException $exception) {
            $this->assertStringContainsString('returned empty content', $exception->getMessage());
        }

        $fresh = $thread->fresh();
        $this->assertSame(2, $fresh->messages()->count());
        $this->assertSame(0, $fresh->messages()->where('is_compacted', true)->count());
        $this->assertSame(0, $fresh->messages()->where('is_memory', true)->count());
        $this->assertNull($fresh->compacted_at);
    }

    public function test_automatic_compaction_uses_active_context_not_cumulative_thread_totals(): void
    {
        $this->seed();

        Http::preventStrayRequests();

        $family = \App\Models\FamilyAgent::query()->where('number', 'default')->firstOrFail();
        $family->forceFill([
            'compaction_threshold_tokens' => 4000,
            'max_context_tokens' => 5000,
        ])->save();

        $thread = Thread::query()->create([
            'public_id' => (string) Str::uuid(),
            'family_agent_id' => $family->id,
            'provider_id' => $family->default_provider_id,
            'provider_model_id' => $family->default_provider_model_id,
            'title' => 'Compaction threshold reset',
            'input_tokens' => 15000,
            'output_tokens' => 7000,
            'max_context_tokens' => 5000,
        ]);

        $thread->messages()->create([
            'role' => 'system',
            'content' => 'Already compacted memory',
            'is_compacted' => true,
            'is_memory' => true,
        ]);
        $thread->messages()->create(['role' => 'user', 'content' => 'short raw']);
        $thread->messages()->create(['role' => 'assistant', 'content' => 'short reply']);

        $result = app(CompactionService::class)->compact($thread->fresh(), false);

        $this->assertFalse($result->triggered);
        $this->assertNull($thread->fresh()->compacted_at);
        $this->assertSame(0, $thread->fresh()->messages()->where('is_memory', true)->where('is_compacted', false)->count());
    }

    public function test_next_compaction_reuses_latest_uncompacted_memory_with_new_raw_messages(): void
    {
        $this->seed();

        Http::fake([
            'openrouter.ai/*' => Http::sequence()
                ->push([
                    'choices' => [
                        ['message' => ['content' => 'Compressed memory round 1'], 'finish_reason' => 'stop'],
                    ],
                    'usage' => ['prompt_tokens' => 90, 'completion_tokens' => 10],
                ])
                ->push([
                    'choices' => [
                        ['message' => ['content' => 'Compressed memory round 2'], 'finish_reason' => 'stop'],
                    ],
                    'usage' => ['prompt_tokens' => 110, 'completion_tokens' => 11],
                ]),
        ]);

        $family = \App\Models\FamilyAgent::query()->where('number', 'default')->firstOrFail();
        $thread = Thread::query()->create([
            'public_id' => (string) Str::uuid(),
            'family_agent_id' => $family->id,
            'provider_id' => $family->default_provider_id,
            'provider_model_id' => $family->default_provider_model_id,
            'title' => 'Compaction chaining',
            'input_tokens' => 1,
            'output_tokens' => 1,
            'max_context_tokens' => $family->max_context_tokens,
        ]);

        $thread->messages()->create(['role' => 'user', 'content' => 'first raw']);
        $thread->messages()->create(['role' => 'assistant', 'content' => 'first reply']);

        $service = app(CompactionService::class);
        $this->assertTrue($service->compact($thread, true)->triggered);

        $firstMemory = $thread->fresh()->messages()->where('is_memory', true)->latest('id')->firstOrFail();
        $this->assertFalse($firstMemory->is_compacted);

        $thread->messages()->create(['role' => 'user', 'content' => 'second raw']);
        $thread->messages()->create(['role' => 'assistant', 'content' => 'second reply']);

        $this->assertTrue($service->compact($thread->fresh(), true)->triggered);

        $allMemory = $thread->fresh()->messages()->where('is_memory', true)->orderBy('id')->get();
        $latestMemory = $allMemory->last();

        $this->assertCount(2, $allMemory);
        $this->assertTrue($allMemory->first()->is_compacted);
        $this->assertFalse($latestMemory->is_compacted);
        $this->assertSame('Compressed memory round 2', $latestMemory->content);
        $this->assertSame(2, $latestMemory->metadata['raw_message_count']);
        $this->assertSame(1, $latestMemory->metadata['pending_memory_count']);

        $history = app(HistoryBuilder::class)->build($family, $thread->fresh());
        $historyContent = collect($history)->pluck('content');

        $this->assertTrue($historyContent->contains('Compressed memory round 2'));
        $this->assertFalse($historyContent->contains('Compressed memory round 1'));

        Http::assertSentCount(2);
        Http::assertSent(function ($request) {
            $payload = $request->data();
            $content = $payload['messages'][0]['content'] ?? '';

            return str_contains($content, 'Compressed memory round 1')
                && str_contains($content, 'second raw')
                && str_contains($content, 'second reply');
        });
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
