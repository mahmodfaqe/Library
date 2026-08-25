@use('App\Support\BookLanguage')
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
            <span class="text-[#6b6b80]" dir="auto">{{ $selected->localName() }}</span>
        @else
            <span class="text-[#6b6b80]">{{ __('books.title') }}</span>
        @endif
    </nav>

    @if ($selected)
        <a href="{{ Locale::booksUrl() }}" class="inline-block text-[#667eea] no-underline mb-3 hover:underline" style="font-size:0.92rem;">
            <span aria-hidden="true">{{ Locale::isLtr() ? '←' : '→' }}</span> {{ __('books.back_to_subjects') }}
        </a>
        <h1 class="font-bold text-[#2d2d3a] mb-3" style="font-size:clamp(1.6rem,4.5vw,2.2rem);" dir="auto">{{ $selected->localName() }}</h1>
    @else
        <h1 class="font-bold text-[#2d2d3a] mb-3" style="font-size:clamp(1.6rem,4.5vw,2.2rem);">{{ __('books.title') }}</h1>
        <p class="text-[#6b6b80] mb-8" style="font-size:clamp(0.95rem,2.4vw,1.08rem);">{{ __('books.intro') }}</p>
    @endif

    {{-- The form still submits and the catalogue still renders without any of
         this; the suggestions below only make the answer arrive sooner. --}}
    <form method="GET" action="{{ url()->current() }}" class="search-form" role="search"
          data-suggest="{{ Locale::suggestUrl() }}">
        @if ($selected)
            <input type="hidden" name="category" value="{{ $selected->id }}">
        @endif

        <div class="search-field">
            <label for="q" class="block font-semibold mb-1 text-[#4a4a5c]" style="font-size:0.86rem;">{{ __('books.search_label') }}</label>

            <div class="search-input-wrap">
                <svg class="search-icon" width="18" height="18" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true">
                    <circle cx="11" cy="11" r="7"></circle><path d="m20 20-3.5-3.5"></path>
                </svg>

                <input type="search" id="q" name="q" value="{{ $search }}"
                       placeholder="{{ __('books.search_placeholder') }}" dir="auto"
                       autocomplete="off" role="combobox" aria-expanded="false"
                       aria-controls="search-suggestions" aria-autocomplete="list">

                <span class="search-spinner" aria-hidden="true"></span>
            </div>

            <div id="search-suggestions" class="search-suggestions" role="listbox"
                 aria-label="{{ __('books.suggest.books') }}" hidden></div>
        </div>

        <button type="submit" class="section-btn search-submit font-semibold text-white rounded-full cursor-pointer">
            {{ __('books.search') }}
        </button>

        @if ($search !== null || $selected)
            <a href="{{ Locale::booksUrl() }}" class="search-clear text-[#6b6b80] no-underline hover:underline">
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
                    <bdi>{{ BookLanguage::name($name) }}</bdi> <span class="lang-count">{{ $total }}</span>
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
                    <span class="font-bold text-[#2d2d3a]" style="font-size:1.02rem; line-height:1.6;" dir="auto">{{ $shelf->localName() }}</span>
                    <span class="text-[#6b6b80]" style="font-size:0.85rem; margin-top:0.6rem;">
                        {{ trans_choice('books.results', $shelf->books_count, ['count' => $shelf->books_count]) }}
                    </span>
                </a>
            @endforeach
        </div>
    @else
        @forelse ($books as $book)
            {{-- Books arrive shelved by language, so a heading goes in
                 wherever the shelf changes. When the visitor has already
                 picked one language there is only ever one shelf, and the
                 chips above already say which.

                 The shelf is grouped by what the catalogue stores and headed
                 by what the reader reads: two spellings of one language must
                 not split a shelf in two. --}}
            @php
                $shelf = $book->language ?: '';
                $shelfName = BookLanguage::name($book->language) ?: __('books.other_language');
            @endphp

            @if ($language === null && (! isset($currentShelf) || $currentShelf !== $shelf))
                @unless ($loop->first)
                    </div>
                @endunless
                <h2 class="language-shelf" dir="auto">
                    <bdi>{{ $shelfName }}</bdi>
                </h2>
                <div class="book-grid">
                @php $currentShelf = $shelf; @endphp
            @elseif ($loop->first)
                <div class="book-grid">
            @endif

            <article class="book-card bg-white/85 backdrop-blur-md border border-white/70 rounded-[16px] flex flex-col">
                {{-- The cover leads to the book's own page, like the title
                     beneath it. It is hidden from the keyboard and from screen
                     readers on purpose: the title is the same link, and
                     announcing each book twice would only get in the way. --}}
                @php $cover = $book->coverUrl(); @endphp

                <a class="book-cover" href="{{ Locale::bookUrl($book->id) }}"
                   aria-hidden="true" tabindex="-1">
                    @if ($cover)
                        <img src="{{ $cover }}" alt="" loading="lazy" decoding="async"
                             referrerpolicy="no-referrer">
                    @endif
                    {{-- Shown when there is no cover, and when one fails to load. --}}
                    <span class="book-cover-fallback" aria-hidden="true">{{ $book->category?->icon ?: '📘' }}</span>
                </a>

                <div class="book-body">
                    <div>
                        <h3 class="book-title font-bold text-[#2d2d3a] mb-1">
                            <a href="{{ Locale::bookUrl($book->id) }}"
                               class="no-underline text-inherit hover:underline" dir="auto">{{ $book->title }}</a>
                        </h3>
                        @if ($book->author)
                            <p class="text-[#6b6b80] mb-1" style="font-size:0.88rem;" dir="auto">{{ $book->author }}</p>
                        @endif
                        <p class="text-[#8a8aa0] mb-3" style="font-size:0.8rem;">
                            @foreach (collect([
                                $book->year,
                                // Redundant once the shelf heading says it.
                                $language === null ? null : BookLanguage::name($book->language),
                                $selected ? null : $book->category?->localName(),
                            ])->filter() as $fact)
                                <bdi>{{ $fact }}</bdi>@unless ($loop->last) <span class="opacity-60">·</span> @endunless
                            @endforeach
                        </p>
                    </div>
                    @if ($book->readUrl())
                        <a href="{{ $book->readUrl() }}"
                           @unless ($book->hasFile()) target="_blank" rel="noopener" @endunless
                           class="section-btn inline-block font-semibold text-white rounded-full no-underline text-center"
                           style="background:linear-gradient(135deg,#667eea 0%,#764ba2 100%); padding:0.55rem 1.2rem; font-size:0.86rem;">
                            {{ $book->hasFile() ? __('books.download') : __('books.open') }}
                            @if ($book->humanFileSize())
                                <bdi style="opacity:0.75; font-size:0.85em;">({{ $book->humanFileSize() }})</bdi>
                            @endif
                        </a>
                    @endif
                </div>
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

@push('scripts')
@php
    $labels = [
        'subjects' => __('books.suggest.subjects'),
        'books' => __('books.suggest.books'),
        'empty' => __('books.suggest.empty'),
        'all' => __('books.suggest.all'),
        'download' => __('books.download'),
        'open' => __('books.open'),
    ];
@endphp
<script>
(function () {
    var form = document.querySelector('.search-form');
    if (!form || !window.fetch || !window.AbortController) return;

    var input = form.querySelector('#q');
    var panel = form.querySelector('#search-suggestions');
    var wrap  = form.querySelector('.search-input-wrap');
    var text  = @js($labels);
    var url   = form.dataset.suggest;
    var category = (form.querySelector('[name=category]') || {}).value || '';

    var timer = null, inflight = null, items = [], active = -1, lastTerm = '';

    function escapeHtml(s) {
        return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) {
            return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c];
        });
    }

    // Show the visitor where their words landed, without trusting either the
    // text or the term as markup.
    function highlight(value, term) {
        var safe = escapeHtml(value);
        var words = term.split(/\s+/).filter(function (w) { return w.length > 1; });
        words.forEach(function (word) {
            var pattern = word.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
            safe = safe.replace(new RegExp('(' + pattern + ')', 'ig'), '<mark>$1</mark>');
        });
        return safe;
    }

    function close() {
        panel.hidden = true;
        panel.innerHTML = '';
        input.setAttribute('aria-expanded', 'false');
        items = []; active = -1;
    }

    function move(step) {
        if (!items.length) return;
        if (active >= 0) items[active].classList.remove('is-active');
        active = (active + step + items.length) % items.length;
        items[active].classList.add('is-active');
        items[active].scrollIntoView({ block: 'nearest' });
    }

    function render(data, term) {
        var html = '';

        if (data.categories.length) {
            html += '<p class="suggest-heading">' + escapeHtml(text.subjects) + '</p>';
            data.categories.forEach(function (c) {
                html += '<a class="suggest-item suggest-subject" role="option" href="' + escapeHtml(c.url) + '">'
                     +  '<span class="suggest-title" dir="auto">' + highlight(c.name, term) + '</span>'
                     +  '<span class="suggest-count">' + escapeHtml(c.count) + '</span></a>';
            });
        }

        if (data.books.length) {
            html += '<p class="suggest-heading">' + escapeHtml(text.books) + '</p>';
            data.books.forEach(function (b) {
                var facts = [b.year, b.language, b.subject].filter(Boolean)
                    .map(function (f) { return '<bdi>' + escapeHtml(f) + '</bdi>'; }).join(' · ');
                html += '<a class="suggest-item" role="option" href="' + escapeHtml(b.url) + '">'
                     +  '<span class="suggest-cover">' + (b.cover
                            ? '<img src="' + escapeHtml(b.cover) + '" alt="" loading="lazy" referrerpolicy="no-referrer">'
                            : '\u{1F4D8}') + '</span>'
                     +  '<span class="suggest-body">'
                     +    '<span class="suggest-title" dir="auto">' + highlight(b.title, term) + '</span>'
                     +    (b.author ? '<span class="suggest-author" dir="auto">' + highlight(b.author, term) + '</span>' : '')
                     +    (facts ? '<span class="suggest-facts">' + facts + '</span>' : '')
                     +  '</span></a>';
            });
        }

        if (!html) {
            html = '<p class="suggest-empty">' + escapeHtml(text.empty) + '</p>';
        } else {
            html += '<button type="submit" class="suggest-all">' + escapeHtml(text.all) + '</button>';
        }

        panel.innerHTML = html;
        panel.hidden = false;
        input.setAttribute('aria-expanded', 'true');
        items = Array.prototype.slice.call(panel.querySelectorAll('.suggest-item'));
        active = -1;
    }

    function ask(term) {
        if (inflight) inflight.abort();
        inflight = new AbortController();
        wrap.classList.add('is-loading');

        fetch(url + '?q=' + encodeURIComponent(term) + (category ? '&category=' + encodeURIComponent(category) : ''),
              { signal: inflight.signal, headers: { 'Accept': 'application/json' } })
            .then(function (r) { return r.ok ? r.json() : null; })
            .then(function (data) {
                wrap.classList.remove('is-loading');
                // A slower earlier request must not overwrite a later answer.
                if (data && input.value.trim() === term) render(data, term);
            })
            .catch(function (e) {
                if (e.name !== 'AbortError') wrap.classList.remove('is-loading');
            });
    }

    input.addEventListener('input', function () {
        var term = input.value.trim();
        clearTimeout(timer);

        if (term.length < 2) { close(); wrap.classList.remove('is-loading'); return; }
        if (term === lastTerm) return;
        lastTerm = term;

        // Long enough that a fast typist makes one request, not eight.
        timer = setTimeout(function () { ask(term); }, 160);
    });

    input.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') { close(); return; }
        if (panel.hidden) return;

        if (e.key === 'ArrowDown') { e.preventDefault(); move(1); }
        else if (e.key === 'ArrowUp') { e.preventDefault(); move(-1); }
        else if (e.key === 'Enter' && active >= 0) { e.preventDefault(); items[active].click(); }
    });

    input.addEventListener('focus', function () {
        if (input.value.trim().length >= 2 && panel.innerHTML) {
            panel.hidden = false;
            input.setAttribute('aria-expanded', 'true');
        }
    });

    document.addEventListener('click', function (e) {
        if (!form.contains(e.target)) close();
    });
})();
</script>
@endpush
