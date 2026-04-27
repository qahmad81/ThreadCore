<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FamilyAgent;
use App\Models\Provider;
use App\Models\ProviderModel;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FamilyAgentController extends Controller
{
    public function index(): View
    {
        return view('admin.family-agents.index', [
            'families' => FamilyAgent::query()->with(['defaultProvider', 'defaultProviderModel'])->orderBy('number')->get(),
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
        $familyAgent->update($this->validated($request));

        return redirect()->route('admin.family-agents.index')->with('status', 'Family agent updated.');
    }

    private function formData(FamilyAgent $family): array
    {
        return [
            'family' => $family,
            'providers' => Provider::query()->orderBy('name')->get(),
            'models' => ProviderModel::query()->with('provider')->orderBy('provider_id')->orderBy('name')->get(),
        ];
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'number' => ['required', 'string', 'max:80'],
            'name' => ['required', 'string', 'max:120'],
            'system_prompt' => ['nullable', 'string'],
            'default_provider_id' => ['nullable', 'exists:providers,id'],
            'default_provider_model_id' => ['nullable', 'exists:provider_models,id'],
            'max_context_tokens' => ['required', 'integer', 'min:1'],
            'compaction_threshold_tokens' => ['required', 'integer', 'min:1'],
            'is_enabled' => ['nullable', 'boolean'],
        ]);

        return [
            ...$data,
            'number' => str($data['number'])->slug()->toString(),
            'is_enabled' => $request->boolean('is_enabled'),
        ];
    }
}
