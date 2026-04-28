<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Provider;
use App\Models\ProviderModel;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProviderModelController extends Controller
{
    public function index(): View
    {
        return view('admin.provider-models.index', [
            'models' => ProviderModel::query()->with('provider')->orderBy('provider_id')->orderBy('role')->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.provider-models.form', $this->formData(new ProviderModel()));
    }

    public function store(Request $request): RedirectResponse
    {
        ProviderModel::query()->create($this->validated($request));

        return redirect()->route('admin.provider-models.index')->with('status', 'Model created.');
    }

    public function edit(ProviderModel $providerModel): View
    {
        return view('admin.provider-models.form', $this->formData($providerModel));
    }

    public function update(Request $request, ProviderModel $providerModel): RedirectResponse
    {
        $providerModel->update($this->validated($request));

        return redirect()->route('admin.provider-models.index')->with('status', 'Model updated.');
    }

    public function destroy(ProviderModel $providerModel): RedirectResponse
    {
        if ($providerModel->familyAgents()->exists()) {
            return back()->withErrors(['model' => 'Disable models used by family agents instead of deleting them.']);
        }

        $providerModel->delete();

        return redirect()->route('admin.provider-models.index')->with('status', 'Model deleted.');
    }

    private function formData(ProviderModel $model): array
    {
        return [
            'model' => $model,
            'providers' => Provider::query()->orderBy('name')->get(),
        ];
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'provider_id' => ['required', 'exists:providers,id'],
            'name' => ['required', 'string', 'max:120'],
            'model_key' => ['required', 'string', 'max:160'],
            'role' => ['nullable', 'string', 'max:80'],
            'context_window' => ['nullable', 'integer', 'min:1'],
            'pricing' => ['nullable', 'json'],
            'is_enabled' => ['nullable', 'boolean'],
            'is_default' => ['nullable', 'boolean'],
        ]);

        return [
            ...$data,
            'pricing' => $request->filled('pricing')
                ? json_decode($data['pricing'], true, 512, JSON_THROW_ON_ERROR)
                : null,
            'is_enabled' => $request->boolean('is_enabled'),
            'is_default' => $request->boolean('is_default'),
        ];
    }
}
