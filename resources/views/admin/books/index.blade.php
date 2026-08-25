@use('App\Support\BookLanguage')
@use('App\Support\Locale')
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
            {{-- The author and year columns are editable in place. More than a
                 thousand books arrived from Drive with neither, and opening a
                 form for each one is a day's work. Saving is one button for
                 the whole page, so it works with no script at all; the script
                 below only adds a nudge before leaving with unsaved edits. --}}
            <form method="POST" action="{{ route('admin.books.quick') }}" id="quick-edit">
                @csrf
                @method('PUT')

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
                            <td>
                                <div class="row-book">
                                    {{-- The cover opens the book itself, so a
                                         librarian can check what they are
                                         cataloguing without leaving the list.
                                         Skipped by the keyboard: the row's own
                                         edit button is the way through it.

                                         Written as two whole elements rather
                                         than one with a tag name decided at
                                         run time: the latter reads as broken
                                         HTML to everything that is not Blade,
                                         the editor's own checks included. --}}
                                    @if ($book->readUrl())
                                        <a class="row-cover" href="{{ $book->readUrl() }}"
                                           target="_blank" rel="noopener"
                                           tabindex="-1" aria-hidden="true"
                                           title="{{ $book->hasFile() ? __('admin.books.file') : __('admin.books.table.url') }}">
                                            @include('admin.books.partials.cover', ['book' => $book])
                                        </a>
                                    @else
                                        <span class="row-cover">
                                            @include('admin.books.partials.cover', ['book' => $book])
                                        </span>
                                    @endif

                                    <span class="row-title" dir="auto">{{ $book->title }}</span>
                                </div>
                            </td>
                            <td>
                                <input class="cell" type="text" dir="auto" maxlength="190"
                                       name="books[{{ $book->id }}][author]"
                                       value="{{ $book->author }}"
                                       aria-label="{{ __('admin.books.table.author') }}">
                            </td>
                            <td>
                                <input class="cell cell-narrow" type="number" dir="ltr" inputmode="numeric"
                                       min="1400" max="{{ (int) date('Y') + 1 }}"
                                       name="books[{{ $book->id }}][year]"
                                       value="{{ $book->year }}"
                                       aria-label="{{ __('admin.books.table.year') }}">
                            </td>
                            {{-- Named as the staff member reads, like the
                                 subject beside it; the edit form is where the
                                 stored word itself is changed. --}}
                            <td dir="auto">{{ BookLanguage::name($book->language) ?: '—' }}</td>
                            <td dir="auto">{{ $book->category?->localName() ?: '—' }}</td>
                            <td class="actions">
                                <a href="{{ route('admin.books.edit', $book) }}" class="btn btn-secondary btn-sm">{{ __('admin.actions.edit') }}</a>
                                {{-- The reader's side of the same book, so a
                                     correction can be checked where it shows. --}}
                                <a href="{{ Locale::bookUrl($book->id) }}" target="_blank" rel="noopener"
                                   class="btn btn-secondary btn-sm">{{ __('admin.actions.view') }}</a>
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

            <div class="quick-bar">
                <button type="submit" class="btn btn-primary">{{ __('admin.books.quick_save') }}</button>
                <span class="quick-hint">{{ __('admin.books.quick_hint') }}</span>
            </div>
            </form>

            @if ($books->hasPages())
                @include('admin.partials.pagination', ['paginator' => $books])
            @endif
        @endif
    </div>
@endsection

@push('scripts')
<script>
(function () {
    var form = document.getElementById('quick-edit');
    if (!form) return;

    var bar = form.querySelector('.quick-bar');
    var dirty = false;

    // Mark the row so it is obvious which lines will be written, and keep the
    // save button in view once there is something to save.
    form.addEventListener('input', function (e) {
        if (!e.target.classList.contains('cell')) return;
        e.target.closest('tr').classList.add('is-edited');
        dirty = true;
        bar.classList.add('is-active');
    });

    // Enter in a cell saves rather than doing nothing, which is what a
    // spreadsheet would do.
    form.addEventListener('keydown', function (e) {
        if (e.key === 'Enter' && e.target.classList.contains('cell')) {
            e.preventDefault();
            form.requestSubmit();
        }
    });

    form.addEventListener('submit', function () { dirty = false; });

    window.addEventListener('beforeunload', function (e) {
        if (!dirty) return;
        e.preventDefault();
        e.returnValue = '';
    });
})();
</script>
@endpush
