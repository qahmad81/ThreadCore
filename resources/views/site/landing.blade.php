<x-layouts.app title="{{ $page?->title ?? 'ThreadCore' }}">
    <main class="landing">
        <nav class="landing-nav" aria-label="Primary navigation">
            <div class="landing-wordmark-wrap">
                <img class="landing-wordmark-icon" src="/icons/logo/apple-touch-icon.png" alt="ThreadCore public icon">
                <strong class="landing-wordmark">ThreadCore</strong>
            </div>
            <div class="actions">
                @foreach ($publishedPages ?? [] as $navPage)
                    <a class="button" href="{{ route('site.page', $navPage->slug) }}">{{ $navPage->title }}</a>
                @endforeach
                <a class="button" href="{{ route('customer.dashboard') }}">Customer dashboard</a>
                <a class="button" href="{{ route('login') }}">Sign in</a>
            </div>
        </nav>

        <section class="landing-hero">
            <div class="hero-main">
                <div>
                    <p class="eyebrow">AI thread orchestration</p>
                    <h1>{{ $page?->headline ?? 'ThreadCore' }}</h1>
                    <p class="hero-copy">
                        {{ $page?->summary ?? 'Manage providers, family agents, memory, and gateway requests from one Laravel microsaas.' }}
                    </p>
                    <div class="hero-actions">
                        <a class="button primary" href="{{ route('customer.dashboard') }}">Open customer dashboard</a>
                        <span class="muted">Provider-aware by design</span>
                    </div>
                </div>

                <aside class="hero-console" aria-label="ThreadCore workflow preview">
                    <div class="console-head">
                        <span></span>
                        <strong>Gateway flow</strong>
                    </div>
                    <ol>
                        <li><span>01</span>Create an API key</li>
                        <li><span>02</span>Open a thread</li>
                        <li><span>03</span>Route to OpenRouter or Ollama</li>
                        <li><span>04</span>Track tokens and memory</li>
                    </ol>
                </aside>
            </div>
        </section>

        <section class="landing-band" aria-label="ThreadCore capabilities">
            <div class="landing-band-grid">
                @foreach (($page?->blocks ?? []) as $block)
                    <article>
                        <span>{{ $block['label'] ?? '' }}</span>
                        <strong>{{ $block['title'] ?? '' }}</strong>
                        <p>{{ $block['body'] ?? '' }}</p>
                    </article>
                @endforeach
            </div>
        </section>

        <footer class="landing-footer" aria-label="ThreadCore footer">
            <div class="landing-footer-row">
                <span>ThreadCore is built for customer dashboards, provider routing, and memory-aware gateways.</span>
                <div class="landing-footer-links">
                    <a href="{{ route('customer.dashboard') }}">Customer dashboard</a>
                    <a href="{{ route('login') }}">Sign in</a>
                </div>
            </div>
        </footer>
    </main>
</x-layouts.app>
