<x-layouts.app title="Providers - ThreadCore">
    <main class="shell">
        @include('admin._chrome', ['title' => 'Providers'])
        <p><a class="button primary" href="{{ route('admin.providers.create') }}">New provider</a></p>

        <section class="grid">
            @forelse ($providers as $provider)
                <article class="panel provider">
                    <div class="provider-head">
                        <div>
                            <h2>{{ $provider->name }}</h2>
                            <div class="muted">{{ $provider->driver }} · {{ $provider->base_url ?: 'local runtime' }}</div>
                        </div>
                        <div style="display: flex; gap: 8px; flex-wrap: wrap; justify-content: flex-end;">
                            @if ($provider->is_default)
                                <span class="badge ok">Default</span>
                            @endif
                            <span class="badge">{{ $provider->is_enabled ? 'Enabled' : 'Disabled' }}</span>
                        </div>
                    </div>

                    <table>
                        <thead>
                            <tr>
                                <th>Model</th>
                                <th>Role</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($provider->models as $model)
                                <tr>
                                    <td>
                                        <strong>{{ $model->name }}</strong>
                                        <div class="muted">{{ $model->model_key }}</div>
                                    </td>
                                    <td>{{ $model->role ?: 'general' }}</td>
                                    <td>{{ $model->is_enabled ? 'Enabled' : 'Disabled' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <div class="actions" style="margin-top: 14px;">
                        <a class="button" href="{{ route('admin.providers.edit', $provider) }}">Edit</a>
                        <form method="POST" action="{{ route('admin.providers.destroy', $provider) }}">
                            @csrf
                            @method('DELETE')
                            <button class="button danger" type="submit">Delete</button>
                        </form>
                    </div>
                </article>
            @empty
                <section class="panel panel-pad">
                    <strong>No providers seeded yet.</strong>
                    <p class="muted">Run the database seed command to create OpenRouter and Ollama records.</p>
                </section>
            @endforelse
        </section>
    </main>
</x-layouts.app>
