@extends('layouts.admin')

@section('title', __('Dashboard'))
@section('page_subtitle', __('Overview of revenue, orders, and recent activity.'))

@section('content')
    <div class="row g-3 mb-4">
        <div class="col-6 col-lg">
            <div class="admin-stat-card admin-stat-card--accent h-100">
                <div class="admin-stat-card__label">{{ __('Revenue (paid)') }}</div>
                <div class="admin-stat-card__value">₹{{ number_format($revenue, 0) }}</div>
                <p class="text-muted small mb-0 mt-1">{{ __('All paid orders') }}</p>
            </div>
        </div>
        <div class="col-6 col-lg">
            <div class="admin-stat-card h-100">
                <div class="admin-stat-card__label">{{ __('Orders') }}</div>
                <div class="admin-stat-card__value">{{ number_format($ordersCount) }}</div>
                <p class="text-muted small mb-0 mt-1">{{ __('Total orders') }}</p>
            </div>
        </div>
        <div class="col-6 col-lg">
            <div class="admin-stat-card h-100">
                <div class="admin-stat-card__label">{{ __('Pending orders') }}</div>
                <div class="admin-stat-card__value">{{ number_format($pendingOrders) }}</div>
                <p class="text-muted small mb-0 mt-1">{{ __('Awaiting processing') }}</p>
            </div>
        </div>
        <div class="col-6 col-lg">
            <div class="admin-stat-card h-100">
                <div class="admin-stat-card__label">{{ __('Delivered orders') }}</div>
                <div class="admin-stat-card__value">{{ number_format($deliveredOrders) }}</div>
                <p class="text-muted small mb-0 mt-1">{{ __('Successfully delivered') }}</p>
            </div>
        </div>
        <div class="col-6 col-lg">
            <div class="admin-stat-card h-100">
                <div class="admin-stat-card__label">{{ __('Today orders') }}</div>
                <div class="admin-stat-card__value">{{ number_format($todayOrders) }}</div>
                <p class="text-muted small mb-0 mt-1">{{ __('Orders placed today') }}</p>
            </div>
        </div>
        <div class="col-6 col-lg">
            <div class="admin-stat-card h-100">
                <div class="admin-stat-card__label">{{ __('Vendors') }}</div>
                <div class="admin-stat-card__value">{{ number_format($vendors) }}</div>
                <p class="text-muted small mb-0 mt-1">{{ __('Active sellers') }}</p>
            </div>
        </div>
        <div class="col-6 col-lg">
            <div class="admin-stat-card h-100">
                <div class="admin-stat-card__label">{{ __('Customers') }}</div>
                <div class="admin-stat-card__value">{{ number_format($users) }}</div>
                <p class="text-muted small mb-0 mt-1">{{ __('Registered users') }}</p>
            </div>
        </div>
        <div class="col-6 col-lg">
            <div class="admin-stat-card h-100">
                <div class="admin-stat-card__label">{{ __('Products') }}</div>
                <div class="admin-stat-card__value">{{ number_format($products) }}</div>
                <p class="text-muted small mb-0 mt-1">{{ __('Catalog items') }}</p>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h2 class="h6 fw-bold mb-1">{{ __('Orders — last 7 days') }}</h2>
                    <p class="text-muted small mb-3">{{ __('Daily order volume') }}</p>
                    <div class="d-flex align-items-end gap-2" style="min-height: 140px;" role="img" aria-label="{{ __('Orders chart last 7 days') }}">
                        @foreach($chartDays as $day)
                            @php
                                $count = (int) ($ordersByDay[$day] ?? 0);
                                $pct = round(($count / $chartMax) * 100);
                                $label = \Carbon\Carbon::parse($day)->format('D');
                            @endphp
                            <div class="flex-fill text-center">
                                <div
                                    class="bg-primary bg-opacity-75 rounded-top mx-auto"
                                    style="height: {{ max(4, $pct) }}%; max-height: 120px; width: 100%; max-width: 2.5rem;"
                                    title="{{ $count }} {{ __('orders') }}"
                                ></div>
                                <span class="d-block small text-muted mt-1">{{ $label }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h2 class="h6 fw-bold mb-1">{{ __('Orders by status') }}</h2>
                    <p class="text-muted small mb-3">{{ __('Distribution across workflow') }}</p>
                    @php $statusTotal = max(1, (int) $byStatus->sum()); @endphp
                    <ul class="list-unstyled mb-0">
                        @forelse($byStatus as $status => $count)
                            <li class="d-flex align-items-center gap-2 mb-2 small">
                                <span class="text-nowrap" style="min-width: 6rem;">{{ \App\Models\Order::statusLabel($status) }}</span>
                                <div class="progress flex-grow-1" style="height: 6px;">
                                    <div class="progress-bar" style="width: {{ round(($count / $statusTotal) * 100) }}%"></div>
                                </div>
                                <strong class="text-nowrap">{{ $count }}</strong>
                            </li>
                        @empty
                            <li class="text-muted small">{{ __('No orders yet.') }}</li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm admin-data-card">
        <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2 bg-transparent">
            <div>
                <span class="admin-data-card__title d-block fw-bold">{{ __('Recent orders') }}</span>
                <span class="admin-data-card__meta text-muted small">{{ __('Latest activity at a glance.') }}</span>
            </div>
            <a href="{{ route('admin.orders.index') }}" class="btn btn-sm btn-outline-primary">{{ __('View all') }}</a>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 admin-table">
                    <thead>
                        <tr>
                            <th scope="col">{{ __('#') }}</th>
                            <th scope="col">{{ __('Customer') }}</th>
                            <th scope="col">{{ __('Total') }}</th>
                            <th scope="col">{{ __('Status') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recent as $o)
                            <tr>
                                <td class="fw-semibold font-monospace small">{{ $o->order_number }}</td>
                                <td class="small text-break">{{ $o->user->email ?? $o->user->name }}</td>
                                <td class="fw-semibold">₹{{ number_format($o->total, 2) }}</td>
                                <td class="small">
                                    <span class="admin-chip admin-chip--muted">{{ \App\Models\Order::statusLabel($o->status) }}</span>
                                    <span class="text-muted">/</span>
                                    <span class="admin-chip admin-chip--success">{{ $o->payment_status }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted py-4">{{ __('No orders yet.') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
