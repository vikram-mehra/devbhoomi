@extends('layouts.admin')

@section('title', __('Purchase :invoice', ['invoice' => $purchase->invoice_number]))

@section('content')
    <div class="d-flex flex-wrap justify-content-between gap-2 mb-3">
        <div>
            <h1 class="h4 mb-1">{{ $purchase->invoice_number }}</h1>
            <p class="text-muted mb-0">{{ optional($purchase->purchase_date)->format('d M Y') }} · {{ $purchase->supplier->name ?? __('No supplier') }}</p>
        </div>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-outline-secondary" onclick="window.print()">{{ __('Print') }}</button>
            <a href="{{ route('admin.purchases.index') }}" class="btn btn-primary rounded-pill">{{ __('Back') }}</a>
        </div>
    </div>

    <div class="card border-0 shadow-sm admin-data-card">
        <div class="card-body">
            <p class="mb-1"><strong>{{ __('Subtotal') }}:</strong> ₹{{ number_format((float) $purchase->subtotal, 2) }}</p>
            <p class="mb-1"><strong>{{ __('Tax') }}:</strong> ₹{{ number_format((float) $purchase->tax_amount, 2) }}</p>
            <p class="mb-1"><strong>{{ __('Delivery charge') }}:</strong> ₹{{ number_format((float) $purchase->delivery_charge, 2) }}</p>
            <p class="mb-1"><strong>{{ __('Total') }}:</strong> ₹{{ number_format((float) $purchase->total_amount, 2) }}</p>
            <p class="mb-0"><strong>{{ __('Payment') }}:</strong> {{ ucfirst($purchase->payment_status) }}</p>
        </div>
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
                    @foreach ($purchase->items as $item)
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
