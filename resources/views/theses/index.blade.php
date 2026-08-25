@use('App\Support\Locale')
@extends('layouts.base')

@section('title', __('theses.title').' — '.__('messages.site_title'))
@section('description', __('theses.subtitle'))

@section('content')
<div class="book-page">

    <nav class="mb-4 flex items-center gap-2 flex-wrap" style="font-size:0.9rem;" aria-label="{{ __('theses.breadcrumb') }}">
        <a href="{{ Locale::url() }}" class="text-[#667eea] no-underline hover:underline">{{ __('messages.library_name') }}</a>
        <span class="text-[#b5b5c8]">/</span>
        <span class="text-[#6b6b80]">{{ __('theses.title') }}</span>
    </nav>

    <h1 class="repo-title" dir="auto">{{ __('theses.title') }}</h1>
    <p class="repo-subtitle" dir="auto">{{ __('theses.subtitle') }}</p>

    <form method="GET" action="{{ Locale::thesesUrl() }}" class="repo-filters">
        <div class="repo-search">
            <label for="q" class="sr-only">{{ __('theses.search') }}</label>
            <input type="search" id="q" name="q" value="{{ $term }}" dir="auto"
                   placeholder="{{ __('theses.search') }}" autocomplete="off">
        </div>

        <label class="sr-only" for="degree">{{ __('theses.degree') }}</label>
        <select id="degree" name="degree">
            <option value="">{{ __('theses.all_degrees') }}</option>
            @foreach (\App\Models\Thesis::DEGREES as $option)
                <option value="{{ $option }}" @selected($degree === $option)>
                    {{ __('theses.degrees.'.$option) }}@if ($degrees[$option] ?? false) ({{ $degrees[$option] }})@endif
                </option>
            @endforeach
        </select>

        <label class="sr-only" for="department">{{ __('theses.department') }}</label>
        <select id="department" name="department">
            <option value="">{{ __('theses.all_departments') }}</option>
            @foreach ($departments as $option)
                <option value="{{ $option->id }}" @selected((string) $department === (string) $option->id)>
                    {{ $option->translation(app()->getLocale(), 'title') }}
                </option>
            @endforeach
        </select>

        <label class="sr-only" for="year">{{ __('theses.year') }}</label>
        <select id="year" name="year">
            <option value="">{{ __('theses.all_years') }}</option>
            @foreach ($years as $option)
                <option value="{{ $option }}" @selected((string) $year === (string) $option)>{{ $option }}</option>
            @endforeach
        </select>

        <button type="submit" class="section-btn repo-go">{{ __('books.search') }}</button>
    </form>

    @if ($theses->total() > 0)
        <p class="repo-count" aria-live="polite">
            {{ trans_choice('theses.results', $theses->total(), ['count' => $theses->total()]) }}
        </p>
    @endif

    @forelse ($theses as $thesis)
        <article class="thesis-card">
            <span class="thesis-degree" aria-hidden="true">{{ __('theses.degrees.'.$thesis->degree) }}</span>

            <div class="thesis-body">
                <h2 class="thesis-title">
                    <a href="{{ Locale::thesisUrl($thesis->id) }}" dir="auto">{{ $thesis->localTitle() }}</a>
                </h2>

                <p class="thesis-people" dir="auto">
                    <bdi>{{ $thesis->author }}</bdi>
                    @if ($thesis->supervisor)
                        <span class="opacity-60">·</span>
                        <bdi>{{ __('theses.supervisor') }}: {{ $thesis->supervisor }}</bdi>
                    @endif
                </p>

                <p class="thesis-facts">
                    @foreach (collect([
                        $thesis->year,
                        $thesis->department?->translation(app()->getLocale(), 'title'),
                    ])->filter() as $fact)
                        <bdi>{{ $fact }}</bdi>@unless ($loop->last) <span class="opacity-60">·</span> @endunless
                    @endforeach

                    @if ($thesis->isUnderEmbargo())
                        <span class="thesis-embargo">{{ __('theses.embargoed') }}</span>
                    @endif
                </p>
            </div>
        </article>
    @empty
        <p class="text-center text-[#6b6b80]" style="padding:3rem 0;">{{ __('theses.empty') }}</p>
    @endforelse

    @include('partials.pagination', ['paginator' => $theses])
</div>
@endsection
