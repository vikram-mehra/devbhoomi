@extends('layouts.account')

@section('account_title', __('My orders'))

@push('breadcrumb')
    @include('market.partials.breadcrumbs', [
        'title' => __('My orders'),
        'items' => [['label' => __('My orders')]],
    ])
@endpush

@php
    $paymentMethodLabels = [
        'cod' => __('Cash on delivery'),
        'razorpay' => 'Razorpay',
    ];
@endphp

@section('account_content')
    @if($orders->isEmpty())
        <p class="text-muted mb-0">{{ __('You have not placed any orders yet.') }}</p>
    @else
        <div class="account-orders__card">
            <div class="table-responsive">
                <table class="table account-orders__table mb-0">
                    <thead>
                        <tr>
                            <th scope="col">{{ __('Order Number') }}</th>
                            <th scope="col">{{ __('Date') }}</th>
                            <th scope="col">{{ __('Amount') }}</th>
                            <th scope="col">{{ __('Payment Status') }}</th>
                            <th scope="col">{{ __('Payment Method') }}</th>
                            <th scope="col">{{ __('Option') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($orders as $o)
                            @php
                                $ps = strtolower((string) $o->payment_status);
                                if ($ps === 'pending') {
                                    $badgeClass = 'account-orders-badge account-orders-badge--pending';
                                } elseif ($ps === 'paid') {
                                    $badgeClass = 'account-orders-badge account-orders-badge--paid';
                                } elseif ($ps === 'failed' || $ps === 'refunded') {
                                    $badgeClass = 'account-orders-badge account-orders-badge--danger';
                                } else {
                                    $badgeClass = 'account-orders-badge account-orders-badge--muted';
                                }
                                $payLabel = $paymentMethodLabels[$o->payment_method] ?? strtoupper(str_replace('_', ' ', (string) $o->payment_method));
                            @endphp
                            <tr>
                                <td data-label="{{ __('Order Number') }}">
                                    <span class="account-orders__order-num">#{{ $o->order_number }}</span>
                                </td>
                                <td data-label="{{ __('Date') }}">
                                    <div class="account-orders__date-line">{{ $o->created_at->format('d M Y') }}</div>
                                    <div class="account-orders__date-time">{{ $o->created_at->format('h:i A') }}</div>
                                </td>
                                <td data-label="{{ __('Amount') }}">
                                    <span class="account-orders__amount">₹{{ number_format((float) $o->total, 2) }}</span>
                                </td>
                                <td data-label="{{ __('Payment Status') }}">
                                    <span class="{{ $badgeClass }}">{{ ucfirst($o->payment_status) }}</span>
                                </td>
                                <td data-label="{{ __('Payment Method') }}">
                                    {{ $payLabel }}
                                </td>
                                <td class="account-orders__td-option" data-label="{{ __('Option') }}">
                                    <a href="{{ route('orders.show', $o) }}" class="account-orders__view-btn" title="{{ __('View order') }}" aria-label="{{ __('View order') }}">
                                        <i class="bi bi-eye" aria-hidden="true"></i>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="account-orders__pagination mt-4">
            {{ $orders->links() }}
        </div>
    @endif
@endsection
