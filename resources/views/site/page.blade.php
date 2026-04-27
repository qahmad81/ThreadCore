<x-layouts.app title="{{ $page->title }}">
    <main class="landing">
        <nav class="landing-nav" aria-label="Primary navigation">
            <strong><a href="{{ route('landing') }}" style="text-decoration: none;">ThreadCore</a></strong>
            <div class="actions">
                @foreach ($publishedPages ?? [] as $navPage)
                    <a class="button" href="{{ route('site.page', $navPage->slug) }}">{{ $navPage->title }}</a>
                @endforeach
                <a class="button" href="{{ route('customer.dashboard') }}">Customer dashboard</a>
                <a class="button" href="{{ route('login') }}">Sign in</a>
            </div>
        </nav>

        <section class="landing-hero">
            <p class="eyebrow">ThreadCore</p>
            <h1>{{ $page->headline }}</h1>
            @if ($page->summary)
                <p class="hero-copy">{{ $page->summary }}</p>
            @endif
        </section>

        @if ($page->blocks)
            <section class="landing-band" aria-label="{{ $page->title }} sections">
                @foreach ($page->blocks as $block)
                    <article>
                        <span>{{ $block['label'] ?? '' }}</span>
                        <strong>{{ $block['title'] ?? '' }}</strong>
                        <p>{{ $block['body'] ?? '' }}</p>
                    </article>
                @endforeach
            </section>
        @endif
    </main>
</x-layouts.app>
