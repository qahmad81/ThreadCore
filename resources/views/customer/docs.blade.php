<x-layouts.app title="Docs - ThreadCore">
    <main class="shell">
        @include('customer._chrome', ['title' => 'Gateway Docs'])
        <section class="panel panel-pad">
            <p class="muted">Use your API key as a bearer token.</p>
<pre>curl -X POST {{ url('/api/v1/threads') }} \
  -H "Authorization: Bearer tc_live_your_key" \
  -H "Content-Type: application/json" \
  -d '{"family_agent":"default","content":"Hello ThreadCore"}'</pre>
        </section>
    </main>
</x-layouts.app>
