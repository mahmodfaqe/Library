@use('App\Support\Asset')
@use('App\Support\Locale')
<!DOCTYPE html>
<html lang="{{ Locale::htmlLang() }}" dir="{{ Locale::dir() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <meta name="theme-color" content="#393736">
    <link rel="icon" href="{{ Asset::versioned('uor-icon.ico') }}" sizes="16x16 32x32 48x48">
    <link rel="icon" type="image/png" href="{{ Asset::versioned('uor-icon-96.png') }}" sizes="96x96">
    <link rel="apple-touch-icon" href="{{ Asset::versioned('uor-apple-icon.png') }}">
    <title>@yield('title', __('admin.title')) — {{ __('admin.suffix') }}</title>
    <link rel="preload" href="{{ asset('fonts/Rabar_015.woff2') }}" as="font" type="font/woff2" crossorigin>

    {{-- The university's Latin faces, Heebo and Lora; Rabar follows them for Kurdish. --}}
    <link rel="stylesheet" href="{{ Asset::versioned('fonts/uor.css') }}">
    <link rel="stylesheet" href="{{ Asset::versioned('admin.css') }}">
    <link rel="stylesheet" href="{{ Asset::versioned('uor-header.css') }}">
    <script src="{{ Asset::versioned('uor-header.js') }}" defer></script>
    {{-- In the head, not at the foot: an image can fail before the end of the
         document is parsed, and a listener registered afterwards would never
         hear it. The sweep afterwards catches any that failed even earlier. --}}
    <script>
    // Lets the header's script-only behaviour switch itself on.
    document.documentElement.classList.add('js');

    (function(){
        function blank(image) {
            var frame = image.closest('.book-cover, .suggest-cover, .row-cover');

            if (frame) frame.classList.add('is-blank');
        }

        // The error event does not bubble, so this listens in the capture
        // phase: one listener for every cover on the page, including the ones
        // the search suggestions build later, which had no fallback at all.
        //
        // It replaces an onerror attribute on each image. Inline handlers are
        // what oblige the Content-Security-Policy to allow inline script, and
        // the fewer of them there are, the closer that header can be tightened.
        document.addEventListener('error', function (event) {
            if (event.target && event.target.tagName === 'IMG') blank(event.target);
        }, true);

        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('.book-cover img, .suggest-cover img, .row-cover img')
                .forEach(function (image) {
                    if (image.complete && image.naturalWidth === 0) blank(image);
                });
        });
    })();
    </script>
</head>
<body>

<a class="skip-link" href="#main">{{ __('messages.skip_to_content') }}</a>

{{-- The same header as the public site, so the panel reads as part of the
     university too. Its menu is the panel's own sections; the top row holds
     the way back to the site, the account and signing out. --}}
@php
    $links = [
        ['admin.index', 'admin.nav.overview'],
        ['admin.departments', 'admin.nav.departments'],
        ['admin.categories', 'admin.nav.categories'],
        ['admin.books', 'admin.nav.books'],
        ['admin.theses', 'admin.nav.theses'],
        ['admin.feedback', 'admin.nav.feedback'],
    ];

    if (auth()->check() && auth()->user()->isAdmin()) {
        $links[] = ['admin.users', 'admin.nav.users'];
        $links[] = ['admin.activity', 'admin.nav.activity'];
    }

    $nav = array_map(fn (array $link) => [
        'url' => route($link[0]),
        'label' => __($link[1]),
        'active' => request()->routeIs($link[0]) || request()->routeIs($link[0].'.*'),
    ], $links);
@endphp
{{-- The panel has no localised address, so the language is a choice the
     staff member makes here rather than something the URL says. --}}
<x-uor-header :home="route('admin.index')" :title="__('admin.heading')" :subtitle="__('admin.suffix')"
              :nav="$nav" :language-url="fn (string $code) => route('admin.language', $code)">
    <x-slot:quicklinks>
        <a href="{{ Locale::url() }}">{{ __('admin.back_home') }}</a>
        @auth
            <a class="quicklinks__extra" href="{{ route('admin.account') }}" dir="auto"
               @if (request()->routeIs('admin.account')) aria-current="page" @endif>{{ auth()->user()->name }}</a>
        @endauth
    </x-slot>

    <x-slot:end>
        @auth
            <form method="POST" action="{{ route('admin.logout') }}" class="hdr-signout">
                @csrf
                <button type="submit">{{ __('admin.nav.sign_out') }}</button>
            </form>
        @endauth
    </x-slot>

    <x-slot:mobileExtra>
        @auth
            <li><a href="{{ route('admin.account') }}" @if (request()->routeIs('admin.account')) aria-current="page" @endif>{{ __('admin.nav.account') }}</a></li>
        @endauth
    </x-slot>
</x-uor-header>

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

@stack('scripts')

</body>
</html>
