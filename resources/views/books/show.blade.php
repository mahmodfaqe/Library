@use('App\Support\Locale')
@extends('layouts.base')

@section('title', $book->title.' — '.__('messages.site_title'))
@section('description', collect([
    $book->author,
    $book->year,
    $book->category?->localName(),
    __('messages.site_title'),
])->filter()->implode(' · '))

@push('head')
    {{-- So a search engine, and anything reading the page for a citation,
         knows this is one book rather than a page that mentions one.

         It is built in a plain PHP block, because in a Blade expression the
         "@context" key would be compiled as one of Blade's own directives and
         lost; and the angle brackets are escaped because a title is
         staff-entered text, so a closing script tag inside one would
         otherwise end this block early. --}}
    @php
        $schema = array_filter([
            '@context' => 'https://schema.org',
            '@type' => 'Book',
            'name' => $book->title,
            'author' => $book->author ? ['@type' => 'Person', 'name' => $book->author] : null,
            'datePublished' => $book->year ? (string) $book->year : null,
            'inLanguage' => $book->language,
            'genre' => $book->category?->localName(),
            'url' => Locale::bookUrl($book->id),
            'image' => $book->coverUrl(),
            'isAccessibleForFree' => true,
            'publisher' => ['@type' => 'CollegeOrUniversity', 'name' => __('messages.university_name')],
        ]);
    @endphp
    <script type="application/ld+json">
    {!! json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) !!}
    </script>
@endpush

@section('content')
@php
    // Written the way a reader would cite it, from what the catalogue knows.
    $author = $book->author ?: __('messages.university_name');
    $year = $book->year ?: 'n.d.';
    $publisher = '<bdi>'.e(__('messages.university_name')).'</bdi>';
    $link = Locale::bookUrl($book->id);

    $name = '<bdi>'.e($author).'</bdi>';
    $work = '<bdi><i>'.e($book->title).'</i></bdi>';
    $where = '<bdi>'.e($link).'</bdi>';

    $citations = [
        'APA' => "{$name} ({$year}). {$work}. {$publisher}. {$where}",
        'MLA' => "{$name}. {$work}. {$publisher}, {$year}, {$where}.",
        'Chicago' => "{$name}. {$work}. {$publisher}, {$year}. {$where}.",
    ];
@endphp

<div class="book-page">

    <nav class="mb-4 flex items-center gap-2 flex-wrap" style="font-size:0.9rem;" aria-label="{{ __('books.breadcrumb') }}">
        <a href="{{ Locale::url() }}" class="text-[#667eea] no-underline hover:underline">{{ __('messages.library_name') }}</a>
        <span class="text-[#b5b5c8]">/</span>
        <a href="{{ Locale::booksUrl() }}" class="text-[#667eea] no-underline hover:underline">{{ __('books.title') }}</a>
        @if ($book->category)
            <span class="text-[#b5b5c8]">/</span>
            <a href="{{ Locale::booksUrl() }}?category={{ $book->category->id }}"
               class="text-[#667eea] no-underline hover:underline" dir="auto">{{ $book->category->localName() }}</a>
        @endif
    </nav>

    <article class="book-hero">
        <div class="book-hero-cover">
            <div class="book-cover">
                @if ($book->coverUrl())
                    <img src="{{ $book->coverUrl() }}" alt="" loading="eager" decoding="async"
                         referrerpolicy="no-referrer"
                         onerror="this.closest('.book-cover').classList.add('is-blank')">
                @endif
                <span class="book-cover-fallback" aria-hidden="true">{{ $book->category?->icon ?: '📘' }}</span>
            </div>
        </div>

        <div class="book-hero-body">
            <h1 dir="auto">{{ $book->title }}</h1>

            @if ($book->author)
                <p class="book-byline" dir="auto">{{ $book->author }}</p>
            @endif

            <dl class="book-facts">
                @foreach ([
                    'year' => $book->year,
                    'language' => $book->language,
                    'subject' => $book->category?->localName(),
                    'size' => $book->humanFileSize(),
                ] as $key => $value)
                    @if ($value)
                        <div>
                            <dt>{{ __("books.book.$key") }}</dt>
                            <dd dir="auto">{{ $value }}</dd>
                        </div>
                    @endif
                @endforeach
            </dl>

            @if ($book->readUrl())
                <a href="{{ $book->readUrl() }}"
                   @unless ($book->hasFile()) target="_blank" rel="noopener" @endunless
                   class="section-btn book-open">
                    {{ $book->hasFile() ? __('books.download') : __('books.open') }}
                    @if ($book->humanFileSize())
                        <bdi style="opacity:0.75; font-size:0.85em;">({{ $book->humanFileSize() }})</bdi>
                    @endif
                </a>
            @else
                <p class="book-unavailable">{{ __('books.book.no_link') }}</p>
            @endif
        </div>
    </article>

    {{-- The point of a page per book: something a reader can put in a
         bibliography. --}}
    <section class="cite-box" aria-labelledby="cite-heading">
        <h2 id="cite-heading">{{ __('books.book.cite') }}</h2>
        <p class="cite-hint">{{ __('books.book.cite_hint') }}</p>

        @foreach ($citations as $style => $text)
            <div class="cite-row">
                <span class="cite-style">{{ $style }}</span>
                <p class="cite-text" dir="auto">{!! $text !!}</p>
                <button type="button" class="cite-copy"
                        data-copy="{{ html_entity_decode(strip_tags($text), ENT_QUOTES) }}"
                        data-done="{{ __('books.book.copied') }}">{{ __('books.book.copy') }}</button>
            </div>
        @endforeach
    </section>

    @if ($related->isNotEmpty())
        <section aria-labelledby="related-heading">
            <h2 id="related-heading" class="language-shelf">
                <bdi>{{ __('books.book.related') }}</bdi>
            </h2>

            <div class="book-grid">
                @foreach ($related as $other)
                    <article class="book-card bg-white/85 backdrop-blur-md border border-white/70 rounded-[16px] flex flex-col">
                        <a class="book-cover" href="{{ Locale::bookUrl($other->id) }}" aria-hidden="true" tabindex="-1">
                            @if ($other->coverUrl())
                                <img src="{{ $other->coverUrl() }}" alt="" loading="lazy" decoding="async"
                                     referrerpolicy="no-referrer"
                                     onerror="this.closest('.book-cover').classList.add('is-blank')">
                            @endif
                            <span class="book-cover-fallback" aria-hidden="true">{{ $other->category?->icon ?: '📘' }}</span>
                        </a>

                        <div class="book-body">
                            <div>
                                <h3 class="book-title font-bold text-[#2d2d3a] mb-1">
                                    <a href="{{ Locale::bookUrl($other->id) }}" class="no-underline text-inherit hover:underline" dir="auto">{{ $other->title }}</a>
                                </h3>
                                @if ($other->author)
                                    <p class="text-[#6b6b80] mb-1" style="font-size:0.88rem;" dir="auto">{{ $other->author }}</p>
                                @endif
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        </section>
    @endif

</div>
@endsection

@push('scripts')
<script>
(function () {
    // The clipboard API needs a secure context; where it is missing the text
    // is still on the page to select by hand.
    document.querySelectorAll('.cite-copy').forEach(function (button) {
        button.addEventListener('click', function () {
            var text = button.dataset.copy;
            var done = function () {
                var was = button.textContent;
                button.textContent = button.dataset.done;
                button.classList.add('is-done');
                setTimeout(function () {
                    button.textContent = was;
                    button.classList.remove('is-done');
                }, 1600);
            };

            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(text).then(done, function () {});
                return;
            }

            var field = document.createElement('textarea');
            field.value = text;
            field.setAttribute('readonly', '');
            field.style.cssText = 'position:fixed;top:-1000px';
            document.body.appendChild(field);
            field.select();
            try { document.execCommand('copy'); done(); } catch (e) {}
            field.remove();
        });
    });
})();
</script>
@endpush
