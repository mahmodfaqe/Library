@php
    /**
     * Admin pagination, styled with the panel's own CSS.
     *
     * The panel does not load Tailwind, so Laravel's default $paginator->links()
     * renders unstyled here. This also windows the page numbers — the book list
     * runs to dozens of pages and printing every number is unusable.
     */
    $last = $paginator->lastPage();
    $current = $paginator->currentPage();
    $window = 2;

    $pages = collect(range(max(1, $current - $window), min($last, $current + $window)))
        ->when($current - $window > 1, fn ($p) => $p->prepend(null)->prepend(1))
        ->when($current + $window < $last, fn ($p) => $p->push(null)->push($last));

    // The panel is read right-to-left in Kurdish and Arabic, where "back" is
    // the arrow pointing right.
    $previous = \App\Support\Locale::isLtr() ? '«' : '»';
    $next = \App\Support\Locale::isLtr() ? '»' : '«';
@endphp

@if ($paginator->hasPages())
    <nav class="pagination" aria-label="{{ __('admin.pagination') }}">
        @if ($paginator->onFirstPage())
            <span aria-hidden="true">{{ $previous }}</span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" rel="prev">{{ $previous }}</a>
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
            <a href="{{ $paginator->nextPageUrl() }}" rel="next">{{ $next }}</a>
        @else
            <span aria-hidden="true">{{ $next }}</span>
        @endif
    </nav>
@endif
