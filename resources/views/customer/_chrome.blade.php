@props(['title' => 'ThreadCore Customer'])

<header class="topbar">
    <div class="brand">
        <strong>{{ $title }}</strong>
        <span>Customer workspace</span>
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
