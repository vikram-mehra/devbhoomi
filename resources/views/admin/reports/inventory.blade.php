@extends('layouts.admin')

@section('title', __('Inventory reports'))

@section('content')
    <form method="get" class="row g-2 mb-4">
        <div class="col-md-3"><input type="date" name="from" value="{{ $from->toDateString() }}" class="form-control"></div>
        <div class="col-md-3"><input type="date" name="to" value="{{ $to->toDateString() }}" class="form-control"></div>
        <div class="col-md-2"><button class="btn btn-outline-primary w-100">{{ __('Apply') }}</button></div>
    </form>

    <div class="row g-3 mb-4">
        <div class="col-md-3"><div class="admin-stat-card h-100"><div class="admin-stat-card__label">{{ __('Purchased qty') }}</div><div class="admin-stat-card__value">{{ $purchasedQty }}</div></div></div>
        <div class="col-md-3"><div class="admin-stat-card h-100"><div class="admin-stat-card__label">{{ __('Sold qty') }}</div><div class="admin-stat-card__value">{{ $soldQty }}</div></div></div>
        <div class="col-md-3"><div class="admin-stat-card h-100"><div class="admin-stat-card__label">{{ __('Available stock') }}</div><div class="admin-stat-card__value">{{ $overview['total_stock_available'] ?? 0 }}</div></div></div>
        <div class="col-md-3"><div class="admin-stat-card h-100"><div class="admin-stat-card__label">{{ __('Inventory value') }}</div><div class="admin-stat-card__value">₹{{ number_format($overview['inventory_value_retail'] ?? 0, 0) }}</div></div></div>
    </div>

    <div class="card border-0 shadow-sm admin-data-card mt-4">
        <div class="card-header d-flex justify-content-between">
            <strong>{{ __('Profit / loss (period)') }}</strong>
            <span class="fw-semibold {{ $profitLoss >= 0 ? 'text-success' : 'text-danger' }}">₹{{ number_format($profitLoss, 2) }}</span>
        </div>
        <div class="card-body small text-muted">
            {{ __('Sales') }} ₹{{ number_format($salesTotal, 2) }} · {{ __('Purchases') }} ₹{{ number_format($purchaseTotal, 2) }}
        </div>
    </div>
@endsection
