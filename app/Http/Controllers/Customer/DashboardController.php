<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $account = auth()->user()->customerAccount?->load(['apiKeys', 'activeSubscription.plan', 'threads']);

        abort_unless($account, 403);

        return view('customer.dashboard', ['account' => $account]);
    }
}
