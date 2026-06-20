@extends('layouts.account')

@section('account_title', __('Order :num', ['num' => $order->order_number]))

@push('breadcrumb')
    @include('market.partials.breadcrumbs', [
        'title' => $order->order_number,
        'items' => [
            ['label' => __('My orders'), 'url' => route('orders.index')],
            ['label' => $order->order_number],
        ],
    ])
@endpush

@section('account_content')
@php
    $methodKey = strtolower((string) $order->payment_method);
    $methodLabels = [
        'cod' => __('Cash on delivery'),
        'razorpay' => __('Razorpay'),
    ];
    $methodLabel = $methodLabels[$methodKey] ?? ucfirst($methodKey);
    $payStatusKey = strtolower((string) $order->payment_status);
    $itemCount = $order->items->count();
@endphp
<div class="pro-order-detail">
    <a href="{{ route('orders.index') }}" class="pro-order-detail__back">
        <i class="bi bi-arrow-left" aria-hidden="true"></i>{{ __('Back to orders') }}
    </a>

    <header class="pro-order-detail__hero">
        <div class="pro-order-detail__hero-glow" aria-hidden="true"></div>
        <div class="pro-order-detail__hero-inner">
            <p class="pro-order-detail__eyebrow">{{ __('Order details') }}</p>
            <h1 class="pro-order-detail__title">
                <span class="pro-order-detail__order-label">{{ __('Order') }}</span>
                <span class="pro-order-detail__order-id">{{ $order->order_number }}</span>
            </h1>
            <div class="pro-order-detail__meta">
                <span class="pro-order-detail__pill"><i class="bi bi-calendar3" aria-hidden="true"></i>{{ $order->created_at->format('d M Y') }}</span>
                <span class="pro-order-detail__pill"><i class="bi bi-clock" aria-hidden="true"></i>{{ $order->created_at->format('h:i A') }}</span>
            </div>
        </div>
    </header>

    {{-- Row 1: journey + address --}}
    <div class="row g-4 pro-order-detail__row-top">
        <div class="col-lg-6">
            <div class="pro-order-detail__card pro-order-detail__card--timeline">
                <div class="pro-order-detail__card-head">
                    <div class="pro-order-detail__card-icon" aria-hidden="true"><i class="bi bi-signpost-split"></i></div>
                    <div>
                        <h2 class="pro-order-detail__card-title">{{ __('Order journey') }}</h2>
                        <p class="pro-order-detail__card-sub">{{ __('Track progress from placement to delivery.') }}</p>
                    </div>
                </div>

                @if($order->status === 'cancelled')
                    <div class="pro-order-detail__cancel-banner">
                        <i class="bi bi-x-octagon-fill" aria-hidden="true"></i>
                        <div>
                            <strong>{{ __('Order cancelled') }}</strong>
                            <p class="mb-0 small">{{ __('This order is no longer active. Contact support if you need help.') }}</p>
                        </div>
                    </div>
                @else
                    @php
                        $opts = \App\Models\Order::adminStatusOptions();
                        $timelineKeys = \App\Models\Order::fulfillmentTimelineKeys();
                        $currentIdx = array_search($order->status, $timelineKeys, true);
                    @endphp
                    <ol class="pro-order-detail__steps">
                        @foreach($timelineKeys as $idx => $key)
                            @php
                                $done = $currentIdx !== false && $idx < $currentIdx;
                                $current = $order->status === $key;
                                $pending = ! $done && ! $current;
                            @endphp
                            <li class="pro-order-detail__step {{ $done ? 'is-done' : '' }} {{ $current ? 'is-current' : '' }} {{ $pending ? 'is-pending' : '' }}">
                                <div class="pro-order-detail__step-track">
                                    <span class="pro-order-detail__step-dot" aria-hidden="true">
                                        @if($done)
                                            <i class="bi bi-check-lg"></i>
                                        @elseif($current)
                                            <i class="bi bi-record-fill"></i>
                                        @else
                                            <span class="pro-order-detail__step-num">{{ $loop->iteration }}</span>
                                        @endif
                                    </span>
                                    @unless($loop->last)
                                        <span class="pro-order-detail__step-line" aria-hidden="true"></span>
                                    @endunless
                                </div>
                                <div class="pro-order-detail__step-body">
                                    <span class="pro-order-detail__step-label">{{ $opts[$key] ?? $key }}</span>
                                    @if($current)
                                        <span class="pro-order-detail__step-tag">{{ __('You are here') }}</span>
                                    @endif
                                </div>
                            </li>
                        @endforeach
                    </ol>
                @endif

                <div class="pro-order-detail__status-chip">
                    <span class="pro-order-detail__status-chip-dot" aria-hidden="true"></span>
                    <span>{{ __('Current status') }}: <strong>{{ \App\Models\Order::statusLabel($order->status) }}</strong></span>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="pro-order-detail__card pro-order-detail__card--address">
                <div class="pro-order-detail__card-head pro-order-detail__card-head--compact">
                    <div class="pro-order-detail__card-icon pro-order-detail__card-icon--geo" aria-hidden="true"><i class="bi bi-geo-alt"></i></div>
                    <div>
                        <h2 class="pro-order-detail__card-title">{{ __('Delivery address') }}</h2>
                        <p class="pro-order-detail__card-sub">{{ __('Shipping destination for this order') }}</p>
                    </div>
                </div>
                @if($order->address)
                    <div class="pro-order-detail__address-block">
                        <div class="pro-order-detail__address-name">{{ $order->address->name }}</div>
                        <div class="pro-order-detail__address-lines">{{ $order->address->line1 }}</div>
                        @if($order->address->line2)
                            <div class="pro-order-detail__address-lines">{{ $order->address->line2 }}</div>
                        @endif
                        <div class="pro-order-detail__address-lines">{{ $order->address->city }}, {{ $order->address->state }} {{ $order->address->pincode }}</div>
                        @if($order->address->phone)
                            <div class="pro-order-detail__address-phone"><i class="bi bi-telephone" aria-hidden="true"></i>{{ $order->address->phone }}</div>
                        @endif
                    </div>
                @else
                    <p class="text-muted mb-0">{{ __('No address on file.') }}</p>
                @endif
            </div>
        </div>
    </div>

    {{-- Row 2: items (left) + payment (right), return under items --}}
    <div class="row g-4 align-items-start pro-order-detail__row-main">
        <div class="col-lg-7">
            <section class="pro-order-detail__items-section" aria-labelledby="pro-order-items-heading">
                <h2 class="pro-order-detail__section-title mb-3" id="pro-order-items-heading">{{ __('Items in this order') }}</h2>
                <ul class="pro-order-detail__item-list list-unstyled mb-0">
                    @foreach($order->items as $it)
                        @php $initial = \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr(trim($it->product_name) ?: '?', 0, 1)); @endphp
                        <li class="pro-order-detail__item-row">
                            <span class="pro-order-detail__item-thumb" aria-hidden="true">{{ $initial }}</span>
                            <div class="pro-order-detail__item-main">
                                <span class="pro-order-detail__item-name">{{ $it->product_name }}</span>
                                <span class="pro-order-detail__item-meta">{{ $it->variant_label }} · {{ __('Qty') }} {{ $it->qty }}</span>
                            </div>
                            <span class="pro-order-detail__item-price">₹{{ number_format($it->line_total, 2) }}</span>
                        </li>
                    @endforeach
                </ul>
            </section>

            <section class="pro-order-detail__return mt-4" aria-labelledby="pro-order-return-heading">
                <h2 class="pro-order-detail__return-title" id="pro-order-return-heading"><i class="bi bi-arrow-return-left me-2" aria-hidden="true"></i>{{ __('Need a return?') }}</h2>
                @if(!empty($returnRequest))
                    <p class="small mb-2">
                        {{ __('Return status') }}:
                        <span class="badge rounded-pill {{ match($returnRequest->normalizedStatus()) {
                            'pending' => 'text-bg-warning',
                            'under_review' => 'text-bg-info',
                            'approved' => 'text-bg-success',
                            'rejected' => 'text-bg-danger',
                            'refunded' => 'text-bg-primary',
                            'cancelled' => 'text-bg-secondary',
                            default => 'text-bg-secondary',
                        } }}">{{ $returnRequest->statusLabel() }}</span>
                    </p>
                    @if(filled($returnRequest->admin_note))
                        <p class="small text-muted mb-2">{{ $returnRequest->admin_note }}</p>
                    @endif
                @else
                    <p class="pro-order-detail__return-hint small">{{ __('Tell us why — we will review your request.') }}</p>
                    <form action="{{ route('orders.return', $order) }}" method="post" class="pro-order-detail__return-form">
                        @csrf
                        <label class="form-label small fw-semibold" for="returnReason">{{ __('Reason') }}</label>
                        <textarea id="returnReason" name="reason" class="form-control pro-order-detail__return-textarea" rows="3" required placeholder="{{ __('e.g. Wrong size, damaged packaging…') }}"></textarea>
                        <button class="btn pro-order-detail__return-btn" type="submit">{{ __('Submit return request') }}</button>
                    </form>
                @endif
            </section>
        </div>

        <div class="col-lg-5">
            <div class="pro-order-detail__card pro-order-detail__card--billing pro-order-detail__billing-aside">
                <div class="pro-order-detail__billing-head">
                    <div class="pro-order-detail__billing-head-left">
                        <div class="pro-order-detail__card-icon pro-order-detail__card-icon--wallet" aria-hidden="true"><i class="bi bi-credit-card-2-front"></i></div>
                        <div>
                            <h2 class="pro-order-detail__card-title mb-0">{{ __('Payment & total') }}</h2>
                            <p class="pro-order-detail__card-sub mb-0 mt-1">{{ __('Order summary') }}</p>
                        </div>
                    </div>
                    <span class="pro-order-detail__pay-items-badge">{{ $itemCount }} {{ strtoupper($itemCount === 1 ? __('item') : __('items')) }}</span>
                </div>

                <div class="pro-order-detail__pay-row">
                    <span>{{ __('Method') }}</span>
                    <span class="pro-order-detail__pay-method">{{ $methodLabel }}</span>
                </div>
                <div class="pro-order-detail__pay-row">
                    <span>{{ __('Payment status') }}</span>
                    <span class="pro-order-detail__pay-badge pro-order-detail__pay-badge--{{ $payStatusKey === 'paid' ? 'paid' : 'pending' }}">{{ strtoupper($order->payment_status) }}</span>
                </div>
                <div class="pro-order-detail__breakdown">
                    <div class="pro-order-detail__break-line">
                        <span>{{ __('Subtotal') }}</span>
                        <span>₹{{ number_format($order->subtotal + $order->tax_amount, 2) }}</span>
                    </div>
                    <div class="pro-order-detail__break-line">
                        <span>{{ __('Shipping') }}</span>
                        <span>₹{{ number_format($order->shipping, 2) }}</span>
                    </div>
                    <div class="pro-order-detail__break-line">
                        <span>{{ __('Discount') }}</span>
                        <span class="text-success">− ₹{{ number_format($order->discount, 2) }}</span>
                    </div>
                    @if($order->wallet_used > 0)
                        <div class="pro-order-detail__break-line">
                            <span>{{ __('Wallet used') }}</span>
                            <span>₹{{ number_format($order->wallet_used, 2) }}</span>
                        </div>
                    @endif
                </div>
                <div class="pro-order-detail__total-bar">
                    <span>{{ __('Total paid') }}</span>
                    <span class="pro-order-detail__total-amt">₹{{ number_format($order->total, 2) }}</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
