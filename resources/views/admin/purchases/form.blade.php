@extends('layouts.admin')

@section('title', __('New purchase'))

@section('page_subtitle')
    {{ __('Record supplier purchase and increase stock automatically.') }}
@endsection

@section('content')
    <form method="post" action="{{ route('admin.purchases.store') }}" class="card border-0 shadow-sm p-3 p-md-4" id="purchaseForm">
        @csrf
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <label class="form-label fw-semibold">{{ __('Invoice number') }} *</label>
                <input type="text" name="invoice_number" class="form-control @error('invoice_number') is-invalid @enderror" value="{{ old('invoice_number', 'PUR-'.now()->format('Ymd-His')) }}" required>
                @error('invoice_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold">{{ __('Purchase date') }} *</label>
                <input type="date" name="purchase_date" class="form-control" value="{{ old('purchase_date', now()->toDateString()) }}" required>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold">{{ __('Supplier') }}</label>
                <select name="supplier_id" class="form-select">
                    <option value="">{{ __('Select supplier') }}</option>
                    @foreach ($suppliers as $supplier)
                        <option value="{{ $supplier->id }}" @selected((string) old('supplier_id') === (string) $supplier->id)>{{ $supplier->name }}</option>
                    @endforeach
                </select>
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
                <label class="form-label fw-semibold">{{ __('GST / tax %') }}</label>
                <input type="number" step="0.01" min="0" name="tax_percent" class="form-control @error('tax_percent') is-invalid @enderror" value="{{ old('tax_percent', 0) }}">
                @error('tax_percent')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold">{{ __('Delivery charge') }} (₹)</label>
                <input type="number" step="0.01" min="0" name="delivery_charge" class="form-control @error('delivery_charge') is-invalid @enderror" value="{{ old('delivery_charge', 0) }}" placeholder="{{ __('Optional') }}">
                @error('delivery_charge')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold">{{ __('Payment status') }}</label>
                <select name="payment_status" class="form-select">
                    @foreach (['pending', 'partial', 'paid'] as $status)
                        <option value="{{ $status }}" @selected(old('payment_status', 'pending') === $status)>{{ ucfirst($status) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-12">
                <label class="form-label fw-semibold">{{ __('Notes') }}</label>
                <textarea name="notes" class="form-control" rows="2">{{ old('notes') }}</textarea>
            </div>
        </div>

        <div class="d-flex justify-content-between align-items-center mb-2">
            <h2 class="h6 mb-0">{{ __('Purchase lines') }}</h2>
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
                        <th>{{ __('Line tax') }}</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody id="invLineRows">
                    @php $oldItems = old('items', [['product_variant_id' => '', 'quantity' => 1, 'unit_price' => '', 'tax_amount' => 0]]); @endphp
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
                            <td><input type="number" step="0.01" min="0" name="items[{{ $i }}][tax_amount]" class="form-control" value="{{ $line['tax_amount'] ?? 0 }}"></td>
                            <td><button type="button" class="btn btn-sm btn-outline-danger js-remove-line"><i class="bi bi-trash"></i></button></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="d-flex flex-wrap gap-2 justify-content-end">
            <a href="{{ route('admin.purchases.index') }}" class="btn btn-outline-secondary rounded-pill">{{ __('Cancel') }}</a>
            <button type="submit" class="btn btn-primary rounded-pill px-4">{{ __('Save purchase') }}</button>
        </div>
    </form>
@endsection

@include('admin.partials.inventory-line-script')
