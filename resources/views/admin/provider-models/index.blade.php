<x-layouts.app title="Models - ThreadCore">
    <main class="shell">
        @include('admin._chrome', ['title' => 'Provider Models'])
        <p><a class="button primary" href="{{ route('admin.provider-models.create') }}">New model</a></p>
        <section class="panel panel-pad">
            <table>
                <thead><tr><th>Model</th><th>Provider</th><th>Role</th><th>Actions</th></tr></thead>
                <tbody>
                    @foreach ($models as $model)
                        <tr>
                            <td><strong>{{ $model->name }}</strong><div class="muted">{{ $model->model_key }}</div></td>
                            <td>{{ $model->provider?->name }}</td>
                            <td>{{ $model->role ?: 'general' }}</td>
                            <td class="actions">
                                <a class="button" href="{{ route('admin.provider-models.edit', $model) }}">Edit</a>
                                <form method="POST" action="{{ route('admin.provider-models.destroy', $model) }}">
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
