@extends('admin.layout')

@section('title', __('admin.dashboard.dash_title'))

@section('content')
    <div class="stat-row">
        @foreach ([
            ['books', 'admin.nav.books', 'admin.books', '📚'],
            ['categories', 'admin.nav.categories', 'admin.categories', '🗂'],
            ['departments', 'admin.nav.departments', 'admin.departments', '🏛'],
            ['feedback', 'admin.nav.feedback', 'admin.feedback', '💬'],
        ] as [$key, $label, $route, $icon])
            <a href="{{ route($route) }}" class="stat">
                <span class="stat-icon" aria-hidden="true">{{ $icon }}</span>
                <span class="stat-value">{{ number_format($counts[$key]) }}</span>
                <span class="stat-label">{{ __($label) }}</span>
            </a>
        @endforeach
    </div>

    <div class="dash-grid">
        {{-- The gaps are the point of this page: each is a link to the books
             it counted, so a librarian can act on it rather than note it. --}}
        <div class="card">
            <div class="card-head">
                <h2>{{ __('admin.dashboard.gaps') }}</h2>
            </div>
            <p class="muted">{{ __('admin.dashboard.gaps_hint') }}</p>

            @php
                $missing = collect([
                    'author' => 'admin.dashboard.no_author',
                    'year' => 'admin.dashboard.no_year',
                    'language' => 'admin.dashboard.no_language',
                    'category' => 'admin.dashboard.no_category',
                    'link' => 'admin.dashboard.no_link',
                ])->filter(fn ($label, $key) => $gaps[$key] > 0);
            @endphp

            @if ($missing->isEmpty())
                <p class="empty">{{ __('admin.dashboard.complete') }}</p>
            @else
                <ul class="gap-list">
                    @foreach ($missing as $key => $label)
                        @php $share = $counts['books'] > 0 ? $gaps[$key] / $counts['books'] : 0; @endphp
                        <li>
                            <a href="{{ route('admin.books', ['missing' => $key]) }}">
                                <span class="gap-label">{{ __($label) }}</span>
                                <span class="gap-bar" aria-hidden="true">
                                    <span style="width: {{ round($share * 100) }}%"></span>
                                </span>
                                <span class="gap-count">{{ number_format($gaps[$key]) }}</span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>

        <div class="card">
            <div class="card-head">
                <h2>{{ __('admin.dashboard.languages') }}</h2>
            </div>

            @if ($languages->isEmpty())
                <p class="empty">{{ __('admin.dashboard.nothing') }}</p>
            @else
                <ul class="gap-list">
                    @foreach ($languages as $language => $total)
                        @php $share = $counts['books'] > 0 ? $total / $counts['books'] : 0; @endphp
                        <li>
                            <a href="{{ route('admin.books', ['language' => $language]) }}">
                                <span class="gap-label" dir="auto">{{ $language }}</span>
                                <span class="gap-bar gap-bar-good" aria-hidden="true">
                                    <span style="width: {{ round($share * 100) }}%"></span>
                                </span>
                                <span class="gap-count">{{ number_format($total) }}</span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>

        <div class="card">
            <div class="card-head">
                <h2>{{ __('admin.dashboard.busiest') }}</h2>
                <a href="{{ route('admin.categories') }}" class="btn btn-secondary btn-sm">{{ __('admin.dashboard.see_all') }}</a>
            </div>

            @if ($busiest->isEmpty())
                <p class="empty">{{ __('admin.dashboard.nothing') }}</p>
            @else
                <ul class="gap-list">
                    @foreach ($busiest as $category)
                        @php $share = $counts['books'] > 0 ? $category->books_count / $counts['books'] : 0; @endphp
                        <li>
                            <a href="{{ route('admin.books', ['category' => $category->id]) }}">
                                <span class="gap-label" dir="auto">{{ $category->icon }} {{ $category->localName() }}</span>
                                <span class="gap-bar gap-bar-good" aria-hidden="true">
                                    <span style="width: {{ round($share * 100) }}%"></span>
                                </span>
                                <span class="gap-count">{{ number_format($category->books_count) }}</span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>

        @auth
            @if (auth()->user()->isAdmin())
                <div class="card">
                    <div class="card-head">
                        <h2>{{ __('admin.dashboard.recent_activity') }}</h2>
                        <a href="{{ route('admin.activity') }}" class="btn btn-secondary btn-sm">{{ __('admin.dashboard.see_all') }}</a>
                    </div>

                    @forelse ($recentActivity as $entry)
                        <div class="feed-row">
                            <span class="feed-who" dir="auto">{{ $entry->user?->name ?? '—' }}</span>
                            <span class="feed-what"><code dir="ltr">{{ $entry->action }}</code></span>
                            <time class="feed-when" dir="ltr" datetime="{{ $entry->created_at->toIso8601String() }}">
                                {{ $entry->created_at->diffForHumans() }}
                            </time>
                        </div>
                    @empty
                        <p class="empty">{{ __('admin.dashboard.nothing') }}</p>
                    @endforelse
                </div>
            @endif
        @endauth

        <div class="card">
            <div class="card-head">
                <h2>{{ __('admin.dashboard.recent_feedback') }}</h2>
                <a href="{{ route('admin.feedback') }}" class="btn btn-secondary btn-sm">{{ __('admin.dashboard.see_all') }}</a>
            </div>

            @forelse ($recentFeedback as $item)
                <div class="feed-row feed-row-block">
                    <span class="feed-who" dir="auto">{{ $item->name ?: '—' }}</span>
                    <p class="feed-message" dir="auto">{{ Str::limit($item->message, 140) }}</p>
                    <time class="feed-when" dir="ltr" datetime="{{ $item->created_at->toIso8601String() }}">
                        {{ $item->created_at->diffForHumans() }}
                    </time>
                </div>
            @empty
                <p class="empty">{{ __('admin.dashboard.nothing') }}</p>
            @endforelse
        </div>
    </div>
@endsection
