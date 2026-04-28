<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Provider;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ProviderController extends Controller
{
    public function index(): View
    {
        return view('admin.providers.index', [
            'providers' => Provider::query()
                ->with('models')
                ->orderByDesc('is_default')
                ->orderByDesc('is_enabled')
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.providers.form', ['provider' => new Provider()]);
    }

    public function store(Request $request): RedirectResponse
    {
        Provider::query()->create($this->validated($request));

        return redirect()->route('admin.resources.index')->with('status', 'Provider created.');
    }

    public function edit(Provider $provider): View
    {
        return view('admin.providers.form', ['provider' => $provider]);
    }

    public function update(Request $request, Provider $provider): RedirectResponse
    {
        $provider->update($this->validated($request));

        return redirect()->route('admin.resources.index')->with('status', 'Provider updated.');
    }

    public function toggleEnabled(Provider $provider): RedirectResponse
    {
        $provider->update([
            'is_enabled' => ! $provider->is_enabled,
        ]);

        return redirect()->route('admin.resources.index')
            ->with('status', $provider->is_enabled ? 'Provider enabled.' : 'Provider disabled.');
    }

    public function destroy(Provider $provider): RedirectResponse
    {
        DB::transaction(function () use ($provider): void {
            $provider->models()->delete();
            $provider->delete();
        });

        return redirect()->route('admin.resources.index')->with('status', 'Provider deleted.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'slug' => ['required', 'string', 'max:80'],
            'driver' => ['required', 'in:openai,openrouter,google,anthropic,lmstudio,vllm,ollama'],
            'base_url' => ['nullable', 'url'],
            'api_key_env' => ['nullable', 'string', 'max:120'],
            'is_enabled' => ['nullable', 'boolean'],
            'is_default' => ['nullable', 'boolean'],
        ]);

        return [
            ...$data,
            'slug' => str($data['slug'])->slug()->toString(),
            'is_enabled' => $request->boolean('is_enabled'),
            'is_default' => $request->boolean('is_default'),
        ];
    }
}
