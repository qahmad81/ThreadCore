<x-layouts.app title="Customer Dashboard - ThreadCore">
    <main class="shell">
        @include('customer._chrome', ['title' => $account->name])

        @if (session('plain_api_token'))
            <div class="alert ok">New API key: <code>{{ session('plain_api_token') }}</code>. Store it now; it will not be shown again.</div>
        @endif

        <section class="grid two">
            <article class="panel stat"><span class="muted">Plan</span><strong>{{ $plan?->name ?? 'None' }}</strong></article>
            <article class="panel stat"><span class="muted">Requests</span><strong>{{ $subscription?->requests_used ?? 0 }} / {{ $plan?->monthly_request_limit ?? 0 }}</strong><div class="progress"><span style="width: {{ $requestPercent }}%"></span></div></article>
            <article class="panel stat"><span class="muted">Tokens</span><strong>{{ $subscription?->tokens_used ?? 0 }} / {{ $plan?->monthly_token_limit ?? 0 }}</strong><div class="progress"><span style="width: {{ $tokenPercent }}%"></span></div></article>
            <article class="panel stat"><span class="muted">Status</span><strong>{{ $account->status }}</strong></article>
        </section>

        @if ($account->apiKeys->isEmpty())
            <section class="panel panel-pad" style="margin-top: 16px;">
                <strong>Create your first API key</strong>
                <p class="muted">Your gateway calls need a bearer token. Create one here, then use it with the example in the docs.</p>
                <form method="POST" action="{{ route('customer.api-keys.store') }}">
                    @csrf
                    <label>Name</label>
                    <input name="name" type="text" value="Default gateway key" required>
                    <button class="button primary" type="submit" style="margin-top: 12px;">Create API key</button>
                    <a class="button" href="{{ route('customer.docs') }}" style="margin-top: 12px;">View docs</a>
                </form>
            </section>
        @endif

        <div class="section-title">
            <h2>Recent API keys</h2>
            <a class="button" href="{{ route('customer.api-keys.index') }}">Manage keys</a>
        </div>
        <section class="panel panel-pad">
            @if ($account->apiKeys->isEmpty())
                <div class="empty">No API keys yet.</div>
            @else
                <table>
                    <thead><tr><th>Name</th><th>Prefix</th><th>Last used</th><th>Status</th></tr></thead>
                    <tbody>
                        @foreach ($account->apiKeys as $key)
                            <tr>
                                <td>{{ $key->name }}</td>
                                <td><code>{{ $key->prefix }}</code></td>
                                <td>{{ $key->last_used_at?->diffForHumans() ?? 'Never' }}</td>
                                <td>{{ $key->revoked_at ? 'Revoked' : 'Active' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </section>

        <div class="section-title">
            <h2>Recent gateway requests</h2>
            <a class="button" href="{{ route('customer.usage') }}">View usage</a>
        </div>
        <section class="panel panel-pad">
            @if ($recentLogs->isEmpty())
                <div class="empty">No gateway requests yet.</div>
            @else
                <table>
                    <thead><tr><th>Status</th><th>Thread</th><th>Tokens</th><th>Created</th></tr></thead>
                    <tbody>
                        @foreach ($recentLogs as $log)
                            <tr>
                                <td>{{ $log->status }}</td>
                                <td>{{ $log->thread_id }}</td>
                                <td>{{ $log->input_tokens + $log->output_tokens }}</td>
                                <td>{{ $log->created_at->toDateTimeString() }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </section>
    </main>
</x-layouts.app>
