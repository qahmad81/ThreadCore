<x-layouts.app title="Family Agents - ThreadCore">
    <main class="shell">
        @include('admin._chrome', ['title' => 'Family Agents'])
        <p><a class="button primary" href="{{ route('admin.family-agents.create') }}">New family agent</a></p>
        <section class="panel panel-pad">
            <table>
                <thead><tr><th>Family</th><th>Default route</th><th>Capacity</th><th>Actions</th></tr></thead>
                <tbody>
                    @foreach ($families as $family)
                        <tr>
                            <td>
                                <strong>{{ $family->name }}</strong>
                                <div class="muted">{{ $family->number }}</div>
                                @if ($family->description)
                                    <div class="muted">{{ $family->description }}</div>
                                @endif
                            </td>
                            <td>
                                {{ $family->defaultProvider?->name ?? $family->defaultProviderModel?->provider?->name }} / {{ $family->defaultProviderModel?->model_key }}
                                <div class="muted">
                                    Compaction:
                                    {{ $family->compactionProvider?->name ?? $family->compactionProviderModel?->provider?->name ?? 'Default provider' }} /
                                    {{ $family->compactionProviderModel?->model_key ?? 'Default model' }}
                                </div>
                            </td>
                            <td>{{ $family->max_context_tokens }} tokens</td>
                            <td>
                                <div class="actions">
                                    <a class="button" href="{{ route('admin.family-agents.edit', $family) }}">Edit</a>
                                    <form method="POST" action="{{ route('admin.family-agents.destroy', $family) }}" data-confirm="Delete this family agent?">
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
        </section>
    </main>
</x-layouts.app>
