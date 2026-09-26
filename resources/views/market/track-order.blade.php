@extends('layouts.market')

@section('title', __('Track order').' | Devbhoomi Naturals')
@section('meta_description', __('Track your Devbhoomi Naturals shipment with your order number and email or mobile.'))
@section('canonical', route('orders.track'))

@push('breadcrumb')
    @include('market.partials.breadcrumbs', [
        'title' => __('Track order'),
        'items' => [['label' => __('Track order')]],
    ])
@endpush

@section('content')
@php
    $dummyEnabled = $dummyEnabled ?? false;
    $dummy = $dummy ?? [];
    $courier = $courier ?? ['name' => 'Delhivery', 'env' => 'test', 'is_test' => true, 'configured' => false];
    $shipment = $shipment ?? null;
@endphp
<div class="pro-track">
    <header class="pro-track__intro">
        <p class="pro-section-head__eyebrow mb-1">{{ __('Help center') }}</p>
        <h1 class="pro-track__title">{{ __('Track your order') }}</h1>
        <p class="pro-track__lead">{{ __('Enter your order number and the email or mobile used at checkout.') }}</p>
    </header>

    <div class="pro-track__dummy" role="status">
        <div class="pro-track__dummy-head">
            <i class="bi bi-truck" aria-hidden="true"></i>
            <strong>{{ __('Delhivery') }}</strong>
            <span class="pro-track__env">{{ !empty($courier['is_test']) ? __('Test API') : __('Live API') }}</span>
        </div>
        @if(!empty($courier['is_test']))
            <p class="mb-2">{{ __('Tracking is connected to Delhivery’s test API. Live credentials can be switched on later.') }}</p>
        @else
            <p class="mb-2">{{ __('Tracking is connected to Delhivery’s live API.') }}</p>
        @endif
        <p class="pro-track__token-state mb-2">
            @if(!empty($courier['configured']))
                {{ __('Shipment scans are stored on this site. Track order reads our database, not Delhivery, on each visit.') }}
            @else
                {{ __('A Delhivery token is not set yet. Dummy credentials below still show a sample timeline.') }}
            @endif
        </p>
        @if($dummyEnabled)
            <p class="mb-2">{{ __('Use these dummy details to preview the module:') }}</p>
            <dl class="pro-track__dummy-creds">
                <div><dt>{{ __('Order number') }}</dt><dd><code>{{ $dummy['order'] }}</code></dd></div>
                <div><dt>{{ __('Email') }}</dt><dd><code>{{ $dummy['email'] }}</code></dd></div>
                <div><dt>{{ __('Mobile') }}</dt><dd><code>{{ $dummy['phone'] }}</code></dd></div>
                <div><dt>{{ __('Test AWB') }}</dt><dd><code>{{ $dummy['awb'] }}</code></dd></div>
            </dl>
            <button type="button" class="btn btn-outline-primary btn-sm rounded-pill js-track-fill-dummy">{{ __('Fill dummy credentials') }}</button>
        @endif
    </div>

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

@push('scripts')
<script>
(function () {
    var btn = document.querySelector('.js-track-fill-dummy');
    if (!btn) return;
    btn.addEventListener('click', function () {
        var order = document.getElementById('trackOrderNumber');
        var contact = document.getElementById('trackContact');
        if (order) order.value = @json($dummy['order'] ?? '');
        if (contact) contact.value = @json($dummy['email'] ?? '');
    });
})();
</script>
@endpush
