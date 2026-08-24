@use('App\Support\Asset')
@use('App\Support\Locale')
<!DOCTYPE html>
<html lang="{{ Locale::htmlLang() }}" dir="{{ Locale::dir() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <meta name="theme-color" content="#4c3f8f">
    <link rel="icon" href="{{ Asset::versioned('uor-icon.ico') }}" sizes="16x16 32x32 48x48">
    <link rel="icon" type="image/png" href="{{ Asset::versioned('uor-icon-96.png') }}" sizes="96x96">
    <link rel="apple-touch-icon" href="{{ Asset::versioned('uor-apple-icon.png') }}">
    <title>@yield('title', __('admin.title')) — {{ __('admin.suffix') }}</title>
    <link rel="preload" href="{{ asset('fonts/Rabar_015.woff2') }}" as="font" type="font/woff2" crossorigin>
    <link rel="stylesheet" href="{{ Asset::versioned('admin.css') }}">
</head>
<body>

<a class="skip-link" href="#main">{{ __('messages.skip_to_content') }}</a>

{{-- The panel is a sidebar on a desk and a top bar on a phone; one list of
     links either way, so there is nothing to keep in step. --}}
<div class="shell">
    <aside class="sidebar">
        <div class="brand">
            <img src="{{ asset('file/uor-logo.webp') }}" alt="" width="512" height="523">
            <div>
                <strong>{{ __('admin.heading') }}</strong>
                <span>{{ __('admin.suffix') }}</span>
            </div>
        </div>

        @php
            $links = [
                ['admin.index', 'admin.nav.overview', '📊'],
                ['admin.departments', 'admin.nav.departments', '🏛'],
                ['admin.categories', 'admin.nav.categories', '🗂'],
                ['admin.books', 'admin.nav.books', '📚'],
                ['admin.feedback', 'admin.nav.feedback', '💬'],
            ];

            if (auth()->check() && auth()->user()->isAdmin()) {
                $links[] = ['admin.users', 'admin.nav.users', '👤'];
                $links[] = ['admin.activity', 'admin.nav.activity', '📋'];
            }
        @endphp

        <nav class="nav" aria-label="{{ __('admin.heading') }}">
            @foreach ($links as [$route, $label, $icon])
                @php $active = request()->routeIs($route) || request()->routeIs($route.'.*'); @endphp
                <a href="{{ route($route) }}" class="nav-link{{ $active ? ' is-active' : '' }}"
                   @if ($active) aria-current="page" @endif>
                    <span class="nav-icon" aria-hidden="true">{{ $icon }}</span>
                    <span>{{ __($label) }}</span>
                </a>
            @endforeach
        </nav>

        {{-- The panel has no localised address, so the language is a choice
             the staff member makes here rather than something the URL says.
             The same five controls as the public header. --}}
        <div class="lang-switch">
            <details class="lang-ku">
                <summary>
                    <span>کوردی</span>
                    <span class="caret" aria-hidden="true">▾</span>
                </summary>
                <div class="lang-ku-list">
                    @foreach ([
                        'ku-sorani' => 'سۆرانی',
                        'ku-badini' => 'بادینی',
                        'ku-badini-lat' => 'Badînî',
                        'ku-hawrami' => 'هەورامی',
                    ] as $dialect => $label)
                        <a href="{{ route('admin.language', $dialect) }}"
                           dir="{{ Locale::dir($dialect) }}"
                           class="{{ app()->getLocale() === $dialect ? 'is-active' : '' }}">{{ $label }}</a>
                    @endforeach
                </div>
            </details>

            @foreach (['en' => 'EN', 'ar' => 'ع', 'fa' => 'فا', 'tr' => 'TR'] as $code => $label)
                <a href="{{ route('admin.language', $code) }}"
                   class="lang-pill{{ app()->getLocale() === $code ? ' is-active' : '' }}"
                   title="{{ $code }}">{{ $label }}</a>
            @endforeach
        </div>

        <div class="sidebar-foot">
            @auth
                <a href="{{ route('admin.account') }}" class="who">
                    <span class="avatar" aria-hidden="true">{{ mb_substr(auth()->user()->name, 0, 1) }}</span>
                    <span class="who-name" dir="auto">{{ auth()->user()->name }}</span>
                </a>
                <form method="POST" action="{{ route('admin.logout') }}">
                    @csrf
                    <button type="submit" class="sign-out">{{ __('admin.nav.sign_out') }}</button>
                </form>
            @endauth
            <a href="{{ Locale::url() }}" class="back-site">{{ __('admin.back_home') }}</a>
        </div>
    </aside>

    <main class="content" id="main">
        @if (session('status'))
            <div class="alert alert-good" role="status">
                <span aria-hidden="true">✓</span>
                <p>{{ session('status') }}</p>
            </div>
        @endif

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

        @yield('content')
    </main>
</div>

@stack('scripts')

</body>
</html>
