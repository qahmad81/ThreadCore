<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\GatewayRequestLog;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $account = auth()->user()->customerAccount?->load([
            'apiKeys' => fn ($query) => $query->latest()->limit(5),
            'activeSubscription.plan',
            'threads' => fn ($query) => $query->latest()->limit(5),
        ]);

        abort_unless($account, 403);

        $subscription = $account->activeSubscription;
        $plan = $subscription?->plan;
        $requestLimit = max(1, (int) ($plan?->monthly_request_limit ?? 1));
        $tokenLimit = max(1, (int) ($plan?->monthly_token_limit ?? 1));

        return view('customer.dashboard', [
            'account' => $account,
            'subscription' => $subscription,
            'plan' => $plan,
            'requestPercent' => min(100, (int) round((($subscription?->requests_used ?? 0) / $requestLimit) * 100)),
            'tokenPercent' => min(100, (int) round((($subscription?->tokens_used ?? 0) / $tokenLimit) * 100)),
            'recentLogs' => GatewayRequestLog::query()
                ->where('customer_account_id', $account->id)
                ->latest()
                ->limit(5)
                ->get(),
        ]);
    }
}
