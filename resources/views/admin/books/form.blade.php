@extends('admin.layout')

@section('title', $book->exists ? __('admin.books.edit_title') : __('admin.books.new_title'))

@section('content')
    <div class="card">
        <div class="card-head">
            <h2>{{ $book->exists ? __('admin.books.edit_title') : __('admin.books.new_title') }}</h2>
            <a href="{{ route('admin.books') }}" class="btn btn-secondary">{{ __('admin.actions.back') }}</a>
        </div>

        <form method="POST" enctype="multipart/form-data"
              action="{{ $book->exists ? route('admin.books.update', $book) : route('admin.books.store') }}">
            @csrf
            @if ($book->exists)
                @method('PUT')
            @endif

            <div class="field-grid">

            <div class="field field-wide">
                <label for="title">{{ __('admin.books.table.title') }}</label>
                <input type="text" id="title" name="title" required maxlength="255" dir="auto"
                       value="{{ old('title', $book->title) }}">
            </div>

            <div class="field">
                <label for="author">{{ __('admin.books.table.author') }}</label>
                <input type="text" id="author" name="author" maxlength="190" dir="auto"
                       value="{{ old('author', $book->author) }}">
            </div>

            <div class="field">
                <label for="year">{{ __('admin.books.table.year') }}</label>
                <input type="number" id="year" name="year" dir="ltr" min="1400" max="{{ date('Y') + 1 }}"
                       value="{{ old('year', $book->year) }}">
            </div>

            <div class="field">
                <label for="language">{{ __('admin.books.table.language') }}</label>
                <input type="text" id="language" name="language" maxlength="40" dir="auto"
                       value="{{ old('language', $book->language) }}">
            </div>

            <div class="field">
                <label for="publisher">{{ __('admin.books.table.publisher') }}</label>
                <input type="text" id="publisher" name="publisher" maxlength="190" dir="auto"
                       value="{{ old('publisher', $book->publisher) }}">
            </div>

            <div class="field">
                <label for="isbn">{{ __('admin.books.table.isbn') }}</label>
                <input type="text" id="isbn" name="isbn" maxlength="20" dir="ltr"
                       inputmode="numeric" placeholder="978-0-8153-4432-2"
                       value="{{ old('isbn', $book->isbnForDisplay()) }}">
            </div>

            <div class="field">
                <label for="edition">{{ __('admin.books.table.edition') }}</label>
                <input type="text" id="edition" name="edition" maxlength="60" dir="auto"
                       value="{{ old('edition', $book->edition) }}">
            </div>

            <div class="field">
                <label for="pages">{{ __('admin.books.table.pages') }}</label>
                <input type="number" id="pages" name="pages" dir="ltr" min="1" max="65535"
                       value="{{ old('pages', $book->pages) }}">
            </div>

            <div class="field">
                <label for="keywords">{{ __('admin.books.table.keywords') }}</label>
                <input type="text" id="keywords" name="keywords" maxlength="500" dir="auto"
                       value="{{ old('keywords', $book->keywords) }}">
                <div class="hint">{{ __('admin.books.table.keywords_hint') }}</div>
            </div>

            <div class="field field-wide">
                <label for="abstract">{{ __('admin.books.table.abstract') }}</label>
                <textarea id="abstract" name="abstract" rows="5" maxlength="4000"
                          dir="auto">{{ old('abstract', $book->abstract) }}</textarea>
            </div>

            <div class="field">
                <label for="category_id">{{ __('admin.books.table.category') }}</label>
                <select id="category_id" name="category_id">
                    <option value="">{{ __('admin.books.no_department') }}</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" @selected((string) old('category_id', $book->category_id) === (string) $category->id)>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="field">
                <label for="department_id">{{ __('admin.books.table.department') }}</label>
                <select id="department_id" name="department_id">
                    <option value="">{{ __('admin.books.no_department') }}</option>
                    @foreach ($departments as $dept)
                        <option value="{{ $dept->id }}" @selected((string) old('department_id', $book->department_id) === (string) $dept->id)>
                            {{ $dept->translation('ku-sorani', 'title') }}
                        </option>
                    @endforeach
                </select>
            </div>

            </div>

            <div class="field">
                <label for="file">{{ __('admin.books.file') }}</label>
                <input type="file" id="file" name="file" accept="application/pdf">
                <div class="hint">
                    @if ($book->hasFile())
                        {{ __('admin.books.file_present', ['size' => $book->humanFileSize()]) }}
                    @else
                        {{ __('admin.books.file_hint') }}
                    @endif
                </div>
            </div>

            <div class="field">
                <label for="url">{{ __('admin.books.table.url') }}</label>
                <input type="url" id="url" name="url" maxlength="500" dir="ltr"
                       placeholder="https://drive.google.com/…"
                       value="{{ old('url', $book->url) }}">
            </div>

            <div class="field">
                <label for="cover_url">{{ __('admin.books.table.cover') }}</label>
                <input type="url" id="cover_url" name="cover_url" maxlength="500" dir="ltr"
                       value="{{ old('cover_url', $book->cover_url) }}">
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">{{ $book->exists ? __('admin.actions.save') : __('admin.actions.create') }}</button>
                <a href="{{ route('admin.books') }}" class="btn btn-secondary">{{ __('admin.actions.cancel') }}</a>
            </div>
        </form>
    </div>
@endsection
