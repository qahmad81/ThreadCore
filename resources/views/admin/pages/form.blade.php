<x-layouts.app title="Page - ThreadCore">
    <main class="shell">
        @include('admin._chrome', ['title' => $page->exists ? 'Edit Page' : 'New Page'])

        <form class="panel panel-pad" method="POST" action="{{ $page->exists ? route('admin.pages.update', $page) : route('admin.pages.store') }}">
            @csrf
            @if ($page->exists)
                @method('PUT')
            @endif
            @php
                $blocksText = old('blocks_text', collect($page->blocks ?? [])->map(fn ($b) => ($b['label'] ?? '').' | '.($b['title'] ?? '').' | '.($b['body'] ?? ''))->implode("\n\n"));
            @endphp

            <div class="grid two">
                <div><label>Slug</label><input name="slug" type="text" value="{{ old('slug', $page->slug) }}" required></div>
                <div><label>Title</label><input name="title" type="text" value="{{ old('title', $page->title) }}" required></div>
            </div>
            <label>Headline</label>
            <input name="headline" type="text" value="{{ old('headline', $page->headline) }}" required>
            <label>Summary</label>
            <textarea name="summary">{{ old('summary', $page->summary) }}</textarea>
            <label>Blocks</label>
            <textarea name="blocks_text" placeholder="Label | Title | Body">{{ $blocksText }}</textarea>
            <label><input name="is_published" type="checkbox" value="1" @checked(old('is_published', $page->is_published))> Published</label>
            <button class="button primary" type="submit">Save page</button>
        </form>
    </main>
</x-layouts.app>
