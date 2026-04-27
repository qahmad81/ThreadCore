<x-layouts.app title="Family Agent - ThreadCore">
    <main class="shell">
        @include('admin._chrome', ['title' => $family->exists ? 'Edit Family Agent' : 'New Family Agent'])
        <form class="panel panel-pad" method="POST" action="{{ $family->exists ? route('admin.family-agents.update', $family) : route('admin.family-agents.store') }}">
            @csrf
            @if ($family->exists) @method('PUT') @endif
            <div class="grid two">
                <div><label>Number</label><input name="number" type="text" value="{{ old('number', $family->number) }}" required></div>
                <div><label>Name</label><input name="name" type="text" value="{{ old('name', $family->name) }}" required></div>
            </div>
            <label>Description</label>
            <textarea name="description" placeholder="Optional short description for this family agent">{{ old('description', $family->description) }}</textarea>
            <label>System prompt</label>
            <textarea name="system_prompt">{{ old('system_prompt', $family->system_prompt) }}</textarea>
            <div class="grid two">
                <div>
                    <label>Default provider</label>
                    <select name="default_provider_id">
                        <option value="">None</option>
                        @foreach ($providers as $provider)
                            <option value="{{ $provider->id }}" @selected(old('default_provider_id', $family->default_provider_id) == $provider->id)>{{ $provider->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label>Default model</label>
                    <select name="default_provider_model_id">
                        <option value="">None</option>
                        @foreach ($models as $model)
                            <option value="{{ $model->id }}" @selected(old('default_provider_model_id', $family->default_provider_model_id) == $model->id)>{{ $model->provider?->name }} / {{ $model->model_key }}</option>
                        @endforeach
                    </select>
                </div>
                <div><label>Max context tokens</label><input name="max_context_tokens" type="number" value="{{ old('max_context_tokens', $family->max_context_tokens ?: 8192) }}" required></div>
                <div><label>Compaction threshold</label><input name="compaction_threshold_tokens" type="number" value="{{ old('compaction_threshold_tokens', $family->compaction_threshold_tokens ?: 7000) }}" required></div>
            </div>
            <label><input name="is_enabled" type="checkbox" value="1" @checked(old('is_enabled', $family->is_enabled ?? true))> Enabled</label>
            <button class="button primary" type="submit">Save family agent</button>
        </form>
    </main>
</x-layouts.app>
