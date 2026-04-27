<x-layouts.app title="Sign in - ThreadCore">
    <main class="login-wrap">
        <section class="panel panel-pad">
            <div class="brand" style="margin-bottom: 22px;">
                <strong>ThreadCore</strong>
                <span>Sign in to the local admin workspace.</span>
            </div>

            <form method="POST" action="{{ route('login.store') }}">
                @csrf

                <label for="email">Email</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus>

                <label for="password">Password</label>
                <input id="password" name="password" type="password" required>

                <label style="display: flex; align-items: center; gap: 8px; font-weight: 500;">
                    <input name="remember" type="checkbox" value="1">
                    Remember this session
                </label>

                @if ($errors->any())
                    <p class="error">{{ $errors->first() }}</p>
                @endif

                <button class="button primary" type="submit" style="width: 100%; margin-top: 18px;">Sign in</button>
            </form>
        </section>
    </main>
</x-layouts.app>
