<x-layouts.app title="API Keys - ThreadCore">
    <main class="shell">
        @include('customer._chrome', ['title' => 'API Keys'])
        @if ($plainToken)
            <div class="alert ok">New API key: <code>{{ $plainToken }}</code>. Store it now; it will not be shown again.</div>
        @endif
        <form class="panel panel-pad" method="POST" action="{{ route('customer.api-keys.store') }}">
            @csrf
            <label>Name</label>
            <input name="name" type="text" placeholder="Production gateway" required>
            <button class="button primary" type="submit" style="margin-top: 12px;">Create API key</button>
        </form>
        <section class="panel panel-pad" style="margin-top: 16px;">
            <table>
                <thead><tr><th>Name</th><th>Prefix</th><th>Last used</th><th>Status</th><th>Actions</th></tr></thead>
                <tbody>
                    @foreach ($keys as $key)
                        <tr>
                            <td>{{ $key->name }}</td>
                            <td><code>{{ $key->prefix }}</code></td>
                            <td>{{ $key->last_used_at?->diffForHumans() ?? 'Never' }}</td>
                            <td>{{ $key->revoked_at ? 'Revoked' : 'Active' }}</td>
                            <td>
                                @unless ($key->revoked_at)
                                    <form method="POST" action="{{ route('customer.api-keys.destroy', $key) }}">
                                        @csrf @method('DELETE')
                                        <button class="button danger" type="submit">Revoke</button>
                                    </form>
                                @endunless
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </section>
    </main>
</x-layouts.app>
