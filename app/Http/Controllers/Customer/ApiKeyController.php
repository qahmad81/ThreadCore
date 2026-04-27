<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\ApiKey;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ApiKeyController extends Controller
{
    public function index(): View
    {
        $account = auth()->user()->customerAccount;

        abort_unless($account, 403);

        return view('customer.api-keys.index', [
            'keys' => $account->apiKeys()->latest()->get(),
            'plainToken' => session('plain_api_token'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $account = $request->user()->customerAccount;
        abort_unless($account, 403);

        $data = $request->validate(['name' => ['required', 'string', 'max:120']]);
        $plainToken = ApiKey::makePlainToken();

        $account->apiKeys()->create([
            'name' => $data['name'],
            'prefix' => substr($plainToken, 0, 12),
            'token_hash' => ApiKey::hashToken($plainToken),
            'scopes' => ['gateway:write'],
        ]);

        return redirect()->route('customer.api-keys.index')->with('plain_api_token', $plainToken);
    }

    public function destroy(ApiKey $apiKey): RedirectResponse
    {
        abort_unless($apiKey->customer_account_id === auth()->user()->customer_account_id, 403);

        $apiKey->forceFill(['revoked_at' => now()])->save();

        return redirect()->route('customer.api-keys.index')->with('status', 'API key revoked.');
    }
}
