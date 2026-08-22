@use('App\Support\Locale')
@extends('layouts.base')

@section('title', __('books.title').' — '.__('messages.site_title'))
@section('description', __('books.intro'))

@section('content')
<div class="max-w-[1200px] mx-auto px-4" style="padding: clamp(2rem,5vw,3.5rem) 1rem;">

    <nav class="mb-4 flex items-center gap-2 flex-wrap" style="font-size:0.9rem;" aria-label="{{ __('books.breadcrumb') }}">
        <a href="{{ Locale::url() }}" class="text-[#667eea] no-underline hover:underline">{{ __('messages.library_name') }}</a>
        <span class="text-[#b5b5c8]">/</span>
        @if ($selected)
            <a href="{{ Locale::booksUrl() }}" class="text-[#667eea] no-underline hover:underline">{{ __('books.title') }}</a>
            <span class="text-[#b5b5c8]">/</span>
            <span class="text-[#6b6b80]">{{ $selected->localName() }}</span>
        @else
            <span class="text-[#6b6b80]">{{ __('books.title') }}</span>
        @endif
    </nav>

    @if ($selected)
        <a href="{{ Locale::booksUrl() }}" class="inline-block text-[#667eea] no-underline mb-3 hover:underline" style="font-size:0.92rem;">
            <span aria-hidden="true">{{ Locale::isLtr() ? '←' : '→' }}</span> {{ __('books.back_to_subjects') }}
        </a>
        <h1 class="font-bold text-[#2d2d3a] mb-3" style="font-size:clamp(1.6rem,4.5vw,2.2rem);">{{ $selected->localName() }}</h1>
    @else
        <h1 class="font-bold text-[#2d2d3a] mb-3" style="font-size:clamp(1.6rem,4.5vw,2.2rem);">{{ __('books.title') }}</h1>
        <p class="text-[#6b6b80] mb-8" style="font-size:clamp(0.95rem,2.4vw,1.08rem);">{{ __('books.intro') }}</p>
    @endif

    <form method="GET" action="{{ url()->current() }}" class="mb-8"
          style="display:flex; gap:0.7rem; flex-wrap:wrap; align-items:flex-end;">
        @if ($selected)
            <input type="hidden" name="category" value="{{ $selected->id }}">
        @endif
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
        @if ($search !== null || $selected)
            <a href="{{ Locale::booksUrl() }}" class="text-[#6b6b80] no-underline hover:underline self-center" style="font-size:0.88rem;">
                {{ __('books.clear') }}
            </a>
        @endif
    </form>

    @if ($languages->count() > 1)
        @php
            $base = collect(['q' => $search, 'category' => $selected?->id])->filter()->all();
        @endphp
        <nav class="lang-chips" aria-label="{{ __('books.language_label') }}">
            <a href="{{ url()->current() }}{{ $base ? '?'.http_build_query($base) : '' }}"
               class="lang-chip{{ $language === null ? ' is-active' : '' }}">
                {{ __('books.all_languages') }}
            </a>
            @foreach ($languages as $name => $total)
                <a href="{{ url()->current() }}?{{ http_build_query($base + ['language' => $name]) }}"
                   class="lang-chip{{ $language === $name ? ' is-active' : '' }}">
                    {{ $name }} <span class="lang-count">{{ $total }}</span>
                </a>
            @endforeach
        </nav>
    @endif

    @if (! $browsing && $books->total() > 0)
        <p class="text-[#6b6b80] mb-5" style="font-size:0.92rem;" aria-live="polite">
            {{ trans_choice('books.results', $books->total(), ['count' => $books->total()]) }}
        </p>
    @endif

    @if ($browsing)
        <div class="grid gap-4" style="grid-template-columns: repeat(auto-fill, minmax(min(230px,100%),1fr));">
            @foreach ($categories as $shelf)
                <a href="{{ url()->current() }}?category={{ $shelf->id }}"
                   class="subject-card bg-white/85 backdrop-blur-md border border-white/70 rounded-[16px] no-underline flex flex-col justify-between"
                   style="padding:1.3rem; box-shadow:0 4px 16px rgba(102,126,234,0.10);">
                    <span class="font-bold text-[#2d2d3a]" style="font-size:1.02rem; line-height:1.6;">{{ $shelf->localName() }}</span>
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

            <article class="book-card bg-white/85 backdrop-blur-md border border-white/70 rounded-[16px] flex flex-col justify-between">
                <div>
                    <h2 class="book-title font-bold text-[#2d2d3a] mb-1">{{ $book->title }}</h2>
                    @if ($book->author)
                        <p class="text-[#6b6b80] mb-1" style="font-size:0.88rem;">{{ $book->author }}</p>
                    @endif
                    <p class="text-[#8a8aa0] mb-3" style="font-size:0.8rem;">
                        {{ collect([
                            $book->year,
                            $book->language,
                            // Redundant once the whole page is one subject.
                            $selected ? null : $book->category?->localName(),
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

    @if (! $browsing)
        @include('partials.pagination', ['paginator' => $books])
    @endif

</div>
@endsection
