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
    <title>{{ __('messages.site_title') }}</title>
    <meta name="description" content="{{ __('messages.meta_description') }}">
    <meta name="robots" content="index, follow">
    <meta name="theme-color" content="#667eea">
    <link rel="icon" href="{{ Asset::versioned('favicon.ico') }}" sizes="16x16 32x32 48x48">
    <link rel="icon" type="image/png" href="{{ Asset::versioned('favicon-96.png') }}" sizes="96x96">
    <link rel="apple-touch-icon" href="{{ Asset::versioned('apple-touch-icon.png') }}">
    <link rel="canonical" href="{{ Locale::url() }}">
    @foreach (App\Support\Locale::SUPPORTED as $alternate)
        <link rel="alternate" hreflang="{{ Locale::languageTag($alternate) }}" href="{{ Locale::url($alternate) }}">
    @endforeach
    <link rel="alternate" hreflang="x-default" href="{{ url('/') }}">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="{{ __('messages.site_title') }}">
    <meta property="og:title" content="{{ __('messages.site_title') }}">
    <meta property="og:description" content="{{ __('messages.meta_description') }}">
    <meta property="og:url" content="{{ Locale::url() }}">
    <meta property="og:image" content="{{ asset('file/uor-logo.png') }}">
    <meta property="og:locale" content="{{ Locale::languageTag() }}">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ __('messages.site_title') }}">
    <meta name="twitter:description" content="{{ __('messages.meta_description') }}">
    @vite(['resources/css/app.css'])
    <script type="application/ld+json">
    {
        "@@context": "https://schema.org",
        "@type": "Library",
        "name": "کتێبخانەی ئەلیکترۆنی کۆلێژی زانست - زانکۆی ڕاپەڕین",
        "alternateName": "Raparin Science College Electronic Library",
        "url": "{{ url('/') }}",
        "logo": "{{ asset('file/uor-logo.png') }}",
        "description": @json(__('messages.jsonld_description')),
        "inLanguage": ["ckb", "en", "ar", "fa", "tr"],
        "isAccessibleForFree": true,
        "sameAs": ["https://github.com/mahmodfaqe/Library"]
    }
    </script>
</head>
<body class="bg-[#f8f9ff] text-[#2d2d3a] overflow-x-hidden" dir="{{ Locale::dir() }}">

<a class="skip-link" href="#main">{{ __('messages.skip_to_content') }}</a>

<div id="scrollProgress"></div>

<!-- ══════════ HEADER ══════════ -->
<header id="site-header" class="sticky top-0 z-[1000] backdrop-blur-md transition-shadow duration-300"
        style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); box-shadow: 0 4px 24px rgba(102,126,234,0.25);">
    <div class="max-w-[1200px] mx-auto px-4 sm:px-6 lg:px-8 py-2 sm:py-3">
        <div class="flex items-center gap-2 sm:gap-4 flex-nowrap">

            <!-- Logo -->
            <a class="logo-link flex items-center gap-1.5 sm:gap-2.5 shrink min-w-0 no-underline" href="#">
                <img src="{{ asset('file/uor-logo.webp') }}"
                     alt="University of Raparin Logo"
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

            <!-- Language Switcher -->
            <div class="flex items-center gap-1 flex-nowrap ms-auto shrink-0">
                <!-- Kurdish dropdown group -->
                <div class="relative" id="kuGroup">
                    <button id="kuMainBtn"
                            class="{{ str_starts_with(app()->getLocale(), 'ku') ? 'active ' : '' }}flex items-center gap-1 px-2 py-1.5 rounded-md text-white border border-white/20 bg-white/15 cursor-pointer transition-all duration-200 hover:-translate-y-0.5 hover:border-white/45 hover:shadow-md font-[inherit]"
                            style="font-size: clamp(0.52rem,1.4vw,0.8rem); min-height:30px;"
                            onclick="toggleKuDropdown()">
                        کوردی <span class="text-[0.5rem] transition-transform duration-300" id="kuArrow">▼</span>
                    </button>
                    <div class="ku-dropdown" id="kuDropdown">
                        <a href="{{ Locale::url('ku-sorani') }}" class="ku-dialect-btn block w-full bg-transparent border-0 text-white text-right cursor-pointer transition-colors duration-200 hover:bg-white/15 font-[Rabar,sans-serif] no-underline{{ app()->getLocale() === 'ku-sorani' ? ' active' : '' }}" style="padding: clamp(0.38rem,1.2vw,0.55rem) clamp(0.6rem,2vw,1rem); font-size:clamp(0.58rem,1.4vw,0.8rem); min-height:36px;">
                            سۆرانی<span class="block text-[0.5em] opacity-65 mt-px">Soranî</span>
                        </a>
                        <a href="{{ Locale::url('ku-badini') }}" class="ku-dialect-btn block w-full bg-transparent border-0 text-white text-right cursor-pointer transition-colors duration-200 hover:bg-white/15 font-[Rabar,sans-serif] no-underline{{ app()->getLocale() === 'ku-badini' ? ' active' : '' }}" style="padding: clamp(0.38rem,1.2vw,0.55rem) clamp(0.6rem,2vw,1rem); font-size:clamp(0.58rem,1.4vw,0.8rem); min-height:36px;">
                            بادینی<span class="block text-[0.5em] opacity-65 mt-px">Kurmancî (عەرەبی)</span>
                        </a>
                        <a href="{{ Locale::url('ku-badini-lat') }}" class="ku-dialect-btn block w-full bg-transparent border-0 text-white text-left cursor-pointer transition-colors duration-200 hover:bg-white/15 font-sans no-underline{{ app()->getLocale() === 'ku-badini-lat' ? ' active' : '' }}" style="padding: clamp(0.38rem,1.2vw,0.55rem) clamp(0.6rem,2vw,1rem); font-size:clamp(0.58rem,1.4vw,0.8rem); min-height:36px;">
                            Badînî<span class="block text-[0.5em] opacity-65 mt-px">Kurmancî (Latînî)</span>
                        </a>
                        <a href="{{ Locale::url('ku-hawrami') }}" class="ku-dialect-btn block w-full bg-transparent border-0 text-white text-right cursor-pointer transition-colors duration-200 hover:bg-white/15 font-[Rabar,sans-serif] no-underline{{ app()->getLocale() === 'ku-hawrami' ? ' active' : '' }}" style="padding: clamp(0.38rem,1.2vw,0.55rem) clamp(0.6rem,2vw,1rem); font-size:clamp(0.58rem,1.4vw,0.8rem); min-height:36px;">
                            هەورامی<span class="block text-[0.5em] opacity-65 mt-px">Hewramî</span>
                        </a>
                    </div>
                </div>

                <!-- Divider -->
                <div class="shrink-0 w-px bg-white/25 mx-1" style="height:clamp(16px,3.5vw,22px);"></div>

                <!-- Other languages -->
                <div class="flex items-center gap-1 flex-nowrap overflow-x-auto" style="scrollbar-width:none; max-width:clamp(100px,40vw,400px);">
                    <a href="{{ Locale::url('en') }}" class="lang-btn px-2 py-1.5 rounded-md text-white border border-white/20 bg-white/15 cursor-pointer transition-all duration-200 hover:-translate-y-0.5 hover:border-white/45 hover:shadow-md whitespace-nowrap shrink-0 font-[inherit] no-underline{{ app()->getLocale() === 'en' ? ' active' : '' }}" style="font-size:clamp(0.52rem,1.4vw,0.8rem); min-height:30px;">English</a>
                    <a href="{{ Locale::url('ar') }}" class="lang-btn px-2 py-1.5 rounded-md text-white border border-white/20 bg-white/15 cursor-pointer transition-all duration-200 hover:-translate-y-0.5 hover:border-white/45 hover:shadow-md whitespace-nowrap shrink-0 font-[inherit] no-underline{{ app()->getLocale() === 'ar' ? ' active' : '' }}" style="font-size:clamp(0.52rem,1.4vw,0.8rem); min-height:30px;">العربية</a>
                    <a href="{{ Locale::url('fa') }}" class="lang-btn px-2 py-1.5 rounded-md text-white border border-white/20 bg-white/15 cursor-pointer transition-all duration-200 hover:-translate-y-0.5 hover:border-white/45 hover:shadow-md whitespace-nowrap shrink-0 font-[inherit] no-underline{{ app()->getLocale() === 'fa' ? ' active' : '' }}" style="font-size:clamp(0.52rem,1.4vw,0.8rem); min-height:30px;">فارسی</a>
                    <a href="{{ Locale::url('tr') }}" class="lang-btn px-2 py-1.5 rounded-md text-white border border-white/20 bg-white/15 cursor-pointer transition-all duration-200 hover:-translate-y-0.5 hover:border-white/45 hover:shadow-md whitespace-nowrap shrink-0 font-[inherit] no-underline{{ app()->getLocale() === 'tr' ? ' active' : '' }}" style="font-size:clamp(0.52rem,1.4vw,0.8rem); min-height:30px;">Türkçe</a>
                </div>
            </div>
        </div>
    </div>
</header>



<main id="main">

<!-- ══════════ HERO ══════════ -->
<section id="hero-section" class="relative overflow-hidden flex items-center text-white text-center transition-all duration-1000 ease-in-out"
         style="background-image: url('{{ asset('file/image1.webp') }}'); background-size: cover; background-position: center; background-repeat: no-repeat; padding: clamp(6rem,14vw,11rem) 0 clamp(5rem,10vw,9rem); min-height: clamp(420px,65vh,680px);">

    <div class="absolute inset-0 bg-black/40 z-0"></div>

    <div class="absolute inset-0 overflow-hidden" aria-hidden="true">
        <span class="mesh-blob" style="width:45vw;height:45vw;background:rgba(255,255,255,0.15);top:-10%;left:-8%;animation-delay:0s;"></span>
        <span class="mesh-blob" style="width:35vw;height:35vw;background:rgba(255,107,107,0.2);top:20%;right:-5%;animation-delay:-4s;"></span>
        <span class="mesh-blob" style="width:30vw;height:30vw;background:rgba(255,255,255,0.1);bottom:-10%;left:30%;animation-delay:-8s;"></span>
    </div>

    <div class="container mx-auto relative z-20 px-4">
        <div class="max-w-4xl mx-auto">

            <p class="opacity-80 tracking-widest mb-2" style="font-size:clamp(1rem,3vw,1.3rem);">{{ __('messages.hero.welcome') }}</p>
            <h1 class="font-bold mb-6 leading-tight" style="font-size:clamp(2.5rem,8vw,4.5rem);">{{ __('messages.hero.title') }}</h1>
            <p class="mb-8 opacity-95 mx-auto max-w-3xl" style="font-size:clamp(0.95rem,2.5vw,1.25rem);">{{ __('messages.hero.subtitle') }}</p>

        </div>
    </div>
</section>

<!-- ══════════ INTRO SECTION ══════════ -->
<section class="bg-white" style="padding: clamp(3rem,8vw,6rem) 0;">
    <div class="max-w-[1200px] mx-auto px-4 sm:px-6 lg:px-8">

        <div class="text-center max-w-[900px] mx-auto">
            <h2 class="font-bold text-[#2d2d3a] mb-8" style="font-size:clamp(1.7rem,4.5vw,2.4rem);">{{ __('messages.intro.heading') }}</h2>

            <div class="relative rounded-[18px] overflow-hidden shimmer-border reveal"
                 style="background: linear-gradient(145deg, #f5f7ff 0%, #eef0f8 100%); padding: clamp(1.5rem,5vw,3rem); box-shadow: 0 8px 32px rgba(102,126,234,0.15);">

                @foreach (__('messages.intro.paragraphs') as $paragraph)
                    <p class="text-[#6b6b80] leading-[1.9] mb-6 text-justify" style="font-size:clamp(0.95rem,2.5vw,1.15rem);">{{ $paragraph }}</p>
                @endforeach

                <!-- Objectives -->
                <div class="rounded-[10px] p-6 mb-6 border border-[rgba(102,126,234,0.16)] transition-all duration-300 hover:shadow-md reveal"
                     style="background:rgba(102,126,234,0.08);">
                    <h3 class="text-[#667eea] mb-4 font-bold" style="font-size:clamp(1.1rem,3vw,1.4rem);">{{ __('messages.intro.objectives_heading') }}</h3>
                    <ul class="list-none p-0 text-[#6b6b80] leading-loose" style="font-size:clamp(0.9rem,2.5vw,1.05rem);">
                        @php
                            $qrLabel = e(__('messages.intro.qr_label'));
                            $qrLink = config('library.qr_url')
                                ? '<a href="'.e(config('library.qr_url')).'" style="color: gold; text-shadow: 0 0 8px gold, 0 0 15px gold;">'.$qrLabel.'</a>'
                                : $qrLabel;
                        @endphp
                        @foreach (__('messages.intro.objectives') as $objective)
                            <li class="mb-1 pl-6">{{ RichText::make($objective, ['qr' => $qrLink]) }}</li>
                        @endforeach
                    </ul>
                </div>

                <!-- Prepared by -->
                <div class="rounded-[10px] p-6 border border-[rgba(255,107,107,0.15)] reveal" style="background:rgba(255,107,107,0.07);">
                    <h3 class="text-[#ff6b6b] mb-4 font-bold" style="font-size:clamp(1.1rem,3vw,1.4rem);">{{ __('messages.intro.prepared_heading') }}</h3>
                    <div class="grid gap-4" style="grid-template-columns: repeat(auto-fit, minmax(min(220px,100%),1fr));">
                        @foreach (__('messages.intro.people') as $person)
                            <div class="text-center p-5 bg-white rounded-xl shadow-xs transition-all duration-300 hover:-translate-y-1 hover:shadow-md">
                                <h4 class="text-[#2d2d3a] mb-2 font-bold">{{ $person['name'] }}</h4>
                                <p class="text-[#6b6b80]">{{ $person['role'] }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

    </div>
</section>
    
<!-- ══════════ HISTORY SECTION ══════════ -->
<section class="bg-white" style="padding: clamp(3rem,8vw,6rem) 0;">
    <div class="max-w-[1200px] mx-auto px-4 sm:px-6 lg:px-8">
        <div class="max-w-[900px] mx-auto">

            <h2 class="font-bold text-[#2d2d3a] mb-8 text-center" style="font-size:clamp(1.7rem,4.5vw,2.4rem);">{{ __('messages.history.heading') }}</h2>

            <div class="relative rounded-[18px] overflow-hidden shimmer-border reveal"
                 style="background: linear-gradient(145deg, #f5f7ff 0%, #eef0f8 100%); padding: clamp(1.5rem,5vw,3rem); box-shadow: 0 8px 32px rgba(102,126,234,0.15);">
                @php
                    $openingDate = '<strong>'.e(__('messages.history.opening_date')).'</strong>';
                @endphp
                @foreach (__('messages.history.paragraphs') as $paragraph)
                    <p class="text-[#6b6b80] leading-[1.9] {{ $loop->last ? '' : 'mb-4' }} text-justify" style="font-size:clamp(0.95rem,2.5vw,1.15rem);">{{ RichText::make($paragraph, ['date' => $openingDate]) }}</p>
                @endforeach
            </div>

        </div>
    </div>
</section>
<!-- ══════════ LIBRARY SECTION 1 ══════════ -->
<section style="padding: clamp(1rem,4vw,0.5rem) 0; background: linear-gradient(160deg, #f0f2ff 0%, #e8ebff 100%);">
    <div class="max-w-[1200px] mx-auto px-6 sm:px-8 lg:px-10">
        <div class="text-center mb-10 reveal">
            <h2 class="font-bold text-[#2d2d3a] mb-6" style="font-size:clamp(1.7rem,4.5vw,2.4rem);">{{ __('messages.library.heading') }}</h2>
            @if (config('library.drive.main'))
                <a href="{{ config('library.drive.main') }}"
                   class="main-library-btn relative inline-block font-bold text-white rounded-full no-underline text-center transition-all duration-300 hover:-translate-y-1.5"
                   style="background: linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%); padding: clamp(0.9rem,3vw,1.15rem) clamp(2rem,6vw,3rem); font-size:clamp(0.95rem,2.5vw,1.15rem); box-shadow: 0 8px 24px rgba(255,107,107,0.35); min-width:200px;"
                   target="_blank">{{ __('messages.library.button_1') }}</a>
            @endif
        </div>
    </div>
</section>

<!-- ══════════ LIBRARY 2 + DEPARTMENTS ══════════ -->
<section style="padding: clamp(1rem,4vw,3rem) 0; background: linear-gradient(160deg, #f0f2ff 0%, #e8ebff 100%);">
    <div class="max-w-[1200px] mx-auto px-6 sm:px-8 lg:px-10">

        <!-- Library 2 button -->
        <div class="text-center">
            @if (config('library.drive.secondary'))
            <a href="{{ config('library.drive.secondary') }}" class="main-library-btn relative inline-block font-bold text-white rounded-full no-underline text-center transition-all duration-300 hover:-translate-y-1.5" style="background: linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%); padding: clamp(0.9rem,3vw,1.15rem) clamp(2rem,6vw,3rem); font-size:clamp(0.95rem,2.5vw,1.15rem); box-shadow: 0 8px 24px rgba(255,107,107,0.35); min-width:200px;" target="_blank">{{ __('messages.library.button_2') }}</a>
            @endif
        </div>

        <div class="text-center" style="margin-top:1.4rem;">
            <a href="{{ Locale::booksUrl() }}"
               class="section-btn inline-block font-semibold text-white rounded-full no-underline text-center transition-all duration-300 hover:-translate-y-1"
               style="background:linear-gradient(135deg,#667eea 0%,#764ba2 100%); padding:clamp(0.6rem,2.2vw,0.85rem) clamp(1.5rem,4vw,2.2rem); font-size:clamp(0.88rem,2.2vw,1rem); box-shadow:0 4px 14px rgba(102,126,234,0.28);">
                {{ __('books.title') }}
            </a>
        </div>

        <!-- Department cards (DB-driven) -->
        <div id="dept-{{ app()->getLocale() }}" style="direction:{{ Locale::dir() }};">
            <h2 class="text-center font-bold text-[#2d2d3a] mb-10" style="font-size:clamp(1.7rem,4.5vw,2.4rem);">{{ __('messages.dept_heading') }}</h2>
            <div class="grid gap-5" style="grid-template-columns: repeat(auto-fit, minmax(260px,1fr));">
                @forelse ($departments as $department)
                <div class="section-card card-top-bar card-glow relative flex flex-col justify-between bg-white/85 backdrop-blur-md border border-white/70 rounded-[18px] text-center transition-all duration-300 hover:-translate-y-3 reveal" style="padding:clamp(1.5rem,4vw,2.2rem); min-height:280px; box-shadow:0 4px 16px rgba(102,126,234,0.10);">
                    <div>
                        <span class="block text-5xl mb-4 transition-transform duration-300">{{ $department->icon }}</span>
                        <h3 class="font-bold text-[#2d2d3a] mb-3" style="font-size:clamp(1.1rem,3vw,1.4rem);">{{ $department->translation(app()->getLocale(), 'title') }}</h3>
                        <p class="text-[#6b6b80] mb-4 grow" style="font-size:clamp(0.88rem,2.2vw,0.98rem); line-height:1.65;">{{ $department->translation(app()->getLocale(), 'desc') }}</p>
                    </div>
                    <a href="{{ $department->drive_url }}" class="section-btn relative inline-block font-semibold text-white rounded-full no-underline text-center transition-all duration-300 hover:-translate-y-1 font-[inherit]" style="background:linear-gradient(135deg,#667eea 0%,#764ba2 100%); padding:clamp(0.6rem,2.2vw,0.85rem) clamp(1.3rem,3.5vw,1.9rem); font-size:clamp(0.85rem,2.2vw,0.95rem); min-width:130px; box-shadow:0 4px 14px rgba(102,126,234,0.28);" target="_blank">{{ $department->translation(app()->getLocale(), 'button') }}</a>
                </div>
                @empty
                <p class="text-center text-[#6b6b80]">{{ __('messages.no_departments') }}</p>
                @endforelse
            </div>
        </div>
    </div>
</section>

<!-- ══════════ ABOUT SECTION ══════════ -->
<section class="bg-white" style="padding: clamp(3rem,8vw,6rem) 0;">
    <div class="max-w-[1200px] mx-auto px-4 sm:px-6 lg:px-8">
        <div class="max-w-[900px] mx-auto">

            <!-- BioNova, the team behind the library -->
            <img src="{{ Asset::versioned('file/bionova-logo.webp') }}"
                 alt="BioNova"
                 width="400" height="464"
                 loading="lazy" decoding="async"
                 class="bionova-mark block mx-auto mb-6 reveal"
                 style="width: clamp(104px, 20vw, 156px); height: auto;">

            <h2 class="font-bold text-[#2d2d3a] mb-8 text-center" style="font-size:clamp(1.7rem,4.5vw,2.4rem);">{{ __('messages.about.heading') }}</h2>

            <div class="relative rounded-[18px] overflow-hidden reveal"
                 style="background: linear-gradient(145deg, #f5f7ff 0%, #eef0f8 100%); padding: clamp(1.5rem,5vw,3rem); box-shadow: 0 8px 32px rgba(102,126,234,0.15);">
                <div class="absolute top-0 left-0 right-0 h-1" style="background: linear-gradient(90deg, #667eea, #764ba2);"></div>

                <p class="text-[#6b6b80] leading-[1.9] mb-6 text-justify" style="font-size:clamp(0.95rem,2.5vw,1.15rem);">{{ __('messages.about.intro') }}</p>

                <div class="rounded-[10px] p-6" style="background:rgba(102,126,234,0.09); border:1px solid rgba(102,126,234,0.16);">
                    <h3 class="text-[#667eea] mb-3 text-center font-bold" style="font-size:clamp(1.1rem,3vw,1.35rem);">{{ __('messages.about.mission_heading') }}</h3>
                    <p class="text-[#6b6b80] mb-0 italic text-justify" style="font-size:clamp(0.95rem,2.5vw,1.15rem); font-style: italic;">{{ __('messages.about.mission_text') }}</p>
                </div>
            </div>

        </div>
    </div>
</section>
<!-- ══════════ PRE-FOOTER BANNER ══════════ -->
<section style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: clamp(1.2rem,3vw,1.8rem) 0;">
    <div class="max-w-[1200px] mx-auto px-4 text-center text-white">
        <p style="font-size:clamp(0.88rem,2.2vw,1.05rem); opacity:0.92; margin:0; line-height:1.7;">
            {{ __('messages.prefooter_note') }}
        </p>
    </div>
</section>
<!-- ══════════ FEEDBACK & SUGGESTIONS ══════════ -->
<section id="feedback" style="padding: clamp(3rem,8vw,6rem) 0; background: linear-gradient(160deg, #eef1ff 0%, #e3e9ff 100%);">
    <div class="max-w-[760px] mx-auto px-4 sm:px-6">
        @if (session('feedback_sent'))
            <div class="mb-6 rounded-[14px] px-5 py-4 text-center font-semibold" style="background:#d1fae5; color:#065f46; font-size:clamp(0.9rem,2vw,1rem);">
                    {{ __('messages.feedback.success') }}
            </div>
        @endif
        @if ($errors->has('message'))
            <div class="mb-6 rounded-[14px] px-5 py-4 text-center font-semibold" style="background:#fee2e2; color:#991b1b; font-size:clamp(0.9rem,2vw,1rem);">
                {{ __('messages.feedback.error') }}
            </div>
        @endif
        <div class="rounded-[22px] bg-white/85 backdrop-blur-md border border-white/70 text-center" style="box-shadow:0 10px 40px rgba(102,126,234,0.14); padding: clamp(1.8rem,5vw,3rem);">
            <h2 class="font-bold text-[#2d2d3a] mb-3" style="font-size:clamp(1.5rem,4vw,2rem);">
                    {{ __('messages.feedback.title') }}
            </h2>
            <p class="text-[#6b6b80] mb-6" style="font-size:clamp(0.9rem,2.2vw,1rem); line-height:1.7;">
                    {{ __('messages.feedback.subtitle') }}
                <br>
                <a href="{{ route('privacy') }}" style="color:#667eea; text-decoration:underline; text-underline-offset:3px; font-size:0.88em;">{{ __('privacy.title') }}</a>
            </p>
            <form method="POST" action="{{ route('feedback.store') }}" style="text-align:start;">
                @csrf
                <div class="mb-4 text-start">
                    <label for="fb-name" class="block font-semibold mb-1 text-[#4a4a5c]" style="font-size:0.9rem;">
                        {{ __('messages.feedback.name_label') }}
                    </label>
                    <input type="text" id="fb-name" name="name" maxlength="120" value="{{ old('name') }}"
                           class="w-full rounded-[12px] px-4 py-3" style="border:1px solid #d5d9ee; font-size:0.95rem; font-family:inherit; text-align:start;">
                </div>
                <div class="mb-5 text-start">
                    <label for="fb-msg" class="block font-semibold mb-1 text-[#4a4a5c]" style="font-size:0.9rem;">
                        {{ __('messages.feedback.message_label') }}
                    </label>
                    <textarea id="fb-msg" name="message" rows="4" maxlength="2000" required
                              class="w-full rounded-[12px] px-4 py-3" style="border:1px solid #d5d9ee; font-size:0.95rem; font-family:inherit; text-align:start;">{{ old('message') }}</textarea>
                </div>
                <div style="text-align:center;">
                    <button type="submit" class="section-btn relative inline-block font-semibold text-white rounded-full no-underline text-center transition-all duration-300 hover:-translate-y-1 font-[inherit] cursor-pointer" style="background:linear-gradient(135deg,#667eea 0%,#764ba2 100%); padding:clamp(0.65rem,2.2vw,0.9rem) clamp(1.6rem,4vw,2.4rem); font-size:clamp(0.9rem,2.2vw,1rem); box-shadow:0 4px 14px rgba(102,126,234,0.28); border:none;">
                        {{ __('messages.feedback.send') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</section>
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
        <p style="font-size:clamp(0.85rem,2vw,0.95rem);"><a href="{{ route('privacy') }}" style="color:#9bb1ff; text-decoration:underline; text-underline-offset:3px;">{{ __('privacy.title') }}</a></p>

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
<button id="scrollTopBtn" title="{{ __('messages.scroll_top') }}" aria-label="{{ __('messages.scroll_top') }}">
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

// Hero particles
(function(){
    var container = document.getElementById('heroParticles');
    if(!container) return;
    for(var i=0;i<22;i++){
        var p = document.createElement('div');
        p.className = 'particle';
        p.style.cssText = [
            'left:'+Math.random()*100+'%',
            'top:'+Math.random()*100+'%',
            'width:'+(Math.random()*4+2)+'px',
            'height:'+(Math.random()*4+2)+'px',
            'animation-duration:'+(Math.random()*14+8)+'s',
            'animation-delay:'+(Math.random()*-15)+'s',
            'opacity:'+(Math.random()*0.5+0.1)
        ].join(';');
        container.appendChild(p);
    }
})();

// Header scroll shadow
(function(){
    var hdr = document.getElementById('site-header');
    window.addEventListener('scroll',function(){
        hdr.style.boxShadow = window.scrollY>40
            ? '0 6px 36px rgba(60,40,100,0.28)'
            : '0 4px 24px rgba(102,126,234,0.25)';
    },{passive:true});
})();

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
        dd.classList.add('open');
        arrow.style.transform='rotate(180deg)';
    }
}
function closeKuDropdown(){
    document.getElementById('kuMainBtn').classList.remove('active');
    document.getElementById('kuDropdown').classList.remove('open');
    document.getElementById('kuArrow').style.transform='';
}
document.addEventListener('click',function(e){
    var kg=document.getElementById('kuGroup');
    if(kg && !kg.contains(e.target)) closeKuDropdown();
});

// Scroll reveal
var revealObserver = new IntersectionObserver(function(entries){
    entries.forEach(function(e){
        if(e.isIntersecting){
            e.target.classList.add('visible');
            revealObserver.unobserve(e.target);
        }
    });
},{threshold:0.1, rootMargin:'0px 0px -40px 0px'});

function triggerReveal(){
    document.querySelectorAll('.reveal').forEach(function(el){
        var rect=el.getBoundingClientRect();
        if(rect.top<window.innerHeight-40){ el.classList.add('visible'); }
        else { el.classList.remove('visible'); revealObserver.observe(el); }
    });
}
triggerReveal();
document.querySelectorAll('.reveal').forEach(function(el){ revealObserver.observe(el); });

// Scroll progress
(function(){
    var bar=document.getElementById('scrollProgress');
    window.addEventListener('scroll',function(){
        var pct=(window.scrollY/(document.documentElement.scrollHeight-window.innerHeight))*100;
        bar.style.width=pct+'%';
    },{passive:true});
})();

// Scroll to top
(function(){
    var btn=document.getElementById('scrollTopBtn');
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

// Section card icon hover
document.querySelectorAll('.section-card').forEach(function(card){
    var icon=card.querySelector('.section-icon, span.text-5xl');
    if(icon){
        card.addEventListener('mouseenter',function(){ icon.style.transform='scale(1.18) translateY(-4px)'; });
        card.addEventListener('mouseleave',function(){ icon.style.transform=''; });
    }
});
</script>
<script>
    const images = [
        '{{ asset('file/image1.webp') }}',
        '{{ asset('file/image2.webp') }}',
        '{{ asset('file/image3.webp') }}',
        '{{ asset('file/image4.webp') }}'
    ];

    let currentIndex = 0;
    const heroSection = document.getElementById('hero-section');

    function changeBackground() {
        currentIndex = (currentIndex + 1) % images.length;
        heroSection.style.backgroundImage = `url('${images[currentIndex]}')`;
    }

    // گۆڕینی وێنەکان هەر ٥ چرکە جارێک
    setInterval(changeBackground, 5000);
</script>

@if (config('library.analytics.host'))
    <script data-goatcounter="{{ rtrim(config('library.analytics.host'), '/') }}/count"
            async src="{{ config('library.analytics.script') }}"></script>
@endif
</body>
</html>
