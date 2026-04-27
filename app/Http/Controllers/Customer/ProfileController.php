<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(Request $request): View
    {
        abort_unless($request->user()->customerAccount, 403);

        return view('customer.profile', [
            'user' => $request->user(),
            'account' => $request->user()->customerAccount,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        abort_unless($request->user()->customerAccount, 403);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:160', 'unique:users,email,'.$request->user()->id],
        ]);

        $request->user()->update($data);

        return redirect()->route('customer.profile.edit')->with('status', 'Profile updated.');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        abort_unless($request->user()->customerAccount, 403);

        $data = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $request->user()->forceFill([
            'password' => Hash::make($data['password']),
        ])->save();

        return redirect()->route('customer.profile.edit')->with('status', 'Password changed.');
    }
}
