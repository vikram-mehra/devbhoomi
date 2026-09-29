@extends('layouts.admin')

@section('title', __('Products'))

@section('page_subtitle')
    {{ __('Manage catalog, visibility, and featured picks.') }}
@endsection

@push('styles')
<style>
    .admin-prod-thumb { width: 44px; height: 44px; object-fit: cover; border-radius: 10px; background: var(--admin-page-bg); }
    .admin-prod-actions .btn { padding: 0.35rem 0.55rem; }
    .admin-prod-table-scroll { max-height: min(72vh, 780px); overflow: auto; -webkit-overflow-scrolling: touch; }
    .admin-prod-table--sticky thead th {
        position: sticky;
        top: 0;
        z-index: 3;
        box-shadow: inset 0 -1px 0 rgba(0, 0, 0, 0.06);
        background-color: var(--bs-table-bg, #f8f9fa) !important;
    }
    .admin-prod-catalog-card.is-loading { pointer-events: none; opacity: 0.55; transition: opacity 0.2s ease; }
    #adm-products-skeleton.adm-prod-skeleton-wrap { min-height: 220px; }
    .adm-prod-skeleton-row { height: 52px; background: linear-gradient(90deg, #f1f3f5 25%, #e9ecef 50%, #f1f3f5 75%); background-size: 200% 100%; animation: adm-prod-shimmer 1.1s ease-in-out infinite; border-radius: 8px; margin-bottom: 8px; }
    @keyframes adm-prod-shimmer { 0% { background-position: 200% 0; } 100% { background-position: -200% 0; } }
    .admin-pagination-advanced .page-link { border-radius: 0.35rem; }
    .admin-stock-ctrl { min-width: 108px; }
    .admin-stock-stepper .btn { line-height: 1; padding: 0.2rem 0.45rem; font-weight: 700; }
    .admin-stock-ctrl.is-busy { opacity: 0.55; pointer-events: none; }
    .admin-stock-variant { max-width: 140px; font-size: 0.75rem; }
    @media (max-width: 575.98px) {
        .admin-pagination-advanced .pagination { gap: 0.15rem !important; }
        .admin-pagination-advanced .page-link { padding: 0.25rem 0.45rem; font-size: 0.8125rem; }
    }
</style>
@endpush

@section('content')
    <div class="alert alert-warning py-2 small mb-3">
        <strong>{{ __('This store') }}:</strong>
        <code>{{ config('app.url') }}</code>
        · {{ __('Database') }}: <code>{{ config('database.connections.'.config('database.default').'.database') }}</code>
        — {{ __('Products appear only on this site when Active, vendor is approved, and you open the same URL on the storefront.') }}
    </div>
    @php
        $hasFilters = request()->hasAny(['q', 'menu_item_id', 'brand', 'stock_filter', 'date_from', 'date_to', 'sort', 'per_page', 'page', 'low_stock']);
        $perPageCurrent = (int) request('per_page', 20);
        if (! in_array($perPageCurrent, [10, 25, 50, 100], true)) {
            $perPageCurrent = 20;
        }
        $stockFilter = request('stock_filter');
        if (request()->boolean('low_stock')) {
            $stockFilter = 'low';
        }
        $stockFilter = in_array($stockFilter, ['low', 'out', 'in_stock'], true) ? $stockFilter : 'all';
    @endphp

    <div class="row g-3 mb-4 admin-prod-stats">
        <div class="col-6 col-md-4 col-xl-2">
            <div class="admin-stat-card admin-stat-card--accent h-100">
                <div class="admin-stat-card__label">{{ __('Total') }}</div>
                <div class="admin-stat-card__value">{{ $stats['total'] }}</div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-xl-2">
            <div class="admin-stat-card h-100">
                <div class="admin-stat-card__label">{{ __('Active') }}</div>
                <div class="admin-stat-card__value text-success">{{ $stats['active'] }}</div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-xl-2">
            <div class="admin-stat-card h-100">
                <div class="admin-stat-card__label">{{ __('Featured') }}</div>
                <div class="admin-stat-card__value" style="color: #c2185b;">{{ $stats['featured'] }}</div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-3 admin-prod-filter-card">
        <div class="card-body p-3">
            <form method="get" action="{{ route('admin.products.index') }}" id="adm-products-filters" class="mb-0">
                <div class="row g-3 align-items-end">
                    <div class="col-12 col-lg-4">
                        <label class="form-label small mb-1 text-muted" for="admProdSearchQ">{{ __('Search') }}</label>
                        <input id="admProdSearchQ" type="search" name="q" value="{{ request('q') }}" class="form-control" placeholder="{{ __('Name, SKU, brand, variant SKU…') }}" autocomplete="off">
                    </div>
                    <div class="col-6 col-md-4 col-lg-2">
                        <label class="form-label small mb-1 text-muted" for="admProdMenu">{{ __('Menu / Service') }}</label>
                        <select name="menu_item_id" id="admProdMenu" class="form-select">
                            <option value="">{{ __('All') }}</option>
                            @foreach ($menuOptions as $opt)
                                <option value="{{ $opt['id'] }}" {{ (string) request('menu_item_id') === (string) $opt['id'] ? 'selected' : '' }}>{{ $opt['label'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-6 col-md-4 col-lg-2">
                        <label class="form-label small mb-1 text-muted" for="admProdBrand">{{ __('Brand') }}</label>
                        <select name="brand" id="admProdBrand" class="form-select">
                            <option value="">{{ __('All') }}</option>
                            @foreach ($brands as $b)
                                <option value="{{ $b }}" {{ request('brand') === $b ? 'selected' : '' }}>{{ $b }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-6 col-md-4 col-lg-2">
                        <label class="form-label small mb-1 text-muted" for="admProdStock">{{ __('Stock') }}</label>
                        <select name="stock_filter" id="admProdStock" class="form-select">
                            <option value="all" {{ $stockFilter === 'all' ? 'selected' : '' }}>{{ __('All') }}</option>
                            <option value="in_stock" {{ $stockFilter === 'in_stock' ? 'selected' : '' }}>{{ __('In stock') }}</option>
                            <option value="low" {{ $stockFilter === 'low' ? 'selected' : '' }}>{{ __('Low (1–4)') }}</option>
                            <option value="out" {{ $stockFilter === 'out' ? 'selected' : '' }}>{{ __('Out of stock') }}</option>
                        </select>
                    </div>
                    <div class="col-6 col-md-4 col-lg-2">
                        <label class="form-label small mb-1 text-muted" for="admProdSort">{{ __('Sort') }}</label>
                        <select name="sort" id="admProdSort" class="form-select">
                            <option value="created_at_desc" {{ request('sort', 'created_at_desc') === 'created_at_desc' ? 'selected' : '' }}>{{ __('Newest') }}</option>
                            <option value="created_at_asc" {{ request('sort') === 'created_at_asc' ? 'selected' : '' }}>{{ __('Oldest') }}</option>
                            <option value="name_asc" {{ request('sort') === 'name_asc' ? 'selected' : '' }}>{{ __('Name A–Z') }}</option>
                            <option value="name_desc" {{ request('sort') === 'name_desc' ? 'selected' : '' }}>{{ __('Name Z–A') }}</option>
                            <option value="price_asc" {{ request('sort') === 'price_asc' ? 'selected' : '' }}>{{ __('Price ↑') }}</option>
                            <option value="price_desc" {{ request('sort') === 'price_desc' ? 'selected' : '' }}>{{ __('Price ↓') }}</option>
                        </select>
                    </div>
                    <div class="col-6 col-md-4 col-lg-2">
                        <label class="form-label small mb-1 text-muted" for="admProdPerPage">{{ __('Per page') }}</label>
                        <select name="per_page" id="admProdPerPage" class="form-select">
                            @foreach ([10, 25, 50, 100] as $pp)
                                <option value="{{ $pp }}" {{ $perPageCurrent === $pp ? 'selected' : '' }}>{{ $pp }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-6 col-md-4 col-lg-2">
                        <label class="form-label small mb-1 text-muted" for="admDateFrom">{{ __('From date') }}</label>
                        <input type="date" name="date_from" id="admDateFrom" class="form-control" value="{{ request('date_from') }}">
                    </div>
                    <div class="col-6 col-md-4 col-lg-2">
                        <label class="form-label small mb-1 text-muted" for="admDateTo">{{ __('To date') }}</label>
                        <input type="date" name="date_to" id="admDateTo" class="form-control" value="{{ request('date_to') }}">
                    </div>
                    <div class="col-12 col-lg-auto d-flex flex-wrap gap-2 align-items-center">
                        <button type="submit" class="btn btn-sm btn-outline-primary rounded-pill px-3">{{ __('Apply filters') }}</button>
                        @if ($hasFilters)
                            <a href="{{ route('admin.products.index') }}" class="btn btn-sm btn-outline-secondary rounded-pill">{{ __('Clear all') }}</a>
                        @endif
                    </div>
                    <div class="col-12 col-lg-auto ms-lg-auto">
                        <a href="{{ route('admin.products.create') }}" class="btn btn-primary rounded-pill px-4 fw-semibold shadow-sm w-100">
                            <i class="bi bi-plus-lg me-1"></i>{{ __('Add product') }}
                        </a>
                    </div>
                </div>
                <p class="small text-muted mb-0 mt-3 pt-2 border-top border-light-subtle">
                    <i class="bi bi-image me-1 opacity-75"></i>{{ __('Gallery & variant images are uploaded when creating or editing a product.') }}
                </p>
            </form>
        </div>
    </div>

    <div
        id="adm-products-mount"
        class="position-relative mb-4"
        data-json-url="{{ route('admin.products.table') }}"
        data-index-url="{{ route('admin.products.index') }}"
    >
        <div id="adm-products-skeleton" class="adm-prod-skeleton-wrap d-none mb-3" aria-live="polite" aria-busy="false" aria-hidden="true">
            @for ($i = 0; $i < 6; $i++)
                <div class="adm-prod-skeleton-row"></div>
            @endfor
        </div>
        <div id="adm-products-content">
            @include('admin.products.partials.catalog-panel', ['products' => $products])
        </div>
    </div>
@endsection

@push('scripts')
<script>
(function () {
    var mount = document.getElementById('adm-products-mount');
    var content = document.getElementById('adm-products-content');
    var skeleton = document.getElementById('adm-products-skeleton');
    var form = document.getElementById('adm-products-filters');
    var jsonUrl = mount && mount.getAttribute('data-json-url');
    var indexUrl = mount && mount.getAttribute('data-index-url');
    if (!mount || !content || !jsonUrl || !form) return;

    function showLoading(show) {
        if (skeleton) {
            skeleton.classList.toggle('d-none', !show);
            skeleton.setAttribute('aria-busy', show ? 'true' : 'false');
            skeleton.setAttribute('aria-hidden', show ? 'false' : 'true');
        }
        content.classList.toggle('d-none', show);
        var card = content.querySelector('.admin-prod-catalog-card');
        if (card) card.classList.toggle('is-loading', show);
    }

    function queryFromForm(resetPage) {
        var fd = new FormData(form);
        var params = new URLSearchParams(fd);
        if (resetPage) params.delete('page');
        return params.toString();
    }

    function fetchPanel(qs, opts) {
        opts = opts || {};
        showLoading(true);
        var url = jsonUrl + (qs ? '?' + qs : '');
        fetch(url, {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            credentials: 'same-origin'
        }).then(function (r) {
            if (!r.ok) throw new Error('Network');
            return r.json();
        }).then(function (data) {
            content.innerHTML = data.html;
            showLoading(false);
            var histUrl = indexUrl + (qs ? '?' + qs : '');
            if (opts.pushState !== false) {
                try { history.replaceState({}, '', histUrl); } catch (e) {}
            }
        }).catch(function () {
            showLoading(false);
            window.location.href = indexUrl + (qs ? '?' + qs : '');
        });
    }

    form.addEventListener('submit', function (e) {
        if (!window.fetch) return;
        e.preventDefault();
        fetchPanel(queryFromForm(true), {});
    });

    document.getElementById('admProdPerPage')?.addEventListener('change', function () {
        if (!window.fetch) return;
        fetchPanel(queryFromForm(true), {});
    });

    mount.addEventListener('click', function (e) {
        var a = e.target.closest('a.js-adm-prod-ajax');
        if (!a || !mount.contains(a)) return;
        e.preventDefault();
        try {
            var u = new URL(a.getAttribute('href'), window.location.origin);
            fetchPanel(u.search.substring(1), {});
        } catch (err) {
            window.location.href = a.href;
        }
    });

    var csrf = document.querySelector('meta[name="csrf-token"]');
    var csrfToken = csrf ? csrf.getAttribute('content') : '';

    function updateStockCtrl(ctrl, data) {
        var qtyEl = ctrl.querySelector('.admin-stock-qty');
        var minusBtn = ctrl.querySelector('.admin-stock-btn[data-delta="-1"]');
        var totalEl = ctrl.querySelector('.admin-stock-total');
        var select = ctrl.querySelector('.admin-stock-variant');
        if (qtyEl) qtyEl.textContent = String(data.variant_stock);
        if (minusBtn) minusBtn.disabled = data.variant_stock <= 0;
        if (totalEl) totalEl.textContent = String(data.stock);
        if (select) {
            var opt = select.querySelector('option[value="' + data.variant_id + '"]');
            if (opt) {
                opt.dataset.stock = String(data.variant_stock);
                var label = opt.textContent.replace(/\(\d+\)\s*$/, '').trim();
                opt.textContent = label + ' (' + data.variant_stock + ')';
            }
        }
        ctrl.dataset.variantId = String(data.variant_id);
        var lowBadge = ctrl.querySelector('.admin-stock-low-badge');
        if (lowBadge && ctrl.dataset.multiVariant !== '1') {
            lowBadge.classList.toggle('d-none', !(data.stock > 0 && data.stock < 5));
        }
    }

    mount.addEventListener('change', function (e) {
        var select = e.target.closest('.admin-stock-variant');
        if (!select || !mount.contains(select)) return;
        var ctrl = select.closest('.admin-stock-ctrl');
        if (!ctrl) return;
        var opt = select.options[select.selectedIndex];
        var stock = parseInt(opt.getAttribute('data-stock') || '0', 10);
        var qtyEl = ctrl.querySelector('.admin-stock-qty');
        var minusBtn = ctrl.querySelector('.admin-stock-btn[data-delta="-1"]');
        if (qtyEl) qtyEl.textContent = String(stock);
        if (minusBtn) minusBtn.disabled = stock <= 0;
        ctrl.dataset.variantId = select.value;
    });

    mount.addEventListener('click', function (e) {
        var btn = e.target.closest('.admin-stock-btn');
        if (!btn || btn.disabled || !mount.contains(btn)) return;
        var ctrl = btn.closest('.admin-stock-ctrl');
        if (!ctrl || ctrl.classList.contains('is-busy')) return;
        var url = ctrl.getAttribute('data-adjust-url');
        if (!url || !window.fetch) return;
        e.preventDefault();

        var delta = parseInt(btn.getAttribute('data-delta') || '0', 10);
        var variantId = ctrl.dataset.variantId || '';
        var body = new URLSearchParams();
        body.set('delta', String(delta));
        if (variantId) body.set('variant_id', variantId);

        ctrl.classList.add('is-busy');
        fetch(url, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest'
            },
            credentials: 'same-origin',
            body: body.toString()
        }).then(function (r) {
            return r.json().then(function (data) {
                if (!r.ok || !data.ok) throw new Error(data.message || 'Error');
                return data;
            });
        }).then(function (data) {
            updateStockCtrl(ctrl, data);
        }).catch(function (err) {
            window.alert(err.message || @json(__('Could not update stock.')));
        }).finally(function () {
            ctrl.classList.remove('is-busy');
        });
    });
})();
</script>
@endpush
