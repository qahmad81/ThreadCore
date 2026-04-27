<x-layouts.app title="Providers - ThreadCore">
    <main class="shell">
        <header class="topbar">
            <div class="brand">
                <strong>ThreadCore Admin</strong>
                <span>Provider bootstrap verification</span>
            </div>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="button" type="submit">Sign out</button>
            </form>
        </header>

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
