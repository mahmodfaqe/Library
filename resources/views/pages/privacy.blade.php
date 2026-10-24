@use('App\Support\Locale')
@extends('layouts.base')

@section('title', __('privacy.title').' — '.__('messages.site_title'))
@section('description', __('privacy.intro'))

@section('body_class', 'has-hero')

@section('content')
<x-page-hero :title="__('privacy.title')" :crumbs="[
    ['label' => __('messages.nav.home'), 'url' => Locale::url()],
    ['label' => __('privacy.title')],
]" />

<div class="max-w-[820px] mx-auto px-4" style="padding-top: var(--section-space);">

    <p class="text-[#52504e] leading-[1.9] mb-8" style="font-size:clamp(0.95rem,2.5vw,1.1rem);">{{ __('privacy.intro') }}</p>

    @foreach (['collect', 'why', 'retention', 'sharing', 'rights'] as $section)
        <section class="mb-8">
            <h2 class=" text-[#1a1a1a] mb-3" style="font-size:clamp(1.15rem,3vw,1.4rem);">
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
