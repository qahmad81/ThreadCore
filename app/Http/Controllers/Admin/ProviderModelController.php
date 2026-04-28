<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Provider;
use App\Models\ProviderModel;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ProviderModelController extends Controller
{
    public function index(): RedirectResponse
    {
        return redirect()->route('admin.resources.index');
    }

    public function create(): View
    {
        return view('admin.provider-models.form', $this->formData(new ProviderModel()));
    }

    public function store(Request $request): RedirectResponse
    {
        try {
            ProviderModel::query()->create($this->validated($request));
        } catch (QueryException $exception) {
            if (! $this->isDuplicateKeyException($exception)) {
                throw $exception;
            }

            throw ValidationException::withMessages([
                'model_key' => 'A model with this key already exists for the selected provider.',
            ]);
        }

        return redirect()->route('admin.resources.index')->with('status', 'Model created.');
    }

    public function edit(ProviderModel $providerModel): View
    {
        return view('admin.provider-models.form', $this->formData($providerModel));
    }

    public function update(Request $request, ProviderModel $providerModel): RedirectResponse
    {
        try {
            $providerModel->update($this->validated($request, $providerModel));
        } catch (QueryException $exception) {
            if (! $this->isDuplicateKeyException($exception)) {
                throw $exception;
            }

            throw ValidationException::withMessages([
                'model_key' => 'A model with this key already exists for the selected provider.',
            ]);
        }

        return redirect()->route('admin.resources.index')->with('status', 'Model updated.');
    }

    public function toggleEnabled(ProviderModel $providerModel): RedirectResponse
    {
        $providerModel->update([
            'is_enabled' => ! $providerModel->is_enabled,
        ]);

        return redirect()->route('admin.resources.index')
            ->with('status', $providerModel->is_enabled ? 'Model enabled.' : 'Model disabled.');
    }

    public function destroy(ProviderModel $providerModel): RedirectResponse
    {
        if ($providerModel->familyAgents()->exists()) {
            return back()->withErrors(['model' => 'Disable models used by family agents instead of deleting them.']);
        }

        $providerModel->delete();

        return redirect()->route('admin.resources.index')->with('status', 'Model deleted.');
    }

    private function formData(ProviderModel $model): array
    {
        return [
            'model' => $model,
            'providers' => Provider::query()->orderBy('name')->get(),
        ];
    }

    private function validated(Request $request, ?ProviderModel $model = null): array
    {
        $data = $request->validate([
            'provider_id' => ['required', 'exists:providers,id'],
            'name' => ['required', 'string', 'max:120'],
            'model_key' => [
                'required',
                'string',
                'max:160',
                Rule::unique('provider_models', 'model_key')
                    ->where(fn ($query) => $query->where('provider_id', $request->input('provider_id')))
                    ->ignore($model?->id),
            ],
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

    private function isDuplicateKeyException(QueryException $exception): bool
    {
        return (string) ($exception->errorInfo[1] ?? '') === '1062';
    }
}
