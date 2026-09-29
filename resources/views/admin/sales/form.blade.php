@extends('layouts.admin')

@section('title', __('New sale'))

@section('page_subtitle')
    {{ __('Create a sales invoice and reduce stock when payment is confirmed.') }}
@endsection

@section('content')
    <form method="post" action="{{ route('admin.sales.store') }}" class="card border-0 shadow-sm p-3 p-md-4">
        @csrf
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <label class="form-label fw-semibold">{{ __('Invoice number') }} *</label>
                <input type="text" name="invoice_number" class="form-control @error('invoice_number') is-invalid @enderror" value="{{ old('invoice_number', 'SAL-'.now()->format('Ymd-His')) }}" required>
                @error('invoice_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold">{{ __('Sale date') }} *</label>
                <input type="date" name="sale_date" class="form-control" value="{{ old('sale_date', now()->toDateString()) }}" required>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold">{{ __('Warehouse') }}</label>
                <select name="warehouse_id" class="form-select">
                    @foreach ($warehouses as $warehouse)
                        <option value="{{ $warehouse->id }}" {{ (string) old('warehouse_id', $warehouses->firstWhere('is_default', true)?->id) === (string) $warehouse->id ? 'selected' : '' }}>{{ $warehouse->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold">{{ __('Customer name') }} *</label>
                <input type="text" name="customer_name" class="form-control" value="{{ old('customer_name') }}" required>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold">{{ __('Customer email') }}</label>
                <input type="email" name="customer_email" class="form-control" value="{{ old('customer_email') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold">{{ __('Customer phone') }}</label>
                <input type="text" name="customer_phone" class="form-control" value="{{ old('customer_phone') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold">{{ __('Payment status') }}</label>
                <select name="payment_status" class="form-select">
                    @foreach (['pending', 'partial', 'paid', 'refunded'] as $status)
                        <option value="{{ $status }}" @selected(old('payment_status', 'paid') === $status)>{{ ucfirst($status) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold">{{ __('Order status') }}</label>
                <select name="order_status" class="form-select">
                    @foreach (['pending', 'confirmed', 'completed', 'shipped', 'cancelled'] as $status)
                        <option value="{{ $status }}" @selected(old('order_status', 'completed') === $status)>{{ ucfirst($status) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold">{{ __('Tax amount') }}</label>
                <input type="number" step="0.01" min="0" name="tax_amount" class="form-control" value="{{ old('tax_amount', 0) }}">
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold">{{ __('Coupon code') }}</label>
                <input type="text" name="coupon_code" id="saleCouponCode" class="form-control text-uppercase" value="{{ old('coupon_code') }}" placeholder="{{ __('Public or internal code') }}">
                @error('coupon_code')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                <div class="form-text">{{ __('Internal coupons work here. Discount is applied on save.') }}</div>
            </div>
            <div class="col-12">
                <label class="form-label fw-semibold">{{ __('Notes') }}</label>
                <textarea name="notes" class="form-control" rows="2">{{ old('notes') }}</textarea>
            </div>
        </div>

        <div class="d-flex justify-content-between align-items-center mb-2">
            <h2 class="h6 mb-0">{{ __('Sale lines') }}</h2>
            <button type="button" class="btn btn-sm btn-outline-primary rounded-pill" id="invAddLine">{{ __('Add line') }}</button>
        </div>
        @error('items')<div class="alert alert-danger py-2">{{ $message }}</div>@enderror
        <div class="table-responsive">
            <table class="table align-middle">
                <thead class="table-light">
                    <tr>
                        <th style="min-width: 280px;">{{ __('Product / variant') }}</th>
                        <th>{{ __('Stock') }}</th>
                        <th>{{ __('Qty') }}</th>
                        <th>{{ __('Unit price') }}</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody id="invLineRows">
                    @php $oldItems = old('items', [['product_variant_id' => '', 'quantity' => 1, 'unit_price' => '']]); @endphp
                    @foreach ($oldItems as $i => $line)
                        <tr>
                            <td class="position-relative">
                                <input type="hidden" name="items[{{ $i }}][product_variant_id]" class="js-variant-id" value="{{ $line['product_variant_id'] ?? '' }}">
                                <div class="input-group">
                                    <input type="search" class="form-control js-variant-search" placeholder="{{ __('Search SKU, name, barcode…') }}" autocomplete="off">
                                    <button type="button" class="btn btn-outline-secondary js-barcode-scan" title="{{ __('Barcode') }}"><i class="bi bi-upc-scan"></i></button>
                                </div>
                                <div class="list-group position-absolute w-100 shadow-sm js-variant-results d-none" style="z-index: 5; max-height: 220px; overflow: auto;"></div>
                            </td>
                            <td class="js-variant-stock text-muted">—</td>
                            <td><input type="number" min="1" name="items[{{ $i }}][quantity]" class="form-control" value="{{ $line['quantity'] ?? 1 }}" required></td>
                            <td><input type="number" step="0.01" min="0" name="items[{{ $i }}][unit_price]" class="form-control js-line-price" value="{{ $line['unit_price'] ?? '' }}" required></td>
                            <td><button type="button" class="btn btn-sm btn-outline-danger js-remove-line"><i class="bi bi-trash"></i></button></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="d-flex flex-wrap gap-2 justify-content-end">
            <a href="{{ route('admin.sales.index') }}" class="btn btn-outline-secondary rounded-pill">{{ __('Cancel') }}</a>
            <button type="submit" class="btn btn-primary rounded-pill px-4">{{ __('Save sale') }}</button>
        </div>
    </form>
@endsection

@include('admin.partials.inventory-line-script')
