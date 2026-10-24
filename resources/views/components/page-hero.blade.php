@props(['title', 'crumbs' => [], 'label' => null])

{{-- Every inner page opens as the university's do: the library's shelves
     under a grey wash, the page's name near the foot at the reading edge and
     the way back beneath it. The last step is the page itself, so it is not
     a link. --}}
<section class="page-hero" style="background-image: url('{{ asset('file/image2.webp') }}');">
    <div class="page-hero__inner">
        <h1 class="page-hero__title" dir="auto">{{ $title }}</h1>
        @if ($crumbs)
            <nav class="page-hero__crumbs" aria-label="{{ $label ?? __('books.breadcrumb') }}">
                <ol>
                    @foreach ($crumbs as $crumb)
                        <li>
                            @if (! $loop->last && isset($crumb['url']))
                                <a href="{{ $crumb['url'] }}" dir="auto">{{ $crumb['label'] }}</a>
                            @else
                                <span dir="auto" @if ($loop->last) aria-current="page" @endif>{{ $crumb['label'] }}</span>
                            @endif
                        </li>
                    @endforeach
                </ol>
            </nav>
        @endif
    </div>
</section>
