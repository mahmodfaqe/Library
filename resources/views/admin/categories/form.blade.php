@extends('admin.layout')

@section('title', $category->exists ? __('admin.categories.edit_title') : __('admin.categories.new_title'))

@section('content')
    <div class="card">
        <div class="card-head">
            <h2>{{ $category->exists ? __('admin.categories.edit_title') : __('admin.categories.new_title') }}</h2>
            <a href="{{ route('admin.categories') }}" class="btn btn-secondary">{{ __('admin.actions.back') }}</a>
        </div>

        <form method="POST" action="{{ $category->exists ? route('admin.categories.update', $category) : route('admin.categories.store') }}">
            @csrf
            @if ($category->exists)
                @method('PUT')
            @endif

            <div class="field">
                <label for="sort_order">{{ __('admin.categories.table.order') }}</label>
                <input type="number" id="sort_order" name="sort_order" min="0" required
                       value="{{ old('sort_order', $category->sort_order ?? 0) }}">
                <div class="hint">{{ __('admin.categories.order_hint') }}</div>
            </div>

            <div class="field">
                <label for="icon">{{ __('admin.categories.table.icon') }}</label>
                <input type="text" id="icon" name="icon" maxlength="16" placeholder="🧬"
                       value="{{ old('icon', $category->icon) }}">
            </div>

            <hr style="border:none; border-top:1px solid #eef0f8; margin:1.4rem 0;">

            <h3 style="margin-bottom:0.4rem; color:#4a4a5c; font-size:1rem;">{{ __('admin.categories.names_heading') }}</h3>
            <p style="color:#6b6b80; font-size:0.85rem; margin-bottom:1rem;">{{ __('admin.categories.names_hint') }}</p>

            @foreach (config('departments.locales') as $locale)
                @php $lang = $locale['lang']; @endphp
                <div class="field">
                    <label for="name-{{ $lang }}">
                        {{ $locale['label'] }}
                        @if ($lang === \App\Support\Locale::DEFAULT)
                            <span style="color:#dc2626;">*</span>
                        @endif
                    </label>
                    @if ($lang === \App\Support\Locale::DEFAULT)
                        <input type="text" id="name-{{ $lang }}" name="name" required maxlength="120"
                               dir="{{ $locale['dir'] }}" value="{{ old('name', $category->name) }}">
                    @else
                        <input type="text" id="name-{{ $lang }}" name="translations[{{ $lang }}]" maxlength="120"
                               dir="{{ $locale['dir'] }}"
                               placeholder="{{ $category->name }}"
                               value="{{ old("translations.$lang", $category->translations[$lang] ?? '') }}">
                    @endif
                </div>
            @endforeach

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">{{ $category->exists ? __('admin.actions.save') : __('admin.actions.create') }}</button>
                <a href="{{ route('admin.categories') }}" class="btn btn-secondary">{{ __('admin.actions.cancel') }}</a>
            </div>
        </form>
    </div>
@endsection
