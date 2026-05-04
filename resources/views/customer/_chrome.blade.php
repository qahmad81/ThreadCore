@props(['title' => 'ThreadCore Customer'])

<header class="topbar">
    <div class="brand">
        <div class="brand-lockup">
            <img class="brand-mark" src="/icons/logo/apple-touch-icon.png" alt="ThreadCore customer icon">
            <div class="brand-copy">
                <strong>{{ $title }}</strong>
                <span>Customer workspace</span>
            </div>
        </div>
        @include('customer._nav')
    </div>

    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button class="button" type="submit">Sign out</button>
    </form>
</header>

@if (session('status'))
    <div class="alert ok">{{ session('status') }}</div>
@endif

@if ($errors->any())
    <div class="alert error">{{ $errors->first() }}</div>
@endif
