@extends('layouts.account')

@section('account_title', __('Track order'))
@section('meta_description', __('Track your Devbhoomi Naturals shipment with your order number and email or mobile.'))
@section('canonical', route('orders.track'))

@push('breadcrumb')
    @include('market.partials.breadcrumbs', [
        'title' => __('Track order'),
        'items' => [
            ['label' => __('My account'), 'url' => route('account.dashboard')],
            ['label' => __('Track order')],
        ],
    ])
@endpush

@section('account_content')
@php
    $shipment = $shipment ?? null;
@endphp
<div class="pro-track">
    <p class="pro-track__lead mb-3">{{ __('Enter your order number and the email or mobile used at checkout.') }}</p>

    @if(session('track_error'))
        <div class="alert alert-danger" role="alert">{{ session('track_error') }}</div>
    @endif

    <form method="post" action="{{ route('orders.track.lookup') }}" class="pro-track__form" id="proTrackForm">
        @csrf
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label fw-semibold" for="trackOrderNumber">{{ __('Order number') }}</label>
                <input type="text" name="order_number" id="trackOrderNumber" value="{{ old('order_number') }}" class="form-control @error('order_number') is-invalid @enderror" required autocomplete="off" placeholder="{{ __('e.g. 100001') }}">
                @error('order_number')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold" for="trackContact">{{ __('Email or mobile') }}</label>
                <input type="text" name="contact" id="trackContact" value="{{ old('contact') }}" class="form-control @error('contact') is-invalid @enderror" required autocomplete="off" placeholder="{{ __('Registered email or 10-digit mobile') }}">
                @error('contact')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>
        <button type="submit" class="btn btn-primary rounded-pill mt-3 px-4">{{ __('Track shipment') }}</button>
    </form>

    @if($shipment)
        <section class="pro-track__result" aria-labelledby="track-result-heading">
            <div class="pro-track__result-head">
                <div>
                    <p class="pro-track__result-kicker">{{ __('Shipment status') }}</p>
                    <h2 class="pro-track__result-title" id="track-result-heading">{{ __('Order') }} {{ $shipment['order_number'] }}</h2>
                </div>
                <span class="pro-track__status">{{ $shipment['status_label'] }}</span>
            </div>

            <div class="pro-track__meta">
                <div>
                    <span>{{ __('Courier') }}</span>
                    <strong>
                        {{ $shipment['courier'] }}
                        @if(($shipment['courier_env'] ?? null) === 'test')
                            <span class="pro-track__env">{{ __('Test') }}</span>
                        @endif
                    </strong>
                </div>
                <div>
                    <span>{{ __('AWB / Waybill') }}</span>
                    <strong>{{ $shipment['tracking_id'] ?: __('Assigned after dispatch') }}</strong>
                </div>
                <div>
                    <span>{{ __('Current location') }}</span>
                    <strong>{{ $shipment['location'] ?: __('Not available yet') }}</strong>
                </div>
                <div>
                    <span>{{ __('Last updated') }}</span>
                    <strong>
                        @if($shipment['last_updated'])
                            {{ $shipment['last_updated']->tz('Asia/Kolkata')->format('d M Y, h:i A') }}
                        @else
                            {{ __('Waiting for courier scans') }}
                        @endif
                    </strong>
                </div>
                <div>
                    <span>{{ __('From') }}</span>
                    <strong>{{ $shipment['origin'] }}</strong>
                </div>
                <div>
                    <span>{{ __('To') }}</span>
                    <strong>{{ $shipment['destination'] }}</strong>
                </div>
                @if($shipment['expected_delivery'])
                    <div>
                        <span>{{ __('Expected delivery') }}</span>
                        <strong>{{ $shipment['expected_delivery']->format('d M Y') }}</strong>
                    </div>
                @endif
            </div>
            @if(!empty($shipment['courier_track_url']))
                <div class="mt-3">
                    @include('market.partials.delhivery-track-button', ['url' => $shipment['courier_track_url']])
                </div>
            @endif

            @if(!empty($shipment['items']))
                <p class="pro-track__items">
                    {{ __('Items') }}:
                    {{ collect($shipment['items'])->map(fn ($item) => $item['name'].' ×'.$item['qty'])->implode(', ') }}
                </p>
            @endif

            <ol class="pro-track__events">
                @foreach($shipment['events'] as $event)
                    <li class="pro-track__event {{ $event['done'] ? 'is-done' : '' }} {{ $event['current'] ? 'is-current' : '' }} {{ $event['pending'] ? 'is-pending' : '' }}">
                        <span class="pro-track__event-dot" aria-hidden="true">
                            @if($event['done'])
                                <i class="bi bi-check-lg"></i>
                            @elseif($event['current'])
                                <i class="bi bi-record-fill"></i>
                            @endif
                        </span>
                        <div>
                            <div class="pro-track__event-title">{{ $event['title'] }}</div>
                            @if($event['detail'])
                                <p class="pro-track__event-detail">{{ $event['detail'] }}</p>
                            @endif
                            <p class="pro-track__event-meta">
                                @if($event['at'])
                                    <time datetime="{{ $event['at']->toIso8601String() }}">{{ $event['at']->format('d M Y, h:i A') }}</time>
                                @endif
                                @if($event['location'])
                                    <span>{{ $event['location'] }}</span>
                                @endif
                            </p>
                        </div>
                    </li>
                @endforeach
            </ol>
        </section>
    @endif
</div>
@endsection
