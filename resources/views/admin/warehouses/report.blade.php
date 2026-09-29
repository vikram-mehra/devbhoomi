@extends('layouts.admin')

@section('title', __('Warehouse report — :name', ['name' => $warehouse->name]))

@section('content')
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="admin-stat-card h-100">
                <div class="admin-stat-card__label">{{ __('Total qty') }}</div>
                <div class="admin-stat-card__value">{{ $totalQty }}</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="admin-stat-card h-100">
                <div class="admin-stat-card__label">{{ __('Damaged') }}</div>
                <div class="admin-stat-card__value">{{ $totalDamaged }}</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="admin-stat-card h-100">
                <div class="admin-stat-card__label">{{ __('Retail value') }}</div>
                <div class="admin-stat-card__value">₹{{ number_format($value, 0) }}</div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm admin-data-card">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 admin-table">
                <thead>
                    <tr>
                        <th>{{ __('Product') }}</th>
                        <th>{{ __('Variant') }}</th>
                        <th>{{ __('Qty') }}</th>
                        <th>{{ __('Damaged') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($stocks as $stock)
                        <tr>
                            <td>{{ $stock->variant->product->name ?? '—' }}</td>
                            <td>{{ $stock->variant->label() ?? $stock->variant->sku ?? '—' }}</td>
                            <td>{{ $stock->qty }}</td>
                            <td>{{ $stock->damaged_qty }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
