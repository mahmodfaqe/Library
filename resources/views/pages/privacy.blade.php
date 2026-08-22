@use('App\Support\Asset')
@use('App\Support\Locale')
<!DOCTYPE html>
<html lang="{{ Locale::htmlLang() }}" dir="{{ Locale::dir() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('privacy.title') }} — {{ __('messages.site_title') }}</title>
    <meta name="description" content="{{ __('privacy.intro') }}">
    <meta name="robots" content="index, follow">
    <meta name="theme-color" content="#667eea">
    <link rel="icon" href="{{ Asset::versioned('favicon.ico') }}" sizes="16x16 32x32 48x48">
    <link rel="icon" type="image/png" href="{{ Asset::versioned('favicon-96.png') }}" sizes="96x96">
    <link rel="apple-touch-icon" href="{{ Asset::versioned('apple-touch-icon.png') }}">
    <link rel="canonical" href="{{ url('privacy') }}">
    <link rel="preload" href="{{ asset('fonts/Rabar_015.woff2') }}" as="font" type="font/woff2" crossorigin>
    <style>
        @font-face {
            font-family: 'Rabar';
            src: url('{{ asset('fonts/Rabar_015.woff2') }}') format('woff2');
            font-weight: normal;
            font-style: normal;
            font-display: swap;
        }
    </style>
    @vite(['resources/css/app.css'])
</head>
<body class="bg-[#f8f9ff] text-[#2d2d3a]" dir="{{ Locale::dir() }}">

<a class="skip-link" href="#main">{{ __('messages.skip_to_content') }}</a>

<header style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: clamp(1rem,3vw,1.6rem) 0;">
    <div class="max-w-[820px] mx-auto px-4 flex items-center justify-between gap-4">
        <a href="{{ Locale::url() }}" class="text-white no-underline font-bold" style="font-size:clamp(0.95rem,2.5vw,1.15rem);">
            {{ __('messages.library_name') }}
        </a>
        <a href="{{ Locale::url() }}" class="text-white/85 no-underline" style="font-size:0.9rem;">
            {{ __('privacy.back') }}
        </a>
    </div>
</header>

<main id="main" class="max-w-[820px] mx-auto px-4" style="padding: clamp(2rem,6vw,4rem) 1rem;">
    <h1 class="font-bold text-[#2d2d3a] mb-6" style="font-size:clamp(1.6rem,4.5vw,2.2rem);">{{ __('privacy.title') }}</h1>

    <p class="text-[#6b6b80] leading-[1.9] mb-8" style="font-size:clamp(0.95rem,2.5vw,1.1rem);">{{ __('privacy.intro') }}</p>

    @foreach (['collect', 'why', 'retention', 'sharing', 'rights'] as $section)
        <section class="mb-8">
            <h2 class="font-bold text-[#2d2d3a] mb-3" style="font-size:clamp(1.15rem,3vw,1.4rem);">
                {{ __("privacy.$section.heading") }}
            </h2>
            <p class="text-[#6b6b80] leading-[1.9]" style="font-size:clamp(0.92rem,2.4vw,1.05rem);">
                {{ __("privacy.$section.body", ['days' => config('library.feedback_retention_days')]) }}
            </p>
        </section>
    @endforeach

    <p class="text-[#8a8aa0] mt-10" style="font-size:0.88rem;">{{ __('privacy.updated') }}</p>
</main>

<footer class="text-center" style="background: linear-gradient(135deg, #1e1e2e 0%, #2d2b4e 100%); color: rgba(255,255,255,0.82); padding: clamp(1.2rem,3vw,1.8rem) 0;">
    <div class="max-w-[820px] mx-auto px-4">
        <p style="font-size:clamp(0.85rem,2vw,0.95rem);">{{ __('messages.site_title') }}</p>
    </div>
</footer>

</body>
</html>
