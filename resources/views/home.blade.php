<!DOCTYPE html>
<html lang="{{ str_starts_with(app()->getLocale(), 'ku') ? 'ku' : app()->getLocale() }}" dir="{{ in_array(app()->getLocale(), ['en','tr','ku-badini-lat']) ? 'ltr' : 'rtl' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="dns-prefetch" href="https://cdn.tailwindcss.com">
    <link rel="dns-prefetch" href="https://mahmodfaqe.goatcounter.com">
    <link href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@400;700&display=swap" rel="stylesheet">
    <title>{{ __('messages.site_title') }}</title>
    <meta name="description" content="{{ __('messages.meta_description') }}">
    <meta name="robots" content="index, follow">
    <meta name="theme-color" content="#667eea">
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="canonical" href="{{ url('/') }}">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="{{ __('messages.site_title') }}">
    <meta property="og:title" content="{{ __('messages.site_title') }}">
    <meta property="og:description" content="{{ __('messages.meta_description') }}">
    <meta property="og:url" content="{{ url('/') }}">
    <meta property="og:image" content="{{ asset('file/uor-logo.png') }}">
    <meta property="og:locale" content="ku">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ __('messages.site_title') }}">
    <meta name="twitter:description" content="{{ __('messages.meta_description') }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#667eea',
                        secondary: '#764ba2',
                        accent: '#ff6b6b',
                        'text-main': '#2d2d3a',
                        'text-light': '#6b6b80',
                        'bg-main': '#f8f9ff',
                    },
                    fontFamily: {
                      kufi: ['Rabar', 'sans-serif'],
                    },
                    backdropBlur: {
                        xs: '2px',
                    },
                    animation: {
                        'mesh-float': 'meshFloat 12s ease-in-out infinite',
                        'particle-drift': 'particleDrift linear infinite',
                        'hero-fade-up': 'heroFadeUp 0.8s ease-out both',
                        'shimmer-bar': 'shimmerBar 3s linear infinite',
                        'dropdown-open': 'dropdownOpen 0.22s cubic-bezier(0.34,1.56,0.64,1) forwards',
                        'progress-shimmer': 'progressShimmer 3s linear infinite',
                    },
                    keyframes: {
                        meshFloat: {
                            '0%,100%': { transform: 'translate(0,0) scale(1)' },
                            '33%':     { transform: 'translate(3%,4%) scale(1.06)' },
                            '66%':     { transform: 'translate(-2%,2%) scale(0.96)' },
                        },
                        particleDrift: {
                            '0%':   { transform: 'translateY(0) translateX(0) scale(1)', opacity: '0' },
                            '10%':  { opacity: '1' },
                            '90%':  { opacity: '0.6' },
                            '100%': { transform: 'translateY(-120vh) translateX(30px) scale(0)', opacity: '0' },
                        },
                        heroFadeUp: {
                            from: { opacity: '0', transform: 'translateY(28px)' },
                            to:   { opacity: '1', transform: 'translateY(0)' },
                        },
                        shimmerBar: {
                            '0%':   { backgroundPosition: '200% 0' },
                            '100%': { backgroundPosition: '-200% 0' },
                        },
                        dropdownOpen: {
                            from: { opacity: '0', transform: 'scale(0.88) translateY(-8px)' },
                            to:   { opacity: '1', transform: 'scale(1) translateY(0)' },
                        },
                        progressShimmer: {
                            '0%':   { backgroundPosition: '0% 0' },
                            '100%': { backgroundPosition: '200% 0' },
                        },
                    },
                    transitionTimingFunction: {
                        'spring': 'cubic-bezier(0.34,1.56,0.64,1)',
                        'smooth': 'cubic-bezier(0.4,0,0.2,1)',
                    },
                },
            },
        }
    </script>
    <style>
       @font-face {
    font-family: 'Rabar';
    src: url('{{ asset('fonts/Rabar_015.ttf') }}') format('truetype');
    font-weight: normal;
    font-style: normal;
}
        /* Noise texture overlay */
        body::before {
            content: '';
            position: fixed;
            inset: 0;
            background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 256 256' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='noise'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23noise)' opacity='0.03'/%3E%3C/svg%3E");
            pointer-events: none;
            z-index: 9999;
            opacity: 0.4;
        }

        html { font-size: 16px; scroll-behavior: smooth; }
        @media (max-width: 480px)  { html { font-size: 14px; } }
        @media (min-width: 1200px) { html { font-size: 18px; } }

        body { font-family: 'Rabar', sans-serif; }

        /* Scroll progress bar */
        #scrollProgress {
            position: fixed; top: 0; left: 0; width: 0%;
            height: 3px;
            background: linear-gradient(90deg, #ff6b6b, #667eea, #764ba2, #ff6b6b);
            background-size: 200% 100%;
            z-index: 2000;
            transition: width 0.1s linear;
            animation: progressShimmer 3s linear infinite;
        }

        /* Shimmer bar on intro-card top border */
        .shimmer-border::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 4px;
            background: linear-gradient(90deg, #667eea, #ff6b6b, #764ba2);
            background-size: 200% 100%;
            animation: shimmerBar 3s linear infinite;
        }

        /* Section card top bar */
        .card-top-bar::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 3px;
            background: linear-gradient(90deg, #667eea, #764ba2);
            transform: scaleX(0);
            transform-origin: left;
            transition: transform 0.4s cubic-bezier(0.4,0,0.2,1);
        }
        .card-top-bar:hover::before { transform: scaleX(1); }

        /* Card glow on hover */
        .card-glow::after {
            content: '';
            position: absolute;
            inset: 0;
            border-radius: inherit;
            background: radial-gradient(circle at 50% 0%, rgba(102,126,234,0.08), transparent 70%);
            opacity: 0;
            transition: opacity 0.4s ease;
            pointer-events: none;
        }
        .card-glow:hover::after { opacity: 1; }

        /* Scroll reveal */
        .reveal {
            opacity: 0;
            transform: translateY(32px);
            transition: opacity 0.65s cubic-bezier(0.4,0,0.2,1), transform 0.65s cubic-bezier(0.4,0,0.2,1);
        }
        .reveal.visible { opacity: 1; transform: translateY(0); }

        /* Particle */
        .particle {
            position: absolute;
            background: rgba(255,255,255,0.5);
            border-radius: 50%;
            animation: particleDrift linear infinite;
        }
        #hero-section {
        transition: background-image 1.5s ease-in-out;
        }

        /* Hero mesh spans */
        .mesh-blob {
            position: absolute;
            border-radius: 50%;
            filter: blur(60px);
            animation: meshFloat 12s ease-in-out infinite;
            opacity: 0.35;
        }

        /* Scroll-to-top */
        #scrollTopBtn {
            position: fixed;
            bottom: clamp(1.2rem,4vw,2rem);
            left: clamp(1.2rem,4vw,2rem);
            width: clamp(44px,6vw,54px);
            height: clamp(44px,6vw,54px);
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 6px 24px rgba(102,126,234,0.45);
            z-index: 990;
            opacity: 0;
            transform: translateY(20px) scale(0.85);
            transition: opacity 0.35s cubic-bezier(0.4,0,0.2,1), transform 0.35s cubic-bezier(0.34,1.56,0.64,1), box-shadow 0.25s ease;
            pointer-events: none;
        }
        #scrollTopBtn.visible {
            opacity: 1;
            transform: translateY(0) scale(1);
            pointer-events: auto;
        }
        #scrollTopBtn:hover {
            transform: translateY(-5px) scale(1.1);
            box-shadow: 0 14px 36px rgba(102,126,234,0.58);
        }
        #scrollTopBtn:active { transform: translateY(0) scale(0.95); }

        /* KU Dropdown */
        .ku-dropdown {
            display: none;
            position: fixed;
            background: rgba(70,50,130,0.96);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255,255,255,0.15);
            border-radius: 12px;
            overflow: hidden;
            z-index: 9999;
            box-shadow: 0 16px 48px rgba(0,0,0,0.35);
            min-width: clamp(110px,28vw,155px);
            transform-origin: top right;
        }
        .ku-dropdown.open {
            display: block;
            animation: dropdownOpen 0.22s cubic-bezier(0.34,1.56,0.64,1) forwards;
        }

        /* Logo hover */
        .logo-link:hover .uor-logo {
            transform: scale(1.12) rotate(4deg);
            filter: drop-shadow(0 0 10px rgba(255,255,255,0.55));
        }
        .logo-link:hover .logo-title-el  { color: #ffd700; }
        .logo-link:hover .logo-sub-el { color: rgba(255,215,0,0.78); }
        .uor-logo { transition: all 0.45s cubic-bezier(0.4,0,0.2,1); }
        .logo-title-el, .logo-sub-el { transition: color 0.35s ease; }

        /* Main library btn */
        .main-library-btn::after {
            content: '';
            position: absolute;
            inset: 0;
            background: rgba(255,255,255,0);
            transition: background 0.3s ease;
        }
        .main-library-btn:hover::after { background: rgba(255,255,255,0.12); }

        /* section-btn shimmer */
        .section-btn::before {
            content: '';
            position: absolute;
            inset: 0;
            background: rgba(255,255,255,0);
            transition: background 0.25s ease;
        }
        .section-btn:hover::before { background: rgba(255,255,255,0.14); }

        /* Social icon hover */
        .social-link { transition: transform 0.3s cubic-bezier(0.34,1.56,0.64,1), filter 0.3s ease; display: inline-block; }
        .social-link:hover { transform: translateY(-4px) scale(1.15); filter: drop-shadow(0 4px 8px rgba(0,0,0,0.2)); }

        /* Keyboard nav */
        .keyboard-nav *:focus { outline: 2px solid #667eea; outline-offset: 3px; }

        @media (prefers-reduced-motion: reduce) {
            *, ::before, ::after { animation-duration: 0.01ms !important; transition-duration: 0.01ms !important; }
        }
    </style>
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
<body class="bg-[#f8f9ff] text-[#2d2d3a] overflow-x-hidden" dir="{{ in_array(app()->getLocale(), ['en','tr','ku-badini-lat']) ? 'ltr' : 'rtl' }}">

<div id="scrollProgress"></div>

<!-- ══════════ HEADER ══════════ -->
<header id="site-header" class="sticky top-0 z-[1000] backdrop-blur-md transition-shadow duration-300"
        style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); box-shadow: 0 4px 24px rgba(102,126,234,0.25);">
    <div class="max-w-[1200px] mx-auto px-4 sm:px-6 lg:px-8 py-2 sm:py-3">
        <div class="flex items-center gap-2 sm:gap-4 flex-nowrap">

            <!-- Logo -->
            <a class="logo-link flex items-center gap-1.5 sm:gap-2.5 flex-shrink min-w-0 no-underline" href="#">
                <img src="{{ asset('file/uor-logo.png') }}"
                     alt="University of Raparin Logo"
                     class="uor-logo object-contain flex-shrink-0"
                     style="height: clamp(34px,7.5vw,62px);">
                <div class="flex flex-col min-w-0 flex-shrink">
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
            <div class="flex-shrink-0 w-px bg-white/30" style="height: clamp(22px,5vw,36px);"></div>

            <!-- Language Switcher -->
            <div class="flex items-center gap-1 flex-nowrap ms-auto flex-shrink-0">
                <!-- Kurdish dropdown group -->
                <div class="relative" id="kuGroup">
                    <button id="kuMainBtn"
                            class="{{ str_starts_with(app()->getLocale(), 'ku') ? 'active ' : '' }}flex items-center gap-1 px-2 py-1.5 rounded-md text-white border border-white/20 bg-white/15 cursor-pointer transition-all duration-200 hover:-translate-y-0.5 hover:border-white/45 hover:shadow-md font-[inherit]"
                            style="font-size: clamp(0.52rem,1.4vw,0.8rem); min-height:30px;"
                            onclick="toggleKuDropdown()">
                        کوردی <span class="text-[0.5rem] transition-transform duration-300" id="kuArrow">▼</span>
                    </button>
                    <div class="ku-dropdown" id="kuDropdown">
                        <a href="{{ url('language') }}?locale=ku-sorani" class="ku-dialect-btn block w-full bg-transparent border-0 text-white text-right cursor-pointer transition-colors duration-200 hover:bg-white/15 font-[Rabar,sans-serif] no-underline{{ app()->getLocale() === 'ku-sorani' ? ' active' : '' }}" style="padding: clamp(0.38rem,1.2vw,0.55rem) clamp(0.6rem,2vw,1rem); font-size:clamp(0.58rem,1.4vw,0.8rem); min-height:36px;">
                            سۆرانی<span class="block text-[0.5em] opacity-65 mt-px">Soranî</span>
                        </a>
                        <a href="{{ url('language') }}?locale=ku-badini" class="ku-dialect-btn block w-full bg-transparent border-0 text-white text-right cursor-pointer transition-colors duration-200 hover:bg-white/15 font-[Rabar,sans-serif] no-underline{{ app()->getLocale() === 'ku-badini' ? ' active' : '' }}" style="padding: clamp(0.38rem,1.2vw,0.55rem) clamp(0.6rem,2vw,1rem); font-size:clamp(0.58rem,1.4vw,0.8rem); min-height:36px;">
                            بادینی<span class="block text-[0.5em] opacity-65 mt-px">Kurmancî (عەرەبی)</span>
                        </a>
                        <a href="{{ url('language') }}?locale=ku-badini-lat" class="ku-dialect-btn block w-full bg-transparent border-0 text-white text-left cursor-pointer transition-colors duration-200 hover:bg-white/15 font-sans no-underline{{ app()->getLocale() === 'ku-badini-lat' ? ' active' : '' }}" style="padding: clamp(0.38rem,1.2vw,0.55rem) clamp(0.6rem,2vw,1rem); font-size:clamp(0.58rem,1.4vw,0.8rem); min-height:36px;">
                            Badînî<span class="block text-[0.5em] opacity-65 mt-px">Kurmancî (Latînî)</span>
                        </a>
                        <a href="{{ url('language') }}?locale=ku-hawrami" class="ku-dialect-btn block w-full bg-transparent border-0 text-white text-right cursor-pointer transition-colors duration-200 hover:bg-white/15 font-[Rabar,sans-serif] no-underline{{ app()->getLocale() === 'ku-hawrami' ? ' active' : '' }}" style="padding: clamp(0.38rem,1.2vw,0.55rem) clamp(0.6rem,2vw,1rem); font-size:clamp(0.58rem,1.4vw,0.8rem); min-height:36px;">
                            هەورامی<span class="block text-[0.5em] opacity-65 mt-px">Hewramî</span>
                        </a>
                    </div>
                </div>

                <!-- Divider -->
                <div class="flex-shrink-0 w-px bg-white/25 mx-1" style="height:clamp(16px,3.5vw,22px);"></div>

                <!-- Other languages -->
                <div class="flex items-center gap-1 flex-nowrap overflow-x-auto" style="scrollbar-width:none; max-width:clamp(100px,40vw,400px);">
                    <a href="{{ url('language') }}?locale=en" class="lang-btn px-2 py-1.5 rounded-md text-white border border-white/20 bg-white/15 cursor-pointer transition-all duration-200 hover:-translate-y-0.5 hover:border-white/45 hover:shadow-md whitespace-nowrap flex-shrink-0 font-[inherit] no-underline{{ app()->getLocale() === 'en' ? ' active' : '' }}" style="font-size:clamp(0.52rem,1.4vw,0.8rem); min-height:30px;">English</a>
                    <a href="{{ url('language') }}?locale=ar" class="lang-btn px-2 py-1.5 rounded-md text-white border border-white/20 bg-white/15 cursor-pointer transition-all duration-200 hover:-translate-y-0.5 hover:border-white/45 hover:shadow-md whitespace-nowrap flex-shrink-0 font-[inherit] no-underline{{ app()->getLocale() === 'ar' ? ' active' : '' }}" style="font-size:clamp(0.52rem,1.4vw,0.8rem); min-height:30px;">العربية</a>
                    <a href="{{ url('language') }}?locale=fa" class="lang-btn px-2 py-1.5 rounded-md text-white border border-white/20 bg-white/15 cursor-pointer transition-all duration-200 hover:-translate-y-0.5 hover:border-white/45 hover:shadow-md whitespace-nowrap flex-shrink-0 font-[inherit] no-underline{{ app()->getLocale() === 'fa' ? ' active' : '' }}" style="font-size:clamp(0.52rem,1.4vw,0.8rem); min-height:30px;">فارسی</a>
                    <a href="{{ url('language') }}?locale=tr" class="lang-btn px-2 py-1.5 rounded-md text-white border border-white/20 bg-white/15 cursor-pointer transition-all duration-200 hover:-translate-y-0.5 hover:border-white/45 hover:shadow-md whitespace-nowrap flex-shrink-0 font-[inherit] no-underline{{ app()->getLocale() === 'tr' ? ' active' : '' }}" style="font-size:clamp(0.52rem,1.4vw,0.8rem); min-height:30px;">Türkçe</a>
                </div>
            </div>
        </div>
    </div>
</header>

<!-- Social Icons Template (Pure Icons - No Circles) -->
<style>
    .social-icons-container {
        display: flex;
        gap: 18px;
        justify-content: center;
        flex-wrap: wrap;
        margin-top: 15px;
    }

    .social-link {
        display: flex;
        align-items: center;
        justify-content: center;
        transition: transform 0.2s ease, opacity 0.2s ease;
        text-decoration: none;
    }

    .social-link:hover {
        transform: scale(1.2);
        opacity: 0.8;
    }

    .social-link svg {
        width: 30px;
        height: 30px;
    }
</style>

<template id="social-tpl">
    <div class="social-icons-container">
        <!-- Telegram -->
        <a href="https://t.me/mahmod_faqe" target="_blank" title="Telegram" class="social-link">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="#0088cc"><path d="M12 0C5.373 0 0 5.373 0 12s5.373 12 12 12 12-5.373 12-12S18.627 0 12 0zm5.894 8.221-1.97 9.28c-.145.658-.537.818-1.084.508l-3-2.21-1.447 1.394c-.16.16-.295.295-.605.295l.213-3.053 5.56-5.023c.242-.213-.054-.333-.373-.12L8.32 14.26l-2.96-.924c-.643-.204-.657-.643.136-.953l11.57-4.461c.537-.194 1.006.131.828.299z"/></svg>
        </a>

        <!-- Facebook -->
        <a href="https://www.facebook.com/Mahmod.Faqe" target="_blank" title="Facebook" class="social-link">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="#1877f2"><path d="M24 12.073C24 5.405 18.627 0 12 0S0 5.405 0 12.073C0 18.1 4.388 23.094 10.125 24v-8.437H7.078v-3.49h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.49h-2.796V24C19.612 23.094 24 18.1 24 12.073z"/></svg>
        </a>

        <!-- WhatsApp -->
        <a href="https://wa.me/9647704692000" target="_blank" title="WhatsApp" class="social-link">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="#25D366"><path d="M12.004 0C5.374 0 .006 5.367.006 11.997c0 2.117.554 4.103 1.522 5.83L0 24l6.335-1.507a11.964 11.964 0 0 0 5.669 1.43h.005C18.634 23.923 24 18.557 24 11.928 24 5.368 18.634 0 12.004 0zm0 21.887a9.916 9.916 0 0 1-5.058-1.383l-.362-.215-3.757.984 1.003-3.651-.236-.374a9.884 9.884 0 0 1-1.517-5.251c0-5.455 4.439-9.893 9.894-9.893 5.455 0 9.893 4.438 9.893 9.893 0 5.456-4.438 9.89-9.86 9.89zm5.422-7.403c-.297-.149-1.758-.867-2.031-.967-.272-.099-.47-.148-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.457.13-.605.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51a12.1 12.1 0 0 0-.57-.01c-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.463 1.065 2.876 1.213 3.074.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/></svg>
        </a>

        <!-- Phone -->
        <a href="tel:+9647507087901" title="Phone" class="social-link">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="#4CAF50"><path d="M6.62 10.79a15.053 15.053 0 0 0 6.59 6.59l2.2-2.2c.27-.27.67-.36 1.02-.24 1.12.37 2.33.57 3.57.57.55 0 1 .45 1 1V20c0 .55-.45 1-1 1C10.61 21 3 13.39 3 4c0-.55.45-1 1-1h3.5c.55 0 1 .45 1 1 0 1.25.2 2.45.57 3.57.11.35.03.74-.24 1.02l-2.21 2.2z"/></svg>
        </a>

        <!-- Snapchat -->
        <a href="https://snapchat.com/add/mahmod.faqe" target="_blank" title="Snapchat" class="social-link">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 496 512" fill="#FFFC00"><path d="M248 8C111 8 0 119 0 256s111 248 248 248 248-111 248-248S385 8 248 8zm169.5 338.9c-3.5 8.1-18.1 14-44.8 18.2-1.4 1.9-2.5 9.8-4.3 15.9-1.1 3.7-3.7 5.9-8.1 5.9h-.2c-6.2 0-12.8-2.9-25.8-2.9-17.6 0-23.7 4-37.4 13.7-14.5 10.3-28.4 19.1-49.2 18.2-21 1.6-38.6-11.2-48.5-18.2-13.8-9.7-19.8-13.7-37.4-13.7-12.5 0-20.4 3.1-25.8 3.1-5.4 0-7.5-3.3-8.3-6-1.8-6.1-2.9-14.1-4.3-16-13.8-2.1-44.8-7.5-45.5-21.4-.2-3.6 2.3-6.8 5.9-7.4 46.3-7.6 67.1-55.1 68-57.1 0-.1.1-.2.2-.3 2.5-5 3-9.2 1.6-12.5-3.4-7.9-17.9-10.7-24-13.2-15.8-6.2-18-13.4-17-18.3 1.6-8.5 14.4-13.8 21.9-10.3 5.9 2.8 11.2 4.2 15.7 4.2 3.3 0 5.5-.8 6.6-1.4-1.4-23.9-4.7-58 3.8-77.1C183.1 100 230.7 96 244.7 96c.6 0 6.1-.1 6.7-.1 34.7 0 68 17.8 84.3 54.3 8.5 19.1 5.2 53.1 3.8 77.1 1.1.6 2.9 1.3 5.7 1.4 4.3-.2 9.2-1.6 14.7-4.2 4-1.9 9.6-1.6 13.6 0 6.3 2.3 10.3 6.8 10.4 11.9.1 6.5-5.7 12.1-17.2 16.6-1.4.6-3.1 1.1-4.9 1.7-6.5 2.1-16.4 5.2-19 11.5-1.4 3.3-.8 7.5 1.6 12.5.1.1.1.2.2.3.9 2 21.7 49.5 68 57.1 4 1 7.1 5.5 4.9 10.8z"/></svg>
        </a>

        <!-- TikTok -->
        <a href="https://www.tiktok.com/@mahmod.faqe" target="_blank" title="TikTok" class="social-link">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="#000000"><path d="M19.59 6.69a4.83 4.83 0 0 1-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 0 1-2.88 2.5 2.89 2.89 0 0 1-2.89-2.89 2.89 2.89 0 0 1 2.89-2.89c.28 0 .54.04.79.1V9.01a6.33 6.33 0 0 0-.79-.05 6.34 6.34 0 0 0-6.34 6.34 6.34 6.34 0 0 0 6.34 6.34 6.34 6.34 0 0 0 6.33-6.34V8.69a8.18 8.18 0 0 0 4.78 1.52V6.75a4.85 4.85 0 0 1-1.01-.06z"/></svg>
        </a>
    </div>
</template>


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
            
            {!! __('messages.hero_eyebrow') !!}

        </div>
    </div>
</section>

<!-- ══════════ INTRO SECTION ══════════ -->
<section class="bg-white" style="padding: clamp(3rem,8vw,6rem) 0;">
    <div class="max-w-[1200px] mx-auto px-4 sm:px-6 lg:px-8">

        <!-- KU SORANI -->
        {!! __('messages.hero_title') !!}

    </div>
</section>
    
<!-- ══════════ HISTORY SECTION ══════════ -->
<section class="bg-white" style="padding: clamp(3rem,8vw,6rem) 0;">
    <div class="max-w-[1200px] mx-auto px-4 sm:px-6 lg:px-8">
        <div class="max-w-[900px] mx-auto">

            <!-- KU SORANI -->
            {!! __('messages.services_heading') !!}

        </div>
    </div>
</section>
<!-- ══════════ LIBRARY SECTION 1 ══════════ -->
<section style="padding: clamp(1rem,4vw,0.5rem) 0; background: linear-gradient(160deg, #f0f2ff 0%, #e8ebff 100%);">
    <div class="max-w-[1200px] mx-auto px-6 sm:px-8 lg:px-10">
        <div class="text-center mb-10 reveal">
            {!! __('messages.resources_heading') !!}
        </div>
    </div>
</section>

<!-- ══════════ LIBRARY 2 + DEPARTMENTS ══════════ -->
<section style="padding: clamp(1rem,4vw,3rem) 0; background: linear-gradient(160deg, #f0f2ff 0%, #e8ebff 100%);">
    <div class="max-w-[1200px] mx-auto px-6 sm:px-8 lg:px-10">

        <!-- Library 2 button -->
        <div class="text-center">
            <a href="https://drive.google.com/drive/folders/1KkvwcZdKCZzV7gjExlnOdl1JnCELHCkC" class="main-library-btn relative inline-block font-bold text-white rounded-full no-underline text-center transition-all duration-300 hover:-translate-y-1.5" style="background: linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%); padding: clamp(0.9rem,3vw,1.15rem) clamp(2rem,6vw,3rem); font-size:clamp(0.95rem,2.5vw,1.15rem); box-shadow: 0 8px 24px rgba(255,107,107,0.35); min-width:200px;" target="_blank">{{ __('messages.main_library_btn') }}</a>
        </div>

        <!-- Department cards (DB-driven) -->
        <div id="dept-{{ app()->getLocale() }}" style="direction:{{ in_array(app()->getLocale(), ['en','tr','ku-badini-lat']) ? 'ltr' : 'rtl' }};">
            <h2 class="text-center font-bold text-[#2d2d3a] mb-10" style="font-size:clamp(1.7rem,4.5vw,2.4rem);">{{ __('messages.dept_heading') }}</h2>
            <div class="grid gap-5" style="grid-template-columns: repeat(auto-fit, minmax(260px,1fr));">
                @forelse ($departments as $department)
                <div class="section-card card-top-bar card-glow relative flex flex-col justify-between bg-white/85 backdrop-blur-md border border-white/70 rounded-[18px] text-center transition-all duration-300 hover:-translate-y-3 reveal" style="padding:clamp(1.5rem,4vw,2.2rem); min-height:280px; box-shadow:0 4px 16px rgba(102,126,234,0.10);">
                    <div>
                        <span class="block text-5xl mb-4 transition-transform duration-300">{{ $department->icon }}</span>
                        <h3 class="font-bold text-[#2d2d3a] mb-3" style="font-size:clamp(1.1rem,3vw,1.4rem);">{{ $department->translation(app()->getLocale(), 'title') }}</h3>
                        <p class="text-[#6b6b80] mb-4 flex-grow" style="font-size:clamp(0.88rem,2.2vw,0.98rem); line-height:1.65;">{{ $department->translation(app()->getLocale(), 'desc') }}</p>
                    </div>
                    <a href="{{ $department->drive_url }}" class="section-btn relative inline-block font-semibold text-white rounded-full no-underline text-center transition-all duration-300 hover:-translate-y-1 font-[inherit]" style="background:linear-gradient(135deg,#667eea 0%,#764ba2 100%); padding:clamp(0.6rem,2.2vw,0.85rem) clamp(1.3rem,3.5vw,1.9rem); font-size:clamp(0.85rem,2.2vw,0.95rem); min-width:130px; box-shadow:0 4px 14px rgba(102,126,234,0.28);" target="_blank">{{ $department->translation(app()->getLocale(), 'button') }}</a>
                </div>
                @empty
                <p class="text-center text-[#6b6b80]">{{ __('messages.no_departments') }}</p>
                @endforelse
            </div>
        </div>
</section>

<!-- ══════════ ABOUT SECTION ══════════ -->
<section class="bg-white" style="padding: clamp(3rem,8vw,6rem) 0;">
    <div class="max-w-[1200px] mx-auto px-4 sm:px-6 lg:px-8">
        <div class="max-w-[900px] mx-auto">

            {!! __('messages.about_block') !!}

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
                    {{ __('messages.feedback_success') }}
            </div>
        @endif
        @if ($errors->has('message'))
            <div class="mb-6 rounded-[14px] px-5 py-4 text-center font-semibold" style="background:#fee2e2; color:#991b1b; font-size:clamp(0.9rem,2vw,1rem);">
                {{ __('messages.feedback_error') }}
            </div>
        @endif
        <div class="rounded-[22px] bg-white/85 backdrop-blur-md border border-white/70 text-center" style="box-shadow:0 10px 40px rgba(102,126,234,0.14); padding: clamp(1.8rem,5vw,3rem);">
            <h2 class="font-bold text-[#2d2d3a] mb-3" style="font-size:clamp(1.5rem,4vw,2rem);">
                    {{ __('messages.feedback_title') }}
            </h2>
            <p class="text-[#6b6b80] mb-6" style="font-size:clamp(0.9rem,2.2vw,1rem); line-height:1.7;">
                    {{ __('messages.feedback_subtitle') }}
            </p>
            <form method="POST" action="{{ route('feedback.store') }}" style="text-align:right;">
                @csrf
                <div class="mb-4 text-right" style="text-align:right;">
                    <label for="fb-name" class="block font-semibold mb-1 text-[#4a4a5c]" style="font-size:0.9rem;">
                        {{ __('messages.fb_name_label') }}
                    </label>
                    <input type="text" id="fb-name" name="name" maxlength="120" value="{{ old('name') }}"
                           class="w-full rounded-[12px] px-4 py-3" style="border:1px solid #d5d9ee; font-size:0.95rem; font-family:inherit; text-align:right;">
                </div>
                <div class="mb-5 text-right">
                    <label for="fb-msg" class="block font-semibold mb-1 text-[#4a4a5c]" style="font-size:0.9rem;">
                        {{ __('messages.fb_msg_label') }}
                    </label>
                    <textarea id="fb-msg" name="message" rows="4" maxlength="2000" required
                              class="w-full rounded-[12px] px-4 py-3" style="border:1px solid #d5d9ee; font-size:0.95rem; font-family:inherit; text-align:right;">{{ old('message') }}</textarea>
                </div>
                <div style="text-align:center;">
                    <button type="submit" class="section-btn relative inline-block font-semibold text-white rounded-full no-underline text-center transition-all duration-300 hover:-translate-y-1 font-[inherit] cursor-pointer" style="background:linear-gradient(135deg,#667eea 0%,#764ba2 100%); padding:clamp(0.65rem,2.2vw,0.9rem) clamp(1.6rem,4vw,2.4rem); font-size:clamp(0.9rem,2.2vw,1rem); box-shadow:0 4px 14px rgba(102,126,234,0.28); border:none;">
                        {{ __('messages.fb_send') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</section>
<!-- ══════════ FOOTER ══════════ -->
<footer class="text-center" style="background: linear-gradient(135deg, #1e1e2e 0%, #2d2b4e 100%); color: rgba(255,255,255,0.82); padding: clamp(1.5rem,4vw,2.2rem) 0;">
    <div class="max-w-[1200px] mx-auto px-4">
        {!! __('messages.footer_copyright') !!}

        {!! __('messages.footer_uor_link') !!}

        <div class="mt-3 flex items-center justify-center gap-3 opacity-75 hover:opacity-100 transition-opacity duration-300">
            <span style="font-size: 1rem;">👁</span>
            <div class="flex items-center gap-3 no-underline rounded-full border border-white/20 px-4 py-1.5"
            style="background:rgba(255,255,255,0.12); font-size: 1rem; color:rgba(255,255,255,0.85);">
        
            <img src="https://mahmodfaqe.goatcounter.com/counter/TOTAL.svg" 
                  alt="visitor count" 
                  class="h-[40px] w-auto align-middle" 
                  loading="lazy" decoding="async">
             
                <span id="visitor-label">{{ __('messages.visitors_label') }}</span>
            </div>
        </div>
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

// Inject social icons
(function(){
    var tpl = document.getElementById('social-tpl');
    document.querySelectorAll('.social-placeholder').forEach(function(ph){
        ph.replaceWith(tpl.content.cloneNode(true));
    });
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

<script data-goatcounter="https://mahmodfaqe.goatcounter.com/count" async src="//gc.zgo.at/count.js"></script>
</body>
</html>
