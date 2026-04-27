<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CustomerAccount;
use Illuminate\View\View;

class CustomerAccountController extends Controller
{
    public function index(): View
    {
        return view('admin.customers.index', [
            'customers' => CustomerAccount::query()->with(['activeSubscription.plan', 'apiKeys'])->orderBy('name')->get(),
        ]);
    }
}
