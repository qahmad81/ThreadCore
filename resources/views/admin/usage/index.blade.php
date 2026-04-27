<x-layouts.app title="Usage - ThreadCore">
    <main class="shell">
        @include('admin._chrome', ['title' => 'Usage'])
        <section class="grid two">
            <article class="panel stat"><span class="muted">Requests</span><strong>{{ $requests }}</strong></article>
            <article class="panel stat"><span class="muted">Tokens</span><strong>{{ $tokens }}</strong></article>
        </section>
        <section class="panel panel-pad" style="margin-top: 16px;">
            <table>
                <thead><tr><th>Status</th><th>Thread</th><th>Tokens</th><th>Error</th></tr></thead>
                <tbody>
                    @foreach ($logs as $log)
                        <tr>
                            <td>{{ $log->status }}</td>
                            <td>{{ $log->thread?->public_id }}</td>
                            <td>{{ $log->input_tokens + $log->output_tokens }}</td>
                            <td>{{ $log->error_message }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </section>
    </main>
</x-layouts.app>
