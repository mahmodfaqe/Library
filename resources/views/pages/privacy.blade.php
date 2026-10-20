@use('App\Support\Locale')
@extends('layouts.base')

@section('title', __('privacy.title').' — '.__('messages.site_title'))
@section('description', __('privacy.intro'))

@section('content')
<div class="max-w-[820px] mx-auto px-4" style="padding: clamp(2rem,6vw,4rem) 1rem;">

    <nav class="mb-4 flex items-center gap-2 flex-wrap" style="font-size:0.9rem;" aria-label="{{ __('books.breadcrumb') }}">
        <a href="{{ Locale::url() }}" class="text-[#790404] no-underline hover:underline">{{ __('messages.library_name') }}</a>
        <span class="text-[#b8b3ad]">/</span>
        <span class="text-[#52504e]">{{ __('privacy.title') }}</span>
    </nav>

    <h1 class="font-bold text-[#1a1f2c] mb-6" style="font-size:clamp(1.6rem,4.5vw,2.2rem);">{{ __('privacy.title') }}</h1>

    <p class="text-[#52504e] leading-[1.9] mb-8" style="font-size:clamp(0.95rem,2.5vw,1.1rem);">{{ __('privacy.intro') }}</p>

    @foreach (['collect', 'why', 'retention', 'sharing', 'rights'] as $section)
        <section class="mb-8">
            <h2 class="font-bold text-[#1a1f2c] mb-3" style="font-size:clamp(1.15rem,3vw,1.4rem);">
                {{ __("privacy.$section.heading") }}
            </h2>
            <p class="text-[#52504e] leading-[1.9]" style="font-size:clamp(0.92rem,2.4vw,1.05rem);">
                {{ __("privacy.$section.body", ['days' => config('library.feedback_retention_days')]) }}
            </p>
        </section>
    @endforeach

    <p class="text-[#8a8785] mt-10" style="font-size:0.88rem;">{{ __('privacy.updated') }}</p>

    <a href="{{ Locale::url() }}" class="inline-block mt-6 text-[#790404] no-underline hover:underline" style="font-size:0.92rem;">
        <span aria-hidden="true">{{ Locale::isLtr() ? '←' : '→' }}</span> {{ __('privacy.back') }}
    </a>

</div>
@endsection
