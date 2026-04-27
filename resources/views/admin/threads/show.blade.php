<x-layouts.app title="Thread - ThreadCore">
    <main class="shell">
        @include('admin._chrome', ['title' => 'Thread Conversation'])

        <div class="actions" style="margin-bottom: 16px;">
            <a class="button" href="{{ route('admin.threads.index') }}">Back to threads</a>
            <a class="button" href="{{ route('admin.threads.export', $thread) }}">Export md</a>
        </div>

        <section class="grid two">
            <article class="panel panel-pad">
                <div class="section-title" style="margin-top: 0;">
                    <h2>Thread details</h2>
                </div>
                <table>
                    <tbody>
                        <tr><th>Title</th><td>{{ $thread->title ?: 'Untitled' }}</td></tr>
                        <tr><th>Public ID</th><td>{{ $thread->public_id }}</td></tr>
                        <tr><th>Customer</th><td>{{ $thread->customerAccount?->name }}</td></tr>
                        <tr><th>Family</th><td>{{ $thread->familyAgent?->name }}</td></tr>
                        <tr><th>Route</th><td>{{ $thread->provider?->slug }} / {{ $thread->providerModel?->model_key }}</td></tr>
                        <tr><th>API key</th><td>{{ $thread->apiKey?->name }}</td></tr>
                        <tr><th>Tokens</th><td>{{ $thread->input_tokens + $thread->output_tokens }}</td></tr>
                        <tr><th>Context cap</th><td>{{ number_format($thread->max_context_tokens) }}</td></tr>
                        <tr><th>Compacted</th><td>{{ $thread->compacted_at ? $thread->compacted_at->format('M j, Y g:i A') : 'No' }}</td></tr>
                    </tbody>
                </table>
            </article>

            <article class="panel panel-pad">
                <div class="section-title" style="margin-top: 0;">
                    <h2>Metadata</h2>
                </div>
                <pre style="margin: 0; white-space: pre-wrap;">{{ json_encode($thread->metadata, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
            </article>
        </section>

        <section class="panel panel-pad" style="margin-top: 16px;">
            <div class="section-title" style="margin-top: 0;">
                <h2>Messages</h2>
                <span class="muted">{{ $thread->messages->count() }} total</span>
            </div>

            @forelse ($thread->messages as $message)
                <article class="panel" style="margin-bottom: 12px; padding: 16px;">
                    <div class="provider-head" style="margin-bottom: 10px;">
                        <div>
                            <strong>{{ ucfirst($message->role) }}</strong>
                            <div class="muted">
                                {{ $message->created_at?->format('M j, Y g:i A') }}
                                @if ($message->command)
                                    · command: {{ $message->command }}
                                @endif
                            </div>
                        </div>
                        <div style="display: flex; gap: 8px; flex-wrap: wrap; justify-content: flex-end;">
                            @if ($message->is_memory)
                                <span class="badge ok">Memory</span>
                            @endif
                            @if ($message->is_compacted)
                                <span class="badge">Compacted</span>
                            @endif
                            @if ($message->is_forgotten)
                                <span class="badge">Forgotten</span>
                            @endif
                        </div>
                    </div>
                    <p style="margin: 0; white-space: pre-wrap; line-height: 1.6;">{{ $message->content }}</p>
                    <div class="muted" style="margin-top: 10px; font-size: 13px;">
                        Input {{ $message->input_tokens ?: 0 }} · Output {{ $message->output_tokens ?: 0 }}
                    </div>
                </article>
            @empty
                <div class="empty">This thread has no saved messages yet.</div>
            @endforelse
        </section>
    </main>
</x-layouts.app>
