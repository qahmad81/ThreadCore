<x-layouts.app title="Customers - ThreadCore">
    <main class="shell">
        @include('admin._chrome', ['title' => 'Customers'])
        <section class="panel panel-pad">
            <table>
                <thead><tr><th>Customer</th><th>Plan</th><th>API Keys</th><th>Status</th></tr></thead>
                <tbody>
                    @foreach ($customers as $customer)
                        <tr>
                            <td><strong>{{ $customer->name }}</strong><div class="muted">{{ $customer->slug }}</div></td>
                            <td>{{ $customer->activeSubscription?->plan?->name ?? 'No plan' }}</td>
                            <td>{{ $customer->apiKeys->count() }}</td>
                            <td>{{ $customer->status }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </section>
    </main>
</x-layouts.app>
