@php
    $activeTab = $transactionTab ?? 'purchases';
    $filterAction = $filterAction ?? request()->url();
@endphp

<div class="admin-txn-hub mb-4">
    <div class="admin-txn-hub__head">
        <div class="admin-txn-tabs" role="tablist" aria-label="{{ __('Transactions') }}">
            <a href="{{ route('admin.purchases.index', request()->except('page')) }}" class="admin-txn-tabs__item {{ $activeTab === 'purchases' ? 'is-active' : '' }}">{{ __('Purchases') }}</a>
            <a href="{{ route('admin.sales.index', request()->except('page')) }}" class="admin-txn-tabs__item {{ $activeTab === 'sales' ? 'is-active' : '' }}">{{ __('Sales') }}</a>
            <a href="{{ route('admin.returns.index') }}" class="admin-txn-tabs__item {{ $activeTab === 'returns' ? 'is-active' : '' }}">{{ __('Returns') }}</a>
        </div>
        <div class="admin-txn-hub__actions">
            <a href="{{ route('admin.purchases.create') }}" class="btn btn-sm btn-primary rounded-pill"><i class="bi bi-plus-lg me-1"></i>{{ __('Add purchase') }}</a>
            <a href="{{ route('admin.sales.create') }}" class="btn btn-sm btn-outline-primary rounded-pill"><i class="bi bi-plus-lg me-1"></i>{{ __('Add sale') }}</a>
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-6 col-lg-3">
            <div class="admin-txn-stat">
                <span class="admin-txn-stat__label">{{ __('Total purchase') }}</span>
                <span class="admin-txn-stat__value">₹{{ number_format((float) ($summaries['total_purchase'] ?? 0), 0) }}</span>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="admin-txn-stat">
                <span class="admin-txn-stat__label">{{ __('Total sales') }}</span>
                <span class="admin-txn-stat__value">₹{{ number_format((float) ($summaries['total_sales'] ?? 0), 0) }}</span>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="admin-txn-stat">
                <span class="admin-txn-stat__label">{{ __('Pending payments') }}</span>
                <span class="admin-txn-stat__value text-warning">₹{{ number_format((float) ($summaries['pending_payments'] ?? 0), 0) }}</span>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="admin-txn-stat">
                <span class="admin-txn-stat__label">{{ __('Low stock') }}</span>
                <span class="admin-txn-stat__value text-danger">{{ number_format((int) ($summaries['low_stock'] ?? 0)) }}</span>
            </div>
        </div>
    </div>

    @if($activeTab !== 'returns')
        <form method="get" action="{{ $filterAction }}" class="card border-0 shadow-sm admin-txn-filters">
            <div class="card-body p-3">
                <div class="row g-2 align-items-end">
                    <div class="col-6 col-md-2">
                        <label class="form-label small mb-1">{{ __('From date') }}</label>
                        <input type="date" name="date_from" class="form-control form-control-sm" value="{{ request('date_from') }}">
                    </div>
                    <div class="col-6 col-md-2">
                        <label class="form-label small mb-1">{{ __('To date') }}</label>
                        <input type="date" name="date_to" class="form-control form-control-sm" value="{{ request('date_to') }}">
                    </div>
                    @if($activeTab === 'purchases')
                        <div class="col-12 col-md-3">
                            <label class="form-label small mb-1">{{ __('Supplier') }}</label>
                            <select name="supplier_id" class="form-select form-select-sm">
                                <option value="">{{ __('All suppliers') }}</option>
                                @foreach(($suppliers ?? []) as $supplier)
                                    <option value="{{ $supplier->id }}" @selected((string) request('supplier_id') === (string) $supplier->id)>{{ $supplier->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif
                    <div class="col-12 col-md-3">
                        <label class="form-label small mb-1">{{ __('Warehouse') }}</label>
                        <select name="warehouse_id" class="form-select form-select-sm">
                            <option value="">{{ __('All warehouses') }}</option>
                            @foreach(($warehouses ?? []) as $warehouse)
                                <option value="{{ $warehouse->id }}" @selected((string) request('warehouse_id') === (string) $warehouse->id)>{{ $warehouse->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-md-2">
                        <label class="form-label small mb-1">{{ __('Payment status') }}</label>
                        <select name="payment_status" class="form-select form-select-sm">
                            <option value="">{{ __('All') }}</option>
                            @foreach(['pending', 'partial', 'paid', 'refunded'] as $status)
                                <option value="{{ $status }}" @selected(request('payment_status') === $status)>{{ ucfirst($status) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-md-3">
                        <label class="form-label small mb-1">{{ __('Search') }}</label>
                        <input type="search" name="q" class="form-control form-control-sm" value="{{ request('q') }}" placeholder="{{ $activeTab === 'purchases' ? __('Invoice number…') : __('Invoice or customer…') }}">
                    </div>
                    <div class="col-12 col-md-auto d-flex flex-wrap gap-2">
                        <button type="submit" class="btn btn-sm btn-primary rounded-pill">{{ __('Apply') }}</button>
                        <a href="{{ $filterAction }}" class="btn btn-sm btn-outline-secondary rounded-pill">{{ __('Reset') }}</a>
                    </div>
                </div>
            </div>
        </form>
    @endif
</div>
