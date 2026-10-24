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
        // the search suggestions build later.
        //
        // One listener rather than an onerror attribute on each image. Inline
        // handlers are what oblige the Content-Security-Policy to allow inline
        // script, and the fewer of them there are, the closer that header can
        // be tightened.
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
<body class="bg-white text-[#1a1a1a] overflow-x-hidden @yield('body_class')" dir="{{ Locale::dir() }}">

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
<footer class="site-footer">

    {{-- The hills and the city are uor.edu.krd's own two images, so the
         footer begins exactly as theirs does. --}}
    <div class="footer-hills" aria-hidden="true"><div class="footer-hills__city"></div></div>

    @php
        $year = '<span class="'.Locale::yearClass().'"></span>';
    @endphp

    {{-- Columns under gold headings, as the university sets its own footer,
         with the crest under the university's name in the middle. --}}
    <div class="site-footer__cols footer-container">

        <section class="footer-col">
            <h2>{{ __('messages.library_name') }}</h2>
            <ul>
                <li><a href="{{ Locale::booksUrl() }}">{{ __('books.title') }}</a></li>
                <li><a href="{{ Locale::thesesUrl() }}">{{ __('theses.title') }}</a></li>
                <li><a href="{{ Locale::privacyUrl() }}">{{ __('privacy.title') }}</a></li>
                @if (config('library.qr_url'))
                    <li><a href="{{ config('library.qr_url') }}" target="_blank" rel="noopener">{{ __('messages.qr_label') }}</a></li>
                @endif
            </ul>
        </section>

        {{-- The crest leads to the university's site, and under it are the
             university's own accounts in their own colours, as on theirs.
             The marks are Font Awesome Free's (CC BY 4.0), as theirs are. --}}
        <section class="footer-col footer-col--mark">
            <h2>{{ __('messages.university_name') }}</h2>
            <a href="{{ config('library.university_url') }}" target="_blank" rel="noopener" aria-label="{{ __('messages.footer.uor_link_label') }}">
                <img src="{{ asset('file/uor-logo.webp') }}" alt="" width="512" height="523" loading="lazy" decoding="async">
            </a>
            <ul class="footer-social">
                <li><a class="footer-social__facebook" href="{{ config('library.university_social.facebook') }}" target="_blank" rel="noopener" aria-label="Facebook">
                    <svg viewBox="0 0 320 512" fill="currentColor" aria-hidden="true"><path d="M279.14 288l14.22-92.66h-88.91v-60.13c0-25.35 12.42-50.06 52.24-50.06h40.42V6.26S260.43 0 225.36 0c-73.22 0-121.08 44.38-121.08 124.72v70.62H22.89V288h81.39v224h100.17V288z"/></svg>
                </a></li>
                <li><a class="footer-social__x" href="{{ config('library.university_social.x') }}" target="_blank" rel="noopener" aria-label="X">
                    <svg viewBox="0 0 512 512" fill="currentColor" aria-hidden="true"><path d="M389.2 48h70.6L305.6 224.2 487 464H345L233.7 318.6 106.5 464H35.8L200.7 275.5 26.8 48H172.4L272.9 180.9 389.2 48zM364.4 421.8h39.1L151.1 88h-42L364.4 421.8z"/></svg>
                </a></li>
                <li><a class="footer-social__instagram" href="{{ config('library.university_social.instagram') }}" target="_blank" rel="noopener" aria-label="Instagram">
                    <svg viewBox="0 0 448 512" fill="currentColor" aria-hidden="true"><path d="M224.1 141c-63.6 0-114.9 51.3-114.9 114.9s51.3 114.9 114.9 114.9S339 319.5 339 255.9 287.7 141 224.1 141zm0 189.6c-41.1 0-74.7-33.5-74.7-74.7s33.5-74.7 74.7-74.7 74.7 33.5 74.7 74.7-33.6 74.7-74.7 74.7zm146.4-194.3c0 14.9-12 26.8-26.8 26.8-14.9 0-26.8-12-26.8-26.8s12-26.8 26.8-26.8 26.8 12 26.8 26.8zm76.1 27.2c-1.7-35.9-9.9-67.7-36.2-93.9-26.2-26.2-58-34.4-93.9-36.2-37-2.1-147.9-2.1-184.9 0-35.8 1.7-67.6 9.9-93.9 36.1s-34.4 58-36.2 93.9c-2.1 37-2.1 147.9 0 184.9 1.7 35.9 9.9 67.7 36.2 93.9s58 34.4 93.9 36.2c37 2.1 147.9 2.1 184.9 0 35.9-1.7 67.7-9.9 93.9-36.2 26.2-26.2 34.4-58 36.2-93.9 2.1-37 2.1-147.8 0-184.8zM398.8 388c-7.8 19.6-22.9 34.7-42.6 42.6-29.5 11.7-99.5 9-132.1 9s-102.7 2.6-132.1-9c-19.6-7.8-34.7-22.9-42.6-42.6-11.7-29.5-9-99.5-9-132.1s-2.6-102.7 9-132.1c7.8-19.6 22.9-34.7 42.6-42.6 29.5-11.7 99.5-9 132.1-9s102.7-2.6 132.1 9c19.6 7.8 34.7 22.9 42.6 42.6 11.7 29.5 9 99.5 9 132.1s2.7 102.7-9 132.1z"/></svg>
                </a></li>
            </ul>
        </section>

        {{-- This same page in each of the site's languages, every name in its
             own script and direction. --}}
        <section class="footer-col">
            <h2>{{ __('messages.language') }}</h2>
            <ul>
                @foreach (Locale::SUPPORTED as $code)
                    <li><a href="{{ Locale::switchUrl($code) }}" hreflang="{{ Locale::languageTag($code) }}"
                           lang="{{ Locale::languageTag($code) }}" dir="{{ Locale::dir($code) }}"
                           @isset(Locale::KURDISH[$code]) style="font-family:{{ Locale::KURDISH[$code][2] }};" @endisset
                           @if (app()->getLocale() === $code) aria-current="true" @endif>{{ Locale::KURDISH[$code][0] ?? Locale::OTHERS[$code] }}</a></li>
                @endforeach
            </ul>
        </section>

    </div>

    @php
        // The address is the university's, written out in each language as
        // uor.edu.krd gives it; the phone and email are set per server, and a
        // line left empty there is left out here.
        $contact = array_filter([
            'address' => __('messages.footer.address'),
            'phone' => config('library.contact.phone'),
            'email' => config('library.contact.email'),
        ]);
    @endphp

    @if ($contact)
        <div class="footer-info footer-container">
            <h2 class="sr-only">{{ __('messages.footer.contact') }}</h2>
            @foreach ($contact as $kind => $value)
                <div class="footer-info__item">
                    {{-- Solid shapes, as their pin, handset and envelope are. --}}
                    <span class="footer-info__icon" aria-hidden="true">
                        @switch($kind)
                            @case('address')
                                <svg width="26" height="26" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7Zm0 9.5a2.5 2.5 0 1 1 0-5 2.5 2.5 0 0 1 0 5Z"/></svg>
                                @break
                            @case('phone')
                                <svg width="26" height="26" viewBox="0 0 24 24" fill="currentColor"><path d="M6.62 10.79a15.15 15.15 0 0 0 6.59 6.59l2.2-2.2a1 1 0 0 1 1.02-.24c1.12.37 2.33.57 3.57.57a1 1 0 0 1 1 1V20a1 1 0 0 1-1 1C10.61 21 3 13.39 3 4a1 1 0 0 1 1-1h3.5a1 1 0 0 1 1 1c0 1.25.2 2.45.57 3.57a1 1 0 0 1-.25 1.02l-2.2 2.2Z"/></svg>
                                @break
                            @default
                                <svg width="26" height="26" viewBox="0 0 24 24" fill="currentColor"><path d="M20 4H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2Zm0 4-8 5-8-5V6l8 5 8-5v2Z"/></svg>
                        @endswitch
                    </span>
                    @if ($kind === 'address')
                        <p dir="auto">{{ $value }}</p>
                    @else
                        <p><a href="{{ $kind === 'phone' ? 'tel:'.preg_replace('/[^+0-9]/', '', $value) : 'mailto:'.$value }}" dir="ltr">{{ $value }}</a></p>
                    @endif
                </div>
            @endforeach
        </div>
    @endif

    <div class="site-footer__bottom">
        <p class="footer-container">{{ RichText::make(__('messages.footer.copyright'), ['year' => $year]) }}</p>
    </div>
</footer>

<!-- Scroll To Top -->
<button id="scrollTopBtn" type="button" title="{{ __('messages.scroll_top') }}" aria-label="{{ __('messages.scroll_top') }}">
    <svg class="scroll-top__ring" viewBox="0 0 100 100" aria-hidden="true">
        <path class="scroll-top__track" d="M50,1 a50,50 0 0,1 0,100 a50,50 0 0,1 0,-100"/>
        <path class="scroll-top__fill" d="M50,1 a50,50 0 0,1 0,100 a50,50 0 0,1 0,-100" pathLength="100"/>
    </svg>
    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
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
    var fill=btn.querySelector('.scroll-top__fill');
    var shown=false;
    window.addEventListener('scroll',function(){
        var s=window.scrollY>320;
        if(s!==shown){ shown=s; btn.classList.toggle('visible',s); }
        var max=document.documentElement.scrollHeight-window.innerHeight;
        if(fill) fill.style.strokeDashoffset=100-(max>0 ? Math.min(window.scrollY/max,1)*100 : 0);
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
