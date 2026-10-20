@use('App\Support\Locale')
@props([
    // Where the emblem leads.
    'home',
    // The two lines beside the emblem on a narrow screen.
    'title',
    'subtitle',
    // Each item is ['url' => ..., 'label' => ..., 'active' => bool]; split in
    // two around the emblem, the first half before it.
    'nav',
    // Turns a locale code into the address that switches to it.
    'languageUrl',
])
{{-- After the University of Raparin's own site, by way of GWRC-Web: two
     translucent rows over the page, the menu split around the hanging
     emblem, and a red V beneath. Without script it still works: the language
     list is a <details>, the search icon is a link and the phone menu is
     simply shown. Styled by public/uor-header.css, driven by uor-header.js. --}}
<header class="site-header" id="site-header">
    <div class="topbar">
        <div class="hdr-container topbar__inner">
            <details class="lang">
                <summary>
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                        <circle cx="12" cy="12" r="9"></circle>
                        <path d="M3 12h18M12 3a15 15 0 0 1 0 18a15 15 0 0 1 0-18"></path>
                    </svg>
                    <span class="sr-only">{{ __('messages.language') }}: </span><span>{{ Locale::shortLabel() }}</span>
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true"><path d="m6 9 6 6 6-6"></path></svg>
                </summary>
                <ul class="lang__menu">
                    @foreach (Locale::KURDISH as $dialect => [$label, $native, $font])
                        <li><a href="{{ $languageUrl($dialect) }}" dir="{{ Locale::dir($dialect) }}" style="font-family:{{ $font }};"
                               @if (app()->getLocale() === $dialect) aria-current="true" @endif>{{ $label }}<em dir="auto">{{ $native }}</em></a></li>
                    @endforeach
                    <li class="lang__rule" role="separator"></li>
                    @foreach (Locale::OTHERS as $code => $label)
                        <li><a href="{{ $languageUrl($code) }}" dir="{{ Locale::dir($code) }}"
                               @if (app()->getLocale() === $code) aria-current="true" @endif>{{ $label }}</a></li>
                    @endforeach
                </ul>
            </details>

            <nav class="quicklinks" aria-label="{{ __('messages.university_name') }}">
                {{ $quicklinks }}
            </nav>

            {{ $end ?? '' }}
        </div>
        {{ $search ?? '' }}
    </div>

    <div class="mainbar">
        <div class="hdr-container mainbar__inner">
            <a class="emblem" href="{{ $home }}">
                <img src="{{ asset('file/uor-logo.webp') }}" alt="" width="512" height="523" fetchpriority="high" decoding="async">
                <span class="sr-only">{{ $title }} — {{ $subtitle }}</span>
            </a>
            <span class="mainbar__name" aria-hidden="true"><span>{{ $title }}</span><span class="mainbar__name-gold">{{ $subtitle }}</span></span>
            <nav class="mainnav" aria-label="{{ __('messages.nav.menu') }}">
                @foreach (array_chunk($nav, (int) ceil(count($nav) / 2)) as $group)
                    <ul class="mainnav__group">
                        @foreach ($group as $item)
                            <li class="mainnav__item"><a href="{{ $item['url'] }}" @if ($item['active']) aria-current="page" @endif>{{ $item['label'] }}</a></li>
                        @endforeach
                    </ul>
                @endforeach
            </nav>
            <button class="menu-toggle" type="button" aria-expanded="false" aria-controls="mobile-nav" hidden>
                <span class="menu-toggle__bars" aria-hidden="true"></span><span class="sr-only">{{ __('messages.nav.menu') }}</span>
            </button>
        </div>
        <nav class="mobile-nav" id="mobile-nav" aria-label="{{ __('messages.nav.menu') }}">
            <ul>
                @foreach ($nav as $item)
                    <li><a href="{{ $item['url'] }}" @if ($item['active']) aria-current="page" @endif>{{ $item['label'] }}</a></li>
                @endforeach
                {{ $mobileExtra ?? '' }}
            </ul>
        </nav>
    </div>
</header>
