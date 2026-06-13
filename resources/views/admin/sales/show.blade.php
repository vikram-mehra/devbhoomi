@extends('layouts.admin')

@section('title', __('Sale :invoice', ['invoice' => $sale->invoice_number]))

@section('content')
    <div class="d-flex flex-wrap justify-content-between gap-2 mb-3">
        <div>
            <h1 class="h4 mb-1">{{ $sale->invoice_number }}</h1>
            <p class="text-muted mb-0">{{ optional($sale->sale_date)->format('d M Y') }} · {{ $sale->customer_name }}</p>
        </div>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-outline-secondary" onclick="window.print()">{{ __('Print invoice') }}</button>
            <a href="{{ route('admin.sales.index') }}" class="btn btn-primary rounded-pill">{{ __('Back') }}</a>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body">
            <p class="mb-1"><strong>{{ __('Customer email') }}:</strong> {{ $sale->customer_email ?: '—' }}</p>
            <p class="mb-1"><strong>{{ __('Customer phone') }}:</strong> {{ $sale->customer_phone ?: '—' }}</p>
            <p class="mb-1"><strong>{{ __('Total') }}:</strong> ₹{{ number_format((float) $sale->total_amount, 2) }}</p>
            <p class="mb-0"><strong>{{ __('Stock applied') }}:</strong> {{ $sale->stock_applied_at ? $sale->stock_applied_at->format('d M Y H:i') : __('Pending') }}</p>
        </div>
    </div>

    <form method="post" action="{{ route('admin.sales.status', $sale) }}" class="card border-0 shadow-sm mb-3 p-3">
        @csrf
        <div class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label">{{ __('Payment status') }}</label>
                <select name="payment_status" class="form-select">
                    @foreach (['pending', 'partial', 'paid', 'refunded'] as $status)
                        <option value="{{ $status }}" @selected($sale->payment_status === $status)>{{ ucfirst($status) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">{{ __('Order status') }}</label>
                <select name="order_status" class="form-select">
                    @foreach (['pending', 'confirmed', 'completed', 'shipped', 'cancelled'] as $status)
                        <option value="{{ $status }}" @selected($sale->order_status === $status)>{{ ucfirst($status) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3"><button class="btn btn-outline-primary">{{ __('Update status') }}</button></div>
        </div>
    </form>

    <div class="card border-0 shadow-sm admin-data-card">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 admin-table">
                <thead>
                    <tr>
                        <th>{{ __('Product') }}</th>
                        <th>{{ __('Variant') }}</th>
                        <th>{{ __('Qty') }}</th>
                        <th>{{ __('Unit price') }}</th>
                        <th>{{ __('Line total') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($sale->items as $item)
                        <tr>
                            <td>{{ $item->product->name ?? '—' }}</td>
                            <td>{{ $item->variant?->label() ?? $item->variant?->sku ?? '—' }}</td>
                            <td>{{ $item->quantity }}</td>
                            <td>₹{{ number_format((float) $item->unit_price, 2) }}</td>
                            <td>₹{{ number_format((float) $item->line_total, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
