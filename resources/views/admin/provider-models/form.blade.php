<x-layouts.app title="Model - ThreadCore">
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
            <label><input name="is_enabled" type="checkbox" value="1" @checked(old('is_enabled', $model->is_enabled ?? true))> Enabled</label>
            <label><input name="is_default" type="checkbox" value="1" @checked(old('is_default', $model->is_default))> Default</label>
            <button class="button primary" type="submit">Save model</button>
        </form>
    </main>
</x-layouts.app>
