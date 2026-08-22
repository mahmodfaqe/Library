@extends('admin.layout')

@section('title', $book->exists ? __('admin.books.edit_title') : __('admin.books.new_title'))

@section('content')
    <div class="card">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1.2rem;">
            <h2 style="font-size:1.15rem;">{{ $book->exists ? __('admin.books.edit_title') : __('admin.books.new_title') }}</h2>
            <a href="{{ route('admin.books') }}" class="btn btn-secondary">{{ __('admin.actions.back') }}</a>
        </div>

        <form method="POST" enctype="multipart/form-data"
              action="{{ $book->exists ? route('admin.books.update', $book) : route('admin.books.store') }}">
            @csrf
            @if ($book->exists)
                @method('PUT')
            @endif

            <div class="field">
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

            <div style="display:flex; gap:0.6rem; margin-top:1rem;">
                <button type="submit" class="btn btn-primary">{{ $book->exists ? __('admin.actions.save') : __('admin.actions.create') }}</button>
                <a href="{{ route('admin.books') }}" class="btn btn-secondary">{{ __('admin.actions.cancel') }}</a>
            </div>
        </form>
    </div>
@endsection
