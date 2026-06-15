@extends('layouts.admin')

@section('title', __('Order Management'))
@section('page_subtitle', __('Track, filter, and manage customer orders in one place.'))

@php
    $statusClasses = [
        'delivered' => 'success',
        'pending' => 'warning',
        'processing' => 'primary',
        'cancelled' => 'danger',
        'confirmed' => 'info',
        'shipped' => 'secondary',
        'returned' => 'dark',
    ];
@endphp

@section('content')
    <div class="row g-3 mb-4">
        <div class="col-6 col-lg">
            <div class="admin-stat-card h-100"><div class="admin-stat-card__label">Total Orders</div><div class="admin-stat-card__value">{{ number_format($stats['total_orders']) }}</div></div>
        </div>
        <div class="col-6 col-lg">
            <div class="admin-stat-card h-100"><div class="admin-stat-card__label">Pending Orders</div><div class="admin-stat-card__value">{{ number_format($stats['pending_orders']) }}</div></div>
        </div>
        <div class="col-6 col-lg">
            <div class="admin-stat-card h-100"><div class="admin-stat-card__label">Delivered Orders</div><div class="admin-stat-card__value">{{ number_format($stats['delivered_orders']) }}</div></div>
        </div>
        <div class="col-6 col-lg">
            <div class="admin-stat-card h-100"><div class="admin-stat-card__label">Total Revenue</div><div class="admin-stat-card__value">₹{{ number_format($stats['total_revenue'], 2) }}</div></div>
        </div>
        <div class="col-6 col-lg">
            <div class="admin-stat-card h-100"><div class="admin-stat-card__label">Today Orders</div><div class="admin-stat-card__value">{{ number_format($stats['today_orders']) }}</div></div>
        </div>
    </div>

    <div class="card border-0 shadow-sm admin-data-card mb-3">
        <div class="card-body">
            <form method="get" class="row g-2 align-items-end">
                <div class="col-6 col-md-4 col-xl-2">
                    <label class="form-label small text-muted mb-1">Order ID</label>
                    <input type="text" class="form-control" name="order_id" value="{{ request('order_id') }}" placeholder="Order ID">
                </div>
                <div class="col-6 col-md-4 col-xl-2">
                    <label class="form-label small text-muted mb-1">Customer Name</label>
                    <input type="text" class="form-control" name="customer_name" value="{{ request('customer_name') }}" placeholder="Customer Name">
                </div>
                <div class="col-6 col-md-4 col-xl-2">
                    <label class="form-label small text-muted mb-1">Mobile Number</label>
                    <input type="text" class="form-control" name="mobile" value="{{ request('mobile') }}" placeholder="Mobile Number">
                </div>
                <div class="col-6 col-md-4 col-xl-2">
                    <label class="form-label small text-muted mb-1">Payment Status</label>
                    <select class="form-select" name="payment_status">
                        <option value="">All</option>
                        @foreach(\App\Models\Order::paymentStatusOptions() as $k => $v)
                            <option value="{{ $k }}" @selected(request('payment_status') === $k)>{{ $v }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-4 col-xl-2">
                    <label class="form-label small text-muted mb-1">Order Status</label>
                    <select class="form-select" name="order_status">
                        <option value="">All</option>
                        @foreach(\App\Models\Order::adminStatusOptions() as $k => $v)
                            <option value="{{ $k }}" @selected(request('order_status') === $k)>{{ $v }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-4 col-xl-1">
                    <label class="form-label small text-muted mb-1">From</label>
                    <input type="date" class="form-control" name="date_from" value="{{ request('date_from') }}">
                </div>
                <div class="col-6 col-md-4 col-xl-1">
                    <label class="form-label small text-muted mb-1">To</label>
                    <input type="date" class="form-control" name="date_to" value="{{ request('date_to') }}">
                </div>
                <div class="col-12 col-xl-12">
                    <div class="d-flex flex-wrap gap-2 pt-xl-1">
                        <button class="btn btn-primary"><i class="bi bi-funnel me-1"></i>Apply</button>
                        <a href="{{ route('admin.orders.index') }}" class="btn btn-outline-secondary">Reset</a>
                        <a href="{{ route('admin.orders.export', ['type' => 'csv'] + request()->query()) }}" class="btn btn-outline-success">Export CSV</a>
                        <a href="{{ route('admin.orders.export', ['type' => 'excel'] + request()->query()) }}" class="btn btn-outline-success">Export Excel</a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <form method="post" action="{{ route('admin.orders.bulk-status') }}" class="card border-0 shadow-sm admin-data-card mb-4">
        @csrf
        <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
            <strong>Orders</strong>
            <div class="d-flex flex-wrap gap-2">
                <select name="status" class="form-select form-select-sm" style="min-width: 10rem;">
                    <option value="">Bulk Status</option>
                    @foreach(\App\Models\Order::adminStatusOptions() as $k => $v)
                        <option value="{{ $k }}">{{ $v }}</option>
                    @endforeach
                </select>
                <button type="submit" class="btn btn-sm btn-outline-primary">Update Selected</button>
                <button type="submit" formaction="{{ route('admin.orders.bulk-shipping') }}" class="btn btn-sm btn-outline-success">Save Shipping Selected</button>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 admin-table">
                <thead>
                    <tr>
                        <th style="width: 2.5rem;"><input type="checkbox" onclick="document.querySelectorAll('.order-checkbox').forEach(cb=>cb.checked=this.checked)"></th>
                        <th>Order ID</th>
                        <th>Customer Name</th>
                        <th>Customer Phone</th>
                        <th>Total Amount</th>
                        <th>Payment Status</th>
                        <th>Courier Name</th>
                        <th>Tracking ID</th>
                        <th>Order Status</th>
                        <th>Order Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($orders as $o)
                        <tr>
                            <td><input class="order-checkbox" type="checkbox" name="order_ids[]" value="{{ $o->id }}"></td>
                            <td class="fw-semibold text-nowrap">{{ $o->order_number }}</td>
                            <td>{{ $o->customer_name ?: ($o->user->name ?? 'N/A') }}</td>
                            <td class="text-nowrap">{{ $o->customer_phone ?: ($o->shippingAddress->phone ?? 'N/A') }}</td>
                            <td class="fw-semibold text-nowrap">₹{{ number_format((float) $o->total, 2) }}</td>
                            <td><span class="badge bg-{{ $o->payment_status === 'paid' ? 'success' : ($o->payment_status === 'failed' ? 'danger' : 'warning text-dark') }}">{{ ucfirst((string) $o->payment_status) }}</span></td>
                            <td>
                                <input type="text" name="courier_name[{{ $o->id }}]" value="{{ old('courier_name.'.$o->id, $o->courier_name) }}" class="form-control form-control-sm" style="min-width: 120px;">
                            </td>
                            <td>
                                <input type="text" name="tracking_id[{{ $o->id }}]" value="{{ old('tracking_id.'.$o->id, $o->tracking_id) }}" class="form-control form-control-sm" style="min-width: 120px;">
                            </td>
                            <td><span class="badge bg-{{ $statusClasses[$o->status] ?? 'secondary' }}">{{ \App\Models\Order::statusLabel($o->status) }}</span></td>
                            <td class="text-nowrap">{{ $o->created_at?->format('d M Y, h:i A') }}</td>
                            <td class="text-nowrap">
                                <a href="{{ route('admin.orders.show', $o) }}" class="btn btn-sm btn-outline-dark">View</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="11" class="text-center py-5 text-muted">No orders found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($orders->total() > 0)
            <div class="card-footer bg-white border-top py-3">
                <div class="d-flex flex-column flex-lg-row align-items-stretch align-items-lg-center justify-content-between gap-3">
                    <div class="small text-muted text-center text-lg-start">
                        {{ __('Showing :from–:to of :total orders', [
                            'from' => $orders->firstItem(),
                            'to' => $orders->lastItem(),
                            'total' => number_format($orders->total()),
                        ]) }}
                        @if ($orders->hasPages())
                            · {{ __('Page :current of :last', ['current' => $orders->currentPage(), 'last' => $orders->lastPage()]) }}
                        @endif
                    </div>
                    @if ($orders->hasPages())
                        <div class="admin-pagination-wrap mt-0 pt-0 pb-0">
                            {{ $orders->links('admin.components.pagination-advanced') }}
                        </div>
                    @endif
                </div>
            </div>
        @endif
    </form>
@endsection
