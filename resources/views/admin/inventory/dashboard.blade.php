@extends('layouts.admin')

@section('title', __('Inventory analytics'))

@section('page_subtitle')
    {{ __('Stock, sales velocity, variant insights, and warehouse snapshot.') }}
@endsection

@section('content')
    <div class="d-flex flex-wrap gap-2 align-items-center justify-content-between mb-3">
        <div class="d-flex flex-wrap gap-2 align-items-center">
            <span class="badge rounded-pill text-bg-danger" title="{{ __('Out of stock variants') }}">{{ __('OOS') }}: {{ $alerts['out'] }}</span>
            <span class="badge rounded-pill text-bg-warning">{{ __('Low') }}: {{ $alerts['low'] }}</span>
            @if($alerts['overstock'] > 0)
                <span class="badge rounded-pill text-bg-secondary">{{ __('Overstock') }} (&gt;500): {{ $alerts['overstock'] }}</span>
            @endif
        </div>
        <button type="button" class="btn btn-sm btn-outline-primary rounded-pill" id="invAjaxRefresh">{{ __('Refresh data') }}</button>
    </div>

    <form method="get" action="{{ route('admin.inventory.dashboard') }}" class="card border-0 shadow-sm p-3 mb-4">
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
            @if($warehouses->isNotEmpty())
                <div class="col-12 col-md-2">
                    <label class="form-label small mb-0">{{ __('Warehouse (variants)') }}</label>
                    <select name="warehouse_id" class="form-select form-select-sm">
                        <option value="">{{ __('All') }}</option>
                        @foreach($warehouses as $w)
                            <option value="{{ $w->id }}" @selected((string) request('warehouse_id') === (string) $w->id)>{{ $w->name }}</option>
                        @endforeach
                    </select>
                </div>
            @endif
            <div class="col-12 d-flex flex-wrap gap-2">
                <button type="submit" class="btn btn-sm btn-primary rounded-pill">{{ __('Apply') }}</button>
                <a href="{{ route('admin.inventory.dashboard') }}" class="btn btn-sm btn-outline-secondary rounded-pill">{{ __('Reset') }}</a>
                <a href="{{ route('admin.inventory.export.products', request()->query()) }}" class="btn btn-sm btn-outline-success rounded-pill">{{ __('Export products CSV') }}</a>
                <a href="{{ route('admin.inventory.export.variants', request()->query()) }}" class="btn btn-sm btn-outline-success rounded-pill">{{ __('Export variants CSV') }}</a>
            </div>
        </div>
    </form>

    <div id="invOverviewCards">
        <div class="row g-3 mb-3">
            <div class="col-6 col-lg-4 col-xl-3">
                <div class="admin-stat-card h-100">
                    <div class="admin-stat-card__label">{{ __('Total products') }}</div>
                    <div class="admin-stat-card__value" data-inv-kpi="total_products">{{ number_format($overview['total_products']) }}</div>
                </div>
            </div>
            <div class="col-6 col-lg-4 col-xl-3">
                <div class="admin-stat-card h-100">
                    <div class="admin-stat-card__label">{{ __('Total variants') }}</div>
                    <div class="admin-stat-card__value" data-inv-kpi="total_variants">{{ number_format($overview['total_variants']) }}</div>
                </div>
            </div>
            <div class="col-6 col-lg-4 col-xl-3">
                <div class="admin-stat-card h-100">
                    <div class="admin-stat-card__label">{{ __('Current stock (sum)') }}</div>
                    <div class="admin-stat-card__value" data-inv-kpi="total_stock_available">{{ number_format($overview['total_stock_available']) }}</div>
                </div>
            </div>
            <div class="col-6 col-lg-4 col-xl-3">
                <div class="admin-stat-card h-100">
                    <div class="admin-stat-card__label">{{ __('Remaining (after carts)') }}</div>
                    <div class="admin-stat-card__value" data-inv-kpi="remaining_stock">{{ number_format($overview['remaining_stock']) }}</div>
                </div>
            </div>
            <div class="col-6 col-lg-4 col-xl-3">
                <div class="admin-stat-card h-100">
                    <div class="admin-stat-card__label">{{ __('Reserved (carts)') }}</div>
                    <div class="admin-stat-card__value text-warning" data-inv-kpi="reserved_stock_total">{{ number_format($overview['reserved_stock_total']) }}</div>
                </div>
            </div>
            <div class="col-6 col-lg-4 col-xl-3">
                <div class="admin-stat-card h-100">
                    <div class="admin-stat-card__label">{{ __('Returned units (ledger)') }}</div>
                    <div class="admin-stat-card__value" data-inv-kpi="returned_stock_total">{{ number_format($overview['returned_stock_total']) }}</div>
                </div>
            </div>
            <div class="col-6 col-lg-4 col-xl-3">
                <div class="admin-stat-card h-100">
                    <div class="admin-stat-card__label">{{ __('Damaged (warehouse)') }}</div>
                    <div class="admin-stat-card__value" data-inv-kpi="damaged_stock_total">{{ number_format($overview['damaged_stock_total']) }}</div>
                </div>
            </div>
            <div class="col-6 col-lg-4 col-xl-3">
                <div class="admin-stat-card h-100">
                    <div class="admin-stat-card__label">{{ __('Pending order units') }}</div>
                    <div class="admin-stat-card__value" data-inv-kpi="pending_order_units">{{ number_format($overview['pending_order_units']) }}</div>
                </div>
            </div>
        </div>
        <div class="row g-3 mb-4">
            <div class="col-6 col-lg-4 col-xl-3">
                <div class="admin-stat-card h-100">
                    <div class="admin-stat-card__label">{{ __('Incoming (open POs)') }}</div>
                    <div class="admin-stat-card__value text-info" data-inv-kpi="incoming_stock_units">{{ number_format($overview['incoming_stock_units']) }}</div>
                </div>
            </div>
            <div class="col-6 col-lg-4 col-xl-3">
                <div class="admin-stat-card h-100">
                    <div class="admin-stat-card__label">{{ __('Stock added (est. lifetime)') }}</div>
                    <div class="admin-stat-card__value" data-inv-kpi="stock_added_lifetime_estimate">{{ number_format($overview['stock_added_lifetime_estimate']) }}</div>
                </div>
            </div>
            <div class="col-6 col-lg-4 col-xl-3">
                <div class="admin-stat-card h-100">
                    <div class="admin-stat-card__label">{{ __('Units sold (period)') }}</div>
                    <div class="admin-stat-card__value" data-inv-kpi="total_units_sold_period">{{ number_format($overview['total_units_sold_period']) }}</div>
                </div>
            </div>
            <div class="col-6 col-lg-4 col-xl-3">
                <div class="admin-stat-card h-100">
                    <div class="admin-stat-card__label">{{ __('Units sold (all-time)') }}</div>
                    <div class="admin-stat-card__value" data-inv-kpi="total_units_sold_all">{{ number_format($overview['total_units_sold_all']) }}</div>
                </div>
            </div>
            <div class="col-6 col-lg-4 col-xl-3">
                <div class="admin-stat-card h-100">
                    <div class="admin-stat-card__label">{{ __('OOS products') }}</div>
                    <div class="admin-stat-card__value text-danger" data-inv-kpi="out_of_stock_products">{{ number_format($overview['out_of_stock_products']) }}</div>
                </div>
            </div>
            <div class="col-6 col-lg-4 col-xl-3">
                <div class="admin-stat-card h-100">
                    <div class="admin-stat-card__label">{{ __('Low-stock variants') }}</div>
                    <div class="admin-stat-card__value text-warning" data-inv-kpi="low_stock_variants">{{ number_format($overview['low_stock_variants']) }}</div>
                </div>
            </div>
            <div class="col-6 col-lg-4 col-xl-3">
                <div class="admin-stat-card admin-stat-card--accent h-100">
                    <div class="admin-stat-card__label">{{ __('Revenue (period, paid)') }}</div>
                    <div class="admin-stat-card__value" data-inv-kpi="total_revenue" data-inv-format="inr">₹{{ number_format($overview['total_revenue'], 0) }}</div>
                </div>
            </div>
            <div class="col-6 col-lg-4 col-xl-3">
                <div class="admin-stat-card h-100">
                    <div class="admin-stat-card__label">{{ __('Orders (period, paid)') }}</div>
                    <div class="admin-stat-card__value" data-inv-kpi="total_orders">{{ number_format($overview['total_orders']) }}</div>
                </div>
            </div>
            <div class="col-6 col-lg-4 col-xl-3">
                <div class="admin-stat-card h-100">
                    <div class="admin-stat-card__label">{{ __('Est. gross profit') }}</div>
                    <div class="small text-muted mb-1">{{ __('Margin') }} {{ number_format($overview['margin_percent_setting'], 0) }}%</div>
                    <div class="admin-stat-card__value" data-inv-kpi="estimated_gross_profit" data-inv-format="inr">₹{{ number_format($overview['estimated_gross_profit'], 0) }}</div>
                </div>
            </div>
            <div class="col-6 col-lg-4 col-xl-3">
                <div class="admin-stat-card h-100">
                    <div class="admin-stat-card__label">{{ __('Inventory value (retail)') }}</div>
                    <div class="admin-stat-card__value" data-inv-kpi="inventory_value_retail" data-inv-format="inr">₹{{ number_format($overview['inventory_value_retail'], 0) }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3"><span class="fw-bold">{{ __('Sales & fulfillment (30 days)') }}</span></div>
                <div class="card-body" style="height: 320px;">
                    <canvas id="invChartTrend"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3"><span class="fw-bold">{{ __('Reports snapshot') }}</span></div>
                <div class="card-body small">
                    <div class="d-flex justify-content-between mb-2"><span>{{ __('Today — paid revenue') }}</span><strong>₹{{ number_format($dailyReport['revenue'], 0) }}</strong></div>
                    <div class="d-flex justify-content-between mb-2"><span>{{ __('Today — items') }}</span><strong>{{ number_format($dailyReport['items']) }}</strong></div>
                    <div class="d-flex justify-content-between mb-2"><span>{{ __('7 days — paid orders') }}</span><strong>{{ number_format($weeklyReport['orders']) }}</strong></div>
                    <div class="d-flex justify-content-between"><span>{{ __('7 days — revenue') }}</span><strong>₹{{ number_format($weeklyReport['revenue'], 0) }}</strong></div>
                    <p class="text-muted mt-3 mb-0">{{ __('Monthly PDF: use Export CSV or browser print; full PDF pipeline can plug in later.') }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <span class="fw-bold">{{ __('Fast movers & high demand') }}</span>
                    <span class="badge bg-success bg-opacity-10 text-success">{{ __('30d') }}</span>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm table-hover mb-0 admin-table">
                        <thead class="table-light"><tr><th>{{ __('Product') }}</th><th class="text-end">{{ __('Sold') }}</th><th class="text-end">{{ __('Rev') }}</th><th class="text-end">{{ __('Stock') }}</th><th class="text-end">{{ __('Growth %') }}</th></tr></thead>
                        <tbody>
                            @foreach($fast as $row)
                                @php $fastP = \App\Models\Product::find($row['product_id']); @endphp
                                <tr>
                                    <td>
                                        @if($fastP && filled($fastP->slug))
                                            <a href="{{ route('admin.products.edit', ['product' => $fastP->slug]) }}" class="text-decoration-none fw-semibold" style="color: var(--admin-teal);">{{ \Illuminate\Support\Str::limit($row['name'], 28) }}</a>
                                        @else
                                            <span class="fw-semibold">{{ \Illuminate\Support\Str::limit($row['name'], 28) }}</span>
                                        @endif
                                    </td>
                                    <td class="text-end">{{ $row['sold_qty'] }}</td>
                                    <td class="text-end">₹{{ number_format($row['revenue'], 0) }}</td>
                                    <td class="text-end">{{ $row['remaining_stock'] }}</td>
                                    <td class="text-end @if($row['growth_pct'] >= 0) text-success @else text-danger @endif">{{ $row['growth_pct'] }}%</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3"><span class="fw-bold">{{ __('Best sellers (catalog units)') }}</span></div>
                <div class="table-responsive">
                    <table class="table table-sm table-hover mb-0 admin-table">
                        <thead class="table-light"><tr><th>{{ __('Product') }}</th><th class="text-end">{{ __('Lifetime sales #') }}</th></tr></thead>
                        <tbody>
                            @foreach($mostOrdered as $p)
                                <tr>
                                    <td>
                                        @if(filled($p->slug))
                                            <a href="{{ route('admin.products.edit', ['product' => $p->slug]) }}" class="text-decoration-none">{{ \Illuminate\Support\Str::limit($p->name, 36) }}</a>
                                        @else
                                            {{ \Illuminate\Support\Str::limit($p->name, 36) }}
                                        @endif
                                    </td>
                                    <td class="text-end fw-semibold">{{ number_format($p->sales_count) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="card-footer small text-muted border-0">{{ __('“Most viewed” uses lifetime sales as a proxy until product view tracking is enabled.') }}</div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white py-3"><span class="fw-bold">{{ __('Slow / dead stock (no paid sales 90d, highest on-hand)') }}</span></div>
        <div class="table-responsive">
            <table class="table table-sm table-hover mb-0 admin-table">
                <thead class="table-light"><tr><th>{{ __('Product') }}</th><th>{{ __('SKU') }}</th><th class="text-end">{{ __('On hand') }}</th><th>{{ __('Note') }}</th></tr></thead>
                <tbody>
                    @foreach($slow as $row)
                        <tr>
                            <td>{{ \Illuminate\Support\Str::limit($row['name'], 40) }}</td>
                            <td class="font-monospace small">{{ $row['sku'] }}</td>
                            <td class="text-end">{{ $row['stock'] }}</td>
                            <td class="small text-muted">{{ $row['reason'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white py-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
            <span class="fw-bold">{{ __('Top variants (paid orders)') }}</span>
            <span class="small text-muted">{{ __('By SKU — units sold') }}</span>
        </div>
        <div class="table-responsive">
            <table class="table table-sm table-hover mb-0 admin-table">
                <thead class="table-light"><tr><th>{{ __('Product') }}</th><th>{{ __('SKU') }}</th><th class="text-end">{{ __('Sold') }}</th><th class="text-end">{{ __('Revenue') }}</th></tr></thead>
                <tbody>
                    @foreach($variantCombo as $vc)
                        <tr>
                            <td>{{ \Illuminate\Support\Str::limit($vc['product_name'], 40) }}</td>
                            <td class="font-monospace small">{{ $vc['sku'] }}</td>
                            <td class="text-end">{{ $vc['sold'] }}</td>
                            <td class="text-end">₹{{ number_format($vc['revenue'], 0) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white py-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div>
                <span class="fw-bold">{{ __('Variant inventory') }}</span>
                <span class="small text-muted d-block">{{ __('Per SKU: carts and paid sales in the selected period.') }}</span>
            </div>
            <span class="badge rounded-pill border" style="background: var(--admin-teal-soft); color: var(--admin-teal-hover);">{{ $variantInventory->total() }}</span>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 admin-table">
                <thead class="table-light">
                    <tr>
                        <th>{{ __('Product') }}</th>
                        <th>{{ __('SKU') }}</th>
                        @if($hasProductBarcode)<th>{{ __('Barcode') }}</th>@endif
                        @if($hasProductBrand)<th>{{ __('Brand') }}</th>@endif
                        <th class="text-end">{{ __('Stock') }}</th>
                        <th class="text-end">{{ __('Reserved') }}</th>
                        <th class="text-end">{{ __('Avail (est.)') }}</th>
                        <th class="text-end">{{ __('Sold') }}</th>
                        <th class="text-end">{{ __('Revenue') }}</th>
                        <th class="text-end">{{ __('Margin %') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($variantInventory as $vv)
                        @php
                            $pv = $vv->product;
                            $stk = (int) $vv->stock_qty;
                            $rv = (int) ($vv->va_reserved ?? 0);
                            $avail = max(0, $stk - $rv);
                            $rev = (float) ($vv->va_revenue ?? 0);
                            $marginPct = (float) ($overview['margin_percent_setting'] ?? 25);
                        @endphp
                        <tr>
                            <td class="fw-semibold small">
                                @if($pv && filled($pv->slug))
                                    <a href="{{ route('admin.products.edit', ['product' => $pv->slug]) }}" class="text-decoration-none" style="color: var(--admin-teal);">{{ \Illuminate\Support\Str::limit($pv->name, 32) }}</a>
                                @else
                                    {{ \Illuminate\Support\Str::limit($pv->name ?? '—', 32) }}
                                @endif
                            </td>
                            <td class="small font-monospace">{{ $vv->sku }}</td>
                            @if($hasProductBarcode)<td class="small font-monospace">{{ $pv->barcode ?? '—' }}</td>@endif
                            @if($hasProductBrand)<td class="small">{{ $pv->brand ?? '—' }}</td>@endif
                            <td class="text-end">{{ $stk }}</td>
                            <td class="text-end @if($rv > 0) text-warning @endif">{{ $rv }}</td>
                            <td class="text-end">{{ $avail }}</td>
                            <td class="text-end">{{ (int) ($vv->va_sold_qty ?? 0) }}</td>
                            <td class="text-end">₹{{ number_format($rev, 0) }}</td>
                            <td class="text-end small text-muted">{{ number_format($marginPct, 0) }}%</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if ($variantInventory->hasPages())
            <div class="card-footer bg-white border-top py-3">
                <div class="d-flex flex-column flex-xl-row align-items-stretch align-items-xl-center justify-content-between gap-3">
                    <div class="small text-muted text-center text-xl-start">
                        {{ __('Showing :from to :to of :total variants', [
                            'from' => $variantInventory->firstItem(),
                            'to' => $variantInventory->lastItem(),
                            'total' => $variantInventory->total(),
                        ]) }}
                    </div>
                    <div class="flex-grow-1 d-flex justify-content-center justify-content-xl-end">
                        {{ $variantInventory->links('admin.components.pagination-advanced') }}
                    </div>
                </div>
            </div>
        @endif
    </div>

    <p class="small text-muted mb-0">
        {{ __('Product-level stock, bulk adjustments, and movement history are on') }}
        <a href="{{ route('admin.inventory.product-inventory') }}">{{ __('Product inventory') }}</a>.
    </p>
@endsection
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    if (typeof Chart === 'undefined') return;

    var trendLabels = @json($trend['labels']);
    var rev = @json($trend['rev']);
    var ord = @json($trend['ord']);
    var invOut = @json($trend['invOut']);

    var ctx1 = document.getElementById('invChartTrend');
    if (ctx1) {
        new Chart(ctx1, {
            type: 'line',
            data: {
                labels: trendLabels,
                datasets: [
                    { label: @json(__('Revenue (₹)')), data: rev, borderColor: '#0d9488', tension: 0.25, yAxisID: 'y' },
                    { label: @json(__('Orders')), data: ord, borderColor: '#6366f1', tension: 0.2, yAxisID: 'y1' },
                    { label: @json(__('Units shipped')), data: invOut, borderColor: '#f97316', tension: 0.2, yAxisID: 'y1' }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                scales: {
                    y: { type: 'linear', display: true, position: 'left' },
                    y1: { type: 'linear', display: true, position: 'right', grid: { drawOnChartArea: false } }
                }
            }
        });
    }

    document.getElementById('invAjaxRefresh')?.addEventListener('click', function () {
        var u = new URL(@json(route('admin.inventory.api.summary')), window.location.origin);
        u.searchParams.set('from', document.querySelector('input[name=from]')?.value || '');
        u.searchParams.set('to', document.querySelector('input[name=to]')?.value || '');
        fetch(u, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (!data.overview) return;
                var el = document.getElementById('invOverviewCards');
                if (!el) return;
                var o = data.overview;
                el.querySelectorAll('[data-inv-kpi]').forEach(function (node) {
                    var k = node.getAttribute('data-inv-kpi');
                    if (o[k] === undefined || o[k] === null) return;
                    var fmt = node.getAttribute('data-inv-format');
                    var v = o[k];
                    if (fmt === 'inr') {
                        node.textContent = '₹' + Number(v).toLocaleString(undefined, { maximumFractionDigits: 0 });
                    } else {
                        node.textContent = Number(v).toLocaleString();
                    }
                });
            });
    });
});
</script>
@endpush
