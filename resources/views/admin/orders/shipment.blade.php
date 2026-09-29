@extends('layouts.admin')

@section('title', __('Shipment').' '.$order->order_number)

@section('content')
    @if(session('courier_shipment_summary'))
        @include('admin.orders.partials.shipment-summary', ['summary' => session('courier_shipment_summary')])
    @endif
    <div class="mb-3">
        <a href="{{ route('admin.orders.index') }}" class="btn btn-sm btn-outline-secondary">{{ __('Back to orders') }}</a>
        <a href="{{ route('admin.orders.show', $order) }}" class="btn btn-sm btn-outline-dark">{{ __('Order details') }}</a>
    </div>

    @forelse($order->shipments as $shipment)
        <div class="card border-0 shadow-sm admin-data-card mb-3">
            <div class="card-body">
                <div class="d-flex flex-wrap justify-content-between gap-2 mb-3">
                    <div>
                        <div class="fw-semibold">{{ $shipment->partnerLabel() }}</div>
                        <div class="small text-muted">AWB: {{ $shipment->trackingNumber() ?: '—' }} · {{ $shipment->statusLabel() }}</div>
                    </div>
                    <div class="d-flex flex-wrap gap-2">
                        <form method="post" action="{{ route('admin.orders.shipments.track', $order) }}">@csrf
                            <button class="btn btn-sm btn-outline-primary" type="submit">{{ __('Track') }}</button>
                        </form>
                        @if($shipment->status === \App\Enums\ShipmentStatus::FAILED)
                            <form method="post" action="{{ route('admin.orders.shipments.retry', $order) }}">@csrf
                                <button class="btn btn-sm btn-primary" type="submit">{{ __('Retry') }}</button>
                            </form>
                        @endif
                        @if($shipment->isActive())
                            <form method="post" action="{{ route('admin.orders.shipments.cancel', $order) }}" onsubmit="return confirm('Cancel this shipment?');">@csrf
                                <button class="btn btn-sm btn-outline-danger" type="submit">{{ __('Cancel') }}</button>
                            </form>
                        @endif
                    </div>
                </div>
                @if($shipment->failure_reason)
                    <div class="alert alert-danger py-2">{{ $shipment->failure_reason }}</div>
                @endif
                @if($shipment->trackingEvents->isNotEmpty())
                    <ul class="list-unstyled small mb-0">
                        @foreach($shipment->trackingEvents as $event)
                            <li class="mb-1">
                                <strong>{{ \App\Enums\ShipmentStatus::label($event->status) }}</strong>
                                @if($event->location) · {{ $event->location }} @endif
                                @if($event->event_time) · {{ $event->event_time->format('d M Y, h:i A') }} @endif
                                @if($event->description)<div class="text-muted">{{ $event->description }}</div>@endif
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>
    @empty
        <div class="alert alert-secondary">{{ __('No shipments yet for this order.') }}</div>
    @endforelse
@endsection
