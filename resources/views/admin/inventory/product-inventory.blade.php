@extends('layouts.admin')

@section('title', __('Product inventory'))

@section('page_subtitle')
    {{ __('Product-level stock, sales, adjustments, and recent movements.') }}
@endsection

@section('content')
    <form method="get" action="{{ route('admin.inventory.product-inventory') }}" class="card border-0 shadow-sm p-3 mb-4">
        <div class="row g-2 align-items-end">
            <div class="col-6 col-md-2">
                <label class="form-label small mb-0">{{ __('From') }}</label>
                <input type="date" name="from" class="form-control form-control-sm" value="{{ request('from', $from?->toDateString()) }}">
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label small mb-0">{{ __('To') }}</label>
                <input type="date" name="to" class="form-control form-control-sm" value="{{ request('to', $to?->toDateString()) }}">
            </div>
            <div class="col-12 col-md-3">
                <label class="form-label small mb-0">{{ __('Menu / Service') }}</label>
                <select name="menu_item_id" class="form-select form-select-sm">
                    <option value="">{{ __('All') }}</option>
                    @foreach($categories as $c)
                        <option value="{{ $c->id }}" @selected((string) request('menu_item_id') === (string) $c->id)>{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-12 col-md-2">
                <label class="form-label small mb-0">{{ __('Stock') }}</label>
                <select name="stock_status" class="form-select form-select-sm">
                    <option value="">{{ __('Any') }}</option>
                    <option value="low" @selected(request('stock_status') === 'low')>{{ __('Low') }}</option>
                    <option value="out" @selected(request('stock_status') === 'out')>{{ __('Out') }}</option>
                    <option value="ok" @selected(request('stock_status') === 'ok')>{{ __('Healthy') }}</option>
                </select>
            </div>
            <div class="col-12 col-md-3">
                <label class="form-label small mb-0">{{ __('Search') }}</label>
                <input type="search" name="q" class="form-control form-control-sm" value="{{ request('q') }}" placeholder="{{ __('Name / SKU') }}">
            </div>
            @if($hasProductBrand && $brands->isNotEmpty())
                <div class="col-12 col-md-2">
                    <label class="form-label small mb-0">{{ __('Brand') }}</label>
                    <select name="brand" class="form-select form-select-sm">
                        <option value="">{{ __('All') }}</option>
                        @foreach($brands as $b)
                            <option value="{{ $b }}" @selected(request('brand') === $b)>{{ $b }}</option>
                        @endforeach
                    </select>
                </div>
            @endif
            <div class="col-12 d-flex flex-wrap gap-2">
                <button type="submit" class="btn btn-sm btn-primary rounded-pill">{{ __('Apply') }}</button>
                <a href="{{ route('admin.inventory.product-inventory') }}" class="btn btn-sm btn-outline-secondary rounded-pill">{{ __('Reset') }}</a>
                <a href="{{ route('admin.inventory.export.products', request()->query()) }}" class="btn btn-sm btn-outline-success rounded-pill">{{ __('Export products CSV') }}</a>
            </div>
        </div>
    </form>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white py-3"><span class="fw-bold">{{ __('Bulk stock adjust (audit logged)') }}</span></div>
        <div class="card-body">
            <form method="post" action="{{ route('admin.inventory.bulk-adjust') }}" class="row g-2 align-items-end">
                @csrf
                <div class="col-md-4">
                    <label class="form-label small">{{ __('Variant SKU') }}</label>
                    <input name="variant_sku" class="form-control form-control-sm" required placeholder="SKU-001">
                </div>
                <div class="col-md-2">
                    <label class="form-label small">{{ __('Qty +/-') }}</label>
                    <input type="number" name="qty_delta" class="form-control form-control-sm" required value="0">
                </div>
                <div class="col-md-4">
                    <label class="form-label small">{{ __('Note') }}</label>
                    <input name="note" class="form-control form-control-sm" placeholder="{{ __('Stock count / correction') }}">
                </div>
                <div class="col-md-2">
                    <button class="btn btn-sm btn-primary w-100 rounded-pill">{{ __('Apply') }}</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4 admin-data-card">
        <div class="card-header bg-white py-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div>
                <span class="fw-bold">{{ __('Product analytics') }}</span>
                <span class="small text-muted d-block">{{ __('Stock, reserved carts, paid sales, and returns for the selected period.') }}</span>
            </div>
            <span class="badge rounded-pill border" style="background: var(--admin-teal-soft); color: var(--admin-teal-hover);">{{ $products->total() }}</span>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 admin-table">
                <thead class="table-light">
                    <tr>
                        <th>{{ __('Product') }}</th>
                        <th>{{ __('SKU') }}</th>
                        @if($hasProductBarcode)<th>{{ __('Barcode') }}</th>@endif
                        @if($hasProductBrand)<th>{{ __('Brand') }}</th>@endif
                        <th>{{ __('Menu') }}</th>
                        <th class="text-end">{{ __('Stock') }}</th>
                        <th class="text-end">{{ __('Reserved') }}</th>
                        <th class="text-end">{{ __('Sold') }}</th>
                        <th class="text-end">{{ __('Returned') }}</th>
                        <th class="text-end">{{ __('Revenue') }}</th>
                        <th>{{ __('Last sale') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($products as $p)
                        @php
                            $stock = (int) $p->variants->sum('stock_qty');
                            $res = (int) ($reservedByProduct[$p->id] ?? 0);
                        @endphp
                        <tr>
                            <td class="fw-semibold">
                                @if(filled($p->slug))
                                    <a href="{{ route('admin.products.edit', ['product' => $p->slug]) }}" class="text-decoration-none" style="color: var(--admin-teal);">{{ \Illuminate\Support\Str::limit($p->name, 36) }}</a>
                                @else
                                    {{ \Illuminate\Support\Str::limit($p->name, 36) }}
                                @endif
                            </td>
                            <td class="small font-monospace">{{ $p->sku }}</td>
                            @if($hasProductBarcode)<td class="small font-monospace">{{ $p->barcode ?? '—' }}</td>@endif
                            @if($hasProductBrand)<td class="small">{{ $p->brand ?? '—' }}</td>@endif
                            <td><span class="badge rounded-pill" style="background: var(--admin-teal-soft); color: var(--admin-teal-hover);">{{ $p->menuItem->title ?? '—' }}</span></td>
                            <td class="text-end">{{ $stock }}</td>
                            <td class="text-end @if($res > 0) text-warning @endif">{{ $res }}</td>
                            <td class="text-end">{{ (int) ($p->analytics_sold_qty ?? 0) }}</td>
                            <td class="text-end">{{ (int) ($p->analytics_returned_qty ?? 0) }}</td>
                            <td class="text-end">₹{{ number_format((float) ($p->analytics_revenue ?? 0), 0) }}</td>
                            <td class="small text-muted">@if($p->analytics_last_sale_at){{ \Carbon\Carbon::parse($p->analytics_last_sale_at)->diffForHumans() }}@else — @endif</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11" class="text-center py-5 text-muted">{{ __('No products match these filters.') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($products->hasPages())
            <div class="card-footer bg-white border-top py-3">
                <div class="d-flex flex-column flex-xl-row align-items-stretch align-items-xl-center justify-content-between gap-3">
                    <div class="small text-muted text-center text-xl-start">
                        {{ __('Showing :from to :to of :total products', [
                            'from' => $products->firstItem(),
                            'to' => $products->lastItem(),
                            'total' => $products->total(),
                        ]) }}
                    </div>
                    <div class="flex-grow-1 d-flex justify-content-center justify-content-xl-end">
                        {{ $products->links('admin.components.pagination-advanced') }}
                    </div>
                </div>
            </div>
        @endif
    </div>

    <div class="card border-0 shadow-sm admin-data-card">
        <div class="card-header bg-white py-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
            <span class="fw-bold">{{ __('Recent stock movements') }}</span>
            <span class="badge rounded-pill border" style="background: var(--admin-teal-soft); color: var(--admin-teal-hover);">{{ $recentMoves->total() }}</span>
        </div>
        <div class="table-responsive">
            <table class="table table-sm mb-0 admin-table">
                <thead class="table-light"><tr><th>{{ __('When') }}</th><th>{{ __('Type') }}</th><th>{{ __('SKU') }}</th><th class="text-end">{{ __('Δ') }}</th><th class="text-end">{{ __('Balance') }}</th></tr></thead>
                <tbody>
                    @forelse($recentMoves as $m)
                        <tr>
                            <td class="small">{{ $m->created_at->diffForHumans() }}</td>
                            <td><code class="small">{{ $m->type }}</code></td>
                            <td class="small font-monospace">{{ $m->variant?->sku }}</td>
                            <td class="text-end @if($m->qty_delta < 0) text-danger @else text-success @endif">{{ $m->qty_delta > 0 ? '+' : '' }}{{ $m->qty_delta }}</td>
                            <td class="text-end">{{ $m->balance_after ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center py-4 text-muted">{{ __('No stock movements yet.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($recentMoves->hasPages())
            <div class="card-footer bg-white border-top py-3">
                {{ $recentMoves->links('admin.components.pagination-advanced') }}
            </div>
        @endif
    </div>
@endsection
