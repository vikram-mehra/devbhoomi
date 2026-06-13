@extends('layouts.market')

@section('title', __('Page not found'))

@push('breadcrumb')
    @include('market.partials.breadcrumbs', [
        'title' => __('Page not found'),
        'items' => [['label' => __('Page not found')]],
    ])
@endpush

@section('content')
    <section class="mk-section py-5">
        <div class="cb-container text-center py-5">
            <p class="display-1 fw-bold text-muted mb-2" aria-hidden="true">404</p>
            <h1 class="h3 fw-bold mb-2">{{ __('This page took a wrong turn') }}</h1>
            <p class="text-muted mb-4 mx-auto" style="max-width: 28rem;">
                {{ __('The link may be broken or the page may have been removed. Try search or return to the storefront.') }}
            </p>
            <div class="d-flex flex-wrap justify-content-center gap-2">
                <a href="{{ route('market.home') }}" class="btn btn-primary rounded-pill px-4">{{ __('Home') }}</a>
                <a href="{{ route('shop.search') }}" class="btn btn-outline-primary rounded-pill px-4">{{ __('Browse shop') }}</a>
            </div>
        </div>
    </section>
@endsection
