<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\GatewayRequestLog;
use Illuminate\View\View;

class UsageController extends Controller
{
    public function __invoke(): View
    {
        $account = auth()->user()->customerAccount;
        abort_unless($account, 403);

        return view('customer.usage', [
            'logs' => GatewayRequestLog::query()->where('customer_account_id', $account->id)->latest()->limit(100)->get(),
            'subscription' => $account->activeSubscription?->load('plan'),
        ]);
    }
}
