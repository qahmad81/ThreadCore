<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class DocsController extends Controller
{
    public function __invoke(): View
    {
        $account = auth()->user()->customerAccount?->load([
            'activeSubscription.plan',
            'apiKeys' => fn ($query) => $query->whereNull('revoked_at')->latest(),
        ]);

        abort_unless($account, 403);

        return view('customer.docs', [
            'account' => $account,
            'subscription' => $account->activeSubscription,
            'plan' => $account->activeSubscription?->plan,
            'activeApiKeys' => $account->apiKeys->values(),
            'activeApiKeyCount' => $account->apiKeys()->whereNull('revoked_at')->count(),
            'recentThreadCount' => $account->threads()->count(),
            'familyAgents' => \App\Models\FamilyAgent::query()
                ->where('is_enabled', true)
                ->with(['defaultProvider', 'defaultProviderModel'])
                ->orderBy('number')
                ->get(),
        ]);
    }
}
