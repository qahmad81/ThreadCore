<x-layouts.app title="API Keys - ThreadCore">
    <main class="shell">
        @include('admin._chrome', ['title' => 'API Keys'])
        <section class="panel panel-pad">
            <table>
                <thead><tr><th>Key</th><th>Customer</th><th>Last used</th><th>Status</th></tr></thead>
                <tbody>
                    @foreach ($keys as $key)
                        <tr>
                            <td><strong>{{ $key->name }}</strong><div class="muted"><code>{{ $key->prefix }}</code></div></td>
                            <td>{{ $key->customerAccount?->name }}</td>
                            <td>{{ $key->last_used_at?->diffForHumans() ?? 'Never' }}</td>
                            <td>{{ $key->revoked_at ? 'Revoked' : 'Active' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </section>
    </main>
</x-layouts.app>
