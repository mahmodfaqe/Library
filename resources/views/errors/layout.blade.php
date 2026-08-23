@use('App\Support\Locale')
@extends('layouts.base')

@section('title', __("errors.$code.title").' — '.__('messages.site_title'))
@section('description', __("errors.$code.body"))

@push('head')
    {{-- An error page has nothing worth indexing. --}}
    <meta name="robots" content="noindex, nofollow">
@endpush

@section('content')
<div class="error-page">
    <p class="error-code" aria-hidden="true">{{ $code }}</p>
    <h1 class="error-title">{{ __("errors.$code.title") }}</h1>
    <p class="error-body">{{ __("errors.$code.body") }}</p>

    <div class="error-actions">
        <a href="{{ Locale::url() }}" class="section-btn error-home">{{ __('privacy.back') }}</a>
        <a href="{{ Locale::booksUrl() }}" class="error-link">{{ __('books.title') }}</a>
    </div>
</div>
@endsection
