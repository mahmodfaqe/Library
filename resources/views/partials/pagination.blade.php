@php
    /**
     * Catalogue pagination.
     *
     * Laravel's own links() prints every page number, which is unusable once a
     * subject runs to fifty pages, and labels the nav in English. This windows
     * the numbers around the current page and takes its labels from the
     * visitor's language. The arrows follow the reading direction, so « means
     * "previous" on an Arabic page too.
     */
    $last = $paginator->lastPage();
    $current = $paginator->currentPage();
    $window = 2;

    $pages = collect(range(max(1, $current - $window), min($last, $current + $window)))
        ->when($current - $window > 1, fn ($p) => $p->prepend(null)->prepend(1))
        ->when($current + $window < $last, fn ($p) => $p->push(null)->push($last));

    $previous = \App\Support\Locale::isLtr() ? '‹' : '›';
    $next = \App\Support\Locale::isLtr() ? '›' : '‹';
@endphp

@if ($paginator->hasPages())
    <nav class="pagination" aria-label="{{ __('books.pagination') }}">
        @if ($paginator->onFirstPage())
            <span aria-hidden="true">{{ $previous }}</span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="{{ __('books.previous_page') }}">{{ $previous }}</a>
        @endif

        @foreach ($pages as $page)
            @if ($page === null)
                <span class="gap" aria-hidden="true">…</span>
            @elseif ($page === $current)
                <span class="current" aria-current="page">{{ $page }}</span>
            @else
                <a href="{{ $paginator->url($page) }}">{{ $page }}</a>
            @endif
        @endforeach

        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="{{ __('books.next_page') }}">{{ $next }}</a>
        @else
            <span aria-hidden="true">{{ $next }}</span>
        @endif
    </nav>

    <p class="pagination-summary">
        {{ __('books.showing', [
            'from' => $paginator->firstItem(),
            'to' => $paginator->lastItem(),
            'total' => $paginator->total(),
        ]) }}
    </p>
@endif
