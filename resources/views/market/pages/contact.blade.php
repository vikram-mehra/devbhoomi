@extends('layouts.market')

@section('title', ($page->meta_title ?: $page->hero_title).' | Devbhoomi Naturals')
@section('meta_description', $page->meta_description ?: Str::limit(strip_tags($page->hero_subtitle), 155))
@if(filled($page->meta_keywords))
@section('meta_keywords', $page->meta_keywords)
@endif
@section('canonical', $page->canonical_url ?: route('pages.contact'))
@if(filled($page->og_image))
@section('og_image', $page->og_image)
@endif

@push('head')
    <link href="{{ asset('css/pages-static.css') }}?v=1" rel="stylesheet">
@endpush

@push('breadcrumb')
    @include('market.partials.breadcrumbs', [
        'title' => '',
        'items' => [['label' => $page->hero_title ?: __('Contact us')]],
    ])
@endpush

@section('content')
    <div class="pro-static-page pro-contact-page">
        <section class="pro-contact-hero">
            <div class="pro-contact-hero__glow" aria-hidden="true"></div>
            <div class="cb-container text-center">
                <h1 class="pro-contact-hero__title">{{ $page->hero_title }}</h1>
                @if($page->hero_subtitle)
                    <p class="pro-contact-hero__lead">{{ $page->hero_subtitle }}</p>
                @endif
            </div>
        </section>

        <section class="pro-contact-main">
            <div class="cb-container">
                @if(session('contact_sent'))
                    <div class="alert alert-success pro-contact-alert" role="alert">
                        <i class="bi bi-check-circle me-2" aria-hidden="true"></i>{{ __('Thank you! We received your message and will reply soon.') }}
                    </div>
                @endif

                <div class="row g-4 g-xl-5">
                    <div class="col-lg-5">
                        <div class="pro-contact-cards">
                            @if($page->email)
                                <a href="mailto:{{ $page->email }}" class="pro-contact-card">
                                    <span class="pro-contact-card__icon"><i class="bi bi-envelope" aria-hidden="true"></i></span>
                                    <span>
                                        <span class="pro-contact-card__label">{{ __('Email') }}</span>
                                        <span class="pro-contact-card__value">{{ $page->email }}</span>
                                    </span>
                                </a>
                            @endif
                            @if($page->phone)
                                <a href="tel:{{ preg_replace('/\s+/', '', $page->phone) }}" class="pro-contact-card">
                                    <span class="pro-contact-card__icon"><i class="bi bi-telephone" aria-hidden="true"></i></span>
                                    <span>
                                        <span class="pro-contact-card__label">{{ __('Phone') }}</span>
                                        <span class="pro-contact-card__value">{{ $page->phone }}</span>
                                    </span>
                                </a>
                            @endif
                            @if($page->whatsapp)
                                <a href="https://wa.me/{{ preg_replace('/\D/', '', $page->whatsapp) }}" class="pro-contact-card pro-contact-card--wa" target="_blank" rel="noopener noreferrer">
                                    <span class="pro-contact-card__icon"><i class="bi bi-whatsapp" aria-hidden="true"></i></span>
                                    <span>
                                        <span class="pro-contact-card__label">{{ __('WhatsApp') }}</span>
                                        <span class="pro-contact-card__value">{{ $page->whatsapp }}</span>
                                    </span>
                                </a>
                            @endif
                            @if($page->address)
                                <div class="pro-contact-card pro-contact-card--static">
                                    <span class="pro-contact-card__icon"><i class="bi bi-geo-alt" aria-hidden="true"></i></span>
                                    <span>
                                        <span class="pro-contact-card__label">{{ __('Address') }}</span>
                                        <span class="pro-contact-card__value">{!! nl2br(e($page->address)) !!}</span>
                                    </span>
                                </div>
                            @endif
                            @if($page->hours_weekdays || $page->hours_weekend)
                                <div class="pro-contact-card pro-contact-card--static">
                                    <span class="pro-contact-card__icon"><i class="bi bi-clock" aria-hidden="true"></i></span>
                                    <span>
                                        <span class="pro-contact-card__label">{{ __('Hours') }}</span>
                                        <span class="pro-contact-card__value">
                                            @if($page->hours_weekdays){{ $page->hours_weekdays }}<br>@endif
                                            @if($page->hours_weekend){{ $page->hours_weekend }}@endif
                                        </span>
                                    </span>
                                </div>
                            @endif
                        </div>

                        @if($page->mapEmbedSrc())
                            <div class="pro-contact-map ratio ratio-16x9 mt-4">
                                <iframe src="{{ $page->mapEmbedSrc() }}" title="{{ __('Map') }}" loading="lazy" referrerpolicy="no-referrer-when-downgrade" allowfullscreen></iframe>
                            </div>
                        @elseif($page->mapLink())
                            <a href="{{ $page->mapLink() }}" class="btn btn-outline-secondary w-100 mt-4" target="_blank" rel="noopener">{{ __('Open in Google Maps') }}</a>
                        @endif
                    </div>

                    <div class="col-lg-7">
                        <div class="pro-contact-form-wrap">
                            @if($page->form_heading)
                                <h2 class="pro-contact-form__title">{{ $page->form_heading }}</h2>
                            @endif
                            @if($page->form_subtext)
                                <p class="pro-contact-form__sub">{{ $page->form_subtext }}</p>
                            @endif

                            <form method="post" action="{{ route('pages.contact.submit') }}" class="pro-contact-form row g-3">
                                @csrf
                                <div class="col-md-6">
                                    <label class="form-label" for="c_name">{{ __('Your name') }} *</label>
                                    <input type="text" name="name" id="c_name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required maxlength="120">
                                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label" for="c_email">{{ __('Email') }} *</label>
                                    <input type="email" name="email" id="c_email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" required maxlength="255">
                                    @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label" for="c_phone">{{ __('Phone') }}</label>
                                    <input type="text" name="phone" id="c_phone" class="form-control" value="{{ old('phone') }}" maxlength="30">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label" for="c_subject">{{ __('Subject') }}</label>
                                    <input type="text" name="subject" id="c_subject" class="form-control" value="{{ old('subject') }}" maxlength="200">
                                </div>
                                <div class="col-12">
                                    <label class="form-label" for="c_message">{{ __('Message') }} *</label>
                                    <textarea name="message" id="c_message" class="form-control @error('message') is-invalid @enderror" rows="5" required maxlength="5000">{{ old('message') }}</textarea>
                                    @error('message')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-12">
                                    <button type="submit" class="btn pro-contact-submit">{{ __('Send message') }} <i class="bi bi-send" aria-hidden="true"></i></button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection
