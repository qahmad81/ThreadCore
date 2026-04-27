<x-layouts.app title="Usage - ThreadCore">
    <main class="shell">
        @include('customer._chrome', ['title' => 'Usage'])
        <section class="grid two">
            <article class="panel stat"><span class="muted">Requests used</span><strong>{{ $subscription?->requests_used ?? 0 }}</strong></article>
            <article class="panel stat"><span class="muted">Tokens used</span><strong>{{ $subscription?->tokens_used ?? 0 }}</strong></article>
        </section>
        <section class="panel panel-pad" style="margin-top: 16px;">
            <table>
                <thead><tr><th>Status</th><th>Thread</th><th>Tokens</th><th>Created</th></tr></thead>
                <tbody>
                    @foreach ($logs as $log)
                        <tr>
                            <td>{{ $log->status }}</td>
                            <td>{{ $log->thread_id }}</td>
                            <td>{{ $log->input_tokens + $log->output_tokens }}</td>
                            <td>{{ $log->created_at->toDateTimeString() }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </section>
    </main>
</x-layouts.app>
