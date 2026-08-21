<!DOCTYPE html>
<html lang="ku">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="dns-prefetch" href="https://cdn.tailwindcss.com">
    <link rel="dns-prefetch" href="https://mahmodfaqe.goatcounter.com">
    <link href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@400;700&display=swap" rel="stylesheet">
    <title>کتێبخانەی ئەلیکترۆنی کۆلێژی زانست - زانکۆی ڕاپەڕین</title>
    <meta name="description" content="کتێبخانەی ئەلیکترۆنی کۆلێژی زانست لە زانکۆی ڕاپەڕین: خزمەتگوزاری کتێبخانەی ئۆنلاین و دەستگەیشتن بە کتێب و سەرچاوە زانستییەکان بە ٨ زمان.">
    <meta name="robots" content="index, follow">
    <meta name="theme-color" content="#667eea">
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="canonical" href="{{ url('/') }}">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="کتێبخانەی ئەلیکترۆنی کۆلێژی زانست - زانکۆی ڕاپەڕین">
    <meta property="og:title" content="کتێبخانەی ئەلیکترۆنی کۆلێژی زانست - زانکۆی ڕاپەڕین">
    <meta property="og:description" content="خزمەتگوزاری کتێبخانەی ئۆنلاین و دەستگەیشتن بە کتێب و سەرچاوە زانستییەکان بە ٨ زمان.">
    <meta property="og:url" content="{{ url('/') }}">
    <meta property="og:image" content="{{ asset('file/uor-logo.png') }}">
    <meta property="og:locale" content="ku">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="کتێبخانەی ئەلیکترۆنی کۆلێژی زانست - زانکۆی ڕاپەڕین">
    <meta name="twitter:description" content="خزمەتگوزاری کتێبخانەی ئۆنلاین و دەستگەیشتن بە کتێب و سەرچاوە زانستییەکان بە ٨ زمان.">
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

        /* Lang content */
        .lang-content { display: none; opacity: 0; transition: opacity 0.35s ease; }
        .lang-content.active { display: block; opacity: 1; }
        .logo-title .lang-content,
        .logo-subtitle .lang-content { display: none; }
        .logo-title .lang-content.active,
        .logo-subtitle .lang-content.active { display: inline; opacity: 1; }

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
        "description": "کتێبخانەی ئەلیکترۆنی کۆلێژی زانست لە زانکۆی ڕاپەڕین.",
        "inLanguage": ["ckb", "en", "ar", "fa", "tr"],
        "isAccessibleForFree": true,
        "sameAs": ["https://github.com/mahmodfaqe/Library"]
    }
    </script>
</head>
<body class="bg-[#f8f9ff] text-[#2d2d3a] overflow-x-hidden" dir="rtl">

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
                        <span class="lang-content active" data-lang="ku-sorani">زانکۆی ڕاپەڕین</span>
                        <span class="lang-content" data-lang="ku-badini">زانینگەها ڕاپەرین</span>
                        <span class="lang-content" data-lang="ku-badini-lat">Zanîngeha Raperin</span>
                        <span class="lang-content" data-lang="ku-hawrami">زانکۆی ڕاپەڕین</span>
                        <span class="lang-content" data-lang="en">University of Raparin</span>
                        <span class="lang-content" data-lang="ar">جامعة رابەرين</span>
                        <span class="lang-content" data-lang="fa">دانشگاه راپەرین</span>
                        <span class="lang-content" data-lang="tr">Raparin Üniversitesi</span>
                    </span>
                    <span class="logo-subtitle logo-sub-el block mt-1 overflow-hidden text-ellipsis whitespace-nowrap"
                          style="font-size: clamp(0.55rem,1.8vw,0.9rem); color: rgba(255,255,255,0.72);">
                        <span class="lang-content active" data-lang="ku-sorani">کتێبخانەی ئەلیکترۆنی</span>
                        <span class="lang-content" data-lang="ku-badini">پرتوکخانەیا ئەلیکترۆنیک</span>
                        <span class="lang-content" data-lang="ku-badini-lat">Kitêbxaneya Elektronîkî</span>
                        <span class="lang-content" data-lang="ku-hawrami">کتێبخانەی ئەلیکترۆنی</span>
                        <span class="lang-content" data-lang="en">Electronic Library</span>
                        <span class="lang-content" data-lang="ar">المكتبة الإلكترونية</span>
                        <span class="lang-content" data-lang="fa">کتابخانه الکترونیکی</span>
                        <span class="lang-content" data-lang="tr">Elektronik Kütüphane</span>
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
                            class="active flex items-center gap-1 px-2 py-1.5 rounded-md text-white border border-white/20 bg-white/15 cursor-pointer transition-all duration-200 hover:-translate-y-0.5 hover:border-white/45 hover:shadow-md font-[inherit]"
                            style="font-size: clamp(0.52rem,1.4vw,0.8rem); min-height:30px;"
                            onclick="toggleKuDropdown()">
                        کوردی <span class="text-[0.5rem] transition-transform duration-300" id="kuArrow">▼</span>
                    </button>
                    <div class="ku-dropdown" id="kuDropdown">
                        <button class="ku-dialect-btn active block w-full bg-transparent border-0 text-white text-right cursor-pointer transition-colors duration-200 hover:bg-white/15 font-[Rabar,sans-serif]"
                                style="padding: clamp(0.38rem,1.2vw,0.55rem) clamp(0.6rem,2vw,1rem); font-size:clamp(0.58rem,1.4vw,0.8rem); min-height:36px;"
                                data-lang="ku-sorani" onclick="selectDialect('ku-sorani',this)">
                            سۆرانی<span class="block text-[0.5em] opacity-65 mt-px">Soranî</span>
                        </button>
                        <button class="ku-dialect-btn block w-full bg-transparent border-0 text-white text-right cursor-pointer transition-colors duration-200 hover:bg-white/15 font-[Rabar,sans-serif]"
                                style="padding: clamp(0.38rem,1.2vw,0.55rem) clamp(0.6rem,2vw,1rem); font-size:clamp(0.58rem,1.4vw,0.8rem); min-height:36px;"
                                data-lang="ku-badini" onclick="selectDialect('ku-badini',this)">
                            بادینی<span class="block text-[0.5em] opacity-65 mt-px">Kurmancî (عەرەبی)</span>
                        </button>
                        <button class="ku-dialect-btn block w-full bg-transparent border-0 text-white text-left cursor-pointer transition-colors duration-200 hover:bg-white/15 font-sans"
                                style="padding: clamp(0.38rem,1.2vw,0.55rem) clamp(0.6rem,2vw,1rem); font-size:clamp(0.58rem,1.4vw,0.8rem); min-height:36px;"
                                data-lang="ku-badini-lat" onclick="selectDialect('ku-badini-lat',this)">
                            Badînî<span class="block text-[0.5em] opacity-65 mt-px">Kurmancî (Latînî)</span>
                        </button>
                        <button class="ku-dialect-btn block w-full bg-transparent border-0 text-white text-right cursor-pointer transition-colors duration-200 hover:bg-white/15 font-[Rabar,sans-serif]"
                                style="padding: clamp(0.38rem,1.2vw,0.55rem) clamp(0.6rem,2vw,1rem); font-size:clamp(0.58rem,1.4vw,0.8rem); min-height:36px;"
                                data-lang="ku-hawrami" onclick="selectDialect('ku-hawrami',this)">
                            هەورامی<span class="block text-[0.5em] opacity-65 mt-px">Hewramî</span>
                        </button>
                    </div>
                </div>

                <!-- Divider -->
                <div class="flex-shrink-0 w-px bg-white/25 mx-1" style="height:clamp(16px,3.5vw,22px);"></div>

                <!-- Other languages -->
                <div class="flex items-center gap-1 flex-nowrap overflow-x-auto" style="scrollbar-width:none; max-width:clamp(100px,40vw,400px);">
                    <button class="lang-btn px-2 py-1.5 rounded-md text-white border border-white/20 bg-white/15 cursor-pointer transition-all duration-200 hover:-translate-y-0.5 hover:border-white/45 hover:shadow-md whitespace-nowrap flex-shrink-0 font-[inherit]"
                            style="font-size:clamp(0.52rem,1.4vw,0.8rem); min-height:30px;"
                            data-lang="en" onclick="switchLang('en')">English</button>
                    <button class="lang-btn px-2 py-1.5 rounded-md text-white border border-white/20 bg-white/15 cursor-pointer transition-all duration-200 hover:-translate-y-0.5 hover:border-white/45 hover:shadow-md whitespace-nowrap flex-shrink-0 font-[inherit]"
                            style="font-size:clamp(0.52rem,1.4vw,0.8rem); min-height:30px;"
                            data-lang="ar" onclick="switchLang('ar')">العربية</button>
                    <button class="lang-btn px-2 py-1.5 rounded-md text-white border border-white/20 bg-white/15 cursor-pointer transition-all duration-200 hover:-translate-y-0.5 hover:border-white/45 hover:shadow-md whitespace-nowrap flex-shrink-0 font-[inherit]"
                            style="font-size:clamp(0.52rem,1.4vw,0.8rem); min-height:30px;"
                            data-lang="fa" onclick="switchLang('fa')">فارسی</button>
                    <button class="lang-btn px-2 py-1.5 rounded-md text-white border border-white/20 bg-white/15 cursor-pointer transition-all duration-200 hover:-translate-y-0.5 hover:border-white/45 hover:shadow-md whitespace-nowrap flex-shrink-0 font-[inherit]"
                            style="font-size:clamp(0.52rem,1.4vw,0.8rem); min-height:30px;"
                            data-lang="tr" onclick="switchLang('tr')">Türkçe</button>
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
            
            <div class="lang-content active" data-lang="ku-sorani" style="direction:rtl;">
                <h2 class="opacity-80 tracking-widest mb-2" style="font-size:clamp(1rem,3vw,1.3rem);">بەخێربێن بۆ</h2>
                <h1 class="font-bold mb-6 leading-tight" style="font-size: clamp(2.5rem, 8vw, 4.5rem);">کتێبخانەی ئەلیکترۆنی کۆلێژی زانست</h1>
                <p class="mb-8 opacity-95 mx-auto max-w-3xl" style="font-size: clamp(0.95rem,2.5vw,1.25rem);">بەردەستبوون و کۆکردنەوەی هەزاران سەرچاوە و پەرتووکی زانستی بە شێوەیەکی ئاسان و خێرا.</p>
            </div>

            <div class="lang-content" data-lang="ku-badini" style="direction:rtl;">
                <h2 style="font-size:clamp(1rem,3vw,1.3rem);">بخێر هاتن بۆ</h2>
                <h1 class="font-bold mb-6 leading-tight" style="font-size: clamp(2.5rem, 8vw, 4.5rem);">کتێبخانەیا ئەلیکترۆنیکی یا کۆلێژا زانستێ</h1>
                <p class="mb-8 opacity-95 mx-auto max-w-3xl" style="font-size: clamp(0.95rem,2.5vw,1.25rem);">بهێسانی و زو پەیدابوونا بی هەزاران چاوکانی و پەرتوکین زانستی.</p>
            </div>

            <div class="lang-content" data-lang="ku-badini-lat" style="direction:ltr;">
                <h2 style="font-size:clamp(1rem,3vw,1.3rem);">Bi xêr hatin</h2>
                <h1 class="font-bold mb-6 leading-tight" style="font-size: clamp(2.5rem, 8vw, 4.5rem);">Kitêbxaneya Elektronîkî ya Kolêja Zanistê</h1>
                <p class="mb-8 opacity-95 mx-auto max-w-3xl" style="font-size: clamp(0.95rem,2.5vw,1.25rem);">Bigihîje hezaran çavkanî û pirtûkên zanistî bi awayekî hêsan û bilez.</p>
            </div>

            <div class="lang-content" data-lang="ku-hawrami" style="direction:rtl;">
                <h2 style="font-size:clamp(1rem,3vw,1.3rem);">خۆش بگەیەیت بۆ</h2>
                <h1 class="font-bold mb-6 leading-tight" style="font-size: clamp(2.5rem, 8vw, 4.5rem);">کتێبخانەی ئەلیکترۆنی کۆلێژی زانست</h1>
                <p class="mb-8 opacity-95 mx-auto max-w-3xl" style="font-size: clamp(0.95rem,2.5vw,1.25rem);">دەستڕەسیەتی بە هەزاران سەرچاوە و پەرتووکی زانستی بە شێوەیەکی ئاسان و خێرا.</p>
            </div>

            <div class="lang-content" data-lang="en" style="direction:ltr;">
                <h2 style="font-size:clamp(1rem,3vw,1.3rem);">Welcome To</h2>
                <h1 class="font-bold mb-6 leading-tight" style="font-size: clamp(2.5rem, 8vw, 4.5rem);">College of Science Electronic Library</h1>
                <p class="mb-8 opacity-95 mx-auto max-w-3xl" style="font-size: clamp(0.95rem,2.5vw,1.25rem);">Access and gather thousands of scientific resources and books easily and quickly.</p>
            </div>

            <div class="lang-content" data-lang="ar" style="direction:rtl;">
                <h2 style="font-size:clamp(1rem,3vw,1.3rem);">مرحبا بكم في</h2>
                <h1 class="font-bold mb-6 leading-tight" style="font-size: clamp(2.5rem, 8vw, 4.5rem);">المكتبة الإلكترونية لكلية العلوم</h1>
                <p class="mb-8 opacity-95 mx-auto max-w-3xl" style="font-size: clamp(0.95rem,2.5vw,1.25rem);">الوصول وتجميع آلاف المصادر والكتب العلمية بسهولة وسرعة.</p>
            </div>

            <div class="lang-content" data-lang="fa" style="direction:rtl;">
                <h2 style="font-size:clamp(1rem,3vw,1.3rem);">خوش آمدید به</h2>
                <h1 class="font-bold mb-6 leading-tight" style="font-size: clamp(2.5rem, 8vw, 4.5rem);">کتابخانه الکترونیکی دانشکده علوم</h1>
                <p class="mb-8 opacity-95 mx-auto max-w-3xl" style="font-size: clamp(0.95rem,2.5vw,1.25rem);">دسترسی و گردآوری هزاران منبع و کتاب علمی به آسانی و سرعت.</p>
            </div>

            <div class="lang-content" data-lang="tr" style="direction:ltr;">
                <h2 style="font-size:clamp(1rem,3vw,1.3rem);">Hoş Geldiniz</h2>
                <h1 class="font-bold mb-6 leading-tight" style="font-size: clamp(2.5rem, 8vw, 4.5rem);">Fen Fakültesi Elektronik Kütüphanesi</h1>
                <p class="mb-8 opacity-95 mx-auto max-w-3xl" style="font-size: clamp(0.95rem,2.5vw,1.25rem);">Binlerce bilimsel kaynak ve kitaba kolayca ve hızlıca erişin ve toplayın.</p>
            </div>

        </div>
    </div>
</section>

<!-- ══════════ INTRO SECTION ══════════ -->
<section class="bg-white" style="padding: clamp(3rem,8vw,6rem) 0;">
    <div class="max-w-[1200px] mx-auto px-4 sm:px-6 lg:px-8">

        <!-- KU SORANI -->
        <div class="lang-content active" style="direction:rtl;" data-lang="ku-sorani">
            <div class="text-center max-w-[900px] mx-auto">
                <h2 class="font-bold text-[#2d2d3a] mb-8" style="font-size:clamp(1.7rem,4.5vw,2.4rem);">پێشەکی</h2>
                <div class="relative rounded-[18px] overflow-hidden shimmer-border reveal"
                     style="background: linear-gradient(145deg, #f5f7ff 0%, #eef0f8 100%); padding: clamp(1.5rem,5vw,3rem); box-shadow: 0 8px 32px rgba(102,126,234,0.15);">
                    <p class="text-[#6b6b80] leading-[1.9] mb-6 text-justify" style="font-size:clamp(0.95rem,2.5vw,1.15rem);"> بەخێربێن بۆ کتێبخانەی ئەلیکترۆنی کۆلێژی زانست - ژینگەیەکی دیجیتاڵییە کە دروستکراوە بۆ ئەوەی گەیشتن بە زانیاری و فێربوون چێژبەخش بێت و توێژینەوە کاراتر بێت بۆ خوێندکاران و ستافی ئەکادیمی.</p>
                    <p class="text-[#6b6b80] leading-[1.9] mb-6 text-justify" style="font-size:clamp(0.95rem,2.5vw,1.15rem);">ئەم کتێبخانە ئەلیکترۆنییە کۆمەڵێک سەرچاوەی زانستی بەرفراوان پێشکەش دەکات، لەوانە کتێبی خوێندن، توێژینەوەی زانستی و ئەکادیمی، گۆڤاری ئەکادیمی، کەرەستەی وانەوتنەوە و ئاماژەی پەروەردەیی، کە هەموویان لە یەک پلاتفۆرمی گونجاودا بەردەستن. ڕێگە بە بەکارهێنەران دەدات لە هەر کاتێکدا و لە هەر شوێنێکدا بە دوای زانیارییەکان بگەڕێن، کە پشتگیری لە هەردوو فێربوونی سەربەخۆ و چالاکییەکانی توێژینەوەی پێشکەوتوو دەکات. هەموو ئەمانە لە ڕێگەی QR کۆدەوە کە بە شێوازێکی ئەلکترۆنی ئامادەکراوە. </p>
                    <p class="text-[#6b6b80] leading-[1.9] mb-6 text-justify" style="font-size:clamp(0.95rem,2.5vw,1.15rem);">کتێبخانەی ئەلیکترۆنی زیاترە لە تەنها کۆمەڵەیەکی دیجیتاڵی، نیشاندەری پەیوەندی نێوان زانست و تەکنەلۆژیایە. هاندەری بیرکردنەوەی ڕەخنەگرانە و فێربوونی بەردەوامە، لە هەمان کاتدا یارمەتی خوێندکاران و توێژەران دەدات کە لە پێشکەوتنە زانستییە مۆدێرنەکاندا ئاگاداربن.</p>
                    <p class="text-[#6b6b80] leading-[1.9] mb-6 text-justify" style="font-size:clamp(0.95rem,2.5vw,1.15rem);">ئەرکی ئێمە بنیاتنانی بۆشاییەکی ئەکادیمی پشتیوانە کە زانیاری بە ئازادی تێدا بەردەست بکرێت، بیرۆکەکان پەرەی پێبدرێت، و زانایانی داهاتوو بەهێز بکرێن بۆ دۆزینەوە و داهێنان و بەشداریکردن لە پێشکەوتنی زانست و کۆمەڵگا.ئەم سیستەمە مۆدێرنە بۆ خزمەتی خوێنەرانی کۆلێژی زانست دروست کراوە.</p>

                    <!-- Objectives -->
                    <div class="rounded-[10px] p-6 mb-6 border border-[rgba(102,126,234,0.16)] transition-all duration-300 hover:shadow-md reveal"
                         style="background:rgba(102,126,234,0.08);">
                        <h3 class="text-[#667eea] mb-4 font-bold" style="font-size:clamp(1.1rem,3vw,1.4rem);">ئامانجەکانی پڕۆژەکە:</h3>
                        <ul class="list-none p-0 text-[#6b6b80] leading-loose" style="font-size:clamp(0.9rem,2.5vw,1.05rem);">
                            <li class="mb-1 ps-6">🎓 پشتگیریکردنی خوێندنی ئۆنلاین لە زانکۆ</li>
                            <li class="mb-1 ps-6">⚡ بەردەستبوونی سەرچاوەی زانستی بە خێرایی و ئاسانی</li>
                            <li class="mb-1 ps-6">📖 کۆکردنەوەی هەزاران سەرچاوەی زانستی لە یەک شوێن</li>
                            <li class="mb-1 ps-6">📱 بەکارهێنانی تەکنەلۆژیای سەردەم <a href="https://scence-bio.github.io/Qr-Code/" style="color: gold; text-shadow: 0 0 8px gold, 0 0 15px gold;">QR code</a> بۆ خزمەتی خوێندن</li>
                            <li class="mb-1 ps-6">👨‍🏫 سوودمەندبوونی مامۆستایان و خوێندکاران لە پەرتووکخانەی دیجیتاڵی</li>
                            <li class="mb-1 ps-6">⏰ پڕکردنەوەی کاتە بەتاڵەکانی خوێندکاران بە پڕۆژەیەکی زانستی</li>
                            <li class="mb-1 ps-6">🌐 بەردەستبوونی پەرتووک و سەرچاوەکان بە هەموو زمانەکان</li>
                        </ul>
                    </div>



                    <!-- Team -->
                    <div class="rounded-[10px] p-6 border border-[rgba(255,107,107,0.15)] transition-all duration-300 hover:shadow-md reveal"
                         style="background:rgba(255,107,107,0.07);">
                        <h3 class="text-[#ff6b6b] mb-4 font-bold" style="font-size:clamp(1.1rem,3vw,1.4rem);">ئامادەکراوە لەلایەن:</h3>
                        <div class="grid gap-4" style="grid-template-columns: repeat(auto-fit, minmax(min(220px,100%),1fr));">
                            <div class="text-center p-5 bg-white rounded-xl shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-md">
                                <h4 class="text-[#2d2d3a] mb-2 font-bold" style="font-size:clamp(0.9rem,2.5vw,1.05rem);">ژیاو یوسف حسێن</h4>
                                <p class="text-center text-[#6b6b80] mb-2" style="font-size:clamp(0.8rem,2vw,0.95rem);">خوێندکاری بەشی بایۆلۆجی - دابینکردنی کتێب</p>
                            </div>
                            <div class="text-center p-5 bg-white rounded-xl shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-md">
                                <h4 class="text-[#2d2d3a] mb-2 font-bold" style="font-size:clamp(0.9rem,2.5vw,1.05rem);">نیار قادر ڕەسوڵ</h4>
                                <p class="text-center text-[#6b6b80] mb-2" style="font-size:clamp(0.8rem,2vw,0.95rem);">خوێندکاری بەشی بایۆلۆجی - ڕێکخستنی کتێبخانە</p>
                            </div>
                            <div class="text-center p-5 bg-white rounded-xl shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-md">
                                <h4 class="text-[#2d2d3a] mb-2 font-bold" style="font-size:clamp(0.9rem,2.5vw,1.05rem);">محمود خدر فقێ ڕەسوڵ</h4>
                                <p class="text-center text-[#6b6b80] mb-2" style="font-size:clamp(0.8rem,2vw,0.95rem);">خوێندکاری بەشی بایۆلۆجی ـ گەشەپێدەری وێبسایت</p>
                                <div class="social-placeholder"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- EN -->
        <div class="lang-content" style="direction:ltr;" data-lang="en">
            <div class="text-center max-w-[900px] mx-auto">
                <h2 class="font-bold text-[#2d2d3a] mb-8" style="font-size:clamp(1.7rem,4.5vw,2.4rem);">Introduction</h2>
                <div class="relative rounded-[18px] overflow-hidden shimmer-border reveal"
                     style="background: linear-gradient(145deg, #f5f7ff 0%, #eef0f8 100%); padding: clamp(1.5rem,5vw,3rem); box-shadow: 0 8px 32px rgba(102,126,234,0.15);">
                    <p class="text-[#6b6b80] leading-[1.9] mb-6 text-justify" style="font-size:clamp(0.95rem,2.5vw,1.15rem);">Welcome to the College of Science e-library - a digital environment designed to make access to information and learning enjoyable and research more efficient for students and academic staff.</p>
                    <p class="text-[#6b6b80] leading-[1.9] mb-6 text-justify" style="font-size:clamp(0.95rem,2.5vw,1.15rem);">This e-library offers a wide range of scientific resources, including textbooks, scientific and academic papers, academic journals, teaching materials and educational references, all available on one convenient platform. It allows users to search for information anytime, anywhere, supporting both independent learning and advanced research activities. All this through an electronically prepared QR code. </p>
                    <p class="text-[#6b6b80] leading-[1.9] mb-6 text-justify" style="font-size:clamp(0.95rem,2.5vw,1.15rem);">An e-library is more than just a digital collection, it demonstrates the relationship between science and technology. It encourages critical thinking and continuous learning, while helping students and researchers stay up-to-date on modern scientific developments.</p>
                    <p class="text-[#6b6b80] leading-[1.9] mb-6 text-justify" style="font-size:clamp(0.95rem,2.5vw,1.15rem);">Our mission is to build a supportive academic space where information is freely available, ideas are developed, and future scientists are empowered to discover, innovate, and contribute to the advancement of science and society.</p>
                    <div class="rounded-[10px] p-6 mb-6 border border-[rgba(102,126,234,0.16)] reveal" style="background:rgba(102,126,234,0.08);">
                        <h3 class="text-[#667eea] mb-4 font-bold" style="font-size:clamp(1.1rem,3vw,1.4rem);">Project Objectives:</h3>
                        <ul class="list-none p-0 text-[#6b6b80] leading-loose" style="font-size:clamp(0.9rem,2.5vw,1.05rem);">
                            <li class="mb-1 pl-6">🎓 Supporting online learning at universities</li>
                            <li class="mb-1 pl-6">⚡ Quick and easy access to scientific resources</li>
                            <li class="mb-1 pl-6">📖 Gathering thousands of scientific resources in one place</li>
                            <li class="mb-1 pl-6">📱 Utilizing modern technology <a href="https://scence-bio.github.io/Qr-Code/" style="color: gold; text-shadow: 0 0 8px gold, 0 0 15px gold;">QR code</a> to serve education</li>
                            <li class="mb-1 pl-6">👨‍🏫 Benefiting teachers and students from digital library resources</li>
                            <li class="mb-1 pl-6">⏰ Filling students' free time with scientific projects</li>
                            <li class="mb-1 pl-6">🌐 Availability of books and resources in all languages</li>
                        </ul>
                    </div>
                    <div class="rounded-[10px] p-6 border border-[rgba(255,107,107,0.15)] reveal" style="background:rgba(255,107,107,0.07);">
                        <h3 class="text-[#ff6b6b] mb-4 font-bold" style="font-size:clamp(1.1rem,3vw,1.4rem);">Prepared by:</h3>
                        <div class="grid gap-4" style="grid-template-columns: repeat(auto-fit, minmax(min(220px,100%),1fr));">
                            <div class="text-center p-5 bg-white rounded-xl shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-md"><h4 class="text-[#2d2d3a] mb-2 font-bold">ZHYAW YUSF HUSEN</h4><p class="text-[#6b6b80]">Biology Department Student - Provision of books</p></div>
                            <div class="text-center p-5 bg-white rounded-xl shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-md"><h4 class="text-[#2d2d3a] mb-2 font-bold">NYAR QADR RASUL</h4><p class="text-[#6b6b80]">Biology Department Student - Organization of the library</p></div>
                            <div class="text-center p-5 bg-white rounded-xl shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-md"><h4 class="text-[#2d2d3a] mb-2 font-bold">MAHMOOD KHDIR FAQE RASUL</h4><p class="text-[#6b6b80]">Biology Department Student - Web Developer</p><div class="social-placeholder"></div></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- AR -->
        <div class="lang-content" style="direction:rtl;" data-lang="ar">
            <div class="text-center max-w-[900px] mx-auto">
                <h2 class="font-bold text-[#2d2d3a] mb-8" style="font-size:clamp(1.7rem,4.5vw,2.4rem);">مقدمة</h2>
                <div class="relative rounded-[18px] overflow-hidden shimmer-border reveal"
                     style="background: linear-gradient(145deg, #f5f7ff 0%, #eef0f8 100%); padding: clamp(1.5rem,5vw,3rem); box-shadow: 0 8px 32px rgba(102,126,234,0.15);">
                    <p class="text-[#6b6b80] leading-[1.9] mb-6 text-justify" style="font-size:clamp(0.95rem,2.5vw,1.15rem);">أهلاً بكم في المكتبة الإلكترونية لكلية العلوم - بيئة رقمية مصممة لجعل الوصول إلى المعلومات والتعلم ممتعًا والبحث أكثر كفاءة للطلاب وأعضاء هيئة التدريس.</p>
                    <p class="text-[#6b6b80] leading-[1.9] mb-6 text-justify" style="font-size:clamp(0.95rem,2.5vw,1.15rem);">توفر هذه المكتبة الإلكترونية مجموعة واسعة من الموارد العلمية، تشمل الكتب الدراسية، والأوراق العلمية والأكاديمية، والمجلات الأكاديمية، والمواد التعليمية، والمراجع التربوية، وكلها متاحة على منصة واحدة سهلة الاستخدام. تتيح هذه المكتبة للمستخدمين البحث عن المعلومات في أي وقت ومن أي مكان، مما يدعم التعلم الذاتي والأنشطة البحثية المتقدمة. كل ذلك من خلال رمز الاستجابة السريعة (QR code) المُعدّ إلكترونيًا.</p>
                    <p class="text-[#6b6b80] leading-[1.9] mb-6 text-justify" style="font-size:clamp(0.95rem,2.5vw,1.15rem);">إن المكتبة الإلكترونية ليست مجرد مجموعة رقمية، بل هي تجسيد للعلاقة بين العلم والتكنولوجيا. فهي تشجع التفكير النقدي والتعلم المستمر، وتساعد الطلاب والباحثين على مواكبة أحدث التطورات العلمية.</p>
                    <p class="text-[#6b6b80] leading-[1.9] mb-6 text-justify" style="font-size:clamp(0.95rem,2.5vw,1.15rem);">مهمتنا هي بناء مساحة أكاديمية داعمة حيث تتوفر المعلومات بحرية، ويتم تطوير الأفكار، ويتم تمكين العلماء المستقبليين من الاكتشاف والابتكار والمساهمة في تقدم العلوم والمجتمع.</p>
                    <div class="rounded-[10px] p-6 mb-6 border border-[rgba(102,126,234,0.16)] reveal" style="background:rgba(102,126,234,0.08);">
                        <h3 class="text-[#667eea] mb-4 font-bold" style="font-size:clamp(1.1rem,3vw,1.4rem);">أهداف المشروع:</h3>
                        <ul class="list-none p-0 text-[#6b6b80] leading-loose" style="font-size:clamp(0.9rem,2.5vw,1.05rem);">
                            <li class="mb-1 ps-6">🎓 دعم التعلم الإلكتروني في الجامعات</li>
                            <li class="mb-1 ps-6">⚡ الوصول السريع والسهل إلى الموارد العلمية</li>
                            <li class="mb-1 ps-6">📖 جمع آلاف الموارد العلمية في مكان واحد</li>
                            <li class="mb-1 ps-6">📱 استخدام التكنولوجيا الحديثة <a href="https://scence-bio.github.io/Qr-Code/" style="color: gold; text-shadow: 0 0 8px gold, 0 0 15px gold;">QR code</a> لخدمة التعليم</li>
                            <li class="mb-1 ps-6">👨‍🏫 استفادة المدرسين والطلاب من موارد المكتبة الرقمية</li>
                            <li class="mb-1 ps-6">⏰ ملء أوقات فراغ الطلاب بمشاريع علمية</li>
                            <li class="mb-1 ps-6">🌐 توفر الكتب والموارد بجميع اللغات</li>
                        </ul>
                    </div>
                    <div class="rounded-[10px] p-6 border border-[rgba(255,107,107,0.15)] reveal" style="background:rgba(255,107,107,0.07);">
                        <h3 class="text-[#ff6b6b] mb-4 font-bold" style="font-size:clamp(1.1rem,3vw,1.4rem);">إعداد:</h3>
                        <div class="grid gap-4" style="grid-template-columns: repeat(auto-fit, minmax(min(220px,100%),1fr));">
                            <div class="text-center p-5 bg-white rounded-xl shadow-sm"><h4 class="text-[#2d2d3a] mb-2 font-bold">ژیاو یوسف حسێن</h4><p class="text-[#6b6b80]">طالب قسم علوم الحياة - توفير الكتب</p></div>
                            <div class="text-center p-5 bg-white rounded-xl shadow-sm"><h4 class="text-[#2d2d3a] mb-2 font-bold">نیار قادر ڕەسوڵ</h4><p class="text-[#6b6b80]">طالبة قسم علوم الحياة - تنظيم المكتبة</p></div>
                            <div class="text-center p-5 bg-white rounded-xl shadow-sm"><h4 class="text-[#2d2d3a] mb-2 font-bold">محمود خدر فقێ ڕەسوڵ</h4><p class="text-[#6b6b80]">طالب قسم علوم الحياة - مطور مواقع الويب</p><div class="social-placeholder"></div></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Other langs (KU-BADINI, KU-BADINI-LAT, KU-HAWRAMI, FA, TR) - abbreviated for brevity but fully functional -->
        <div class="lang-content" style="direction:rtl;" data-lang="ku-badini">
            <div class="text-center max-w-[900px] mx-auto">
                <h2 class="font-bold text-[#2d2d3a] mb-8" style="font-size:clamp(1.7rem,4.5vw,2.4rem);">پێشگرتن</h2>
                <div class="relative rounded-[18px] overflow-hidden shimmer-border reveal" style="background: linear-gradient(145deg, #f5f7ff 0%, #eef0f8 100%); padding: clamp(1.5rem,5vw,3rem); box-shadow: 0 8px 32px rgba(102,126,234,0.15);">
                    <p class="text-[#6b6b80] leading-[1.9] mb-6 text-justify" style="font-size:clamp(0.95rem,2.5vw,1.15rem);">بە ناڤێ خوەدایێ دڵۆڤان </p>
                                        <p class="text-[#6b6b80] leading-[1.9] mb-6 text-justify" style="font-size:clamp(0.95rem,2.5vw,1.15rem);">بخێر هاتن بۆ پرتوکخانەیا ئەلیکترۆنیکی یا کۆلێژا زانستێ
هاوێردۆرەکا دیجیتال کو ژ بۆ هێسانکرنا گهیشتنا ئاگەهداری و فێربوونێ و باندۆرکرنا لێکۆلینێ ژ بۆ خوێندکار و کارمەندێن ئەکادیمی هاتیە چێکرن.</p>
                                        <p class="text-[#6b6b80] leading-[1.9] mb-6 text-justify" style="font-size:clamp(0.95rem,2.5vw,1.15rem);">میسیونا مە ئەوە کو ئەم قادەکا ئەکادیمی یا هاڤکار بیاڤرینین کو تێدا زانین ئازاد دبیت، ڕامان پێشڤە دچیت، و زانایێن پێشەڕۆژێ ژ بۆ کیفشکرن و نووژەنیێ تێنە هێزدارکرن.</p>
                    <p class="text-[#6b6b80] leading-[1.9] mb-6 text-justify" style="font-size:clamp(0.95rem,2.5vw,1.15rem);">ئڤ پرتوکخانەیا ئەلیکترۆنیک جوربەجور چاوکانیێن زانستی پێشکێش دکە، د ناڤ دا پِرتوکێن دەرسێ، لێکۆلینێن زانستی و ئەکادیمی، کۆڤارێن ئەکادیمی، ماتەریالێن هینکرنێ و رێفەرەنسێن پەروەردەهێ، هەمی ل سەر پلاتفۆرمەکا هێسان پەیدا دبن. ئڤ هەمی بە ڕێیا کۆدێن QR تێنە پەیداکرن.</p>
                    <div class="rounded-[10px] p-6 mb-6 border border-[rgba(102,126,234,0.16)] reveal" style="background:rgba(102,126,234,0.08);">
                        <h3 class="text-[#667eea] mb-4 font-bold" style="font-size:clamp(1.1rem,3vw,1.4rem);">ئارمانجێن پڕۆژەیێ:</h3>
                        <ul class="list-none p-0 text-[#6b6b80] leading-loose"><li class="mb-1 ps-6">🎓 پشتگیریا پەروەردەهیا سەرھێل ل زانینگەھێ</li><li class="mb-1 ps-6">⚡ هەبوونا بیلەز و هێسانا چاوکانیێن زانستی</li><li class="mb-1 ps-6">📖 بەرھەڤکرنا بی هەزاران چاوکانیێن زانستی ل یەک جهێکی</li><li class="mb-1 ps-6">📱 بکارئینانا تەکنەلۆژیا نووژەن <a href="https://scence-bio.github.io/Qr-Code/" style="color: gold; text-shadow: 0 0 8px gold, 0 0 15px gold;">QR code</a></li><li class="mb-1 ps-6">👨‍🏫 سوودێن مامۆستا و خوێندکاران ژ کتێبخانەیێن دیجیتال</li><li class="mb-1 ps-6">⏰ داگیرتنا دەمێ ڤالا یا خوێندکاران</li><li class="mb-1 ps-6">🌐 هەبوونا کتێب و چاوکانییان بی هەمی زمانان</li></ul>
                    </div>
                    <div class="rounded-[10px] p-6 border border-[rgba(255,107,107,0.15)] reveal" style="background:rgba(255,107,107,0.07);">
                        <h3 class="text-[#ff6b6b] mb-4 font-bold" style="font-size:clamp(1.1rem,3vw,1.4rem);">هاتیە ئامادەکرن ژ هێلا:</h3>
                        <div class="grid gap-4" style="grid-template-columns: repeat(auto-fit, minmax(min(220px,100%),1fr));">
                            <div class="text-center p-5 bg-white rounded-xl shadow-sm"><h4 class="font-bold text-[#2d2d3a] mb-2">ژیاو یوسف حسێن</h4><p class="text-[#6b6b80]">خینکارێ بایۆلۆجیێ - دابینکرنا پرتوکان</p></div>
                            <div class="text-center p-5 bg-white rounded-xl shadow-sm"><h4 class="font-bold text-[#2d2d3a] mb-2">نیار قادر ڕەسوڵ</h4><p class="text-[#6b6b80]">خینکارێ بایۆلۆجیێ - ڕێکخستنا پرتوکخانەیێ</p></div>
                            <div class="text-center p-5 bg-white rounded-xl shadow-sm"><h4 class="font-bold text-[#2d2d3a] mb-2">محمود خدر فقێ ڕەسوڵ</h4><p class="text-[#6b6b80]">خینکارێ بایۆلۆجیێ - پێشدەبرێ مالپەرێ</p><div class="social-placeholder"></div></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="lang-content" style="direction:ltr;" data-lang="ku-badini-lat">
            <div class="text-center max-w-[900px] mx-auto">
                <h2 class="font-bold text-[#2d2d3a] mb-8" style="font-size:clamp(1.7rem,4.5vw,2.4rem);">Pêşgotin</h2>
                <div class="relative rounded-[18px] overflow-hidden shimmer-border reveal" style="background: linear-gradient(145deg, #f5f7ff 0%, #eef0f8 100%); padding: clamp(1.5rem,5vw,3rem); box-shadow: 0 8px 32px rgba(102,126,234,0.15);">
                    <p class="text-[#6b6b80] leading-[1.9] mb-6 text-justify" style="font-size:clamp(0.95rem,2.5vw,1.15rem);">Bi xêr hatin bo pirtûkxaneya elektronîkî ya Koleja Zanistê - jîngeheke dîjîtal ku ji bo xwendekar û karmendên akademîk gihîştina agahdarî û fêrbûnê xweştir û lêkolînê jî bikêrtir hatîye sêwirandin.</p>
                    <p class="text-[#6b6b80] leading-[1.9] mb-6 text-justify" style="font-size:clamp(0.95rem,2.5vw,1.15rem);">Ev pirtûkxaneya elektronîkî cûrbecûr çavkaniyên zanistî pêşkêş dike, di nav de pirtûkên dersê, gotarên zanistî û akademîk, kovarên akademîk, materyalên hînkirinê û referansên perwerdehiyê, hemî li ser platformek hêsan peyda dibin. Ew dihêle ku bikarhêner her dem, li her deverê li agahdariyê bigerin, hem piştgirî dide fêrbûna serbixwe û hem jî çalakiyên lêkolînê yên pêşkeftî. Ev hemû bi rêya kodek QR-ê ya elektronîkî hatî amadekirin.</p>
                    <p class="text-[#6b6b80] leading-[1.9] mb-6 text-justify" style="font-size:clamp(0.95rem,2.5vw,1.15rem);">Pirtûkxaneya elektronîk ji berhevokeke dîjîtal bêtir e, ew têkiliya di navbera zanist û teknolojiyê de nîşan dide. Ew ramana rexnegir û fêrbûna berdewam teşwîq dike, di heman demê de alîkariya xwendekar û lêkolîneran dike ku li ser pêşkeftinên zanistî yên nûjen agahdar bimînin.</p>
                    <p class="text-[#6b6b80] leading-[1.9] mb-6 text-justify" style="font-size:clamp(0.95rem,2.5vw,1.15rem);">Erka me avakirina qadeke akademîk a piştgir e ku tê de agahî bi azadî peyda bibin, raman werin pêşxistin, û zanyarên pêşerojê werin hêzdar kirin ku kifş bikin, nûjeniyê bikin û beşdarî pêşveçûna zanist û civakê bibin.</p>

                    <div class="rounded-[10px] p-6 mb-6 border border-[rgba(102,126,234,0.16)] reveal" style="background:rgba(102,126,234,0.08);">
                        <h3 class="text-[#667eea] mb-4 font-bold" style="font-size:clamp(1.1rem,3vw,1.4rem);">Armancên Projeyê:</h3>
                        <ul class="list-none p-0 text-[#6b6b80] leading-loose"><li class="mb-1 pl-6">🎓 Piştgirîkirina xwendina serhêl li zanko</li><li class="mb-1 pl-6">⚡ Gihîştina zû û hêsan bo çavkaniyên zanistî</li><li class="mb-1 pl-6">📖 Berhevkirina hezaran çavkaniyên zanistî di yek cihî de</li><li class="mb-1 pl-6">📱 Bikaranîna teknolojiya nû <a href="https://scence-bio.github.io/Qr-Code/" style="color: gold; text-shadow: 0 0 8px gold, 0 0 15px gold;">QR code</a></li><li class="mb-1 pl-6">👨‍🏫 Sûdwergirtina mamoste û xwendekaran ji kitêbxaneya dîjîtal</li><li class="mb-1 pl-6">⏰ Tijîkirina demên vala yên xwendekaran bi projeyek zanistî</li><li class="mb-1 pl-6">🌐 Berdestbûna pirtûk û çavkaniyan bi hemû zimanan</li></ul>
                    </div>
                    <div class="rounded-[10px] p-6 border border-[rgba(255,107,107,0.15)] reveal" style="background:rgba(255,107,107,0.07);">
                        <h3 class="text-[#ff6b6b] mb-4 font-bold" style="font-size:clamp(1.1rem,3vw,1.4rem);">Amadekirî ji aliyê:</h3>
                        <div class="grid gap-4" style="grid-template-columns: repeat(auto-fit, minmax(min(220px,100%),1fr));">
                            <div class="text-center p-5 bg-white rounded-xl shadow-sm"><h4 class="font-bold text-[#2d2d3a] mb-2">JIYAW YUSIF HUSÊN</h4><p class="text-[#6b6b80]">Xwendekarê Beşê Biyolojiyê - Dabînkirina pirtûkan</p></div>
                            <div class="text-center p-5 bg-white rounded-xl shadow-sm"><h4 class="font-bold text-[#2d2d3a] mb-2">NIYAR QADIR RASÛL</h4><p class="text-[#6b6b80]">Xwendekarê Beşê Biyolojiyê - Rêxistina kitêbxanê</p></div>
                            <div class="text-center p-5 bg-white rounded-xl shadow-sm"><h4 class="font-bold text-[#2d2d3a] mb-2">MEHMÛD XIDIR FAQÊ RASÛL</h4><p class="text-[#6b6b80]">Xwendekarê Beşê Biyolojiyê - Pêşxistinkarê malperê</p><div class="social-placeholder"></div></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="lang-content" style="direction:rtl;" data-lang="ku-hawrami">
            <div class="text-center max-w-[900px] mx-auto">
                <h2 class="font-bold text-[#2d2d3a] mb-8" style="font-size:clamp(1.7rem,4.5vw,2.4rem);">پێشەکی</h2>
                <div class="relative rounded-[18px] overflow-hidden shimmer-border reveal" style="background: linear-gradient(145deg, #f5f7ff 0%, #eef0f8 100%); padding: clamp(1.5rem,5vw,3rem); box-shadow: 0 8px 32px rgba(102,126,234,0.15);">
                    <p class="text-[#6b6b80] leading-[1.9] mb-6 text-justify" style="font-size:clamp(0.95rem,2.5vw,1.15rem);">خۆش بگەیەیت بۆ کتێبخانەی ئەلیکترۆنی کۆلێژی زانست — ئەمە یەک ژینگەی دیجیتاڵیە کە دروستکراوە بۆ ئەوەی دەستڕەسیەتی بە زانیاری و فێربوون ئاسان ببێت.</p>
                    <div class="rounded-[10px] p-6 mb-6 border border-[rgba(102,126,234,0.16)] reveal" style="background:rgba(102,126,234,0.08);">
                        <h3 class="text-[#667eea] mb-4 font-bold" style="font-size:clamp(1.1rem,3vw,1.4rem);">ئامانجەکانی پرۆژەکە:</h3>
                        <ul class="list-none p-0 text-[#6b6b80] leading-loose"><li class="mb-1 ps-6">🎓 پشتگیری کردن بە خوێندنی ئۆنلاین لە زانکۆ</li><li class="mb-1 ps-6">⚡ دەستڕەسیەتی خێرا و ئاسان بە سەرچاوەی زانستی</li><li class="mb-1 ps-6">📖 کۆکردنەوەی هەزاران سەرچاوەی زانستی لە یەک شوێن</li><li class="mb-1 ps-6">📱 بەکارهێنانی تەکنەلۆژیای نوی <a href="https://scence-bio.github.io/Qr-Code/" style="color: gold; text-shadow: 0 0 8px gold, 0 0 15px gold;">QR code</a></li><li class="mb-1 ps-6">👨‍🏫 سوودگرتنی مامۆستایان و خوێندکاران لە کتێبخانەی دیجیتاڵ</li><li class="mb-1 ps-6">⏰ پڕکردنی کاتی بەتاڵی خوێندکاران بە پرۆژەیەکی زانستی</li><li class="mb-1 ps-6">🌐 بەردەستبوونی کتێب و سەرچاوەکان بە هەموو زمانەکان</li></ul>
                    </div>
                    <div class="rounded-[10px] p-6 border border-[rgba(255,107,107,0.15)] reveal" style="background:rgba(255,107,107,0.07);">
                        <h3 class="text-[#ff6b6b] mb-4 font-bold" style="font-size:clamp(1.1rem,3vw,1.4rem);">ئامادەکراوە لەلایەن:</h3>
                        <div class="grid gap-4" style="grid-template-columns: repeat(auto-fit, minmax(min(220px,100%),1fr));">
                            <div class="text-center p-5 bg-white rounded-xl shadow-sm"><h4 class="font-bold text-[#2d2d3a] mb-2">ژیاو یوسف حسێن</h4><p class="text-[#6b6b80]">خوێندکاری بەشی بایۆلۆجی - دابینکردنی کتێب</p></div>
                            <div class="text-center p-5 bg-white rounded-xl shadow-sm"><h4 class="font-bold text-[#2d2d3a] mb-2">نیار قادر ڕەسوڵ</h4><p class="text-[#6b6b80]">خوێندکاری بەشی بایۆلۆجی - ڕێکخستنی کتێبخانە</p></div>
                            <div class="text-center p-5 bg-white rounded-xl shadow-sm"><h4 class="font-bold text-[#2d2d3a] mb-2">محمود خدر فقێ ڕەسوڵ</h4><p class="text-[#6b6b80]">خوێندکاری بەشی بایۆلۆجی - گەشەپێدەری وێبسایت</p><div class="social-placeholder"></div></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="lang-content" style="direction:rtl;" data-lang="fa">
            <div class="text-center max-w-[900px] mx-auto">
                <h2 class="font-bold text-[#2d2d3a] mb-8" style="font-size:clamp(1.7rem,4.5vw,2.4rem);">مقدمه</h2>
                <div class="relative rounded-[18px] overflow-hidden shimmer-border reveal" style="background: linear-gradient(145deg, #f5f7ff 0%, #eef0f8 100%); padding: clamp(1.5rem,5vw,3rem); box-shadow: 0 8px 32px rgba(102,126,234,0.15);">
                    <p class="text-[#6b6b80] leading-[1.9] mb-6 text-justify" style="font-size:clamp(0.95rem,2.5vw,1.15rem);">به کتابخانه الکترونیکی دانشکده علوم خوش آمدید - یک محیط دیجیتالی که برای لذت‌بخش کردن دسترسی به اطلاعات و یادگیری و کارآمدتر کردن تحقیقات برای دانشجویان و اعضای هیئت علمی طراحی شده است.</p>
                    <p class="text-[#6b6b80] leading-[1.9] mb-6 text-justify" style="font-size:clamp(0.95rem,2.5vw,1.15rem);">این کتابخانه الکترونیکی طیف گسترده‌ای از منابع علمی، از جمله کتاب‌های درسی، مقالات علمی و دانشگاهی، مجلات دانشگاهی، مطالب آموزشی و منابع آموزشی را ارائه می‌دهد که همگی در یک پلتفرم مناسب در دسترس هستند. این کتابخانه به کاربران امکان می‌دهد تا در هر زمان و هر مکان به جستجوی اطلاعات بپردازند و از یادگیری مستقل و فعالیت‌های تحقیقاتی پیشرفته پشتیبانی کند. همه اینها از طریق یک کد QR که به صورت الکترونیکی تهیه شده است، امکان‌پذیر است.</p>
                    <p class="text-[#6b6b80] leading-[1.9] mb-6 text-justify" style="font-size:clamp(0.95rem,2.5vw,1.15rem);">یک کتابخانه الکترونیکی چیزی بیش از یک مجموعه دیجیتال است، و رابطه بین علم و فناوری را نشان می‌دهد. این کتابخانه تفکر انتقادی و یادگیری مداوم را تشویق می‌کند، در حالی که به دانشجویان و محققان کمک می‌کند تا در جریان پیشرفت‌های علمی مدرن قرار گیرند.</p>
                    <p class="text-[#6b6b80] leading-[1.9] mb-6 text-justify" style="font-size:clamp(0.95rem,2.5vw,1.15rem);">ماموریت ما ایجاد یک فضای دانشگاهی حمایتی است که در آن اطلاعات به صورت رایگان در دسترس باشد، ایده‌ها توسعه یابند و دانشمندان آینده توانمند شوند تا کشف کنند، نوآوری کنند و به پیشرفت علم و جامعه کمک کنند.</p>
                    <div class="rounded-[10px] p-6 mb-6 border border-[rgba(102,126,234,0.16)] reveal" style="background:rgba(102,126,234,0.08);">
                        <h3 class="text-[#667eea] mb-4 font-bold" style="font-size:clamp(1.1rem,3vw,1.4rem);">اهداف پروژه:</h3>
                        <ul class="list-none p-0 text-[#6b6b80] leading-loose"><li class="mb-1 ps-6">🎓 حمایت از یادگیری آنلاین در دانشگاه‌ها</li><li class="mb-1 ps-6">⚡ دسترسی سریع و آسان به منابع علمی</li><li class="mb-1 ps-6">📖 گردآوری هزاران منبع علمی در یک مکان</li><li class="mb-1 ps-6">📱 استفاده از فناوری مدرن <a href="https://scence-bio.github.io/Qr-Code/" style="color: gold; text-shadow: 0 0 8px gold, 0 0 15px gold;">QR code</a></li><li class="mb-1 ps-6">👨‍🏫 بهره‌مندی اساتید و دانشجویان از منابع کتابخانه دیجیتال</li><li class="mb-1 ps-6">⏰ پر کردن اوقات فراغت دانشجویان با پروژه‌های علمی</li><li class="mb-1 ps-6">🌐 در دسترس بودن کتاب‌ها و منابع به همه زبان‌ها</li></ul>
                    </div>
                    <div class="rounded-[10px] p-6 border border-[rgba(255,107,107,0.15)] reveal" style="background:rgba(255,107,107,0.07);">
                        <h3 class="text-[#ff6b6b] mb-4 font-bold" style="font-size:clamp(1.1rem,3vw,1.4rem);">تهیه شده توسط:</h3>
                        <div class="grid gap-4" style="grid-template-columns: repeat(auto-fit, minmax(min(220px,100%),1fr));">
                            <div class="text-center p-5 bg-white rounded-xl shadow-sm"><h4 class="font-bold text-[#2d2d3a] mb-2">ژیاو یوسف حسێن</h4><p class="text-[#6b6b80]">دانشجوی گروه زیست‌شناسی - تأمین کتاب‌ها</p></div>
                            <div class="text-center p-5 bg-white rounded-xl shadow-sm"><h4 class="font-bold text-[#2d2d3a] mb-2">نیار قادر ڕەسوڵ</h4><p class="text-[#6b6b80]">دانشجوی گروه زیست‌شناسی - سازماندهی کتابخانه</p></div>
                            <div class="text-center p-5 bg-white rounded-xl shadow-sm"><h4 class="font-bold text-[#2d2d3a] mb-2">محمود خدر فقێ ڕەسوڵ</h4><p class="text-[#6b6b80]">دانشجوی گروه زیست‌شناسی - توسعه‌دهنده وب</p><div class="social-placeholder"></div></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="lang-content" style="direction:ltr;" data-lang="tr">
            <div class="text-center max-w-[900px] mx-auto">
                <h2 class="font-bold text-[#2d2d3a] mb-8" style="font-size:clamp(1.7rem,4.5vw,2.4rem);">Giriş</h2>
                <div class="relative rounded-[18px] overflow-hidden shimmer-border reveal" style="background: linear-gradient(145deg, #f5f7ff 0%, #eef0f8 100%); padding: clamp(1.5rem,5vw,3rem); box-shadow: 0 8px 32px rgba(102,126,234,0.15);">
                    <p class="text-[#6b6b80] leading-[1.9] mb-6 text-justify" style="font-size:clamp(0.95rem,2.5vw,1.15rem);">Fen Fakültesi e-kütüphanesine hoş geldiniz - öğrenciler ve akademik personel için bilgiye ve öğrenmeye erişimi keyifli hale getirmek ve araştırmayı daha verimli kılmak amacıyla tasarlanmış dijital bir ortam.</p>
                    <p class="text-[#6b6b80] leading-[1.9] mb-6 text-justify" style="font-size:clamp(0.95rem,2.5vw,1.15rem);">Bu e-kütüphane, ders kitapları, bilimsel ve akademik makaleler, akademik dergiler, öğretim materyalleri ve eğitim referansları da dahil olmak üzere çok çeşitli bilimsel kaynakları tek bir kullanışlı platformda sunmaktadır. Kullanıcıların istedikleri zaman, istedikleri yerde bilgi aramalarına olanak tanıyarak hem bağımsız öğrenmeyi hem de ileri düzey araştırma faaliyetlerini desteklemektedir. Tüm bunlar, elektronik olarak hazırlanmış bir QR kodu aracılığıyla gerçekleşmektedir.</p>
                    <p class="text-[#6b6b80] leading-[1.9] mb-6 text-justify" style="font-size:clamp(0.95rem,2.5vw,1.15rem);">E-kütüphane, sadece dijital bir koleksiyon olmaktan öte, bilim ve teknoloji arasındaki ilişkiyi gösterir. Eleştirel düşünmeyi ve sürekli öğrenmeyi teşvik ederken, öğrencilerin ve araştırmacıların modern bilimsel gelişmelerden haberdar olmalarına yardımcı olur.</p>
                    <p class="text-[#6b6b80] leading-[1.9] mb-6 text-justify" style="font-size:clamp(0.95rem,2.5vw,1.15rem);">Misyonumuz, bilginin özgürce erişilebildiği, fikirlerin geliştirildiği ve geleceğin bilim insanlarının keşfetme, yenilik yapma ve bilimin ve toplumun ilerlemesine katkıda bulunma konusunda güçlendirildiği destekleyici bir akademik alan oluşturmaktır.</p>
                    <div class="rounded-[10px] p-6 mb-6 border border-[rgba(102,126,234,0.16)] reveal" style="background:rgba(102,126,234,0.08);">
                        <h3 class="text-[#667eea] mb-4 font-bold" style="font-size:clamp(1.1rem,3vw,1.4rem);">Proje Hedefleri:</h3>
                        <ul class="list-none p-0 text-[#6b6b80] leading-loose"><li class="mb-1 pl-6">🎓 Üniversitelerde çevrimiçi öğrenimi destekleme</li><li class="mb-1 pl-6">⚡ Bilimsel kaynaklara hızlı ve kolay erişim</li><li class="mb-1 pl-6">📖 Binlerce bilimsel kaynağı tek bir yerde toplama</li><li class="mb-1 pl-6">📱 Modern teknolojiyi <a href="https://scence-bio.github.io/Qr-Code/" style="color: gold; text-shadow: 0 0 8px gold, 0 0 15px gold;">QR code</a> eğitime hizmet için kullanma</li><li class="mb-1 pl-6">👨‍🏫 Öğretmenlerin ve öğrencilerin dijital kütüphane kaynaklarından yararlanması</li><li class="mb-1 pl-6">⏰ Öğrencilerin boş zamanlarını bilimsel projelerle değerlendirme</li><li class="mb-1 pl-6">🌐 Kitap ve kaynakların tüm dillerde erişilebilir olması</li></ul>
                    </div>
                    <div class="rounded-[10px] p-6 border border-[rgba(255,107,107,0.15)] reveal" style="background:rgba(255,107,107,0.07);">
                        <h3 class="text-[#ff6b6b] mb-4 font-bold" style="font-size:clamp(1.1rem,3vw,1.4rem);">Hazırlayanlar:</h3>
                        <div class="grid gap-4" style="grid-template-columns: repeat(auto-fit, minmax(min(220px,100%),1fr));">
                            <div class="text-center p-5 bg-white rounded-xl shadow-sm"><h4 class="font-bold text-[#2d2d3a] mb-2">ZHYAW YUSF HUSEN</h4><p class="text-[#6b6b80]">Biyoloji Bölümü Öğrencisi - Kitap temini</p></div>
                            <div class="text-center p-5 bg-white rounded-xl shadow-sm"><h4 class="font-bold text-[#2d2d3a] mb-2">NYAR QADR RASUL</h4><p class="text-[#6b6b80]">Biyoloji Bölümü Öğrencisi - Kütüphane organizasyonu</p></div>
                            <div class="text-center p-5 bg-white rounded-xl shadow-sm"><h4 class="font-bold text-[#2d2d3a] mb-2">MAHMOOD KHDIR FAQE RASUL</h4><p class="text-[#6b6b80]">Biyoloji Bölümü Öğrencisi - Web Geliştirici</p><div class="social-placeholder"></div></div>
                        </div>
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

            <!-- KU SORANI -->
            <div class="lang-content active" style="direction:rtl;" data-lang="ku-sorani">
                <h2 class="font-bold text-[#2d2d3a] mb-8 text-center" style="font-size:clamp(1.7rem,4.5vw,2.4rem);">📚 مێژووی کتێبخانەی کۆلێژی زانست</h2>
                <div class="relative rounded-[18px] overflow-hidden shimmer-border reveal"
                     style="background: linear-gradient(145deg, #f5f7ff 0%, #eef0f8 100%); padding: clamp(1.5rem,5vw,3rem); box-shadow: 0 8px 32px rgba(102,126,234,0.15);">
                    <p class="text-[#6b6b80] leading-[1.9] mb-4 text-justify" style="font-size:clamp(0.95rem,2.5vw,1.15rem);">کتێبخانەی کۆلێژی زانست لەسەر فەرمانی بەڕێز سەرۆکی زانکۆ پڕۆفیسۆری یاریدەدەر د. پەیمان ڕەمەزان احمد بڕیاردرا بە درووستکردنی لە بینای کۆلێژی زانستی زانکۆی ڕاپەڕین نهۆمی دووەم، و لە بەرواری <strong>(١٨/١٠/٢٠٢٥)</strong> لە لایەن بەڕێزان سەرۆکی زانکۆ و ڕاگر و ئەنجومەنی کۆلێژی زانست کرایەوە بە ڕووی زانستخوازانی کۆلێژی زانست.</p>
                    <p class="text-[#6b6b80] leading-[1.9] text-justify" style="font-size:clamp(0.95rem,2.5vw,1.15rem);">کردنەوەی ئەم کتێبخانەیە وەرچەرخانێکی نوێ بوو بۆ خوێندکاران و مامۆستایان و فەرمانبەرانی کۆلێژی زانست، تا لەوێوە بە ئاسانتر و خێراتر دەستیان بگات بە کتێب و سەرچاوە زانستی و ئەکادیمیەکان و پڕۆسەی خوێندن و فێربوون خێراتر و کوالێتی بەرزتر بێت.</p>
                </div>
            </div>

            <!-- EN -->
            <div class="lang-content" style="direction:ltr;" data-lang="en">
                <h2 class="font-bold text-[#2d2d3a] mb-8 text-center" style="font-size:clamp(1.7rem,4.5vw,2.4rem);">📚 History of the College of Science Library</h2>
                <div class="relative rounded-[18px] overflow-hidden shimmer-border reveal"
                     style="background: linear-gradient(145deg, #f5f7ff 0%, #eef0f8 100%); padding: clamp(1.5rem,5vw,3rem); box-shadow: 0 8px 32px rgba(102,126,234,0.15);">
                    <p class="text-[#6b6b80] leading-[1.9] mb-4 text-justify" style="font-size:clamp(0.95rem,2.5vw,1.15rem);">The College of Science Library was established by order of the esteemed University President, Assistant Professor Dr. Payman Ramazan Ahmad, and was built on the second floor of the College of Science building at the University of Raparin. It was officially opened on <strong>18/10/2025</strong> by the University President, the Vice-President, and the College of Science Council, welcoming the science-seeking students of the College.</p>
                    <p class="text-[#6b6b80] leading-[1.9] text-justify" style="font-size:clamp(0.95rem,2.5vw,1.15rem);">The opening of this library marked a new turning point for the students, faculty, and staff of the College of Science, enabling them to access scientific and academic books and resources more easily and quickly, and raising the quality and efficiency of the learning process.</p>
                </div>
            </div>

            <!-- AR -->
            <div class="lang-content" style="direction:rtl;" data-lang="ar">
                <h2 class="font-bold text-[#2d2d3a] mb-8 text-center" style="font-size:clamp(1.7rem,4.5vw,2.4rem);">📚 تاريخ مكتبة كلية العلوم</h2>
                <div class="relative rounded-[18px] overflow-hidden shimmer-border reveal"
                     style="background: linear-gradient(145deg, #f5f7ff 0%, #eef0f8 100%); padding: clamp(1.5rem,5vw,3rem); box-shadow: 0 8px 32px rgba(102,126,234,0.15);">
                    <p class="text-[#6b6b80] leading-[1.9] mb-4 text-justify" style="font-size:clamp(0.95rem,2.5vw,1.15rem);">أُنشئت مكتبة كلية العلوم بأمر من السيد رئيس الجامعة الأستاذ المساعد الدكتور پەیمان ڕەمەزان احمد، وذلك في الطابق الثاني من مبنى كلية العلوم في جامعة رابرين. وقد افتُتحت رسمياً بتاريخ <strong>١٨/١٠/٢٠٢٥</strong> على يد رئيس الجامعة ونائبه ومجلس كلية العلوم، في حفل استقبل طلاب كلية العلوم الساعين إلى المعرفة.</p>
                    <p class="text-[#6b6b80] leading-[1.9] text-justify" style="font-size:clamp(0.95rem,2.5vw,1.15rem);">شكّل افتتاح هذه المكتبة منعطفاً جديداً للطلاب والأساتذة والموظفين في كلية العلوم، إذ أتاح لهم الوصول إلى الكتب والمصادر العلمية والأكاديمية بصورة أيسر وأسرع، مما أسهم في رفع جودة العملية التعليمية وكفاءتها.</p>
                </div>
            </div>

            <!-- KU BADINI -->
            <div class="lang-content" style="direction:rtl;" data-lang="ku-badini">
                <h2 class="font-bold text-[#2d2d3a] mb-8 text-center" style="font-size:clamp(1.7rem,4.5vw,2.4rem);">📚 مێژووی پرتوکخانەیا کۆلێژا زانستێ</h2>
                <div class="relative rounded-[18px] overflow-hidden shimmer-border reveal"
                     style="background: linear-gradient(145deg, #f5f7ff 0%, #eef0f8 100%); padding: clamp(1.5rem,5vw,3rem); box-shadow: 0 8px 32px rgba(102,126,234,0.15);">
                    <p class="text-[#6b6b80] leading-[1.9] mb-4 text-justify" style="font-size:clamp(0.95rem,2.5vw,1.15rem);">پرتوکخانەیا کۆلێژا زانستێ بە فەرمانا رێزدار سەرۆکێ زانینگەهێ پرۆفیسۆرێ جیهێلکار د. پەیمان ڕەمەزان احمد هاتیە دامەزراندن، ل نهۆمێ دووێ یا بینایا کۆلێژا زانستێ یا زانینگەها ڕاپەرین. ل بەرواری <strong>(١٨/١٠/٢٠٢٥)</strong> ژ هێلا سەرۆکێ زانینگەهێ و یارمەتیدەرێ و ئەنجومەنێ کۆلێژا زانستێ هاتیە کرانەوە بۆ خوێندکارێن زانستدۆست.</p>
                    <p class="text-[#6b6b80] leading-[1.9] text-justify" style="font-size:clamp(0.95rem,2.5vw,1.15rem);">ئەڤ کرانەوە وەرگیرانا نوێ بوو ژ بۆ خوێندکار، مامۆستا و کارمەندێن کۆلێژا زانستێ، کو ئیستا بیهێسانی و بی زوانی دەستدا ب پرتوک و چاوکانیێن زانستی و ئەکادیمیک، و قەریقا فێربوونێ باştir û kalîtetir bibe.</p>
                </div>
            </div>

            <!-- KU BADINI LAT -->
            <div class="lang-content" style="direction:ltr;" data-lang="ku-badini-lat">
                <h2 class="font-bold text-[#2d2d3a] mb-8 text-center" style="font-size:clamp(1.7rem,4.5vw,2.4rem);">📚 Dîroka Pirtûkxaneya Koleja Zanistê</h2>
                <div class="relative rounded-[18px] overflow-hidden shimmer-border reveal"
                     style="background: linear-gradient(145deg, #f5f7ff 0%, #eef0f8 100%); padding: clamp(1.5rem,5vw,3rem); box-shadow: 0 8px 32px rgba(102,126,234,0.15);">
                    <p class="text-[#6b6b80] leading-[1.9] mb-4 text-justify" style="font-size:clamp(0.95rem,2.5vw,1.15rem);">Pirtûkxaneya Koleja Zanistê bi fermana rêzdar Serokê Zanîngehê Profesor Alîkar Dr. Peyman Ramezanê Ehmed hat avakirin, li qata duyemîn a avahiya Koleja Zanistê ya Zanîngeha Raperinê. Di tarîxa <strong>18/10/2025</strong> de ji aliyê Serokê Zanîngehê, Cîgirê wî û Encumena Koleja Zanistê ve hat vekirin û xwendekarên zanistxwaz pêşwazî lê hat kirin.</p>
                    <p class="text-[#6b6b80] leading-[1.9] text-justify" style="font-size:clamp(0.95rem,2.5vw,1.15rem);">Vekirina vê pirtûkxaneyê xalek nû û girîng bû ji bo xwendekar, mamoste û karmendên Koleja Zanistê, û derfet da wan ku bi awayekî hêsantir û bilez bigihîjin pirtûk û çavkaniyên zanistî û akademîk, û pêvajoya fêrbûnê kalîtir û kêrhatîtir bibe.</p>
                </div>
            </div>

            <!-- KU HAWRAMI -->
            <div class="lang-content" style="direction:rtl;" data-lang="ku-hawrami">
                <h2 class="font-bold text-[#2d2d3a] mb-8 text-center" style="font-size:clamp(1.7rem,4.5vw,2.4rem);">📚 مێژووی کتێبخانەی کۆلێژی زانست</h2>
                <div class="relative rounded-[18px] overflow-hidden shimmer-border reveal"
                     style="background: linear-gradient(145deg, #f5f7ff 0%, #eef0f8 100%); padding: clamp(1.5rem,5vw,3rem); box-shadow: 0 8px 32px rgba(102,126,234,0.15);">
                    <p class="text-[#6b6b80] leading-[1.9] mb-4 text-justify" style="font-size:clamp(0.95rem,2.5vw,1.15rem);">کتێبخانەی کۆلێژی زانست بەرفەرمانی بەڕێز سەرۆکی زانکۆ پڕۆفیسۆری یاریدەدەر د. پەیمان ڕەمەزان احمد دروستکرا، لە نهۆمی دووەمی بینای کۆلێژی زانستی زانکۆی ڕاپەڕین. لە بەرواری <strong>(١٨/١٠/٢٠٢٥)</strong> لە لایەن سەرۆکی زانکۆ و ڕاگر و ئەنجومەنی کۆلێژی زانست کرایەوە بەر زانستخوازانی کۆلێژی زانست.</p>
                    <p class="text-[#6b6b80] leading-[1.9] text-justify" style="font-size:clamp(0.95rem,2.5vw,1.15rem);">کردنەوەی ئەم کتێبخانەیە پۆینتێکی نوێی گرنگ بوو بۆ خوێندکاران، مامۆستایان و فەرمانبەرانی کۆلێژی زانست، کە بە ئاسانی و خێرایی دەستیان گات بە کتێب و سەرچاوەی زانستی و ئەکادیمی، و پڕۆسەی فێربوون بەرزتر و باشتر بوو.</p>
                </div>
            </div>

            <!-- FA -->
            <div class="lang-content" style="direction:rtl;" data-lang="fa">
                <h2 class="font-bold text-[#2d2d3a] mb-8 text-center" style="font-size:clamp(1.7rem,4.5vw,2.4rem);">📚 تاریخچه کتابخانه دانشکده علوم</h2>
                <div class="relative rounded-[18px] overflow-hidden shimmer-border reveal"
                     style="background: linear-gradient(145deg, #f5f7ff 0%, #eef0f8 100%); padding: clamp(1.5rem,5vw,3rem); box-shadow: 0 8px 32px rgba(102,126,234,0.15);">
                    <p class="text-[#6b6b80] leading-[1.9] mb-4 text-justify" style="font-size:clamp(0.95rem,2.5vw,1.15rem);">کتابخانه دانشکده علوم به دستور ریاست محترم دانشگاه، استادیار دکتر پەیمان ڕەمەزان احمد تأسیس شد و در طبقه دوم ساختمان دانشکده علوم دانشگاه راپەرین قرار دارد. این کتابخانه در تاریخ <strong>۱۸/۱۰/۲۰۲۵</strong> توسط رئیس دانشگاه، معاون ایشان و شورای دانشکده علوم افتتاح شد و به روی دانشجویان علم‌آموز گشوده گردید.</p>
                    <p class="text-[#6b6b80] leading-[1.9] text-justify" style="font-size:clamp(0.95rem,2.5vw,1.15rem);">افتتاح این کتابخانه نقطه عطف جدیدی برای دانشجویان، اساتید و کارمندان دانشکده علوم بود تا از آن طریق به کتاب‌ها و منابع علمی و آکادمیک با سهولت و سرعت بیشتری دسترسی داشته باشند و فرایند آموزش و یادگیری باکیفیت‌تر و کارآمدتر شود.</p>
                </div>
            </div>

            <!-- TR -->
            <div class="lang-content" style="direction:ltr;" data-lang="tr">
                <h2 class="font-bold text-[#2d2d3a] mb-8 text-center" style="font-size:clamp(1.7rem,4.5vw,2.4rem);">📚 Fen Fakültesi Kütüphanesinin Tarihi</h2>
                <div class="relative rounded-[18px] overflow-hidden shimmer-border reveal"
                     style="background: linear-gradient(145deg, #f5f7ff 0%, #eef0f8 100%); padding: clamp(1.5rem,5vw,3rem); box-shadow: 0 8px 32px rgba(102,126,234,0.15);">
                    <p class="text-[#6b6b80] leading-[1.9] mb-4 text-justify" style="font-size:clamp(0.95rem,2.5vw,1.15rem);">Fen Fakültesi Kütüphanesi, saygıdeğer Üniversite Rektörü Yardımcı Doçent Dr. Peyman Ramazan Ahmed'in emriyle kurulmuş olup Raparin Üniversitesi Fen Fakültesi binasının ikinci katında yer almaktadır. Kütüphane, <strong>18/10/2025</strong> tarihinde Rektör, Rektör Yardımcısı ve Fen Fakültesi Kurulu tarafından törenle açılmış ve Fen Fakültesi'nin bilim meraklısı öğrencilerine kapılarını açmıştır.</p>
                    <p class="text-[#6b6b80] leading-[1.9] text-justify" style="font-size:clamp(0.95rem,2.5vw,1.15rem);">Bu kütüphanenin açılışı, Fen Fakültesi'nin öğrencileri, öğretim üyeleri ve personeli için yeni bir dönüm noktası oldu; bilimsel ve akademik kitap ve kaynaklara daha kolay ve hızlı erişim sağlanarak öğrenme sürecinin kalitesi ve verimliliği artırıldı.</p>
                </div>
            </div>

        </div>
    </div>
</section>
<!-- ══════════ LIBRARY SECTION 1 ══════════ -->
<section style="padding: clamp(1rem,4vw,0.5rem) 0; background: linear-gradient(160deg, #f0f2ff 0%, #e8ebff 100%);">
    <div class="max-w-[1200px] mx-auto px-6 sm:px-8 lg:px-10">
        <div class="text-center mb-10 reveal">
            <div class="lang-content active" style="direction:rtl;" data-lang="ku-sorani"><h2 class="font-bold text-[#2d2d3a] mb-6" style="font-size:clamp(1.7rem,4.5vw,2.4rem);">کتێبخانەی سەرەکی</h2><a href="https://drive.google.com/drive/folders/12PipzBzMVgfr1tFSy-4bplnVMnNHTy4d" class="main-library-btn relative inline-block font-bold text-white rounded-full no-underline text-center transition-all duration-300 hover:-translate-y-1.5 hover:scale-[1.03] active:translate-y-0 active:scale-[0.99]" style="background: linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%); padding: clamp(0.9rem,3vw,1.15rem) clamp(2rem,6vw,3rem); font-size:clamp(0.95rem,2.5vw,1.15rem); box-shadow: 0 8px 24px rgba(255,107,107,0.35); min-width:200px;" target="_blank">چونە ناو کتێبخانەی گشتی ١</a></div>
            <div class="lang-content" style="direction:rtl;" data-lang="ku-badini"><h2 class="font-bold text-[#2d2d3a] mb-6" style="font-size:clamp(1.7rem,4.5vw,2.4rem);">کتێبخانەیا سەرەکە</h2><a href="https://drive.google.com/drive/folders/12PipzBzMVgfr1tFSy-4bplnVMnNHTy4d" class="main-library-btn relative inline-block font-bold text-white rounded-full no-underline text-center transition-all duration-300 hover:-translate-y-1.5" style="background: linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%); padding: clamp(0.9rem,3vw,1.15rem) clamp(2rem,6vw,3rem); font-size:clamp(0.95rem,2.5vw,1.15rem); box-shadow: 0 8px 24px rgba(255,107,107,0.35); min-width:200px;" target="_blank">بکەڤن کتێبخانەیا سەرەکە ١</a></div>
            <div class="lang-content" style="direction:ltr;" data-lang="ku-badini-lat"><h2 class="font-bold text-[#2d2d3a] mb-6" style="font-size:clamp(1.7rem,4.5vw,2.4rem);">Kitêbxaneya Sereke</h2><a href="https://drive.google.com/drive/folders/12PipzBzMVgfr1tFSy-4bplnVMnNHTy4d" class="main-library-btn relative inline-block font-bold text-white rounded-full no-underline text-center transition-all duration-300 hover:-translate-y-1.5" style="background: linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%); padding: clamp(0.9rem,3vw,1.15rem) clamp(2rem,6vw,3rem); font-size:clamp(0.95rem,2.5vw,1.15rem); box-shadow: 0 8px 24px rgba(255,107,107,0.35); min-width:200px;" target="_blank">Têkeve Kitêbxaneya Giştî 1</a></div>
            <div class="lang-content" style="direction:rtl;" data-lang="ku-hawrami"><h2 class="font-bold text-[#2d2d3a] mb-6" style="font-size:clamp(1.7rem,4.5vw,2.4rem);">کتێبخانەی سەرەکی</h2><a href="https://drive.google.com/drive/folders/12PipzBzMVgfr1tFSy-4bplnVMnNHTy4d" class="main-library-btn relative inline-block font-bold text-white rounded-full no-underline text-center transition-all duration-300 hover:-translate-y-1.5" style="background: linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%); padding: clamp(0.9rem,3vw,1.15rem) clamp(2rem,6vw,3rem); font-size:clamp(0.95rem,2.5vw,1.15rem); box-shadow: 0 8px 24px rgba(255,107,107,0.35); min-width:200px;" target="_blank">بچۆ ناو کتێبخانەی گشتی ١</a></div>
            <div class="lang-content" style="direction:ltr;" data-lang="en"><h2 class="font-bold text-[#2d2d3a] mb-6" style="font-size:clamp(1.7rem,4.5vw,2.4rem);">Main Library</h2><a href="https://drive.google.com/drive/folders/12PipzBzMVgfr1tFSy-4bplnVMnNHTy4d" class="main-library-btn relative inline-block font-bold text-white rounded-full no-underline text-center transition-all duration-300 hover:-translate-y-1.5" style="background: linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%); padding: clamp(0.9rem,3vw,1.15rem) clamp(2rem,6vw,3rem); font-size:clamp(0.95rem,2.5vw,1.15rem); box-shadow: 0 8px 24px rgba(255,107,107,0.35); min-width:200px;" target="_blank">Enter General Library 1</a></div>
            <div class="lang-content" style="direction:rtl;" data-lang="ar"><h2 cla
                                                                                ss="font-bold text-[#2d2d3a] mb-6" style="font-size:clamp(1.7rem,4.5vw,2.4rem);">المكتبة الرئيسية</h2><a href="https://drive.google.com/drive/folders/12PipzBzMVgfr1tFSy-4bplnVMnNHTy4d" class="main-library-btn relative inline-block font-bold text-white rounded-full no-underline text-center transition-all duration-300 hover:-translate-y-1.5" style="background: linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%); padding: clamp(0.9rem,3vw,1.15rem) clamp(2rem,6vw,3rem); font-size:clamp(0.95rem,2.5vw,1.15rem); box-shadow: 0 8px 24px rgba(255,107,107,0.35); min-width:200px;" target="_blank">دخول إلى المكتبة العامة ١</a></div>
            <div class="lang-content" style="direction:rtl;" data-lang="fa"><h2 class="font-bold text-[#2d2d3a] mb-6" style="font-size:clamp(1.7rem,4.5vw,2.4rem);">کتابخانه اصلی</h2><a href="https://drive.google.com/drive/folders/12PipzBzMVgfr1tFSy-4bplnVMnNHTy4d" class="main-library-btn relative inline-block font-bold text-white rounded-full no-underline text-center transition-all duration-300 hover:-translate-y-1.5" style="background: linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%); padding: clamp(0.9rem,3vw,1.15rem) clamp(2rem,6vw,3rem); font-size:clamp(0.95rem,2.5vw,1.15rem); box-shadow: 0 8px 24px rgba(255,107,107,0.35); min-width:200px;" target="_blank">ورود به کتابخانه عمومی ۱</a></div>
            <div class="lang-content" style="direction:ltr;" data-lang="tr"><h2 class="font-bold text-[#2d2d3a] mb-6" style="font-size:clamp(1.7rem,4.5vw,2.4rem);">Ana Kütüphane</h2><a href="https://drive.google.com/drive/folders/12PipzBzMVgfr1tFSy-4bplnVMnNHTy4d" class="main-library-btn relative inline-block font-bold text-white rounded-full no-underline text-center transition-all duration-300 hover:-translate-y-1.5" style="background: linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%); padding: clamp(0.9rem,3vw,1.15rem) clamp(2rem,6vw,3rem); font-size:clamp(0.95rem,2.5vw,1.15rem); box-shadow: 0 8px 24px rgba(255,107,107,0.35); min-width:200px;" target="_blank">Genel Kütüphane 1'e Gir</a></div>
        </div>
    </div>
</section>

<!-- ══════════ LIBRARY 2 + DEPARTMENTS ══════════ -->
<section style="padding: clamp(1rem,4vw,3rem) 0; background: linear-gradient(160deg, #f0f2ff 0%, #e8ebff 100%);">
    <div class="max-w-[1200px] mx-auto px-6 sm:px-8 lg:px-10">

        <!-- Library 2 button -->
        <div class="text-center mb-12 reveal">
            <div class="lang-content active" style="direction:rtl;" data-lang="ku-sorani"><a href="https://drive.google.com/drive/folders/1KkvwcZdKCZzV7gjExlnOdl1JnCELHCkC" class="main-library-btn relative inline-block font-bold text-white rounded-full no-underline text-center transition-all duration-300 hover:-translate-y-1.5 hover:scale-[1.03]" style="background: linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%); padding: clamp(0.9rem,3vw,1.15rem) clamp(2rem,6vw,3rem); font-size:clamp(0.95rem,2.5vw,1.15rem); box-shadow: 0 8px 24px rgba(255,107,107,0.35); min-width:200px;" target="_blank">چونە ناو کتێبخانەی گشتی ٢</a></div>
            <div class="lang-content" style="direction:rtl;" data-lang="ku-badini"><a href="https://drive.google.com/drive/folders/1KkvwcZdKCZzV7gjExlnOdl1JnCELHCkC" class="main-library-btn relative inline-block font-bold text-white rounded-full no-underline text-center transition-all duration-300 hover:-translate-y-1.5" style="background: linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%); padding: clamp(0.9rem,3vw,1.15rem) clamp(2rem,6vw,3rem); font-size:clamp(0.95rem,2.5vw,1.15rem); box-shadow: 0 8px 24px rgba(255,107,107,0.35); min-width:200px;" target="_blank">بکەڤن کتێبخانەیا سەرەکە ٢</a></div>
            <div class="lang-content" style="direction:ltr;" data-lang="ku-badini-lat"><a href="https://drive.google.com/drive/folders/1KkvwcZdKCZzV7gjExlnOdl1JnCELHCkC" class="main-library-btn relative inline-block font-bold text-white rounded-full no-underline text-center transition-all duration-300 hover:-translate-y-1.5" style="background: linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%); padding: clamp(0.9rem,3vw,1.15rem) clamp(2rem,6vw,3rem); font-size:clamp(0.95rem,2.5vw,1.15rem); box-shadow: 0 8px 24px rgba(255,107,107,0.35); min-width:200px;" target="_blank">Têkeve Kitêbxaneya Giştî 2</a></div>
            <div class="lang-content" style="direction:rtl;" data-lang="ku-hawrami"><a href="https://drive.google.com/drive/folders/1KkvwcZdKCZzV7gjExlnOdl1JnCELHCkC" class="main-library-btn relative inline-block font-bold text-white rounded-full no-underline text-center transition-all duration-300 hover:-translate-y-1.5" style="background: linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%); padding: clamp(0.9rem,3vw,1.15rem) clamp(2rem,6vw,3rem); font-size:clamp(0.95rem,2.5vw,1.15rem); box-shadow: 0 8px 24px rgba(255,107,107,0.35); min-width:200px;" target="_blank">بچۆ ناو کتێبخانەی گشتی ٢</a></div>
            <div class="lang-content" style="direction:ltr;" data-lang="en"><a href="https://drive.google.com/drive/folders/1KkvwcZdKCZzV7gjExlnOdl1JnCELHCkC" class="main-library-btn relative inline-block font-bold text-white rounded-full no-underline text-center transition-all duration-300 hover:-translate-y-1.5" style="background: linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%); padding: clamp(0.9rem,3vw,1.15rem) clamp(2rem,6vw,3rem); font-size:clamp(0.95rem,2.5vw,1.15rem); box-shadow: 0 8px 24px rgba(255,107,107,0.35); min-width:200px;" target="_blank">Enter General Library 2</a></div>
            <div class="lang-content" style="direction:rtl;" data-lang="ar"><a href="https://drive.google.com/drive/folders/1KkvwcZdKCZzV7gjExlnOdl1JnCELHCkC" class="main-library-btn relative inline-block font-bold text-white rounded-full no-underline text-center transition-all duration-300 hover:-translate-y-1.5" style="background: linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%); padding: clamp(0.9rem,3vw,1.15rem) clamp(2rem,6vw,3rem); font-size:clamp(0.95rem,2.5vw,1.15rem); box-shadow: 0 8px 24px rgba(255,107,107,0.35); min-width:200px;" target="_blank">دخول إلى المكتبة العامة ٢</a></div>
            <div class="lang-content" style="direction:rtl;" data-lang="fa"><a href="https://drive.google.com/drive/folders/1KkvwcZdKCZzV7gjExlnOdl1JnCELHCkC" class="main-library-btn relative inline-block font-bold text-white rounded-full no-underline text-center transition-all duration-300 hover:-translate-y-1.5" style="background: linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%); padding: clamp(0.9rem,3vw,1.15rem) clamp(2rem,6vw,3rem); font-size:clamp(0.95rem,2.5vw,1.15rem); box-shadow: 0 8px 24px rgba(255,107,107,0.35); min-width:200px;" target="_blank">ورود به کتابخانه عمومی ۲</a></div>
            <div class="lang-content" style="direction:ltr;" data-lang="tr"><a href="https://drive.google.com/drive/folders/1KkvwcZdKCZzV7gjExlnOdl1JnCELHCkC" class="main-library-btn relative inline-block font-bold text-white rounded-full no-underline text-center transition-all duration-300 hover:-translate-y-1.5" style="background: linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%); padding: clamp(0.9rem,3vw,1.15rem) clamp(2rem,6vw,3rem); font-size:clamp(0.95rem,2.5vw,1.15rem); box-shadow: 0 8px 24px rgba(255,107,107,0.35); min-width:200px;" target="_blank">Genel Kütüphane 2'ye Gir</a></div>
        </div>

        <!-- Department cards (DB-driven) -->
        @php
            $depLocales = [
                ['lang' => 'ku-sorani', 'dir' => 'rtl', 'heading' => 'بەشە زانستییەکان'],
                ['lang' => 'en', 'dir' => 'ltr', 'heading' => 'Scientific Departments'],
                ['lang' => 'ar', 'dir' => 'rtl', 'heading' => 'الأقسام العلمية'],
                ['lang' => 'ku-badini', 'dir' => 'rtl', 'heading' => 'بەشێن زانستی'],
                ['lang' => 'ku-badini-lat', 'dir' => 'ltr', 'heading' => 'Beşên Zanistî'],
                ['lang' => 'ku-hawrami', 'dir' => 'rtl', 'heading' => 'بەشەکانی زانستی'],
                ['lang' => 'fa', 'dir' => 'rtl', 'heading' => 'بخش‌های علمی'],
                ['lang' => 'tr', 'dir' => 'ltr', 'heading' => 'Bilim Bölümleri'],
            ];
        @endphp
        @foreach ($depLocales as $depLocale)
        <div id="dept-{{ $depLocale['lang'] }}" class="lang-content{{ $depLocale['lang'] === 'ku-sorani' ? ' active' : '' }}" style="direction:{{ $depLocale['dir'] }};" data-lang="{{ $depLocale['lang'] }}">
            <h2 class="text-center font-bold text-[#2d2d3a] mb-10" style="font-size:clamp(1.7rem,4.5vw,2.4rem);">{{ $depLocale['heading'] }}</h2>
            <div class="grid gap-5" style="grid-template-columns: repeat(auto-fit, minmax(260px,1fr));">
                @forelse ($departments as $department)
                <div class="section-card card-top-bar card-glow relative flex flex-col justify-between bg-white/85 backdrop-blur-md border border-white/70 rounded-[18px] text-center transition-all duration-300 hover:-translate-y-3 reveal" style="padding:clamp(1.5rem,4vw,2.2rem); min-height:280px; box-shadow:0 4px 16px rgba(102,126,234,0.10);">
                    <div>
                        <span class="block text-5xl mb-4 transition-transform duration-300">{{ $department->icon }}</span>
                        <h3 class="font-bold text-[#2d2d3a] mb-3" style="font-size:clamp(1.1rem,3vw,1.4rem);">{{ $department->translation($depLocale['lang'], 'title') }}</h3>
                        <p class="text-[#6b6b80] mb-4 flex-grow" style="font-size:clamp(0.88rem,2.2vw,0.98rem); line-height:1.65;">{{ $department->translation($depLocale['lang'], 'desc') }}</p>
                    </div>
                    <a href="{{ $department->drive_url }}" class="section-btn relative inline-block font-semibold text-white rounded-full no-underline text-center transition-all duration-300 hover:-translate-y-1 font-[inherit]" style="background:linear-gradient(135deg,#667eea 0%,#764ba2 100%); padding:clamp(0.6rem,2.2vw,0.85rem) clamp(1.3rem,3.5vw,1.9rem); font-size:clamp(0.85rem,2.2vw,0.95rem); min-width:130px; box-shadow:0 4px 14px rgba(102,126,234,0.28);" target="_blank">{{ $department->translation($depLocale['lang'], 'button') }}</a>
                </div>
                @empty
                <p class="text-center text-[#6b6b80]">No departments found.</p>
                @endforelse
            </div>
        </div>
        @endforeach
</section>

<!-- ══════════ ABOUT SECTION ══════════ -->
<section class="bg-white" style="padding: clamp(3rem,8vw,6rem) 0;">
    <div class="max-w-[1200px] mx-auto px-4 sm:px-6 lg:px-8">
        <div class="max-w-[900px] mx-auto">

            <div class="lang-content active" style="direction:rtl;" data-lang="ku-sorani">
                <h2 class="font-bold text-[#2d2d3a] mb-8 text-center" style="font-size:clamp(1.7rem,4.5vw,2.4rem);">👥 دەربارەی ئێمە</h2>
                <div class="relative rounded-[18px] overflow-hidden reveal"
                     style="background: linear-gradient(145deg, #f5f7ff 0%, #eef0f8 100%); padding: clamp(1.5rem,5vw,3rem); box-shadow: 0 8px 32px rgba(102,126,234,0.15);">
                    <div class="absolute top-0 left-0 right-0 h-1" style="background: linear-gradient(90deg, #667eea, #764ba2);"></div>
                    <p class="text-[#6b6b80] leading-[1.9] mb-6 text-justify" style="font-size:clamp(0.95rem,2.5vw,1.15rem);">ئێمە وەک تیمی BioNova کە سێ خوێندکاری زانکۆین خولیای هاوبەشمان بۆ فێربوون و تەکنەلۆژیا هەیە. لە کاتی خوێندندا تێبینیمان کرد کە دۆزینەوەی سەرچاوەی متمانەپێکراو و باش و ڕێکخراو لە یەک شوێن زۆر ئەستەمە. ئەوەش ئیلهامبەخش بوو بۆ دروستکردنی ئەم کتێبخانە ئەلیکترۆنییە.</p>
                    <div class="rounded-[10px] p-6 transition-all duration-300 hover:shadow-md" style="background:rgba(102,126,234,0.09); border:1px solid rgba(102,126,234,0.16);">
                        <h3 class="text-[#667eea] mb-3 text-center font-bold" style="font-size:clamp(1.1rem,3vw,1.35rem);">ئامانجی ئێمە</h3>
                        <p class="text-[#6b6b80] mb-0 italic text-justify" style="font-size:clamp(0.95rem,2.5vw,1.15rem); font-style: italic;"> &ldquo;کردنەوەی دەرگای زانیاری بۆ هەموو خوێندکارێک و دابینکردنی یەک پلاتفۆرم کە هەموو پێداویستییەکانی ئەکادیمی دابین بکات بە شێوەیەکی مۆدێرن و سەردەمیانە &rdquo;</p>
                    </div>
                </div>
            </div>

            <div class="lang-content" style="direction:ltr;" data-lang="en">
                <h2 class="font-bold text-[#2d2d3a] mb-8 text-center" style="font-size:clamp(1.7rem,4.5vw,2.4rem);">👥 About Us</h2>
                <div class="relative rounded-[18px] overflow-hidden reveal" style="background: linear-gradient(145deg, #f5f7ff 0%, #eef0f8 100%); padding: clamp(1.5rem,5vw,3rem); box-shadow: 0 8px 32px rgba(102,126,234,0.15);">
                    <div class="absolute top-0 left-0 right-0 h-1" style="background: linear-gradient(90deg, #667eea, #764ba2);"></div>
                    <p class="text-[#6b6b80] leading-[1.9] mb-6 text-justify" style="font-size:clamp(0.95rem,2.5vw,1.15rem);">We are three university students as BioNova team who share a passion for learning and technology. During our studies, we noticed that finding reliable, quality, and well-organized resources in one place was very difficult. This inspired us to create this electronic library.</p>
                    <div class="rounded-[10px] p-6" style="background:rgba(102,126,234,0.09); border:1px solid rgba(102,126,234,0.16);">
                        <h3 class="text-[#667eea] mb-3 text-center font-bold" style="font-size:clamp(1.1rem,3vw,1.35rem);">Our Mission</h3>
                        <p class="text-[#6b6b80] mb-0 italic text-justify" style="font-size:clamp(0.95rem,2.5vw,1.15rem); font-style: italic;"> &ldquo; Opening the door to information for every student and providing a single platform that meets all academic needs in a modern and contemporary manner &ldquo;</p>
                    </div>
                </div>
            </div>

            <div class="lang-content" style="direction:rtl;" data-lang="ar">
                <h2 class="font-bold text-[#2d2d3a] mb-8 text-center" style="font-size:clamp(1.7rem,4.5vw,2.4rem);">👥 معلومات عنا</h2>
                <div class="relative rounded-[18px] overflow-hidden reveal" style="background: linear-gradient(145deg, #f5f7ff 0%, #eef0f8 100%); padding: clamp(1.5rem,5vw,3rem); box-shadow: 0 8px 32px rgba(102,126,234,0.15);">
                    <div class="absolute top-0 left-0 right-0 h-1" style="background: linear-gradient(90deg, #667eea, #764ba2);"></div>
                    <p class="text-[#6b6b80] leading-[1.9] mb-6 text-justify" style="font-size:clamp(0.95rem,2.5vw,1.15rem);">بصفتنا فريق BioNova المكون من ثلاثة طلاب جامعيين، نتشارك شغفًا بالتعلم والتكنولوجيا. خلال دراستنا، لاحظنا صعوبة بالغة في إيجاد مصادر موثوقة ومنظمة جيدًا في مكان واحد. هذا ما دفعني لإنشاء هذه المكتبة الإلكترونية.</p>
                    <div class="rounded-[10px] p-6" style="background:rgba(102,126,234,0.09); border:1px solid rgba(102,126,234,0.16);">
                        <h3 class="text-[#667eea] mb-3 text-center font-bold">مهمتنا </h3>
                        <p class="text-[#6b6b80] mb-0">"فتح الباب أمام المعلومات لكل طالب وتوفير منصة واحدة تلبي جميع الاحتياجات الأكاديمية بطريقة حديثة ومعاصرة &ldquo;</p>
                    </div>
                </div>
            </div>

            <div class="lang-content" style="direction:rtl;" data-lang="ku-badini">
                <h2 class="font-bold text-[#2d2d3a] mb-8 text-center" style="font-size:clamp(1.7rem,4.5vw,2.4rem);">👥 دەربارا مە</h2>
                <div class="relative rounded-[18px] overflow-hidden reveal" style="background: linear-gradient(145deg, #f5f7ff 0%, #eef0f8 100%); padding: clamp(1.5rem,5vw,3rem); box-shadow: 0 8px 32px rgba(102,126,234,0.15);">
                    <div class="absolute top-0 left-0 right-0 h-1" style="background: linear-gradient(90deg, #667eea, #764ba2);"></div>
                    <p class="text-[#6b6b80] leading-[1.9] mb-6" style="font-size:clamp(0.95rem,2.5vw,1.15rem);">وەک تیما BioNova یا ژ سێ خوێندکارێن زانینگەهێ پێک تێ، ئەم ژ بۆ فێربوون و تەکنەلۆژیێ هەوەسەکا هەڤپار پارڤە دکین. د دەما خوێندنا خوە دە، مە دیت کو دیتنا چاوکانیێن پێباوەر و باش-رێخستینکری ل یەک جهێکی پیر دژوارە. ئەڤ یەک ئیلھام دا مە کو ئەم ڤێ پِرتوکخانەیا ئەلیکترۆنیک بیاڤرینین، کو پلاتفۆرمەکا هێسان و ئاسانە کو خوێندکار و مامۆستا دکارن بی قاسی کو پێکان ئاگەهداریێ ببینن.</p>
                    <div class="rounded-[10px] p-6" style="background:rgba(102,126,234,0.09); border:1px solid rgba(102,126,234,0.16);">
                        <h3 class="text-[#667eea] mb-3 text-center font-bold">ئارمانجا مە </h3>
                        <p class="text-[#6b6b80] mb-0">"ڤەکرنا دەریێ ئاگەهداریێ ژ بۆ هەموو خوێندکاران و پەیداکرنا پلاتفۆرمەک کو بی ئاڤایەکی مودێرن هەموو پێداڤیێن ئەکادیمی پێک تینە &ldquo;</p>
                    </div>
                </div>
            </div>

            <div class="lang-content" style="direction:ltr;" data-lang="ku-badini-lat">
                <h2 class="font-bold text-[#2d2d3a] mb-8 text-center" style="font-size:clamp(1.7rem,4.5vw,2.4rem);">👥 Derbarê Me</h2>
                <div class="relative rounded-[18px] overflow-hidden reveal" style="background: linear-gradient(145deg, #f5f7ff 0%, #eef0f8 100%); padding: clamp(1.5rem,5vw,3rem); box-shadow: 0 8px 32px rgba(102,126,234,0.15);">
                    <div class="absolute top-0 left-0 right-0 h-1" style="background: linear-gradient(90deg, #667eea, #764ba2);"></div>
                    <p class="text-[#6b6b80] leading-[1.9] mb-6" style="font-size:clamp(0.95rem,2.5vw,1.15rem);">Wek tîma BioNova ya ji sê xwendekarên zanîngehê pêk tê, em ji bo fêrbûn û teknolojiyê heweseke hevpar parve dikin. Di dema xwendina xwe de, me dît ku dîtina çavkaniyên pêbawer û baş-rêxistinkirî li yek cîhekî pir dijwar e. Ev yek îlham da min ku ez vê pirtûkxaneya elektronîkî biafirînim.</p>
                    <div class="rounded-[10px] p-6" style="background:rgba(102,126,234,0.09); border:1px solid rgba(102,126,234,0.16);">
                        <h3 class="text-[#667eea] mb-3 text-center font-bold">Mîsyona Me </h3>
                        <p class="text-[#6b6b80] mb-0">"Deriyê agahdariyê ji bo her xwendekarekî vedike û platformek yekane peyda dike ku hemî hewcedariyên akademîk bi awayekî nûjen û hemdem pêk tîne &ldquo;</p>
                    </div>
                </div>
            </div>

            <div class="lang-content" style="direction:rtl;" data-lang="ku-hawrami">
                <h2 class="font-bold text-[#2d2d3a] mb-8 text-center" style="font-size:clamp(1.7rem,4.5vw,2.4rem);">👥 دەربارەی ئێمە</h2>
                <div class="relative rounded-[18px] overflow-hidden reveal" style="background: linear-gradient(145deg, #f5f7ff 0%, #eef0f8 100%); padding: clamp(1.5rem,5vw,3rem); box-shadow: 0 8px 32px rgba(102,126,234,0.15);">
                    <div class="absolute top-0 left-0 right-0 h-1" style="background: linear-gradient(90deg, #667eea, #764ba2);"></div>
                    <p class="text-[#6b6b80] leading-[1.9] mb-6" style="font-size:clamp(0.95rem,2.5vw,1.15rem);">ئێمە وەک تیمی BioNova سێ خوێندکاری زانکۆین کە خولیای هاوبەشمان بۆ فێربوون و تەکنەلۆژی هەیە.</p>
                    <div class="rounded-[10px] p-6" style="background:rgba(102,126,234,0.09); border:1px solid rgba(102,126,234,0.16);">
                        <h3 class="text-[#667eea] mb-3 text-center font-bold">ئامانجمان </h3>
                        <p class="text-[#6b6b80] mb-0">"کردنەوەی دەرگای زانیاری بۆ هەموو خوێندکارێک و دابینکردنی یەک پلاتفۆرم کە هەموو پێداویستییەکانی ئەکادیمی دابین بکات بە شێوەیەکی مۆدێرن و سەردەمیانە &ldquo;</p>
                    </div>
                </div>
            </div>

            <div class="lang-content" style="direction:rtl;" data-lang="fa">
                <h2 class="font-bold text-[#2d2d3a] mb-8 text-center" style="font-size:clamp(1.7rem,4.5vw,2.4rem);">👥 درباره ما</h2>
                <div class="relative rounded-[18px] overflow-hidden reveal" style="background: linear-gradient(145deg, #f5f7ff 0%, #eef0f8 100%); padding: clamp(1.5rem,5vw,3rem); box-shadow: 0 8px 32px rgba(102,126,234,0.15);">
                    <div class="absolute top-0 left-0 right-0 h-1" style="background: linear-gradient(90deg, #667eea, #764ba2);"></div>
                    <p class="text-[#6b6b80] leading-[1.9] mb-6" style="font-size:clamp(0.95rem,2.5vw,1.15rem);">به عنوان تیم BioNova متشکل از سه دانشجوی دانشگاه، ما اشتیاق مشترکی به یادگیری و فناوری داریم. در طول تحصیل، متوجه شدیم که یافتن منابع قابل اعتماد و منظم در یک مکان بسیار دشوار است. این موضوع الهام‌بخش من برای ایجاد این کتابخانه الکترونیکی شد.</p>
                    <div class="rounded-[10px] p-6" style="background:rgba(102,126,234,0.09); border:1px solid rgba(102,126,234,0.16);">
                        <h3 class="text-[#667eea] mb-3 text-center font-bold">مأموریت ما </h3>
                        <p class="text-[#6b6b80] mb-0">"گشودن دریچه‌ای به سوی اطلاعات برای هر دانش‌آموز و فراهم کردن بستری واحد که تمام نیازهای تحصیلی را به شیوه‌ای مدرن و امروزی برآورده می‌کند &ldquo;</p>
                    </div>
                </div>
            </div>

            <div class="lang-content" style="direction:ltr;" data-lang="tr">
                <h2 class="font-bold text-[#2d2d3a] mb-8 text-center" style="font-size:clamp(1.7rem,4.5vw,2.4rem);">👥 Hakkımızda</h2>
                <div class="relative rounded-[18px] overflow-hidden reveal" style="background: linear-gradient(145deg, #f5f7ff 0%, #eef0f8 100%); padding: clamp(1.5rem,5vw,3rem); box-shadow: 0 8px 32px rgba(102,126,234,0.15);">
                    <div class="absolute top-0 left-0 right-0 h-1" style="background: linear-gradient(90deg, #667eea, #764ba2);"></div>
                    <p class="text-[#6b6b80] leading-[1.9] mb-6" style="font-size:clamp(0.95rem,2.5vw,1.15rem);">Üç üniversite öğrencisinden oluşan BioNova ekibi olarak, öğrenmeye ve teknolojiye olan tutkumuzu paylaşıyoruz. Öğrenimimiz sırasında, güvenilir ve iyi organize edilmiş kaynakları tek bir yerde bulmanın çok zor olduğunu fark ettik. Bu da beni bu e-kütüphaneyi oluşturmaya teşvik etti.</p>
                    <div class="rounded-[10px] p-6" style="background:rgba(102,126,234,0.09); border:1px solid rgba(102,126,234,0.16);">
                        <h3 class="text-[#667eea] mb-3 text-center font-bold">Misyonumuz </h3>
                        <p class="text-[#6b6b80] mb-0">"Her öğrenci için bilgiye erişim kapısını açmak ve tüm akademik ihtiyaçları modern ve çağdaş bir şekilde karşılayan tek bir platform sağlamak &ldquo;</p>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>
<!-- ══════════ PRE-FOOTER BANNER ══════════ -->
<section style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: clamp(1.2rem,3vw,1.8rem) 0;">
    <div class="max-w-[1200px] mx-auto px-4 text-center text-white">
        <p style="font-size:clamp(0.88rem,2.2vw,1.05rem); opacity:0.92; margin:0; line-height:1.7;">
            <span class="lang-content active" data-lang="ku-sorani">ئەم ماڵپەڕە وەک ناوەندێکی گەشەسەندوو، لە لایەن تیمی BioNova بەردەوام چاودێری و نوێ دەکرێتەوە.</span>
            <span class="lang-content" data-lang="ku-badini"> ئەڤ ماڵپەڕە وەک سەنتەرەکێ گەشەسەندی، ژ لایێ تیما BioNova ڤە بەردەوام چاڤدێری لێ دهێتە کرن و دهێتە نویژەنکرن. </span>
            <span class="lang-content" data-lang="ku-badini-lat">Ev malpera wek navendek geşesendî, ji aliyê tîma BioNova ve berdewam tê çavdêrîkirin û nûkirin.</span>
            <span class="lang-content" data-lang="ku-hawrami">ئەم ماڵپەڕە وەک ناوەندێکی گەشەسەندوو، لە لایەن تیمی BioNova بەردەوام چاودێری و نوێ دەکرێتەوە.</span>
            <span class="lang-content" data-lang="en">This website, as a growing hub, is continuously monitored and updated by the BioNova team.</span>
            <span class="lang-content" data-lang="ar">هذا الموقع، بوصفه مركزًا في تطور مستمر، يخضع للمراقبة والتحديث المستمر من قِبَل فريق BioNova.</span>
            <span class="lang-content" data-lang="fa">این وب‌سایت به عنوان یک مرکز در حال رشد، به‌طور مداوم توسط تیم BioNova نظارت و به‌روزرسانی می‌شود.</span>
            <span class="lang-content" data-lang="tr">Bu web sitesi, gelişen bir merkez olarak BioNova ekibi tarafından sürekli izlenmekte ve güncellenmektedir.</span>
        </p>
    </div>
</section>
<!-- ══════════ FOOTER ══════════ -->
<footer class="text-center" style="background: linear-gradient(135deg, #1e1e2e 0%, #2d2b4e 100%); color: rgba(255,255,255,0.82); padding: clamp(1.5rem,4vw,2.2rem) 0;">
    <div class="max-w-[1200px] mx-auto px-4">
        <div class="lang-content active" style="direction:rtl;" data-lang="ku-sorani"><p style="font-size:clamp(0.85rem,2vw,0.95rem);">کتێبخانەی ئەلیکترۆنی کۆلێژی زانست - زانکۆی ڕاپەڕین &nbsp;|&nbsp; هەموو مافەکان پارێزراون &copy; <span class="footer-year-ar"></span></p></div>
        <div class="lang-content" style="direction:rtl;" data-lang="ku-badini"><p style="font-size:clamp(0.85rem,2vw,0.95rem);">کتێبخانەیا ئەلیکترۆنیکی یا کۆلێژا زانستێ - زانینگەها ڕاپەرین &nbsp;|&nbsp; هەموو ماف پاراستی نە &copy; <span class="footer-year-ar"></span></p></div>
        <div class="lang-content" style="direction:ltr;" data-lang="ku-badini-lat"><p style="font-size:clamp(0.85rem,2vw,0.95rem);">Kitêbxaneya Elektronîkî ya Kolêja Zanistê - Zanîngeha Raperin &nbsp;|&nbsp; Hemû maf parastî ne &copy; <span class="footer-year-en"></span></p></div>
        <div class="lang-content" style="direction:rtl;" data-lang="ku-hawrami"><p style="font-size:clamp(0.85rem,2vw,0.95rem);">کتێبخانەی ئەلیکترۆنی کۆلێژی زانست - زانکۆی ڕاپەڕین &nbsp;|&nbsp; هەموو مافەکان پارێزراون &copy; <span class="footer-year-ar"></span></p></div>
        <div class="lang-content" style="direction:ltr;" data-lang="en"><p style="font-size:clamp(0.85rem,2vw,0.95rem);">College of Science Electronic Library - Raparin University &nbsp;|&nbsp; All rights reserved &copy; <span class="footer-year-en"></span></p></div>
        <div class="lang-content" style="direction:rtl;" data-lang="ar"><p style="font-size:clamp(0.85rem,2vw,0.95rem);">المكتبة الإلكترونية لكلية العلوم - جامعة رابەرين &nbsp;|&nbsp; جميع الحقوق محفوظة &copy; <span class="footer-year-ar"></span></p></div>
        <div class="lang-content" style="direction:rtl;" data-lang="fa"><p style="font-size:clamp(0.85rem,2vw,0.95rem);">کتابخانه الکترونیکی دانشکده علوم - دانشگاه راپەرین &nbsp;|&nbsp; تمامی حقوق محفوظ است &copy; <span class="footer-year-ar"></span></p></div>
        <div class="lang-content" style="direction:ltr;" data-lang="tr"><p style="font-size:clamp(0.85rem,2vw,0.95rem);">Fen Fakültesi Elektronik Kütüphanesi - Raparin Üniversitesi &nbsp;|&nbsp; Tüm hakları saklıdır &copy; <span class="footer-year-en"></span></p></div>

        <div class="lang-content active" style="direction:rtl;" data-lang="ku-sorani"><p style="font-size:clamp(0.85rem,2vw,0.95rem);">🎓 <a href="https://uor.edu.krd" target="_blank" rel="noopener" style="color:#9bb1ff; text-decoration:underline; text-underline-offset:3px;">ماڵپەڕی زانکۆی ڕاپەڕین</a> &nbsp;|&nbsp; uor.edu.krd</p></div>
        <div class="lang-content" style="direction:rtl;" data-lang="ku-badini"><p style="font-size:clamp(0.85rem,2vw,0.95rem);">🎓 <a href="https://uor.edu.krd" target="_blank" rel="noopener" style="color:#9bb1ff; text-decoration:underline; text-underline-offset:3px;">ماڵپەڕا زانینگەها ڕاپەرین</a> &nbsp;|&nbsp; uor.edu.krd</p></div>
        <div class="lang-content" style="direction:ltr;" data-lang="ku-badini-lat"><p style="font-size:clamp(0.85rem,2vw,0.95rem);">🎓 <a href="https://uor.edu.krd" target="_blank" rel="noopener" style="color:#9bb1ff; text-decoration:underline; text-underline-offset:3px;">Malpera Zanîngeha Raperîn</a> &nbsp;|&nbsp; uor.edu.krd</p></div>
        <div class="lang-content" style="direction:rtl;" data-lang="ku-hawrami"><p style="font-size:clamp(0.85rem,2vw,0.95rem);">🎓 <a href="https://uor.edu.krd" target="_blank" rel="noopener" style="color:#9bb1ff; text-decoration:underline; text-underline-offset:3px;">ماڵپەڕی زانکۆی ڕاپەڕین</a> &nbsp;|&nbsp; uor.edu.krd</p></div>
        <div class="lang-content" style="direction:ltr;" data-lang="en"><p style="font-size:clamp(0.85rem,2vw,0.95rem);">🎓 <a href="https://uor.edu.krd" target="_blank" rel="noopener" style="color:#9bb1ff; text-decoration:underline; text-underline-offset:3px;">University of Raparin Website</a> &nbsp;|&nbsp; uor.edu.krd</p></div>
        <div class="lang-content" style="direction:rtl;" data-lang="ar"><p style="font-size:clamp(0.85rem,2vw,0.95rem);">🎓 <a href="https://uor.edu.krd" target="_blank" rel="noopener" style="color:#9bb1ff; text-decoration:underline; text-underline-offset:3px;">الموقع الرسمي لجامعة رابەرين</a> &nbsp;|&nbsp; uor.edu.krd</p></div>
        <div class="lang-content" style="direction:rtl;" data-lang="fa"><p style="font-size:clamp(0.85rem,2vw,0.95rem);">🎓 <a href="https://uor.edu.krd" target="_blank" rel="noopener" style="color:#9bb1ff; text-decoration:underline; text-underline-offset:3px;">وب‌سایت دانشگاه راپەرین</a> &nbsp;|&nbsp; uor.edu.krd</p></div>
        <div class="lang-content" style="direction:ltr;" data-lang="tr"><p style="font-size:clamp(0.85rem,2vw,0.95rem);">🎓 <a href="https://uor.edu.krd" target="_blank" rel="noopener" style="color:#9bb1ff; text-decoration:underline; text-underline-offset:3px;">Raparin Üniversitesi Web Sitesi</a> &nbsp;|&nbsp; uor.edu.krd</p></div>

        <div class="mt-3 flex items-center justify-center gap-3 opacity-75 hover:opacity-100 transition-opacity duration-300">
            <span style="font-size: 1rem;">👁</span>
            <div class="flex items-center gap-3 no-underline rounded-full border border-white/20 px-4 py-1.5"
            style="background:rgba(255,255,255,0.12); font-size: 1rem; color:rgba(255,255,255,0.85);">
        
            <img src="https://mahmodfaqe.goatcounter.com/counter/TOTAL.svg" 
                  alt="visitor count" 
                  class="h-[40px] w-auto align-middle" 
                  loading="lazy" decoding="async">
             
                <span id="visitor-label">بەکارهێنەری کتێبخانە</span>
            </div>
        </div>
    </div>
</footer>

<!-- Scroll To Top -->
<button id="scrollTopBtn" title="بگەڕێوە سەرەوە" aria-label="بگەڕێوە سەرەوە">
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

// Language system
var VISITOR_LABELS = {
    'ku-sorani':'بەکارهێنەری کتێبخانە','ku-badini':'سەردانکار',
    'ku-badini-lat':'Serdankar','ku-hawrami':'سەردانکەر',
    'en':'Visitors','ar':'زائر','fa':'بازدیدکننده','tr':'Ziyaretçi'
};
function updateVisitorLabel(lang){
    var el = document.getElementById('visitor-label');
    if(el) el.textContent = VISITOR_LABELS[lang]||'Visitors';
}
var currentLang = sessionStorage.getItem('selectedLanguage')||'ku-sorani';
var KU_DIALECTS=['ku-sorani','ku-badini','ku-badini-lat','ku-hawrami'];
var LTR_LANGS=['en','tr','ku-badini-lat'];
applyLanguage(currentLang);
restoreButtons(currentLang);

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

function selectDialect(lang,btn){
    closeKuDropdown();
    document.getElementById('kuMainBtn').classList.add('active');
    document.querySelectorAll('.lang-btn').forEach(function(b){ b.classList.remove('active'); });
    document.querySelectorAll('.ku-dialect-btn').forEach(function(b){ b.classList.remove('active'); });
    btn.classList.add('active');
    applyLanguage(lang);
}
function switchLang(lang){
    closeKuDropdown();
    document.getElementById('kuMainBtn').classList.remove('active');
    document.querySelectorAll('.ku-dialect-btn').forEach(function(b){ b.classList.remove('active'); });
    document.querySelectorAll('.lang-btn').forEach(function(b){
        b.classList.toggle('active',b.getAttribute('data-lang')===lang);
    });
    applyLanguage(lang);
}
function applyLanguage(lang){
    currentLang=lang;
    sessionStorage.setItem('selectedLanguage',lang);
    document.querySelectorAll('.lang-content.active').forEach(function(el){ el.style.opacity='0'; });
    setTimeout(function(){
        document.querySelectorAll('.lang-content').forEach(function(el){
            el.classList.remove('active');
            el.style.opacity='0';
        });
        document.querySelectorAll('[data-lang="'+lang+'"].lang-content').forEach(function(el){
            el.classList.add('active');
            requestAnimationFrame(function(){ el.style.opacity='1'; });
        });
        triggerReveal();
    },180);
    document.body.style.direction=LTR_LANGS.indexOf(lang)>-1?'ltr':'rtl';
    updateVisitorLabel(lang);
}
function restoreButtons(lang){
    if(KU_DIALECTS.indexOf(lang)>-1){
        document.getElementById('kuMainBtn').classList.add('active');
        document.querySelectorAll('.ku-dialect-btn').forEach(function(b){
            b.classList.toggle('active',b.getAttribute('data-lang')===lang);
        });
    } else {
        document.querySelectorAll('.lang-btn').forEach(function(b){
            b.classList.toggle('active',b.getAttribute('data-lang')===lang);
        });
    }
}

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
