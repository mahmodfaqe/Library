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
    <meta name="theme-color" content="#667eea">

    <link rel="icon" href="{{ Asset::versioned('favicon.ico') }}" sizes="16x16 32x32 48x48">
    <link rel="icon" type="image/png" href="{{ Asset::versioned('favicon-96.png') }}" sizes="96x96">
    <link rel="apple-touch-icon" href="{{ Asset::versioned('apple-touch-icon.png') }}">

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
    @stack('head')
</head>
<body class="bg-[#f8f9ff] text-[#2d2d3a] overflow-x-hidden" dir="{{ Locale::dir() }}">

<a class="skip-link" href="#main">{{ __('messages.skip_to_content') }}</a>

<div id="scrollProgress"></div>

<!-- ══════════ HEADER ══════════ -->
<header id="site-header" class="sticky top-0 z-[1000] backdrop-blur-md transition-shadow duration-300"
        style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); box-shadow: 0 4px 24px rgba(102,126,234,0.25);">
    <div class="max-w-[1200px] mx-auto px-4 sm:px-6 lg:px-8 py-2 sm:py-3">
        <div class="flex items-center gap-2 sm:gap-4 flex-nowrap">

            <!-- Logo — always the way back to the library home page -->
            <a class="logo-link flex items-center gap-1.5 sm:gap-2.5 shrink min-w-0 no-underline" href="{{ Locale::url() }}">
                <img src="{{ asset('file/uor-logo.webp') }}"
                     alt="{{ __('messages.university_name') }}"
                     width="512" height="523"
                     fetchpriority="high" decoding="async"
                     class="uor-logo object-contain shrink-0"
                     style="height: clamp(34px,7.5vw,62px); width: auto;">
                <div class="flex flex-col min-w-0 shrink">
                    <span class="logo-title logo-title-el font-bold text-white leading-tight overflow-hidden text-ellipsis whitespace-nowrap"
                          style="font-size: clamp(0.72rem,2.8vw,1.4rem);">
                        {{ __('messages.university_name') }}
                    </span>
                    <span class="logo-subtitle logo-sub-el block mt-1 overflow-hidden text-ellipsis whitespace-nowrap"
                          style="font-size: clamp(0.55rem,1.8vw,0.9rem); color: rgba(255,255,255,0.72);">
                        {{ __('messages.library_name') }}
                    </span>
                </div>
            </a>

            <!-- Divider -->
            <div class="shrink-0 w-px bg-white/30" style="height: clamp(22px,5vw,36px);"></div>

            <!-- Language Switcher — stays on the current page -->
            <div class="flex items-center gap-1 flex-nowrap ms-auto shrink-0">
                <!-- Kurdish dropdown group -->
                <div class="relative" id="kuGroup">
                    <button id="kuMainBtn" type="button"
                            class="{{ str_starts_with(app()->getLocale(), 'ku') ? 'active ' : '' }}flex items-center gap-1 px-2 py-1.5 rounded-md text-white border border-white/20 bg-white/15 cursor-pointer transition-all duration-200 hover:-translate-y-0.5 hover:border-white/45 hover:shadow-md font-[inherit]"
                            style="font-size: clamp(0.52rem,1.4vw,0.8rem); min-height:30px;"
                            aria-haspopup="true" aria-expanded="false"
                            onclick="toggleKuDropdown()">
                        کوردی <span class="text-[0.5rem] transition-transform duration-300" id="kuArrow">▼</span>
                    </button>
                    <div class="ku-dropdown" id="kuDropdown">
                        @foreach ([
                            'ku-sorani' => ['سۆرانی', 'Soranî', 'right', 'Rabar,sans-serif'],
                            'ku-badini' => ['بادینی', 'Kurmancî (عەرەبی)', 'right', 'Rabar,sans-serif'],
                            'ku-badini-lat' => ['Badînî', 'Kurmancî (Latînî)', 'left', 'sans-serif'],
                            'ku-hawrami' => ['هەورامی', 'Hewramî', 'right', 'Rabar,sans-serif'],
                        ] as $dialect => [$label, $native, $align, $font])
                            <a href="{{ Locale::switchUrl($dialect) }}"
                               class="ku-dialect-btn block w-full bg-transparent border-0 text-{{ $align }} cursor-pointer transition-colors duration-200 hover:bg-white/15 no-underline text-white{{ app()->getLocale() === $dialect ? ' active' : '' }}"
                               style="padding: clamp(0.38rem,1.2vw,0.55rem) clamp(0.6rem,2vw,1rem); font-size:clamp(0.58rem,1.4vw,0.8rem); min-height:36px; font-family:{{ $font }};">
                                {{ $label }}<span class="block text-[0.5em] opacity-65 mt-px">{{ $native }}</span>
                            </a>
                        @endforeach
                    </div>
                </div>

                <!-- Divider -->
                <div class="shrink-0 w-px bg-white/25 mx-1" style="height:clamp(16px,3.5vw,22px);"></div>

                <!-- Other languages -->
                <div class="flex items-center gap-1 flex-nowrap overflow-x-auto" style="scrollbar-width:none; max-width:clamp(100px,40vw,400px);">
                    @foreach (['en' => 'English', 'ar' => 'العربية', 'fa' => 'فارسی', 'tr' => 'Türkçe'] as $code => $label)
                        <a href="{{ Locale::switchUrl($code) }}"
                           class="lang-btn px-2 py-1.5 rounded-md text-white border border-white/20 bg-white/15 cursor-pointer transition-all duration-200 hover:-translate-y-0.5 hover:border-white/45 hover:shadow-md whitespace-nowrap shrink-0 font-[inherit] no-underline{{ app()->getLocale() === $code ? ' active' : '' }}"
                           style="font-size:clamp(0.52rem,1.4vw,0.8rem); min-height:30px;">{{ $label }}</a>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</header>

<main id="main">
@yield('content')
</main>

<!-- ══════════ FOOTER ══════════ -->
<footer class="text-center" style="background: linear-gradient(135deg, #1e1e2e 0%, #2d2b4e 100%); color: rgba(255,255,255,0.82); padding: clamp(1.5rem,4vw,2.2rem) 0;">
    <div class="max-w-[1200px] mx-auto px-4">
        @php
            $year = '<span class="'.Locale::yearClass().'"></span>';
            $uorLink = '<a href="'.e(config('library.university_url')).'" target="_blank" rel="noopener" style="color:#9bb1ff; text-decoration:underline; text-underline-offset:3px;">'
                .e(__('messages.footer.uor_link_label')).'</a>';
        @endphp
        <p style="font-size:clamp(0.85rem,2vw,0.95rem);">{{ RichText::make(__('messages.footer.copyright'), ['year' => $year]) }}</p>
        <p style="font-size:clamp(0.85rem,2vw,0.95rem);">{{ RichText::make(__('messages.footer.uor_line'), ['link' => $uorLink]) }}</p>
        <p style="font-size:clamp(0.85rem,2vw,0.95rem);">
            <a href="{{ Locale::booksUrl() }}" style="color:#9bb1ff; text-decoration:underline; text-underline-offset:3px;">{{ __('books.title') }}</a>
            <span class="opacity-40 mx-2">·</span>
            <a href="{{ Locale::privacyUrl() }}" style="color:#9bb1ff; text-decoration:underline; text-underline-offset:3px;">{{ __('privacy.title') }}</a>
            @if (config('library.qr_url'))
                <span class="opacity-40 mx-2">·</span>
                <a href="{{ config('library.qr_url') }}" target="_blank" rel="noopener"
                   style="color:gold; text-shadow:0 0 8px rgba(255,215,0,0.5);">{{ __('messages.qr_label') }}</a>
            @endif
        </p>

        @if (config('library.analytics.host'))
            <div class="mt-3 flex items-center justify-center gap-3 opacity-75 hover:opacity-100 transition-opacity duration-300">
                <span style="font-size: 1rem;">👁</span>
                <div class="flex items-center gap-3 no-underline rounded-full border border-white/20 px-4 py-1.5"
                     style="background:rgba(255,255,255,0.12); font-size: 1rem; color:rgba(255,255,255,0.85);">
                    <img src="{{ rtrim(config('library.analytics.host'), '/') }}/counter/TOTAL.svg"
                         alt="{{ __('messages.visitors_label') }}"
                         class="h-[40px] w-auto align-middle"
                         loading="lazy" decoding="async">
                    <span id="visitor-label">{{ __('messages.visitors_label') }}</span>
                </div>
            </div>
        @endif
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

// Header scroll shadow
(function(){
    var hdr = document.getElementById('site-header');
    if(!hdr) return;
    window.addEventListener('scroll',function(){
        hdr.style.boxShadow = window.scrollY>40
            ? '0 6px 36px rgba(60,40,100,0.28)'
            : '0 4px 24px rgba(102,126,234,0.25)';
    },{passive:true});
})();

// Kurdish dialect dropdown
function toggleKuDropdown(){
    var btn=document.getElementById('kuMainBtn');
    var dd=document.getElementById('kuDropdown');
    var arrow=document.getElementById('kuArrow');
    var open=dd.classList.contains('open');
    closeKuDropdown();
    if(!open){
        var rect=btn.getBoundingClientRect();
        dd.style.top=(rect.bottom+6)+'px';
        dd.style.right=(window.innerWidth-rect.right)+'px';
        btn.classList.add('active');
        btn.setAttribute('aria-expanded','true');
        dd.classList.add('open');
        arrow.style.transform='rotate(180deg)';
    }
}
function closeKuDropdown(){
    var btn=document.getElementById('kuMainBtn');
    if(!btn) return;
    btn.classList.remove('active');
    btn.setAttribute('aria-expanded','false');
    document.getElementById('kuDropdown').classList.remove('open');
    document.getElementById('kuArrow').style.transform='';
}
document.addEventListener('click',function(e){
    var kg=document.getElementById('kuGroup');
    if(kg && !kg.contains(e.target)) closeKuDropdown();
});
document.addEventListener('keydown',function(e){ if(e.key==='Escape') closeKuDropdown(); });

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
