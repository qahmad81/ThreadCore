<x-layouts.app title="Resources - ThreadCore">
    <main class="shell">
        @include('admin._chrome', ['title' => 'Resources'])

        <div class="actions" style="margin-bottom: 16px;">
            <a class="button primary" href="{{ route('admin.providers.create') }}">New provider</a>
            <a class="button" href="{{ route('admin.provider-models.create') }}">New model</a>
        </div>

        <section class="grid">
            @forelse ($providers as $provider)
                <article class="panel provider" data-provider-card>
                    <div class="provider-head">
                        <div>
                            <h2>{{ $provider->name }}</h2>
                            <div class="muted">{{ $provider->driver }} - {{ $provider->base_url ?: 'local runtime' }}</div>
                        </div>
                        <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap; justify-content: flex-end;">
                            <button class="provider-toggle" type="button" aria-expanded="false" aria-controls="provider-models-{{ $provider->id }}" data-provider-toggle>+</button>
                            <span class="badge {{ $provider->is_enabled ? 'ok' : 'warn' }}">{{ $provider->is_enabled ? 'Enabled' : 'Disabled' }}</span>
                            @if ($provider->is_default)
                                <span class="badge ok">Default</span>
                            @endif
                        </div>
                    </div>

                    <div class="provider-models" id="provider-models-{{ $provider->id }}" data-provider-models hidden>
                        <div class="provider-models-inner">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Model</th>
                                        <th>Role</th>
                                        <th>Status</th>
                                        <th>Actions</th>
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
                                            <td>
                                                <div class="actions">
                                                    <a class="button" href="{{ route('admin.provider-models.edit', $model) }}">Edit</a>
                                                    <form method="POST" action="{{ route('admin.provider-models.toggle-enabled', $model) }}">
                                                        @csrf
                                                        @method('PATCH')
                                                        <button class="button" type="submit">{{ $model->is_enabled ? 'Disable' : 'Enable' }}</button>
                                                    </form>
                                                    <form method="POST" action="{{ route('admin.provider-models.destroy', $model) }}" data-confirm="Delete this model?">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button class="button danger" type="submit">Delete</button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="actions" style="margin-top: 14px;">
                        <a class="button" href="{{ route('admin.providers.edit', $provider) }}">Edit</a>
                        <form method="POST" action="{{ route('admin.providers.toggle-enabled', $provider) }}">
                            @csrf
                            @method('PATCH')
                            <button class="button" type="submit">{{ $provider->is_enabled ? 'Disable' : 'Enable' }}</button>
                        </form>
                        <form method="POST" action="{{ route('admin.providers.destroy', $provider) }}" data-confirm="Delete this provider? All models under it will be deleted too. Are you sure?">
                            @csrf
                            @method('DELETE')
                            <button class="button danger" type="submit">Delete</button>
                        </form>
                    </div>
                </article>
            @empty
                <section class="panel panel-pad">
                    <strong>No providers seeded yet.</strong>
                    <p class="muted">Run the database seed command to create provider records.</p>
                </section>
            @endforelse
        </section>
    </main>

    <script>
        (() => {
            const cards = [...document.querySelectorAll('[data-provider-card]')];

            const closeCard = (card) => {
                const button = card.querySelector('[data-provider-toggle]');
                const panel = card.querySelector('[data-provider-models]');
                if (!button || !panel || panel.hidden) return;

                panel.style.maxHeight = `${panel.scrollHeight}px`;
                panel.offsetHeight;
                panel.style.maxHeight = '0px';
                panel.style.opacity = '0';
                panel.style.transform = 'translateY(-4px)';
                button.setAttribute('aria-expanded', 'false');
                button.textContent = '+';

                const onEnd = (event) => {
                    if (event.propertyName !== 'max-height') return;
                    panel.hidden = true;
                    panel.style.removeProperty('max-height');
                    panel.style.removeProperty('opacity');
                    panel.style.removeProperty('transform');
                    panel.removeEventListener('transitionend', onEnd);
                };

                panel.addEventListener('transitionend', onEnd);
            };

            const openCard = (card) => {
                const button = card.querySelector('[data-provider-toggle]');
                const panel = card.querySelector('[data-provider-models]');
                if (!button || !panel || !panel.hidden) return;

                cards.forEach((other) => {
                    if (other !== card) closeCard(other);
                });

                panel.hidden = false;
                panel.style.maxHeight = '0px';
                panel.style.opacity = '0';
                panel.style.transform = 'translateY(-4px)';
                panel.offsetHeight;
                panel.style.maxHeight = `${panel.scrollHeight}px`;
                panel.style.opacity = '1';
                panel.style.transform = 'translateY(0)';
                button.setAttribute('aria-expanded', 'true');
                button.textContent = '-';

                const onEnd = (event) => {
                    if (event.propertyName !== 'max-height') return;
                    panel.style.maxHeight = 'none';
                    panel.removeEventListener('transitionend', onEnd);
                };

                panel.addEventListener('transitionend', onEnd);
            };

            cards.forEach((card) => {
                const button = card.querySelector('[data-provider-toggle]');
                const panel = card.querySelector('[data-provider-models]');
                if (!button || !panel) return;

                button.addEventListener('click', () => {
                    if (panel.hidden) {
                        openCard(card);
                    } else {
                        closeCard(card);
                    }
                });
            });
        })();
    </script>
</x-layouts.app>
