<x-layouts.app title="Profile - ThreadCore">
    <main class="shell">
        @include('customer._chrome', ['title' => 'Profile'])

        <section class="grid two">
            <form class="panel panel-pad" method="POST" action="{{ route('customer.profile.update') }}">
                @csrf
                @method('PUT')

                <strong>Account Profile</strong>
                <p class="muted">{{ $account->name }} customer workspace</p>

                <label>Name</label>
                <input name="name" type="text" value="{{ old('name', $user->name) }}" required>

                <label>Email</label>
                <input name="email" type="email" value="{{ old('email', $user->email) }}" required>

                <button class="button primary" type="submit" style="margin-top: 14px;">Save profile</button>
            </form>

            <form class="panel panel-pad" method="POST" action="{{ route('customer.password.update') }}">
                @csrf
                @method('PUT')

                <strong>Change Password</strong>
                <p class="muted">Update the password for this customer login.</p>

                <label>Current password</label>
                <input name="current_password" type="password" required>

                <label>New password</label>
                <input name="password" type="password" required>

                <label>Confirm new password</label>
                <input name="password_confirmation" type="password" required>

                <button class="button primary" type="submit" style="margin-top: 14px;">Change password</button>
            </form>
        </section>
    </main>
</x-layouts.app>
