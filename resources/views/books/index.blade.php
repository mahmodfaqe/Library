@use('App\Support\Asset')
@use('App\Support\Locale')
<!DOCTYPE html>
<html lang="{{ Locale::htmlLang() }}" dir="{{ Locale::dir() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('books.title') }} — {{ __('messages.site_title') }}</title>
    <meta name="description" content="{{ __('books.intro') }}">
    <meta name="theme-color" content="#667eea">
    <link rel="icon" href="{{ Asset::versioned('favicon.ico') }}" sizes="16x16 32x32 48x48">
    <link rel="icon" type="image/png" href="{{ Asset::versioned('favicon-96.png') }}" sizes="96x96">
    <link rel="apple-touch-icon" href="{{ Asset::versioned('apple-touch-icon.png') }}">
    <link rel="canonical" href="{{ url()->current() }}">
    <link rel="preload" href="{{ asset('fonts/Rabar_015.woff2') }}" as="font" type="font/woff2" crossorigin>
    <style>
        @font-face {
            font-family: 'Rabar';
            src: url('{{ asset('fonts/Rabar_015.woff2') }}') format('woff2');
            font-weight: normal; font-style: normal; font-display: swap;
        }
    </style>
    @vite(['resources/css/app.css'])
</head>
<body class="bg-[#f8f9ff] text-[#2d2d3a]" dir="{{ Locale::dir() }}">

<a class="skip-link" href="#main">{{ __('messages.skip_to_content') }}</a>

<header style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: clamp(1rem,3vw,1.6rem) 0;">
    <div class="max-w-[1200px] mx-auto px-4 flex items-center justify-between gap-4 flex-wrap">
        <a href="{{ Locale::url() }}" class="text-white no-underline font-bold" style="font-size:clamp(0.95rem,2.5vw,1.15rem);">
            {{ __('messages.library_name') }}
        </a>
        <a href="{{ Locale::url() }}" class="text-white/85 no-underline" style="font-size:0.9rem;">
            {{ __('privacy.back') }}
        </a>
    </div>
</header>

<main id="main" class="max-w-[1200px] mx-auto px-4" style="padding: clamp(2rem,5vw,3.5rem) 1rem;">

    @if ($selected)
        <a href="{{ url()->current() }}" class="inline-block text-[#667eea] no-underline mb-3" style="font-size:0.92rem;">
            ← {{ __('books.back_to_subjects') }}
        </a>
        <h1 class="font-bold text-[#2d2d3a] mb-3" style="font-size:clamp(1.6rem,4.5vw,2.2rem);">{{ $selected->name }}</h1>
    @else
        <h1 class="font-bold text-[#2d2d3a] mb-3" style="font-size:clamp(1.6rem,4.5vw,2.2rem);">{{ __('books.title') }}</h1>
        <p class="text-[#6b6b80] mb-8" style="font-size:clamp(0.95rem,2.4vw,1.08rem);">{{ __('books.intro') }}</p>
    @endif

    <form method="GET" action="{{ url()->current() }}" class="mb-8"
          style="display:flex; gap:0.7rem; flex-wrap:wrap; align-items:flex-end;">
        <input type="hidden" name="category" value="{{ $selected?->id }}">
        <div style="flex:1 1 260px;">
            <label for="q" class="block font-semibold mb-1 text-[#4a4a5c]" style="font-size:0.86rem;">{{ __('books.search_label') }}</label>
            <input type="search" id="q" name="q" value="{{ $search }}" placeholder="{{ __('books.search_placeholder') }}"
                   class="w-full rounded-[12px] px-4 py-3"
                   style="border:1px solid #d5d9ee; font-size:0.95rem; font-family:inherit; text-align:start;">
        </div>
        <button type="submit" class="section-btn font-semibold text-white rounded-full cursor-pointer"
                style="background:linear-gradient(135deg,#667eea 0%,#764ba2 100%); padding:0.78rem 1.9rem; font-size:0.95rem; border:none; font-family:inherit;">
            {{ __('books.search') }}
        </button>
    </form>

    @unless ($browsing)
        <p class="text-[#6b6b80] mb-5" style="font-size:0.92rem;" aria-live="polite">
            {{ trans_choice('books.results', $books->total(), ['count' => $books->total()]) }}
        </p>
    @endunless


    @if ($browsing)
        <div class="grid gap-4" style="grid-template-columns: repeat(auto-fill, minmax(min(230px,100%),1fr));">
            @foreach ($categories as $shelf)
                <a href="{{ url()->current() }}?category={{ $shelf->id }}"
                   class="subject-card bg-white/85 backdrop-blur-md border border-white/70 rounded-[16px] no-underline flex flex-col justify-between"
                   style="padding:1.3rem; box-shadow:0 4px 16px rgba(102,126,234,0.10);">
                    <span class="font-bold text-[#2d2d3a]" style="font-size:1.02rem; line-height:1.6;">{{ $shelf->name }}</span>
                    <span class="text-[#6b6b80]" style="font-size:0.85rem; margin-top:0.6rem;">
                        {{ trans_choice('books.results', $shelf->books_count, ['count' => $shelf->books_count]) }}
                    </span>
                </a>
            @endforeach
        </div>
    @else
    @forelse ($books as $book)
        @if ($loop->first)
            <div class="grid gap-4" style="grid-template-columns: repeat(auto-fill, minmax(min(260px,100%),1fr));">
        @endif

        <article class="bg-white/85 backdrop-blur-md border border-white/70 rounded-[16px] flex flex-col justify-between"
                 style="padding:1.2rem; box-shadow:0 4px 16px rgba(102,126,234,0.10);">
            <div>
                <h2 class="font-bold text-[#2d2d3a] mb-1" style="font-size:1.02rem; line-height:1.55;">{{ $book->title }}</h2>
                @if ($book->author)
                    <p class="text-[#6b6b80] mb-1" style="font-size:0.88rem;">{{ $book->author }}</p>
                @endif
                <p class="text-[#8a8aa0] mb-3" style="font-size:0.8rem;">
                    {{ collect([
                        $book->year,
                        $book->language,
                        // Redundant once the whole page is one subject.
                        $selected ? null : $book->category?->name,
                    ])->filter()->implode(' · ') }}
                </p>
            </div>
            @if ($book->readUrl())
                <a href="{{ $book->readUrl() }}"
                   @unless ($book->hasFile()) target="_blank" rel="noopener" @endunless
                   class="section-btn inline-block font-semibold text-white rounded-full no-underline text-center"
                   style="background:linear-gradient(135deg,#667eea 0%,#764ba2 100%); padding:0.55rem 1.2rem; font-size:0.86rem;">
                    {{ $book->hasFile() ? __('books.download') : __('books.open') }}
                    @if ($book->humanFileSize())
                        <span style="opacity:0.75; font-size:0.85em;">({{ $book->humanFileSize() }})</span>
                    @endif
                </a>
            @endif
        </article>

        @if ($loop->last)
            </div>
        @endif
    @empty
        <p class="text-center text-[#6b6b80]" style="padding:3rem 0;">{{ __('books.empty') }}</p>
    @endforelse

    @endif

    @if (! $browsing && $books->hasPages())
        <nav class="pagination" style="margin-top:2rem; display:flex; gap:0.4rem; justify-content:center; flex-wrap:wrap;"
             aria-label="{{ __('books.pagination') }}">
            {{ $books->links() }}
        </nav>
    @endif

</main>

<footer class="text-center" style="background: linear-gradient(135deg, #1e1e2e 0%, #2d2b4e 100%); color: rgba(255,255,255,0.82); padding: clamp(1.2rem,3vw,1.8rem) 0;">
    <div class="max-w-[1200px] mx-auto px-4">
        <p style="font-size:clamp(0.85rem,2vw,0.95rem);">{{ __('messages.site_title') }}</p>
    </div>
</footer>

</body>
</html>
