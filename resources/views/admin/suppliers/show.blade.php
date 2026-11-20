@extends('layouts.admin')

@section('title', $supplier->name)

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <div>
            <h1 class="h4 mb-0 fw-bold">{{ $supplier->name }}</h1>
            <p class="text-muted small mb-0">{{ __('Supplier Profile, Purchased Items & Cost History') }}</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.suppliers.items', ['supplier_id' => $supplier->id]) }}" class="btn btn-outline-primary btn-sm rounded-pill">
                <i class="bi bi-tag me-1"></i>{{ __('Filter Items for this Supplier') }}
            </a>
            <a href="{{ route('admin.suppliers.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill">
                <i class="bi bi-arrow-left me-1"></i>{{ __('All Suppliers') }}
            </a>
        </div>
    </div>

    <div class="card border-0 shadow-sm p-3 p-md-4 mb-4">
        <h6 class="fw-bold mb-3"><i class="bi bi-building me-1"></i>{{ __('Supplier Details') }}</h6>
        <form method="post" action="{{ route('admin.suppliers.update', $supplier) }}">@csrf @method('PATCH')
            <div class="row g-2">
                <div class="col-md-3">
                    <label class="form-label small text-muted mb-1">{{ __('Supplier Name') }}</label>
                    <input name="name" class="form-control" value="{{ $supplier->name }}" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label small text-muted mb-1">{{ __('Phone') }}</label>
                    <input name="phone" class="form-control" value="{{ $supplier->phone }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label small text-muted mb-1">{{ __('Email') }}</label>
                    <input name="email" class="form-control" value="{{ $supplier->email }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label small text-muted mb-1">{{ __('GST Number') }}</label>
                    <input name="gst_number" class="form-control" value="{{ $supplier->gst_number }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label small text-muted mb-1">{{ __('Pending Payment') }}</label>
                    <input name="pending_payment_amount" type="number" step="0.01" class="form-control" value="{{ $supplier->pending_payment_amount }}">
                </div>
                <div class="col-md-1 d-flex align-items-end">
                    <button class="btn btn-primary w-100">{{ __('Save') }}</button>
                </div>
            </div>
        </form>
    </div>

    {{-- Supplier Items with Cost --}}
    <div class="card border-0 shadow-sm admin-data-card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <strong><i class="bi bi-box-seam me-1"></i>{{ __('Supplied Items with Cost') }}</strong>
            <span class="badge bg-light text-dark border">{{ $items->total() }} {{ __('items') }}</span>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 admin-table">
                <thead>
                    <tr>
                        <th>{{ __('Product') }}</th>
                        <th>{{ __('Variant / Size') }}</th>
                        <th>{{ __('Unit Cost') }}</th>
                        <th>{{ __('Qty') }}</th>
                        <th>{{ __('Tax') }}</th>
                        <th>{{ __('Line Total') }}</th>
                        <th>{{ __('Invoice & Date') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($items as $item)
                        @php
                            $prod = $item->product;
                            $thumb = $prod ? \App\Models\Product::publicImageUrl($prod->images->first()?->path) : null;
                        @endphp
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    @if($thumb)
                                        <img src="{{ $thumb }}" alt="" class="rounded border" style="width: 36px; height: 36px; object-fit: contain; background: #fff;">
                                    @else
                                        <div class="rounded border d-flex align-items-center justify-content-center bg-light text-muted" style="width: 36px; height: 36px; font-size: 0.75rem;">
                                            <i class="bi bi-box"></i>
                                        </div>
                                    @endif
                                    <div>
                                        <div class="fw-semibold text-dark">{{ $prod->name ?? __('Unknown product') }}</div>
                                        @if($prod?->sku)
                                            <span class="text-muted small">SKU: {{ $prod->sku }}</span>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td>
                                @if($item->variant)
                                    <span class="badge bg-light text-dark border">{{ $item->variant->size ?: $item->variant->color ?: $item->variant->sku }}</span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td class="fw-bold text-dark">
                                ₹{{ number_format((float) $item->unit_price, 2) }}
                            </td>
                            <td>
                                <span class="badge rounded-pill bg-light text-dark border px-2 py-1">
                                    {{ $item->quantity }}
                                </span>
                            </td>
                            <td class="text-muted small">
                                ₹{{ number_format((float) $item->tax_amount, 2) }}
                            </td>
                            <td class="fw-bold text-primary">
                                ₹{{ number_format((float) $item->line_total, 2) }}
                            </td>
                            <td class="small">
                                @if($item->purchase)
                                    <div><a href="{{ route('admin.purchases.show', $item->purchase) }}" class="text-decoration-none fw-semibold">{{ $item->purchase->invoice_number }}</a></div>
                                    <span class="text-muted">{{ optional($item->purchase->purchase_date)->format('d M Y') }}</span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">
                                {{ __('No items purchased from this supplier yet.') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($items->hasPages())
            <div class="card-footer bg-white border-0">{{ $items->links() }}</div>
        @endif
    </div>

    {{-- Purchase History / Invoices --}}
    <div class="card border-0 shadow-sm admin-data-card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <strong><i class="bi bi-receipt me-1"></i>{{ __('Purchase Invoices History') }}</strong>
            <span class="badge bg-light text-dark border">{{ $purchases->total() }} {{ __('invoices') }}</span>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 admin-table">
                <thead><tr><th>{{ __('Invoice') }}</th><th>{{ __('Date') }}</th><th>{{ __('Total') }}</th><th>{{ __('Payment') }}</th><th>{{ __('Action') }}</th></tr></thead>
                <tbody>
                    @forelse ($purchases as $purchase)
                        <tr>
                            <td><a href="{{ route('admin.purchases.show', $purchase) }}" class="fw-semibold text-decoration-none">{{ $purchase->invoice_number }}</a></td>
                            <td>{{ optional($purchase->purchase_date)->format('d M Y') }}</td>
                            <td class="fw-bold text-dark">₹{{ number_format((float) $purchase->total_amount, 2) }}</td>
                            <td>
                                <span class="badge {{ $purchase->payment_status === 'paid' ? 'bg-success' : 'bg-warning text-dark' }}">
                                    {{ ucfirst($purchase->payment_status) }}
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('admin.purchases.show', $purchase) }}" class="btn btn-sm btn-outline-primary">{{ __('View') }}</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center py-4 text-muted">{{ __('No purchases for this supplier yet.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($purchases->hasPages())
            <div class="card-footer bg-white border-0">{{ $purchases->links() }}</div>
        @endif
    </div>
@endsection
