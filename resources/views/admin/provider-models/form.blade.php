<x-layouts.app title="Resource - ThreadCore">
    <main class="shell">
        @include('admin._chrome', ['title' => $model->exists ? 'Edit Model' : 'New Model'])
        <form class="panel panel-pad" method="POST" action="{{ $model->exists ? route('admin.provider-models.update', $model) : route('admin.provider-models.store') }}">
            @csrf
            @if ($model->exists) @method('PUT') @endif
            <label>Provider</label>
            <select name="provider_id" required>
                @foreach ($providers as $provider)
                    <option value="{{ $provider->id }}" @selected(old('provider_id', $model->provider_id) == $provider->id)>{{ $provider->name }}</option>
                @endforeach
            </select>
            <div class="grid two">
                <div><label>Name</label><input name="name" type="text" value="{{ old('name', $model->name) }}" required></div>
                <div><label>Model key</label><input name="model_key" type="text" value="{{ old('model_key', $model->model_key) }}" required></div>
                <div><label>Role</label><input name="role" type="text" value="{{ old('role', $model->role) }}"></div>
                <div><label>Context window</label><input name="context_window" type="number" value="{{ old('context_window', $model->context_window) }}"></div>
            </div>
            <label>Pricing JSON</label>
            <textarea name="pricing" rows="8" placeholder='{"prompt_tokens": 0.000001, "completion_tokens": 0.000002, "prompt_cache_hit_tokens": 0.00000025, "prompt_cache_miss_tokens": 0.000001, "reasoning_tokens": 0.000003}'>{{ old('pricing', filled($model->pricing ?? null) ? json_encode($model->pricing, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : '') }}</textarea>
            <p class="muted">Use the same usage field names returned by the API result. Leave blank to keep cost at zero.</p>
            <label><input name="is_enabled" type="checkbox" value="1" @checked(old('is_enabled', $model->is_enabled ?? true))> Enabled</label>
            <label><input name="is_default" type="checkbox" value="1" @checked(old('is_default', $model->is_default))> Default</label>
            <div class="actions" style="margin-top: 18px;">
                <button class="button primary" type="submit">Save model</button>
                <a class="button" href="{{ route('admin.resources.index') }}">Back</a>
            </div>
        </form>
    </main>
</x-layouts.app>
