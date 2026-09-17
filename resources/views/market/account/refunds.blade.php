@extends('layouts.account')

@section('account_title', __('Refund history'))

@push('breadcrumb')
    @include('market.partials.breadcrumbs', [
        'title' => __('Refund history'),
        'items' => [
            ['label' => __('My account'), 'url' => route('account.dashboard')],
            ['label' => __('Refund history')],
        ],
    ])
@endpush

@section('account_content')
    @if($returns->isEmpty())
        @include('market.partials.empty-state', [
            'icon' => 'bi-arrow-counterclockwise',
            'title' => __('No refund history'),
            'text' => __('You have no return or refund requests yet. If you need to return an order, you can start a request from your order details.'),
            'cta' => __('View my orders'),
            'ctaUrl' => route('orders.index'),
        ])
    @else
        <h1 class="h5 fw-bold mb-2">{{ __('Refund history') }}</h1>
        <p class="text-muted small mb-4">{{ __('Returns and refund requests linked to your orders.') }}</p>

        @foreach($returns as $ret)
            <div class="border-bottom py-3">
                <div class="d-flex flex-wrap justify-content-between gap-2 align-items-start">
                    <div>
                        <span class="fw-semibold">{{ __('Order') }} #{{ $ret->order->order_number ?? $ret->order_id }}</span>
                        <span class="badge rounded-pill ms-1 {{ match($ret->normalizedStatus()) {
                            'pending' => 'text-bg-warning',
                            'under_review' => 'text-bg-info',
                            'approved' => 'text-bg-success',
                            'rejected' => 'text-bg-danger',
                            'refunded' => 'text-bg-primary',
                            'cancelled' => 'text-bg-secondary',
                            default => 'text-bg-secondary',
                        } }}">{{ $ret->statusLabel() }}</span>
                    </div>
                    <div class="small text-muted">{{ $ret->created_at->format('M j, Y') }}</div>
                </div>
                @if($ret->reason)
                    <p class="small mb-0 mt-2 text-muted"><strong>{{ __('Reason') }}:</strong> {{ $ret->reason }}</p>
                @endif
                @if(filled($ret->admin_note))
                    <p class="small mb-0 mt-1 text-muted"><strong>{{ __('Update') }}:</strong> {{ $ret->admin_note }}</p>
                @endif
            </div>
        @endforeach

        <div class="mt-3">
            {{ $returns->withQueryString()->links() }}
        </div>
    @endif
@endsection
