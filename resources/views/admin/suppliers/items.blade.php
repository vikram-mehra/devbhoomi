@extends('layouts.admin')

@section('title', __('Suppliers Item Listing with Cost'))

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <div>
            <h1 class="h4 mb-0 fw-bold">{{ __('Suppliers Item Listing with Cost') }}</h1>
            <p class="text-muted small mb-0">{{ __('Track purchased products, variants, quantities, and supplier costs.') }}</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.suppliers.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill">
                <i class="bi bi-truck me-1"></i>{{ __('Suppliers List') }}
            </a>
            <a href="{{ route('admin.purchases.create') }}" class="btn btn-primary btn-sm rounded-pill">
                <i class="bi bi-plus-lg me-1"></i>{{ __('New Purchase') }}
            </a>
        </div>
    </div>

    <div class="card border-0 shadow-sm p-3 mb-3">
        <form method="get" action="{{ route('admin.suppliers.items') }}" class="row g-2 align-items-center">
            <div class="col-md-5">
                <div class="input-group">
                    <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                    <input type="search" name="q" value="{{ request('q') }}" class="form-control" placeholder="{{ __('Search product, supplier, or invoice…') }}">
                </div>
            </div>
            <div class="col-md-4">
                <select name="supplier_id" class="form-select" onchange="this.form.submit()">
                    <option value="">{{ __('All Suppliers') }}</option>
                    @foreach($suppliers as $s)
                        <option value="{{ $s->id }}" @selected(request('supplier_id') == $s->id)>{{ $s->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-primary flex-grow-1">{{ __('Filter') }}</button>
                @if(request()->filled('q') || request()->filled('supplier_id'))
                    <a href="{{ route('admin.suppliers.items') }}" class="btn btn-outline-secondary">{{ __('Reset') }}</a>
                @endif
            </div>
        </form>
    </div>

    <div class="card border-0 shadow-sm admin-data-card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <strong>{{ __('Purchased Items & Costs') }}</strong>
            <span class="badge bg-light text-dark border">{{ $items->total() }} {{ __('records') }}</span>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 admin-table">
                <thead>
                    <tr>
                        <th>{{ __('Product') }}</th>
                        <th>{{ __('Variant / Size') }}</th>
                        <th>{{ __('Supplier') }}</th>
                        <th>{{ __('Unit Cost') }}</th>
                        <th>{{ __('Qty') }}</th>
                        <th>{{ __('Tax') }}</th>
                        <th>{{ __('Line Total') }}</th>
                        <th>{{ __('Date & Invoice') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($items as $item)
                        @php
                            $prod = $item->product;
                            $thumb = $prod ? \App\Models\Product::publicImageUrl($prod->images->first()?->path) : null;
                            $supplier = $item->purchase?->supplier;
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
                            <td>
                                @if($supplier)
                                    <a href="{{ route('admin.suppliers.show', $supplier) }}" class="fw-semibold text-decoration-none">
                                        {{ $supplier->name }}
                                    </a>
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
                            <td colspan="8" class="text-center py-4 text-muted">
                                {{ __('No supplier item purchase records found.') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-3">{{ $items->links() }}</div>
@endsection
