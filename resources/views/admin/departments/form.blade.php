@extends('admin.layout')

@section('title', $department->exists ? 'دەستکاری بەش' : 'بەشی نوێ')

@section('content')
    <div class="card">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1.2rem;">
            <h2 style="font-size:1.15rem;">{{ $department->exists ? 'دەستکاری بەش' : 'زیادکردنی بەشی نوێ' }}</h2>
            <a href="{{ route('admin.index') }}" class="btn btn-secondary">گەڕانەوە</a>
        </div>

        <form method="POST"
              action="{{ $department->exists ? route('admin.departments.update', $department) : route('admin.departments.store') }}">
            @csrf
            @if ($department->exists)
                @method('PUT')
            @endif

            <div class="field">
                <label for="sort_order">ڕیز (sort order)</label>
                <input type="number" id="sort_order" name="sort_order" min="0" required
                       value="{{ old('sort_order', $department->sort_order ?? '') }}">
            </div>

            <div class="field">
                <label for="icon">ئایکۆن (ئیمۆجی)</label>
                <input type="text" id="icon" name="icon" maxlength="16" required
                       placeholder="🧬"
                       value="{{ old('icon', $department->icon ?? '') }}">
                <div class="hint">ئیمۆجییەکە لەسەر کارتەکە نیشان دەدرێت.</div>
            </div>

            <div class="field">
                <label for="drive_url">بەستەری درایڤ (Drive URL)</label>
                <input type="url" id="drive_url" name="drive_url" maxlength="500" required dir="ltr"
                       placeholder="https://drive.google.com/drive/folders/..."
                       value="{{ old('drive_url', $department->drive_url ?? '') }}">
            </div>

            <hr style="border:none; border-top:1px solid #eef0f8; margin:1.4rem 0;">

            <h3 style="margin-bottom:1rem; color:#4a4a5c; font-size:1rem;">وەرگێڕانەکان (بە هەموو زمانەکان)</h3>

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
                        <label for="t-{{ $lang }}-title">ناو (Title)</label>
                        <input type="text" id="t-{{ $lang }}-title" name="translations[{{ $lang }}][title]"
                               maxlength="255" required dir="{{ $locale['dir'] }}"
                               value="{{ $t['title'] ?? '' }}">
                    </div>
                    <div class="field">
                        <label for="t-{{ $lang }}-desc">وەسف (Description)</label>
                        <textarea id="t-{{ $lang }}-desc" name="translations[{{ $lang }}][desc]"
                                  maxlength="1000" required rows="2" dir="{{ $locale['dir'] }}">{{ $t['desc'] ?? '' }}</textarea>
                    </div>
                    <div class="field">
                        <label for="t-{{ $lang }}-button">دەقی دوگمە (Button text)</label>
                        <input type="text" id="t-{{ $lang }}-button" name="translations[{{ $lang }}][button]"
                               maxlength="120" required dir="{{ $locale['dir'] }}"
                               value="{{ $t['button'] ?? '' }}">
                    </div>
                </div>
            @endforeach

            <div style="display:flex; gap:0.6rem; margin-top:1rem;">
                <button type="submit" class="btn btn-primary">{{ $department->exists ? 'پاشەکەوتکردن' : 'زیادکردن' }}</button>
                <a href="{{ route('admin.index') }}" class="btn btn-secondary">پاشەکشە</a>
            </div>
        </form>
    </div>
@endsection
