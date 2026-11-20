@extends('layouts.admin')

@section('title', __('Coupon usage').' — '.$coupon->code)

@section('content')
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
        <div>
            <h1 class="h5 fw-bold mb-1 font-monospace">{{ $coupon->code }}</h1>
            <p class="text-muted small mb-0">{{ $coupon->adminStatusLabel() }} · {{ $coupon->discountLabel() }}</p>
        </div>
        <a href="{{ route('admin.coupons.index') }}" class="btn btn-sm btn-outline-secondary">{{ __('Back to coupons') }}</a>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="small text-muted">{{ __('Total usage limit') }}</div>
                    <div class="fs-5 fw-bold">{{ $coupon->usage_limit ?: __('Unlimited') }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="small text-muted">{{ __('Successful uses') }}</div>
                    <div class="fs-5 fw-bold">{{ (int) $coupon->used_count }}</div>
                    <div class="small text-muted">{{ trans_choice(':count reserved|:count reserved', (int) ($coupon->reserved_count ?? 0), ['count' => (int) ($coupon->reserved_count ?? 0)]) }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="small text-muted">{{ __('Remaining uses') }}</div>
                    <div class="fs-5 fw-bold">{{ $coupon->remainingUses() === null ? '—' : $coupon->remainingUses() }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="small text-muted">{{ __('Total discount granted') }}</div>
                    <div class="fs-5 fw-bold">₹{{ number_format((float) ($coupon->total_discount_granted ?? 0), 2) }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div class="row g-3 small">
                <div class="col-md-3"><strong>{{ __('Per-user limit') }}:</strong> {{ $coupon->per_user_limit ?: __('Unlimited') }}</div>
                <div class="col-md-3"><strong>{{ __('Min order') }}:</strong> ₹{{ number_format((float) $coupon->min_cart, 2) }}</div>
                <div class="col-md-3"><strong>{{ __('Max discount') }}:</strong> {{ $coupon->max_discount ? '₹'.number_format((float) $coupon->max_discount, 2) : '—' }}</div>
                <div class="col-md-3"><strong>{{ __('First order only') }}:</strong> {{ $coupon->first_order_only ? __('Yes') : __('No') }}</div>
                <div class="col-md-4"><strong>{{ __('Starts') }}:</strong> {{ optional($coupon->starts_at)->format('d M Y H:i') ?: '—' }}</div>
                <div class="col-md-4"><strong>{{ __('Expires') }}:</strong> {{ optional($coupon->ends_at)->format('d M Y H:i') ?: '—' }}</div>
                <div class="col-md-4"><strong>{{ __('Status') }}:</strong> {{ $coupon->adminStatusLabel() }}</div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm admin-data-card mb-4">
        <div class="card-header">
            <span class="admin-data-card__title d-block">{{ __('Per-customer usage') }}</span>
            <span class="admin-data-card__meta">{{ __('Successful redemptions only.') }}</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 admin-table">
                    <thead>
                        <tr>
                            <th>{{ __('Customer') }}</th>
                            <th>{{ __('Uses') }}</th>
                            <th>{{ __('Discount') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($perCustomer as $row)
                            <tr>
                                <td>{{ $row->user_id ? __('User #:id', ['id' => $row->user_id]) : ($row->guest_key ?: __('Guest')) }}</td>
                                <td>{{ (int) $row->uses }}</td>
                                <td>₹{{ number_format((float) $row->discount_total, 2) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="text-center py-3 text-muted">{{ __('No successful redemptions yet.') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm admin-data-card mb-4">
        <div class="card-header">
            <span class="admin-data-card__title d-block">{{ __('Usage history') }}</span>
            <span class="admin-data-card__meta">{{ __('Reserved = unpaid order holding a slot. Redeemed = successful payment. Released = failed, cancelled, or refunded.') }}</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 admin-table">
                    <thead>
                        <tr>
                            <th>{{ __('When') }}</th>
                            <th>{{ __('Status') }}</th>
                            <th>{{ __('Customer') }}</th>
                            <th>{{ __('Order / sale') }}</th>
                            <th>{{ __('Discount') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($usages as $usage)
                            <tr>
                                <td class="small text-muted">{{ optional($usage->created_at)->format('d M Y H:i') }}</td>
                                <td>{{ $usage->statusLabel() }}</td>
                                <td>
                                    @if($usage->user)
                                        {{ $usage->user->name }}
                                        <div class="small text-muted">{{ $usage->user->email }}</div>
                                    @else
                                        {{ $usage->guest_key ?: __('Guest') }}
                                    @endif
                                </td>
                                <td>
                                    @if($usage->order)
                                        <a href="{{ route('admin.orders.show', $usage->order) }}">{{ $usage->order->order_number }}</a>
                                    @elseif($usage->sale)
                                        {{ $usage->sale->invoice_number }}
                                    @else
                                        —
                                    @endif
                                </td>
                                <td>₹{{ number_format((float) $usage->discount_amount, 2) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center py-3 text-muted">{{ __('No usage records.') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="admin-pagination-wrap">{{ $usages->links() }}</div>
@endsection
