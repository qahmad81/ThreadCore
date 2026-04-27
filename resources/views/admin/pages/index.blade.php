<x-layouts.app title="Pages - ThreadCore">
    <main class="shell">
        @include('admin._chrome', ['title' => 'CMS Pages'])
        <p><a class="button primary" href="{{ route('admin.pages.create') }}">New page</a></p>

        <section class="panel panel-pad">
            <table>
                <thead><tr><th>Page</th><th>Status</th><th>Actions</th></tr></thead>
                <tbody>
                    @foreach ($pages as $page)
                        <tr>
                            <td><strong>{{ $page->title }}</strong><div class="muted">/{{ $page->slug }}</div></td>
                            <td>{{ $page->is_published ? 'Published' : 'Draft' }}</td>
                            <td class="actions">
                                @if ($page->is_published)
                                    <a class="button" href="{{ $page->slug === 'landing' ? route('landing') : route('site.page', $page->slug) }}">View</a>
                                @endif
                                <a class="button" href="{{ route('admin.pages.edit', $page) }}">Edit</a>
                                <form method="POST" action="{{ route('admin.pages.destroy', $page) }}">
                                    @csrf @method('DELETE')
                                    <button class="button danger" type="submit">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </section>
    </main>
</x-layouts.app>
