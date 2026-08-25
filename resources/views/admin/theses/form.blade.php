@use('App\Models\Thesis')
@extends('admin.layout')

@section('title', $thesis->exists ? __('admin.theses.edit_title') : __('admin.theses.new_title'))

@section('content')
    <div class="card">
        <div class="card-head">
            <h2>{{ $thesis->exists ? __('admin.theses.edit_title') : __('admin.theses.new_title') }}</h2>
            <a href="{{ route('admin.theses') }}" class="btn btn-secondary">{{ __('admin.actions.back') }}</a>
        </div>

        <form method="POST" enctype="multipart/form-data"
              action="{{ $thesis->exists ? route('admin.theses.update', $thesis) : route('admin.theses.store') }}">
            @csrf
            @if ($thesis->exists)
                @method('PUT')
            @endif

            <div class="field-grid">

            <div class="field field-wide">
                <label for="title">{{ __('admin.theses.table.title') }} *</label>
                <input type="text" id="title" name="title" required maxlength="500" dir="auto"
                       value="{{ old('title', $thesis->title) }}">
            </div>

            <div class="field field-wide">
                <label for="title_en">{{ __('admin.theses.table.title_en') }}</label>
                <input type="text" id="title_en" name="title_en" maxlength="500" dir="ltr"
                       value="{{ old('title_en', $thesis->title_en) }}">
                <div class="hint">{{ __('admin.theses.title_en_hint') }}</div>
            </div>

            <div class="field">
                <label for="author">{{ __('admin.theses.table.author') }} *</label>
                <input type="text" id="author" name="author" required maxlength="190" dir="auto"
                       value="{{ old('author', $thesis->author) }}">
            </div>

            <div class="field">
                <label for="degree">{{ __('admin.theses.table.degree') }} *</label>
                <select id="degree" name="degree" required>
                    @foreach (Thesis::DEGREES as $option)
                        <option value="{{ $option }}" @selected(old('degree', $thesis->degree) === $option)>
                            {{ __('theses.degrees.'.$option) }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="field">
                <label for="supervisor">{{ __('admin.theses.table.supervisor') }}</label>
                <input type="text" id="supervisor" name="supervisor" maxlength="190" dir="auto"
                       value="{{ old('supervisor', $thesis->supervisor) }}">
            </div>

            <div class="field">
                <label for="co_supervisor">{{ __('admin.theses.table.co_supervisor') }}</label>
                <input type="text" id="co_supervisor" name="co_supervisor" maxlength="190" dir="auto"
                       value="{{ old('co_supervisor', $thesis->co_supervisor) }}">
            </div>

            <div class="field">
                <label for="department_id">{{ __('admin.theses.table.department') }}</label>
                <select id="department_id" name="department_id">
                    <option value="">{{ __('admin.books.no_department') }}</option>
                    @foreach ($departments as $dept)
                        <option value="{{ $dept->id }}" @selected((string) old('department_id', $thesis->department_id) === (string) $dept->id)>
                            {{ $dept->translation('ku-sorani', 'title') }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="field">
                <label for="year">{{ __('admin.theses.table.year') }} *</label>
                <input type="number" id="year" name="year" required dir="ltr" min="1970" max="{{ (int) date('Y') + 1 }}"
                       value="{{ old('year', $thesis->year) }}">
            </div>

            <div class="field">
                <label for="defended_on">{{ __('admin.theses.table.defended_on') }}</label>
                <input type="date" id="defended_on" name="defended_on" dir="ltr"
                       value="{{ old('defended_on', $thesis->defended_on?->toDateString()) }}">
            </div>

            <div class="field">
                <label for="language">{{ __('admin.theses.table.language') }}</label>
                <input type="text" id="language" name="language" maxlength="40" dir="auto"
                       value="{{ old('language', $thesis->language) }}">
            </div>

            <div class="field">
                <label for="pages">{{ __('admin.theses.table.pages') }}</label>
                <input type="number" id="pages" name="pages" dir="ltr" min="1" max="65535"
                       value="{{ old('pages', $thesis->pages) }}">
            </div>

            <div class="field">
                <label for="doi">{{ __('admin.theses.table.doi') }}</label>
                <input type="text" id="doi" name="doi" maxlength="255" dir="ltr"
                       value="{{ old('doi', $thesis->doi) }}">
            </div>

            <div class="field">
                <label for="license">{{ __('admin.theses.table.license') }}</label>
                <select id="license" name="license">
                    <option value="">—</option>
                    @foreach (Thesis::LICENCES as $option)
                        <option value="{{ $option }}" @selected(old('license', $thesis->license) === $option)>
                            {{ __('theses.licences.'.$option) }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="field">
                <label for="keywords">{{ __('admin.theses.table.keywords') }}</label>
                <input type="text" id="keywords" name="keywords" maxlength="500" dir="auto"
                       value="{{ old('keywords', $thesis->keywords) }}">
                <div class="hint">{{ __('admin.books.table.keywords_hint') }}</div>
            </div>

            <div class="field field-wide">
                <label for="abstract">{{ __('admin.theses.table.abstract') }}</label>
                <textarea id="abstract" name="abstract" rows="5" maxlength="8000" dir="auto">{{ old('abstract', $thesis->abstract) }}</textarea>
            </div>

            <div class="field field-wide">
                <label for="abstract_en">{{ __('admin.theses.table.abstract_en') }}</label>
                <textarea id="abstract_en" name="abstract_en" rows="5" maxlength="8000" dir="ltr">{{ old('abstract_en', $thesis->abstract_en) }}</textarea>
                <div class="hint">{{ __('admin.theses.abstract_en_hint') }}</div>
            </div>

            </div>

            <fieldset class="publish-box">
                <legend>{{ __('admin.theses.publishing') }}</legend>

                <div class="field-grid">
                    <div class="field">
                        <label for="status">{{ __('admin.theses.table.status') }} *</label>
                        <select id="status" name="status" required>
                            @foreach (Thesis::STATUSES as $option)
                                <option value="{{ $option }}" @selected(old('status', $thesis->status) === $option)>
                                    {{ __('admin.theses.statuses.'.$option) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="field">
                        <label for="embargo_until">{{ __('admin.theses.table.embargo_until') }}</label>
                        <input type="date" id="embargo_until" name="embargo_until" dir="ltr"
                               value="{{ old('embargo_until', $thesis->embargo_until?->toDateString()) }}">
                        <div class="hint">{{ __('admin.theses.embargo_hint') }}</div>
                    </div>
                </div>

                @if ($thesis->zenodo_url)
                    {{-- Shown, not editable: the deposit happened and cannot
                         be undone, so there is nothing here to change. --}}
                    <p class="hint">
                        {{ __('admin.theses.deposited', ['date' => $thesis->deposited_at?->toDateString() ?? '—']) }}
                        <a href="{{ $thesis->zenodo_url }}" target="_blank" rel="noopener">{{ $thesis->doi }}</a>
                    </p>
                @endif

                @if ($thesis->approved_at)
                    <p class="hint">
                        {{ __('admin.theses.approved_by', [
                            'name' => $thesis->approver?->name ?? '—',
                            'date' => $thesis->approved_at->toDayDateTimeString(),
                        ]) }}
                    </p>
                @endif
            </fieldset>

            <div class="field">
                <label for="file">{{ __('admin.books.file') }}</label>
                <input type="file" id="file" name="file" accept="application/pdf">
                <div class="hint">{{ __('admin.theses.file_hint') }}</div>
                @if ($thesis->hasFile())
                    <div class="hint">{{ $thesis->humanFileSize() }}</div>
                @endif
            </div>

            <div class="field">
                <label for="url">{{ __('admin.books.table.url') }}</label>
                <input type="url" id="url" name="url" maxlength="500" dir="ltr"
                       value="{{ old('url', $thesis->url) }}">
            </div>

            <button type="submit" class="btn btn-primary">{{ __('admin.actions.save') }}</button>
        </form>
    </div>
@endsection
