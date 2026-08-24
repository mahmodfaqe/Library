@extends('admin.layout')

@section('title', __('admin.books.title'))

@section('content')
    <div class="card">
        <div class="card-head">
            <h2>{{ __('admin.books.heading', ['count' => $books->total()]) }}</h2>
            <a href="{{ route('admin.books.create') }}" class="btn btn-primary">{{ __('admin.books.add') }}</a>
        </div>

        {{-- One form for everything: searching, narrowing and sorting all end
             up in the same query string, so a filtered view can be linked to
             and bookmarked. --}}
        <form method="GET" action="{{ route('admin.books') }}" class="filters">
            <input type="search" name="q" value="{{ $search }}"
                   placeholder="{{ __('admin.books.search_placeholder') }}" dir="auto">

            <select name="category" aria-label="{{ __('admin.books.table.category') }}">
                <option value="">{{ __('admin.books.table.category') }} — {{ __('admin.filters.filter_all') }}</option>
                @foreach ($categories as $shelf)
                    <option value="{{ $shelf->id }}" @selected((string) $category === (string) $shelf->id)>
                        {{ $shelf->localName() }}
                    </option>
                @endforeach
            </select>

            <select name="language" aria-label="{{ __('admin.books.table.language') }}">
                <option value="">{{ __('admin.books.table.language') }} — {{ __('admin.filters.filter_all') }}</option>
                @foreach ($languages as $name)
                    <option value="{{ $name }}" @selected($language === $name)>{{ $name }}</option>
                @endforeach
            </select>

            <select name="missing" aria-label="{{ __('admin.filters.filter_missing') }}">
                <option value="">{{ __('admin.filters.filter_missing') }} — {{ __('admin.filters.filter_all') }}</option>
                @foreach ([
                    'author' => 'admin.dashboard.no_author',
                    'year' => 'admin.dashboard.no_year',
                    'language' => 'admin.dashboard.no_language',
                    'category' => 'admin.dashboard.no_category',
                    'link' => 'admin.dashboard.no_link',
                ] as $key => $label)
                    <option value="{{ $key }}" @selected($missing === $key)>{{ __($label) }}</option>
                @endforeach
            </select>

            <input type="hidden" name="sort" value="{{ $sort }}">
            <input type="hidden" name="dir" value="{{ $direction }}">

            <button type="submit" class="btn btn-secondary">{{ __('admin.books.search') }}</button>

            @if ($search || $missing || $language || $category)
                <a href="{{ route('admin.books') }}" class="btn btn-secondary">{{ __('admin.actions.cancel') }}</a>
            @endif
        </form>

        @if ($books->isEmpty())
            <p class="empty">{{ __('admin.books.empty') }}</p>
        @else
            <div class="table-scroll">
            <table>
                <thead>
                    <tr>
                        @foreach ([
                            'title' => 'admin.books.table.title',
                            'author' => 'admin.books.table.author',
                            'year' => 'admin.books.table.year',
                            'language' => 'admin.books.table.language',
                        ] as $column => $label)
                            @php
                                $on = $sort === $column;
                                $next = $on && $direction === 'asc' ? 'desc' : 'asc';
                            @endphp
                            <th @if ($on) aria-sort="{{ $direction === 'asc' ? 'ascending' : 'descending' }}" @endif>
                                <a class="sort{{ $on ? ' is-on' : '' }}"
                                   href="{{ request()->fullUrlWithQuery(['sort' => $column, 'dir' => $next, 'page' => null]) }}">
                                    {{ __($label) }}
                                    <span aria-hidden="true">{{ $on ? ($direction === 'asc' ? '↑' : '↓') : '↕' }}</span>
                                </a>
                            </th>
                        @endforeach
                        <th>{{ __('admin.books.table.category') }}</th>
                        <th>{{ __('admin.books.table.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($books as $book)
                        <tr>
                            <td dir="auto">{{ $book->title }}</td>
                            <td dir="auto">{{ $book->author ?: '—' }}</td>
                            <td><bdi>{{ $book->year ?: '—' }}</bdi></td>
                            <td dir="auto">{{ $book->language ?: '—' }}</td>
                            <td dir="auto">{{ $book->category?->localName() ?: '—' }}</td>
                            <td class="actions">
                                <a href="{{ route('admin.books.edit', $book) }}" class="btn btn-secondary btn-sm">{{ __('admin.actions.edit') }}</a>
                                <form method="POST" action="{{ route('admin.books.destroy', $book) }}" style="display:inline;"
                                      onsubmit="return confirm(@js(__('admin.books.confirm_delete')));">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm">{{ __('admin.actions.delete') }}</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            </div>

            @if ($books->hasPages())
                @include('admin.partials.pagination', ['paginator' => $books])
            @endif
        @endif
    </div>
@endsection
