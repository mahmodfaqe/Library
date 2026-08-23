@extends('admin.layout')

@section('title', $department->exists ? __('admin.departments.edit_title') : __('admin.departments.new_title'))

@section('content')
    <div class="card">
        <div class="card-head">
            <h2>{{ $department->exists ? __('admin.departments.edit_heading') : __('admin.departments.new_heading') }}</h2>
            <a href="{{ route('admin.index') }}" class="btn btn-secondary">{{ __('admin.actions.back') }}</a>
        </div>

        <form method="POST"
              action="{{ $department->exists ? route('admin.departments.update', $department) : route('admin.departments.store') }}">
            @csrf
            @if ($department->exists)
                @method('PUT')
            @endif

            <div class="field">
                <label for="sort_order">{{ __('admin.departments.fields.sort_order') }}</label>
                <input type="number" id="sort_order" name="sort_order" min="0" required
                       value="{{ old('sort_order', $department->sort_order ?? '') }}">
            </div>

            <div class="field">
                <label for="icon">{{ __('admin.departments.fields.icon') }}</label>
                <input type="text" id="icon" name="icon" maxlength="16" required
                       placeholder="🧬"
                       value="{{ old('icon', $department->icon ?? '') }}">
                <div class="hint">{{ __('admin.departments.fields.icon_hint') }}</div>
            </div>

            <div class="field">
                <label for="drive_url">{{ __('admin.departments.fields.drive_url') }}</label>
                <input type="url" id="drive_url" name="drive_url" maxlength="500" required dir="ltr"
                       placeholder="https://drive.google.com/drive/folders/..."
                       value="{{ old('drive_url', $department->drive_url ?? '') }}">
            </div>

            <hr style="border:none; border-top:1px solid #eef0f8; margin:1.4rem 0;">

            <h3 style="margin-bottom:1rem; color:#4a4a5c; font-size:1rem;">{{ __('admin.departments.fields.translations_heading') }}</h3>

            @php
                $oldTranslations = old('translations', $department->translations ?? []);
            @endphp

            @foreach ($locales as $locale)
                @php
                    $lang = $locale['lang'];
                    $t = $oldTranslations[$lang] ?? [];
                @endphp
                <div class="lang-block">
                    <h3>{{ $locale['label'] }} <small style="color:#8a8aa0;">({{ $lang }})</small></h3>
                    <div class="field">
                        <label for="t-{{ $lang }}-title">{{ __('admin.departments.fields.name') }}</label>
                        <input type="text" id="t-{{ $lang }}-title" name="translations[{{ $lang }}][title]"
                               maxlength="255" required dir="{{ $locale['dir'] }}"
                               value="{{ $t['title'] ?? '' }}">
                    </div>
                    <div class="field">
                        <label for="t-{{ $lang }}-desc">{{ __('admin.departments.fields.desc') }}</label>
                        <textarea id="t-{{ $lang }}-desc" name="translations[{{ $lang }}][desc]"
                                  maxlength="1000" required rows="2" dir="{{ $locale['dir'] }}">{{ $t['desc'] ?? '' }}</textarea>
                    </div>
                    <div class="field">
                        <label for="t-{{ $lang }}-button">{{ __('admin.departments.fields.button') }}</label>
                        <input type="text" id="t-{{ $lang }}-button" name="translations[{{ $lang }}][button]"
                               maxlength="120" required dir="{{ $locale['dir'] }}"
                               value="{{ $t['button'] ?? '' }}">
                    </div>
                </div>
            @endforeach

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">{{ $department->exists ? __('admin.actions.save') : __('admin.actions.create') }}</button>
                <a href="{{ route('admin.index') }}" class="btn btn-secondary">{{ __('admin.actions.cancel') }}</a>
            </div>
        </form>
    </div>
@endsection
