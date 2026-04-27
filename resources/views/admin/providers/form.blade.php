<x-layouts.app title="Provider - ThreadCore">
    <main class="shell">
        @include('admin._chrome', ['title' => $provider->exists ? 'Edit Provider' : 'New Provider'])

        <form class="panel panel-pad" method="POST" action="{{ $provider->exists ? route('admin.providers.update', $provider) : route('admin.providers.store') }}">
            @csrf
            @if ($provider->exists)
                @method('PUT')
            @endif

            <div class="grid two">
                <div>
                    <label>Name</label>
                    <input name="name" type="text" value="{{ old('name', $provider->name) }}" required>
                </div>
                <div>
                    <label>Slug</label>
                    <input name="slug" type="text" value="{{ old('slug', $provider->slug) }}" required>
                </div>
                <div>
                    <label>Driver</label>
                    <select name="driver" required>
                        @foreach (['openrouter', 'ollama'] as $driver)
                            <option value="{{ $driver }}" @selected(old('driver', $provider->driver) === $driver)>{{ $driver }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label>API key env or token</label>
                    <input name="api_key_env" type="text" value="{{ old('api_key_env', $provider->api_key_env) }}">
                </div>
            </div>

            <label>Base URL</label>
            <input name="base_url" type="url" value="{{ old('base_url', $provider->base_url) }}">

            <label><input name="is_enabled" type="checkbox" value="1" @checked(old('is_enabled', $provider->is_enabled ?? true))> Enabled</label>
            <label><input name="is_default" type="checkbox" value="1" @checked(old('is_default', $provider->is_default))> Default</label>

            <button class="button primary" type="submit">Save provider</button>
        </form>
    </main>
</x-layouts.app>
