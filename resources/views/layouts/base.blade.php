@use('App\Support\Asset')
@use('App\Support\Locale')
@use('App\Support\RichText')
<!DOCTYPE html>
<html lang="{{ Locale::htmlLang() }}" dir="{{ Locale::dir() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    @if (config('library.analytics.host'))
        <link rel="dns-prefetch" href="{{ config('library.analytics.host') }}">
    @endif
    <link rel="preload" href="{{ asset('fonts/Rabar_015.woff2') }}" as="font" type="font/woff2" crossorigin>

    {{-- Heebo and Lora, the faces uor.edu.krd sets its Latin pages in.
         Neither carries Arabic script, so Rabar follows them in every stack. --}}
    <link rel="stylesheet" href="{{ Asset::versioned('fonts/uor.css') }}">

    <style>
        @font-face {
            font-family: 'Rabar';
            src: url('{{ asset('fonts/Rabar_015.woff2') }}') format('woff2');
            font-weight: normal;
            font-style: normal;
            /* Show the fallback face immediately rather than blocking on the
               download; the Kurdish text stays readable while Rabar loads. */
            font-display: swap;
        }
    </style>

    <title>@yield('title', __('messages.site_title'))</title>
    <meta name="description" content="@yield('description', __('messages.meta_description'))">
    <meta name="robots" content="index, follow">
    <meta name="theme-color" content="#393736">

    <link rel="icon" href="{{ Asset::versioned('uor-icon.ico') }}" sizes="16x16 32x32 48x48">
    <link rel="icon" type="image/png" href="{{ Asset::versioned('uor-icon-96.png') }}" sizes="96x96">
    <link rel="apple-touch-icon" href="{{ Asset::versioned('uor-apple-icon.png') }}">

    {{-- Every page has one address per language, and each is its own canonical. --}}
    <link rel="canonical" href="{{ Locale::switchUrl() }}">
    @foreach (Locale::SUPPORTED as $alternate)
        <link rel="alternate" hreflang="{{ Locale::languageTag($alternate) }}" href="{{ Locale::switchUrl($alternate) }}">
    @endforeach
    <link rel="alternate" hreflang="x-default" href="{{ Locale::switchUrl(Locale::DEFAULT) }}">

    <meta property="og:type" content="website">
    <meta property="og:site_name" content="{{ __('messages.site_title') }}">
    <meta property="og:title" content="@yield('title', __('messages.site_title'))">
    <meta property="og:description" content="@yield('description', __('messages.meta_description'))">
    <meta property="og:url" content="{{ Locale::switchUrl() }}">
    <meta property="og:image" content="{{ asset('file/uor-logo.png') }}">
    <meta property="og:locale" content="{{ Locale::languageTag() }}">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="@yield('title', __('messages.site_title'))">
    <meta name="twitter:description" content="@yield('description', __('messages.meta_description'))">

    @vite(['resources/css/app.css'])
    <link rel="stylesheet" href="{{ Asset::versioned('uor-header.css') }}">
    <script src="{{ Asset::versioned('uor-header.js') }}" defer></script>
    @stack('head')
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
<body class="bg-[#f0f0f0] text-[#1a1f2c] overflow-x-hidden @yield('body_class')" dir="{{ Locale::dir() }}">

<a class="skip-link" href="#main">{{ __('messages.skip_to_content') }}</a>

<div id="scrollProgress"></div>

<!-- ══════════ HEADER ══════════ -->
@php
    $nav = [
        ['url' => Locale::url(), 'label' => __('messages.nav.home'), 'active' => request()->routeIs('home', 'home.localised')],
        ['url' => Locale::booksUrl(), 'label' => __('messages.nav.books'), 'active' => request()->routeIs('books*')],
        ['url' => Locale::thesesUrl(), 'label' => __('messages.nav.theses'), 'active' => request()->routeIs('theses*')],
        ['url' => Locale::url().'#feedback', 'label' => __('messages.nav.feedback'), 'active' => false],
    ];
@endphp
<x-uor-header :home="Locale::url()" :title="__('messages.university_name')" :subtitle="__('messages.library_name')"
              :nav="$nav" :language-url="fn (string $code) => Locale::switchUrl($code)">
    <x-slot:quicklinks>
        <a href="{{ config('library.university_url') }}" target="_blank" rel="noopener">{{ __('messages.university_name') }}</a>
        <a class="quicklinks__extra" href="{{ Locale::privacyUrl() }}">{{ __('privacy.title') }}</a>
        @if (config('library.qr_url'))
            <a class="quicklinks__extra" href="{{ config('library.qr_url') }}" target="_blank" rel="noopener">{{ __('messages.qr_label') }}</a>
        @endif
    </x-slot>

    <x-slot:end>
        <a class="search-toggle" href="{{ Locale::booksUrl() }}" aria-controls="site-search" aria-expanded="false">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <circle cx="11" cy="11" r="7"></circle><path d="m20 20-3.5-3.5"></path>
            </svg>
            <span class="sr-only">{{ __('books.search_label') }}</span>
        </a>
    </x-slot>

    <x-slot:search>
        <form class="site-search" id="site-search" action="{{ Locale::booksUrl() }}" method="get" role="search" hidden>
            <div class="hdr-container site-search__inner">
                <label class="sr-only" for="site-search-q">{{ __('books.search_label') }}</label>
                <input id="site-search-q" type="search" name="q" dir="auto" maxlength="100" placeholder="{{ __('books.search_label') }}">
                <button class="hdr-gold-btn" type="submit">{{ __('books.search') }}</button>
            </div>
        </form>
    </x-slot>

    <x-slot:mobileExtra>
        <li><a href="{{ Locale::privacyUrl() }}">{{ __('privacy.title') }}</a></li>
    </x-slot>
</x-uor-header>

<main id="main">
@yield('content')
</main>

<!-- ══════════ FOOTER ══════════ -->
<footer class="text-center" style="background: #393736; color: rgba(255,255,255,0.82); border-top: 3px solid #790404;">
    <div class="max-w-[1200px] mx-auto px-4" style="padding-block: clamp(1.5rem,4vw,2.2rem);">
        @php
            $year = '<span class="'.Locale::yearClass().'"></span>';
            $uorLink = '<a href="'.e(config('library.university_url')).'" target="_blank" rel="noopener" style="color:#cbb58b; text-decoration:underline; text-underline-offset:3px;">'
                .e(__('messages.footer.uor_link_label')).'</a>';
        @endphp
        <p style="font-size:clamp(0.85rem,2vw,0.95rem);">{{ RichText::make(__('messages.footer.uor_line'), ['link' => $uorLink]) }}</p>
        <p style="font-size:clamp(0.85rem,2vw,0.95rem);">
            <a href="{{ Locale::booksUrl() }}" style="color:#cbb58b; text-decoration:underline; text-underline-offset:3px;">{{ __('books.title') }}</a>
            <span class="opacity-40 mx-2">·</span>
            <a href="{{ Locale::thesesUrl() }}" style="color:#cbb58b; text-decoration:underline; text-underline-offset:3px;">{{ __('theses.title') }}</a>
            <span class="opacity-40 mx-2">·</span>
            <a href="{{ Locale::privacyUrl() }}" style="color:#cbb58b; text-decoration:underline; text-underline-offset:3px;">{{ __('privacy.title') }}</a>
            @if (config('library.qr_url'))
                <span class="opacity-40 mx-2">·</span>
                <a href="{{ config('library.qr_url') }}" target="_blank" rel="noopener"
                   style="color:#cbb58b; text-decoration:underline; text-underline-offset:3px;">{{ __('messages.qr_label') }}</a>
            @endif
        </p>

        @if (config('library.analytics.host'))
            <div class="mt-3 flex items-center justify-center gap-3 opacity-75 hover:opacity-100 transition-opacity duration-300">
                <span style="font-size: 1rem;">👁</span>
                <div class="flex items-center gap-3 no-underline rounded-full border border-white/20 px-4 py-1.5"
                     style="background:rgba(255,255,255,0.12); font-size: 1rem; color:rgba(255,255,255,0.85);">
                    {{-- Built whole rather than pieced together in the
                         attribute: it is an address on the analytics host, not
                         a file in this project. --}}
                    @php $counter = rtrim(config('library.analytics.host'), '/').'/counter/TOTAL.svg'; @endphp
                    <img src="{{ $counter }}"
                         alt="{{ __('messages.visitors_label') }}"
                         class="h-[40px] w-auto align-middle"
                         loading="lazy" decoding="async">
                    <span id="visitor-label">{{ __('messages.visitors_label') }}</span>
                </div>
            </div>
        @endif
    </div>
    <div style="background: rgba(26,25,25,0.9); padding-block: 0.9rem;">
        <p class="max-w-[1200px] mx-auto px-4" style="font-size:clamp(0.8rem,1.9vw,0.9rem); color: rgba(255,255,255,0.7);">{{ RichText::make(__('messages.footer.copyright'), ['year' => $year]) }}</p>
    </div>
</footer>

<!-- Scroll To Top -->
<button id="scrollTopBtn" type="button" title="{{ __('messages.scroll_top') }}" aria-label="{{ __('messages.scroll_top') }}">
    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
        <polyline points="18 15 12 9 6 15"></polyline>
    </svg>
</button>

<script>

// Footer years
(function(){
    var y = new Date().getFullYear();
    function toAr(n){ return String(n).replace(/\d/g,function(d){ return '٠١٢٣٤٥٦٧٨٩'[d]; }); }
    document.querySelectorAll('.footer-year-en').forEach(function(el){ el.textContent=y; });
    document.querySelectorAll('.footer-year-ar').forEach(function(el){ el.textContent=toAr(y); });
})();

// Scroll reveal
var revealObserver = new IntersectionObserver(function(entries){
    entries.forEach(function(e){
        if(e.isIntersecting){
            e.target.classList.add('visible');
            revealObserver.unobserve(e.target);
        }
    });
},{threshold:0.1, rootMargin:'0px 0px -40px 0px'});

document.querySelectorAll('.reveal').forEach(function(el){
    var rect=el.getBoundingClientRect();
    if(rect.top<window.innerHeight-40){ el.classList.add('visible'); }
    else { revealObserver.observe(el); }
});

// Scroll progress
(function(){
    var bar=document.getElementById('scrollProgress');
    if(!bar) return;
    window.addEventListener('scroll',function(){
        var max=document.documentElement.scrollHeight-window.innerHeight;
        bar.style.width=(max>0 ? (window.scrollY/max)*100 : 0)+'%';
    },{passive:true});
})();

// Scroll to top
(function(){
    var btn=document.getElementById('scrollTopBtn');
    if(!btn) return;
    var shown=false;
    window.addEventListener('scroll',function(){
        var s=window.scrollY>320;
        if(s!==shown){ shown=s; btn.classList.toggle('visible',s); }
    },{passive:true});
    btn.addEventListener('click',function(){ window.scrollTo({top:0,behavior:'smooth'}); });
})();

// Keyboard nav
document.addEventListener('keydown',function(e){ if(e.key==='Tab') document.body.classList.add('keyboard-nav'); });
document.addEventListener('mousedown',function(){ document.body.classList.remove('keyboard-nav'); });
</script>

@stack('scripts')

@if (config('library.analytics.host'))
    <script data-goatcounter="{{ rtrim(config('library.analytics.host'), '/') }}/count"
            async src="{{ config('library.analytics.script') }}"></script>
@endif
</body>
</html>
