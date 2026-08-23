@use('App\Support\Asset')
@use('App\Support\Locale')
<!DOCTYPE html>
<html lang="{{ Locale::htmlLang() }}" dir="{{ Locale::dir() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <meta name="theme-color" content="#4c3f8f">
    <link rel="icon" href="{{ Asset::versioned('favicon.ico') }}" sizes="16x16 32x32 48x48">
    <link rel="icon" type="image/png" href="{{ Asset::versioned('favicon-96.png') }}" sizes="96x96">
    <link rel="apple-touch-icon" href="{{ Asset::versioned('apple-touch-icon.png') }}">
    <title>{{ __('admin.login.title') }}</title>
    <link rel="preload" href="{{ asset('fonts/Rabar_015.woff2') }}" as="font" type="font/woff2" crossorigin>
    {{-- The same stylesheet as the panel itself, so the two cannot drift. --}}
    <link rel="stylesheet" href="{{ Asset::versioned('admin.css') }}">
</head>
<body>
<div class="login-shell">
    <div class="login-card">
        <div class="brand">
            <img src="{{ asset('file/uor-logo.webp') }}" alt="" width="512" height="523">
            <div>
                <strong>{{ __('admin.heading') }}</strong>
                <span>{{ __('admin.suffix') }}</span>
            </div>
        </div>

        <h1>{{ __('admin.login.heading') }}</h1>

        @if ($errors->any())
            <div class="alert alert-bad" role="alert">
                <span aria-hidden="true">!</span>
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('admin.login') }}">
            @csrf

            <div class="field">
                <label for="email">{{ __('admin.login.email') }}</label>
                <input type="email" id="email" name="email" required autofocus dir="ltr"
                       autocomplete="username" value="{{ old('email') }}">
            </div>

            <div class="field">
                <label for="password">{{ __('admin.login.password') }}</label>
                <input type="password" id="password" name="password" required dir="ltr"
                       autocomplete="current-password">
            </div>

            <label class="remember">
                <input type="checkbox" name="remember" value="1"> {{ __('admin.login.remember') }}
            </label>

            <button type="submit" class="btn btn-primary">{{ __('admin.login.submit') }}</button>
        </form>

        <div class="login-foot">
            <a href="{{ Locale::url() }}">{{ __('admin.login.back_site') }}</a>
        </div>
    </div>
</div>
</body>
</html>
