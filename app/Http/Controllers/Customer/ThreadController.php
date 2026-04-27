<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Thread;
use Illuminate\View\View;

class ThreadController extends Controller
{
    public function index(): View
    {
        $account = auth()->user()->customerAccount;
        abort_unless($account, 403);

        return view('customer.threads.index', [
            'account' => $account,
            'threads' => Thread::query()
                ->where('customer_account_id', $account->id)
                ->with(['familyAgent', 'provider', 'providerModel'])
                ->latest()
                ->paginate(50)
                ->withQueryString(),
        ]);
    }

    public function show(string $publicId): View
    {
        $account = auth()->user()->customerAccount;
        abort_unless($account, 403);

        $thread = Thread::query()
            ->where('public_id', $publicId)
            ->where('customer_account_id', $account->id)
            ->with([
                'apiKey',
                'familyAgent',
                'provider',
                'providerModel',
                'messages' => fn ($query) => $query->orderBy('created_at'),
            ])
            ->firstOrFail();

        return view('customer.threads.show', [
            'thread' => $thread,
        ]);
    }
}
