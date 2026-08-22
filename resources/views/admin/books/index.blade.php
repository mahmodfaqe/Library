@extends('admin.layout')

@section('title', __('admin.books.title'))

@section('content')
    <div class="card">
        <div style="display:flex; justify-content:space-between; align-items:center; gap:1rem; flex-wrap:wrap; margin-bottom:0.8rem;">
            <h2 style="font-size:1.15rem;">{{ __('admin.books.heading', ['count' => $books->total()]) }}</h2>
            <a href="{{ route('admin.books.create') }}" class="btn btn-primary">{{ __('admin.books.add') }}</a>
        </div>

        <form method="GET" action="{{ route('admin.books') }}" style="display:flex; gap:0.5rem; margin-bottom:1rem;">
            <input type="search" name="q" value="{{ $search }}" placeholder="{{ __('admin.books.search_placeholder') }}"
                   style="flex:1; padding:0.55rem 0.8rem; border:1px solid #d5d9ee; border-radius:10px; font-family:inherit;">
            <button type="submit" class="btn btn-secondary">{{ __('admin.books.search') }}</button>
        </form>

        @if ($books->isEmpty())
            <p style="color:#6b6b80; padding:1.5rem 0; text-align:center;">{{ __('admin.books.empty') }}</p>
        @else
            <div class="table-scroll">
            <table>
                <thead>
                    <tr>
                        <th>{{ __('admin.books.table.title') }}</th>
                        <th>{{ __('admin.books.table.author') }}</th>
                        <th>{{ __('admin.books.table.year') }}</th>
                        <th>{{ __('admin.books.table.department') }}</th>
                        <th>{{ __('admin.books.table.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($books as $book)
                        <tr>
                            <td dir="auto">{{ $book->title }}</td>
                            <td dir="auto">{{ $book->author ?: '—' }}</td>
                            <td><bdi>{{ $book->year ?: '—' }}</bdi></td>
                            <td dir="auto">{{ $book->department?->translation('ku-sorani', 'title') ?: '—' }}</td>
                            <td class="actions">
                                <a href="{{ route('admin.books.edit', $book) }}" class="btn btn-secondary">{{ __('admin.actions.edit') }}</a>
                                <form method="POST" action="{{ route('admin.books.destroy', $book) }}" style="display:inline;"
                                      onsubmit="return confirm(@js(__('admin.books.confirm_delete')));">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger">{{ __('admin.actions.delete') }}</button>
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
