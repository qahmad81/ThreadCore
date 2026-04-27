<x-layouts.app title="Threads - ThreadCore">
    <main class="shell">
        @include('admin._chrome', ['title' => 'Threads'])
        <section class="panel panel-pad">
            <table>
                <thead><tr><th>Thread</th><th>Customer</th><th>Family</th><th>Route</th><th>Tokens</th><th>Actions</th></tr></thead>
                <tbody>
                    @foreach ($threads as $thread)
                        <tr>
                            <td><strong>{{ $thread->title ?: 'Untitled' }}</strong><div class="muted">{{ $thread->public_id }}</div></td>
                            <td>{{ $thread->customerAccount?->name }}</td>
                            <td>{{ $thread->familyAgent?->name }}</td>
                            <td>{{ $thread->provider?->slug }} / {{ $thread->providerModel?->model_key }}</td>
                            <td>{{ $thread->input_tokens + $thread->output_tokens }}</td>
                            <td class="actions">
                                <a class="button" href="{{ route('admin.threads.show', $thread) }}">View conversation</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </section>
    </main>
</x-layouts.app>
