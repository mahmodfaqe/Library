@use('App\Support\Asset')
@use('App\Support\Locale')
@use('App\Support\RichText')
@extends('layouts.base')

@section('body_class', 'has-hero')

@section('content')
<!-- ══════════ HERO ══════════ -->
<section id="hero-section" class="hero relative overflow-hidden flex items-center text-white transition-[background-image,opacity] duration-1000 ease-in-out"
         style="background-image: url('{{ asset('file/image1.webp') }}'); background-size: cover; background-position: center; background-repeat: no-repeat; padding: calc(var(--hero-offset) + clamp(2rem,5vw,4rem)) 0 clamp(5rem,10vw,9rem); min-height: clamp(520px,80vh,780px);">

    <div class="hero__wash absolute inset-0 z-0"></div>

    {{-- Laid out as the university lays out its own: the name on a short
         ruled line, the headline beneath it, and everything ranged to the
         reading edge rather than centred. --}}
    <div class="hdr-container relative z-20">
        <div class="hero__body">

            <p class="eyebrow eyebrow--light">{{ __('messages.university_name') }}</p>
            <p class="hero__welcome">{{ __('messages.hero.welcome') }}</p>
            <h1 class="hero__title">{{ __('messages.hero.title') }}</h1>
            <p class="hero__lede">{{ __('messages.hero.subtitle') }}</p>

            <a class="hero__cta" href="{{ Locale::booksUrl() }}">
                <span class="hero__cta-arrow" aria-hidden="true">&rarr;</span>
                {{ __('books.title') }}
            </a>

        </div>
    </div>
</section>

<!-- ══════════ THE COLLECTION IN FIGURES ══════════ -->
{{-- The same shape as the university's own data-fact-card row: a flat white
     card on the grey band, the number in the house red at weight 800 and the
     label beneath it in plain text. --}}
@if ($facts)
<section class="band band--grey">
    <div class="hdr-container">
        <div class="fact-row">
            @foreach ($facts as $key => $value)
                <div class="fact-card">
                    <span class="fact-card__count">{{ number_format($value) }}</span>
                    <span class="fact-card__title">{{ __('messages.facts.'.$key) }}</span>
                </div>
            @endforeach
        </div>
    </div>
</section>
@endif

<!-- ══════════ INTRO SECTION ══════════ -->
<section class="band band--white">
    <div class="max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-8">

        <div class="max-w-[900px] mx-auto">
            <p class="eyebrow">{{ __('messages.university_name') }}</p>
            <h2 class="section-title">{{ __('messages.intro.heading') }}</h2>

            {{-- Set straight on the band, as they set theirs: no panel, no
                 border, nothing between the reader and the words. --}}
            <div class="prose-uor reveal">
                @foreach (__('messages.intro.paragraphs') as $paragraph)
                    <p>{{ $paragraph }}</p>
                @endforeach
            </div>
        </div>

    </div>
</section>
    
<!-- ══════════ HISTORY SECTION ══════════ -->
<section class="band band--grey">
    <div class="max-w-[1280px] mx-auto px-4 sm:px-6 lg:px-8">
        <div class="max-w-[900px] mx-auto">

            <p class="eyebrow">{{ __('messages.university_name') }}</p>
            <h2 class="section-title">{{ __('messages.history.heading') }}</h2>

            <div class="prose-uor reveal">
                @php
                    $openingDate = '<strong>'.e(__('messages.history.opening_date')).'</strong>';
                @endphp
                @foreach (__('messages.history.paragraphs') as $paragraph)
                    <p>{{ RichText::make($paragraph, ['date' => $openingDate]) }}</p>
                @endforeach
            </div>

        </div>
    </div>
</section>
<!-- ══════════ LIBRARY 2 + DEPARTMENTS ══════════ -->
<section class="band band--white" style="padding-block: clamp(1rem,4vw,3rem);">
    <div class="max-w-[1280px] mx-auto px-6 sm:px-8 lg:px-10">

        <div style="margin-top:1.4rem; text-align:start;">
            <a href="{{ Locale::booksUrl() }}"
               class="section-btn inline-block text-[#f0f0f0] rounded-[5px] no-underline text-center transition-[transform,box-shadow,background,border-color,color] duration-300 hover:-translate-y-1"
               style="background:#790404; padding:clamp(0.6rem,2.2vw,0.85rem) clamp(1.5rem,4vw,2.2rem); font-size:clamp(0.88rem,2.2vw,1rem);">
                {{ __('books.title') }}
            </a>
        </div>

        <!-- Department cards (DB-driven) -->
        <div id="dept-{{ app()->getLocale() }}" style="direction:{{ Locale::dir() }};">
            <p class="eyebrow">{{ __('messages.library_name') }}</p>
            <h2 class="section-title">{{ __('messages.dept_heading') }}</h2>
            <div class="grid gap-5" style="grid-template-columns: repeat(auto-fit, minmax(260px,1fr));">
                @forelse ($subjects as $subject)
                <a href="{{ Locale::booksUrl() }}?category={{ $subject->id }}" class="dept-card reveal" dir="auto">
                    <h3 class="dept-card__name">{{ $subject->localName() }}</h3>
                    <span class="dept-card__count">
                        {{ trans_choice('books.results', $subject->books_count, ['count' => $subject->books_count]) }}
                    </span>
                </a>
                @empty
                <p class="text-center text-[#52504e]">{{ __('messages.no_departments') }}</p>
                @endforelse
            </div>
        </div>
    </div>
</section>

<!-- ══════════ FEEDBACK & SUGGESTIONS ══════════ -->
<section id="feedback" class="band band--grey">
    <div class="max-w-[760px] mx-auto px-4 sm:px-6">
        @if (session('feedback_sent'))
            <div class="mb-6 rounded-[15px] px-5 py-4 text-center font-semibold" style="background:#d1fae5; color:#065f46; font-size:clamp(0.9rem,2vw,1rem);">
                    {{ __('messages.feedback.success') }}
            </div>
        @endif
        @if ($errors->has('message'))
            <div class="mb-6 rounded-[15px] px-5 py-4 text-center font-semibold" style="background:#fee2e2; color:#991b1b; font-size:clamp(0.9rem,2vw,1rem);">
                {{ __('messages.feedback.error') }}
            </div>
        @endif
        <div class="rounded-[15px] bg-white" style="box-shadow:none; padding: clamp(1.8rem,5vw,3rem);">
            <p class="eyebrow">{{ __('messages.library_name') }}</p>
            <h2 class="section-title" style="margin-bottom:0.7rem;">{{ __('messages.feedback.title') }}</h2>
            <p class="text-[#52504e] mb-6" style="font-size:1.03rem; line-height:1.5;">
                    {{ __('messages.feedback.subtitle') }}
                <br>
                <a href="{{ route('privacy') }}" style="color:#790404; text-decoration:underline; text-underline-offset:3px; font-size:0.88em;">{{ __('privacy.title') }}</a>
            </p>
            <form method="POST" action="{{ route('feedback.store') }}" style="text-align:start;">
                @csrf
                <div class="mb-4 text-start">
                    <label for="fb-name" class="block font-semibold mb-1 text-[#393736]" style="font-size:0.9rem;">
                        {{ __('messages.feedback.name_label') }}
                    </label>
                    <input type="text" id="fb-name" name="name" maxlength="120" dir="auto" value="{{ old('name') }}"
                           class="w-full rounded-[5px] px-4 py-3" style="border:1px solid #d8d2c8; font-size:0.95rem; font-family:inherit; text-align:start;">
                </div>
                <div class="mb-5 text-start">
                    <label for="fb-msg" class="block font-semibold mb-1 text-[#393736]" style="font-size:0.9rem;">
                        {{ __('messages.feedback.message_label') }}
                    </label>
                    <textarea id="fb-msg" name="message" rows="4" maxlength="2000" required dir="auto"
                              class="w-full rounded-[5px] px-4 py-3" style="border:1px solid #d8d2c8; font-size:0.95rem; font-family:inherit; text-align:start;">{{ old('message') }}</textarea>
                </div>
                <div style="text-align:start;">
                    <button type="submit" class="section-btn relative inline-block text-[#f0f0f0] rounded-[5px] no-underline text-center transition-[transform,box-shadow,background,border-color,color] duration-300 hover:-translate-y-1 font-[inherit] cursor-pointer" style="background:#790404; padding:clamp(0.65rem,2.2vw,0.9rem) clamp(1.6rem,4vw,2.4rem); font-size:clamp(0.9rem,2.2vw,1rem); border:none;">
                        {{ __('messages.feedback.send') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</section>
@endsection

@push('head')
    <script type="application/ld+json">
    {
        "@@context": "https://schema.org",
        "@type": "Library",
        "name": "کتێبخانەی ئەلیکترۆنی کۆلێژی زانست - زانکۆی ڕاپەڕین",
        "alternateName": "Raparin Science College Electronic Library",
        "url": "{{ url('/') }}",
        "logo": "{{ asset('file/uor-logo.png') }}",
        "description": @json(__('messages.jsonld_description')),
        "inLanguage": ["ckb", "kmr", "en", "ar", "fa", "tr"],
        "isAccessibleForFree": true,
        "sameAs": ["https://github.com/mahmodfaqe/Library"]
    }
    </script>
@endpush

@push('scripts')
<script>
// Hero particles
(function(){
    var container = document.getElementById('heroParticles');
    if(!container) return;
    for(var i=0;i<22;i++){
        var p = document.createElement('div');
        p.className = 'particle';
        p.style.cssText = [
            'left:'+Math.random()*100+'%',
            'top:'+Math.random()*100+'%',
            'width:'+(Math.random()*4+2)+'px',
            'height:'+(Math.random()*4+2)+'px',
            'animation-duration:'+(Math.random()*14+8)+'s',
            'animation-delay:'+(Math.random()*-15)+'s',
            'opacity:'+(Math.random()*0.5+0.1)
        ].join(';');
        container.appendChild(p);
    }
})();

</script>
@endpush
