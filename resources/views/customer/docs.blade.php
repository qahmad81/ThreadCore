<x-layouts.app title="Docs - ThreadCore">
    <main class="shell">
        @include('customer._chrome', ['title' => 'Gateway Docs'])

        <section class="grid two" style="margin-bottom: 16px;">
            <article class="panel panel-pad">
                <div class="section-title" style="margin-top: 0;">
                    <h2>Active customer workspace</h2>
                </div>
                <table>
                    <tbody>
                        <tr><th>Account</th><td>{{ $account->name }}</td></tr>
                        <tr><th>Status</th><td>{{ ucfirst($account->status) }}</td></tr>
                        <tr><th>Plan</th><td>{{ $plan?->name ?? 'No active plan' }}</td></tr>
                        <tr><th>API keys</th><td>{{ $activeApiKeyCount }}</td></tr>
                        <tr><th>Recent threads</th><td>{{ $recentThreadCount }}</td></tr>
                    </tbody>
                </table>
            </article>

            <article class="panel panel-pad">
                <div class="section-title" style="margin-top: 0;">
                    <h2>Current API key usage</h2>
                </div>
                <p class="muted">Use a bearer token that starts with <code>tc_live_</code>.</p>
                @if ($activeApiKeys->isEmpty())
                    <div class="empty">No active API keys yet. Create one from the customer dashboard first.</div>
                @else
                    <table>
                        <thead>
                            <tr><th>Key</th><th>Status</th></tr>
                        </thead>
                        <tbody>
                            @foreach ($activeApiKeys as $apiKey)
                                <tr>
                                    <td><strong>{{ $apiKey->name }}</strong><div class="muted">{{ $apiKey->prefix }}...</div></td>
                                    <td>{{ $apiKey->revoked_at ? 'Revoked' : 'Active' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </article>
        </section>

        <section class="panel panel-pad" style="margin-top: 16px;">
            <div class="section-title" style="margin-top: 0;">
                <h2>Active agents</h2>
                <span class="muted">Choose the family agent number when creating a thread</span>
            </div>
            @if ($familyAgents->isEmpty())
                <div class="empty">No enabled family agents available.</div>
            @else
                <table>
                    <thead>
                        <tr>
                            <th>Agent Name</th>
                            <th>Agent Code</th>
                            <th>Description</th>
                            <th>Default (provider &amp; model)</th>
                            <th>Context Length</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($familyAgents as $family)
                            <tr>
                                <td><strong>{{ $family->name }}</strong></td>
                                <td><code>{{ $family->number }}</code></td>
                                <td>{{ $family->description ?: 'No description set' }}</td>
                                <td>{{ $family->defaultProvider?->name ?? 'None' }} / {{ $family->defaultProviderModel?->model_key ?? 'None' }}</td>
                                <td>{{ number_format($family->max_context_tokens) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </section>

        <section class="panel panel-pad">
            <div class="section-title" style="margin-top: 0;">
                <h2>Create a thread</h2>
                <span class="muted">Required fields: `family_agent`, `content`</span>
            </div>
            <p class="muted">This endpoint opens a new conversation and returns a `thread_id` you can reuse later.</p>
<pre>curl -X POST {{ url('/api/v1/threads') }} \
  -H "Authorization: Bearer tc_live_your_key" \
  -H "Content-Type: application/json" \
  -d '{
    "family_agent": "default",
    "content": "Hello ThreadCore"
  }'</pre>
            <table>
                <thead>
                    <tr><th>Field</th><th>Required</th><th>Meaning</th></tr>
                </thead>
                <tbody>
                    <tr><td><code>family_agent</code></td><td>Yes</td><td>Family agent number, for example <code>default</code>.</td></tr>
                    <tr><td><code>content</code></td><td>Yes</td><td>The first user message to send into the new thread.</td></tr>
                    <tr><td><code>provider</code></td><td>No</td><td>Optional provider override when the account is allowed to choose one.</td></tr>
                    <tr><td><code>model</code></td><td>No</td><td>Optional provider model override.</td></tr>
                    <tr><td><code>inner_provider</code></td><td>No</td><td>Optional OpenRouter inner provider selection.</td></tr>
                    <tr><td><code>encryption_key</code></td><td>No</td><td>Optional request-side encryption hint stored in metadata.</td></tr>
                </tbody>
            </table>
        </section>

        <section class="panel panel-pad" style="margin-top: 16px;">
            <div class="section-title" style="margin-top: 0;">
                <h2>Reply to an existing thread</h2>
                <span class="muted">Required fields: `content` plus the thread public ID</span>
            </div>
            <p class="muted">Use this endpoint when you already have a conversation and want to continue it.</p>
<pre>curl -X POST {{ url('/api/v1/threads') }}/THREAD_PUBLIC_ID/messages \
  -H "Authorization: Bearer tc_live_your_key" \
  -H "Content-Type: application/json" \
  -d '{
    "content": "Continue the conversation"
  }'</pre>
            <table>
                <thead>
                    <tr><th>Field</th><th>Required</th><th>Meaning</th></tr>
                </thead>
                <tbody>
                    <tr><td><code>public_id</code></td><td>Path</td><td>The thread identifier returned from the create-thread response.</td></tr>
                    <tr><td><code>content</code></td><td>Yes</td><td>The new user message to append to the existing thread.</td></tr>
                    <tr><td><code>provider</code></td><td>No</td><td>Optional provider override for this message.</td></tr>
                    <tr><td><code>model</code></td><td>No</td><td>Optional provider model override for this message.</td></tr>
                    <tr><td><code>inner_provider</code></td><td>No</td><td>Optional OpenRouter inner provider selection.</td></tr>
                    <tr><td><code>encryption_key</code></td><td>No</td><td>Optional request-side encryption hint stored in metadata.</td></tr>
                </tbody>
            </table>
        </section>

        <section class="panel panel-pad" style="margin-top: 16px;">
            <div class="section-title" style="margin-top: 0;">
                <h2>Supported commands</h2>
                <span class="muted">Place these at the start of `content`</span>
            </div>
            <table>
                <thead>
                    <tr><th>Command</th><th>Behavior</th><th>Example</th></tr>
                </thead>
                <tbody>
                    <tr>
                        <td><code>/whisper</code></td>
                        <td>Calls the model, then stores both turns as forgotten so they stay out of future history.</td>
                        <td><code>/whisper private note</code></td>
                    </tr>
                    <tr>
                        <td><code>/skip</code></td>
                        <td>Calls the model with reduced memory, then stores both turns as forgotten so they stay out of future history.</td>
                        <td><code>/skip answer this without memory</code></td>
                    </tr>
                    <tr>
                        <td><code>/dayend</code></td>
                        <td>Handled programmatically with no stored turn. It returns a JSON status message and may still trigger internal compaction.</td>
                        <td><code>/dayend summarize today</code></td>
                    </tr>
                    <tr>
                        <td><code>/forget</code></td>
                        <td>Handled programmatically, marks matching memory items as forgotten, and stores the command as forgotten.</td>
                        <td><code>/forget alpha</code></td>
                    </tr>
                </tbody>
            </table>
        </section>
    </main>
</x-layouts.app>
