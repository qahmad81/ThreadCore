<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FamilyAgent;
use App\Models\ProviderModel;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class FamilyAgentController extends Controller
{
    public function index(): View
    {
        return view('admin.family-agents.index', [
            'families' => FamilyAgent::query()
                ->with([
                    'defaultProvider',
                    'defaultProviderModel.provider',
                    'compactionProvider',
                    'compactionProviderModel.provider',
                ])
                ->orderBy('number')
                ->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.family-agents.form', $this->formData(new FamilyAgent()));
    }

    public function store(Request $request): RedirectResponse
    {
        FamilyAgent::query()->create($this->validated($request));

        return redirect()->route('admin.family-agents.index')->with('status', 'Family agent created.');
    }

    public function edit(FamilyAgent $familyAgent): View
    {
        return view('admin.family-agents.form', $this->formData($familyAgent));
    }

    public function update(Request $request, FamilyAgent $familyAgent): RedirectResponse
    {
        $familyAgent->update($this->validated($request, $familyAgent));

        return redirect()->route('admin.family-agents.index')->with('status', 'Family agent updated.');
    }

    public function destroy(FamilyAgent $familyAgent): RedirectResponse
    {
        if ($familyAgent->threads()->exists()) {
            return back()->withErrors(['family_agent' => 'Disable family agents that still have threads instead of deleting them.']);
        }

        $familyAgent->delete();

        return redirect()->route('admin.family-agents.index')->with('status', 'Family agent deleted.');
    }

    private function formData(FamilyAgent $family): array
    {
        return [
            'family' => $family,
            'models' => ProviderModel::query()
                ->where('is_enabled', true)
                ->whereHas('provider', fn ($query) => $query->where('is_enabled', true))
                ->with('provider')
                ->orderBy('provider_id')
                ->orderBy('name')
                ->get(),
        ];
    }

    private function validated(Request $request, ?FamilyAgent $family = null): array
    {
        $data = $request->validate([
            'number' => ['required', 'string', 'max:80'],
            'name' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string'],
            'system_prompt' => ['nullable', 'string'],
            'default_provider_model_id' => ['nullable', 'exists:provider_models,id'],
            'compaction_provider_model_id' => ['nullable', 'exists:provider_models,id'],
            'max_context_tokens' => ['required', 'integer', 'min:1'],
            'compaction_threshold_tokens' => ['required', 'integer', 'min:1'],
            'compaction_prompt' => ['nullable', 'string'],
            'is_enabled' => ['nullable', 'boolean'],
        ]);

        $defaultProviderModel = filled($data['default_provider_model_id'] ?? null)
            ? ProviderModel::query()->find($data['default_provider_model_id'])
            : null;
        $compactionProviderModel = filled($data['compaction_provider_model_id'] ?? null)
            ? ProviderModel::query()->find($data['compaction_provider_model_id'])
            : null;

        if ($defaultProviderModel && ! $defaultProviderModel->is_enabled) {
            throw ValidationException::withMessages([
                'default_provider_model_id' => 'The selected default model is disabled.',
            ]);
        }

        if ($defaultProviderModel && ! $defaultProviderModel->provider?->is_enabled) {
            throw ValidationException::withMessages([
                'default_provider_model_id' => 'The selected default model provider is disabled.',
            ]);
        }

        if ($compactionProviderModel && ! $compactionProviderModel->is_enabled) {
            throw ValidationException::withMessages([
                'compaction_provider_model_id' => 'The selected compaction model is disabled.',
            ]);
        }

        if ($compactionProviderModel && ! $compactionProviderModel->provider?->is_enabled) {
            throw ValidationException::withMessages([
                'compaction_provider_model_id' => 'The selected compaction model provider is disabled.',
            ]);
        }

        return [
            ...$data,
            'number' => str($data['number'])->slug()->toString(),
            'default_provider_id' => $defaultProviderModel?->provider_id ?? $family?->default_provider_id,
            'compaction_provider_id' => $compactionProviderModel?->provider_id ?? $family?->compaction_provider_id,
            'compaction_prompt' => filled($data['compaction_prompt'] ?? null) ? $data['compaction_prompt'] : 'Compacted memory',
            'is_enabled' => $request->boolean('is_enabled'),
        ];
    }
}
