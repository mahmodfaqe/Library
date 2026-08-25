@use('App\Support\BookLanguage')
@use('App\Support\Citation')
@use('App\Support\Locale')
@extends('layouts.base')

@section('title', $thesis->localTitle().' — '.__('messages.site_title'))
@section('description', collect([
    $thesis->author,
    __('theses.degrees.'.$thesis->degree),
    $thesis->year,
    __('messages.university_name'),
])->filter()->implode(' · '))

@push('head')
    {{-- A thesis is not a book, and a search engine that is told otherwise
         indexes it as one. Built in a plain PHP block because in a Blade
         expression the "@context" key would be compiled as a directive. --}}
    @php
        $schema = array_filter([
            '@context' => 'https://schema.org',
            '@type' => 'Thesis',
            'name' => $thesis->localTitle(),
            'alternateName' => $thesis->title_en && $thesis->title_en !== $thesis->localTitle()
                ? $thesis->title_en
                : null,
            'author' => ['@type' => 'Person', 'name' => $thesis->author],
            'datePublished' => (string) $thesis->year,
            'inSupportOf' => __('theses.degrees.'.$thesis->degree, [], 'en'),
            'inLanguage' => BookLanguage::locale($thesis->language) ?? $thesis->language,
            'abstract' => $thesis->localAbstract(),
            'keywords' => $thesis->keywords,
            'numberOfPages' => $thesis->pages,
            'url' => Locale::thesisUrl($thesis->id),
            'sameAs' => $thesis->doiUrl(),
            'publisher' => [
                '@type' => 'CollegeOrUniversity',
                'name' => __('messages.university_name'),
            ],
            'isAccessibleForFree' => ! $thesis->isUnderEmbargo(),
        ]);
    @endphp
    <script type="application/ld+json">
    {!! json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) !!}
    </script>

    {{-- Highwire Press tags. Google Scholar indexes theses from repositories
         that carry these, and Zotero files them without the reader typing. --}}
    <meta name="citation_title" content="{{ $thesis->title }}">
    <meta name="citation_author" content="{{ $thesis->author }}">
    {{-- The institution that awarded the degree, named once. The supervisors
         are people, and Scholar has no tag for them. --}}
    <meta name="citation_dissertation_institution" content="{{ __('messages.university_name', [], 'en') }}">
    <meta name="citation_publication_date" content="{{ $thesis->year }}">
    <meta name="citation_dissertation_name" content="{{ __('theses.degrees.'.$thesis->degree, [], 'en') }}">
    @if ($thesis->doi)
        <meta name="citation_doi" content="{{ $thesis->doi }}">
    @endif
    @if ($thesis->language)
        <meta name="citation_language" content="{{ BookLanguage::locale($thesis->language) ?? $thesis->language }}">
    @endif
    @foreach ($thesis->keywordList() as $keyword)
        <meta name="citation_keywords" content="{{ $keyword }}">
    @endforeach
    <meta name="citation_abstract_html_url" content="{{ Locale::thesisUrl($thesis->id) }}">
    @if ($thesis->hasFile() && ! $thesis->isUnderEmbargo())
        <meta name="citation_pdf_url" content="{{ route('theses.download', $thesis) }}">
    @endif
@endpush

@section('content')
@php
    // Cited as the university's own work, which is what it is.
    $university = __('messages.university_name', [], BookLanguage::citationLocale($thesis->language));
    $degree = __('theses.degrees.'.$thesis->degree, [], BookLanguage::citationLocale($thesis->language));
    $where = '<bdi>'.e($thesis->permanentUrl()).'</bdi>';
    $name = '<bdi>'.e($thesis->author).'</bdi>';
    // A work in another language is cited by its own title, with a
    // translation in brackets — which is what APA asks for, and what lets an
    // English-reading examiner tell what the thesis was about.
    $englished = $thesis->title_en && $thesis->title_en !== $thesis->title
        ? ' [<bdi>'.e($thesis->title_en).'</bdi>]'
        : '';
    $work = '<bdi><i>'.e($thesis->title).'</i></bdi>'.$englished;
    $issuer = '<bdi>'.e($university).'</bdi>';
    $kind = '<bdi>'.e($degree).'</bdi>';

    $citations = [
        'APA' => "{$name} ({$thesis->year}). {$work} [{$kind}, {$issuer}]. {$where}",
        'MLA' => "{$name}. {$work}. {$kind}, {$issuer}, {$thesis->year}, {$where}.",
        'Chicago' => "{$name}. {$work}. {$kind}, {$issuer}, {$thesis->year}. {$where}.",
    ];
@endphp

<div class="book-page">

    <nav class="mb-4 flex items-center gap-2 flex-wrap" style="font-size:0.9rem;" aria-label="{{ __('theses.breadcrumb') }}">
        <a href="{{ Locale::url() }}" class="text-[#667eea] no-underline hover:underline">{{ __('messages.library_name') }}</a>
        <span class="text-[#b5b5c8]">/</span>
        <a href="{{ Locale::thesesUrl() }}" class="text-[#667eea] no-underline hover:underline">{{ __('theses.title') }}</a>
        @if ($thesis->department)
            <span class="text-[#b5b5c8]">/</span>
            <a href="{{ Locale::thesesUrl() }}?department={{ $thesis->department_id }}"
               class="text-[#667eea] no-underline hover:underline" dir="auto">{{ $thesis->department->translation(app()->getLocale(), 'title') }}</a>
        @endif
    </nav>

    <article class="thesis-hero">
        <span class="thesis-degree-large">{{ __('theses.degrees.'.$thesis->degree) }}</span>

        <h1 dir="auto">{{ $thesis->localTitle() }}</h1>

        @if ($thesis->title_en && $thesis->title_en !== $thesis->localTitle())
            <p class="thesis-alt-title" dir="ltr">{{ $thesis->title_en }}</p>
        @endif

        <dl class="book-facts">
            @foreach ([
                'author' => $thesis->author,
                'supervisor' => $thesis->supervisor,
                'co_supervisor' => $thesis->co_supervisor,
                'department' => $thesis->department?->translation(app()->getLocale(), 'title'),
                'year' => $thesis->year,
                'defended' => $thesis->defended_on?->toFormattedDateString(),
                'language' => BookLanguage::name($thesis->language),
                'pages' => $thesis->pages,
                'doi' => $thesis->doi,
                'license' => $thesis->license ? __('theses.licences.'.$thesis->license) : null,
                'size' => $thesis->humanFileSize(),
            ] as $key => $value)
                @if ($value)
                    <div>
                        <dt>{{ __("theses.$key") }}</dt>
                        <dd dir="auto">
                            @if ($key === 'doi')
                                <a href="{{ $thesis->doiUrl() }}" target="_blank" rel="noopener" dir="ltr">{{ $value }}</a>
                            @else
                                {{ $value }}
                            @endif
                        </dd>
                    </div>
                @endif
            @endforeach
        </dl>

        @if ($thesis->isUnderEmbargo())
            {{-- The record stays open. What waits is only the reading. --}}
            <div class="embargo-notice" role="note">
                <strong>{{ __('theses.embargoed') }}</strong>
                <p>{{ __('theses.embargoed_until', ['date' => $thesis->embargo_until->toFormattedDateString()]) }}</p>
                <p class="embargo-why">{{ __('theses.embargo_why') }}</p>
            </div>
        @elseif ($thesis->readUrl())
            <a href="{{ $thesis->readUrl() }}"
               @unless ($thesis->hasFile()) target="_blank" rel="noopener" @endunless
               class="section-btn book-open">
                {{ $thesis->hasFile() ? __('theses.download') : __('theses.open') }}
                @if ($thesis->humanFileSize())
                    <bdi style="opacity:0.75; font-size:0.85em;">({{ $thesis->humanFileSize() }})</bdi>
                @endif
            </a>
        @else
            <p class="book-unavailable">{{ __('theses.no_file') }}</p>
        @endif
    </article>

    @if ($thesis->localAbstract() || $thesis->keywordList())
        <section class="book-about" aria-labelledby="about-heading">
            @if ($thesis->localAbstract())
                <h2 id="about-heading">{{ __('theses.abstract') }}</h2>
                <p class="book-abstract" dir="auto">{{ $thesis->localAbstract() }}</p>
            @endif

            @if ($thesis->keywordList())
                <ul class="book-keywords" aria-label="{{ __('theses.keywords') }}">
                    @foreach ($thesis->keywordList() as $keyword)
                        <li>
                            <a href="{{ Locale::thesesUrl() }}?q={{ urlencode($keyword) }}" dir="auto">{{ $keyword }}</a>
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>
    @endif

    <section class="cite-box" aria-labelledby="cite-heading">
        <h2 id="cite-heading">{{ __('theses.cite') }}</h2>
        <p class="cite-hint">{{ __('theses.cite_hint') }}</p>

        <p class="cite-managers">
            <span>{{ __('theses.cite_download') }}</span>
            <a href="{{ route('theses.cite', [$thesis, 'bib']) }}" class="cite-file">BibTeX</a>
            <a href="{{ route('theses.cite', [$thesis, 'ris']) }}" class="cite-file">RIS</a>
        </p>

        @foreach ($citations as $style => $text)
            <div class="cite-row">
                <span class="cite-style">{{ $style }}</span>
                <p class="cite-text" dir="auto">{!! $text !!}</p>
                <button type="button" class="cite-copy"
                        data-copy="{{ html_entity_decode(strip_tags($text), ENT_QUOTES) }}"
                        data-done="{{ __('theses.copied') }}">{{ __('theses.copy') }}</button>
            </div>
        @endforeach
    </section>

    @if ($related->isNotEmpty())
        <section aria-labelledby="related-heading">
            <h2 id="related-heading" class="language-shelf"><bdi>{{ __('theses.related') }}</bdi></h2>

            @foreach ($related as $other)
                <article class="thesis-card">
                    <span class="thesis-degree" aria-hidden="true">{{ __('theses.degrees.'.$other->degree) }}</span>
                    <div class="thesis-body">
                        <h3 class="thesis-title">
                            <a href="{{ Locale::thesisUrl($other->id) }}" dir="auto">{{ $other->localTitle() }}</a>
                        </h3>
                        <p class="thesis-people" dir="auto"><bdi>{{ $other->author }}</bdi></p>
                        <p class="thesis-facts"><bdi>{{ $other->year }}</bdi></p>
                    </div>
                </article>
            @endforeach
        </section>
    @endif
</div>
@endsection
