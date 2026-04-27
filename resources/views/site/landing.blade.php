<x-layouts.app title="{{ $page?->title ?? 'ThreadCore' }}">
    <main class="landing">
        <nav class="landing-nav" aria-label="Primary navigation">
            <strong>ThreadCore</strong>
            <a class="button" href="{{ route('login') }}">Sign in</a>
        </nav>

        <section class="landing-hero">
            <p class="eyebrow">AI thread orchestration</p>
            <h1>{{ $page?->headline ?? 'ThreadCore' }}</h1>
            <p class="hero-copy">
                {{ $page?->summary ?? 'Manage providers, family agents, memory, and gateway requests from one Laravel microsaas.' }}
            </p>
            <div class="hero-actions">
                <a class="button primary" href="{{ route('login') }}">Open workspace</a>
                <span class="muted">Provider-aware by design</span>
            </div>
        </section>

        <section class="landing-band" aria-label="ThreadCore capabilities">
            @foreach (($page?->blocks ?? []) as $block)
                <article>
                    <span>{{ $block['label'] ?? '' }}</span>
                    <strong>{{ $block['title'] ?? '' }}</strong>
                    <p>{{ $block['body'] ?? '' }}</p>
                </article>
            @endforeach
        </section>
    </main>
</x-layouts.app>
