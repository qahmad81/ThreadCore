<x-layouts.app title="Threads - ThreadCore">
    <main class="shell">
        @include('customer._chrome', ['title' => 'Threads'])

        <div class="section-title">
            <h2>Your conversations</h2>
            <a class="button" href="{{ route('customer.docs') }}">Docs</a>
        </div>

        <section class="panel panel-pad">
            @if ($threads->isEmpty())
                <div class="empty">No threads yet. Create one from the gateway docs or start from the API.</div>
            @else
                <table>
                    <thead>
                        <tr>
                            <th>Thread</th>
                            <th>Family</th>
                            <th>Route</th>
                            <th>Tokens</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($threads as $thread)
                            <tr>
                                <td>
                                    <strong>{{ $thread->title ?: 'Untitled' }}</strong>
                                    <div class="muted">{{ $thread->public_id }}</div>
                                </td>
                                <td>{{ $thread->familyAgent?->name }}</td>
                                <td>{{ $thread->provider?->slug }} / {{ $thread->providerModel?->model_key }}</td>
                                <td>{{ $thread->input_tokens + $thread->output_tokens }}</td>
                                <td class="actions">
                                    <a class="button" href="{{ route('customer.threads.show', $thread->public_id) }}">View conversation</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <div style="margin-top: 16px;">
                    {{ $threads->links() }}
                </div>
            @endif
        </section>
    </main>
</x-layouts.app>
