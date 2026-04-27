<x-layouts.app title="Customer Dashboard - ThreadCore">
    <main class="shell">
        @include('customer._chrome', ['title' => $account->name])
        <section class="grid two">
            <article class="panel stat"><span class="muted">Plan</span><strong>{{ $account->activeSubscription?->plan?->name ?? 'None' }}</strong></article>
            <article class="panel stat"><span class="muted">Threads</span><strong>{{ $account->threads->count() }}</strong></article>
            <article class="panel stat"><span class="muted">API Keys</span><strong>{{ $account->apiKeys->count() }}</strong></article>
            <article class="panel stat"><span class="muted">Status</span><strong>{{ $account->status }}</strong></article>
        </section>
    </main>
</x-layouts.app>
