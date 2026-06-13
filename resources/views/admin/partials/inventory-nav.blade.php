@php
    $inventoryOpen = request()->routeIs('admin.inventory.*', 'admin.products.*');
    $transactionsOpen = request()->routeIs('admin.purchases.*', 'admin.sales.*', 'admin.returns.*');
    $managementOpen = request()->routeIs('admin.suppliers.*', 'admin.warehouses.*');
    $reportsOpen = request()->routeIs('admin.reports.inventory', 'admin.inventory.ledger*', 'admin.reports.variant-sales');
@endphp

<div class="admin-nav-group {{ $inventoryOpen ? 'is-open' : '' }}" data-nav-group>
    <button type="button" class="admin-nav-group__toggle" aria-expanded="{{ $inventoryOpen ? 'true' : 'false' }}">
        <span class="admin-nav-group__title"><i class="bi bi-box-seam" aria-hidden="true"></i>{{ __('Inventory') }}</span>
        <i class="bi bi-chevron-down admin-nav-group__chevron" aria-hidden="true"></i>
    </button>
    <div class="admin-nav-group__panel">
        <a href="{{ route('admin.inventory.dashboard') }}" class="admin-sidebar__link {{ request()->routeIs('admin.inventory.dashboard') ? 'is-active' : '' }}">
            <i class="bi bi-speedometer2" aria-hidden="true"></i>{{ __('Dashboard') }}
        </a>
        <a href="{{ route('admin.products.index') }}" class="admin-sidebar__link {{ request()->routeIs('admin.products.*') ? 'is-active' : '' }}">
            <i class="bi bi-bag" aria-hidden="true"></i>{{ __('Products') }}
        </a>
        <a href="{{ route('admin.inventory.product-inventory') }}" class="admin-sidebar__link {{ request()->routeIs('admin.inventory.product-inventory') ? 'is-active' : '' }}">
            <i class="bi bi-boxes" aria-hidden="true"></i>{{ __('Product inventory / stock') }}
        </a>
    </div>
</div>

<div class="admin-nav-group {{ $transactionsOpen ? 'is-open' : '' }}" data-nav-group>
    <button type="button" class="admin-nav-group__toggle" aria-expanded="{{ $transactionsOpen ? 'true' : 'false' }}">
        <span class="admin-nav-group__title"><i class="bi bi-cash-stack" aria-hidden="true"></i>{{ __('Transactions') }}</span>
        <i class="bi bi-chevron-down admin-nav-group__chevron" aria-hidden="true"></i>
    </button>
    <div class="admin-nav-group__panel">
        <a href="{{ route('admin.purchases.index') }}" class="admin-sidebar__link {{ request()->routeIs('admin.purchases.*') ? 'is-active' : '' }}">
            <i class="bi bi-cart-plus" aria-hidden="true"></i>{{ __('Purchases') }}
        </a>
        <a href="{{ route('admin.sales.index') }}" class="admin-sidebar__link {{ request()->routeIs('admin.sales.*') ? 'is-active' : '' }}">
            <i class="bi bi-receipt" aria-hidden="true"></i>{{ __('Sales') }}
        </a>
        <a href="{{ route('admin.returns.index') }}" class="admin-sidebar__link {{ request()->routeIs('admin.returns.*') ? 'is-active' : '' }}">
            <i class="bi bi-arrow-counterclockwise" aria-hidden="true"></i>{{ __('Returns') }}
        </a>
    </div>
</div>

<div class="admin-nav-group {{ $managementOpen ? 'is-open' : '' }}" data-nav-group>
    <button type="button" class="admin-nav-group__toggle" aria-expanded="{{ $managementOpen ? 'true' : 'false' }}">
        <span class="admin-nav-group__title"><i class="bi bi-building" aria-hidden="true"></i>{{ __('Management') }}</span>
        <i class="bi bi-chevron-down admin-nav-group__chevron" aria-hidden="true"></i>
    </button>
    <div class="admin-nav-group__panel">
        <a href="{{ route('admin.suppliers.index') }}" class="admin-sidebar__link {{ request()->routeIs('admin.suppliers.*') ? 'is-active' : '' }}">
            <i class="bi bi-truck" aria-hidden="true"></i>{{ __('Suppliers') }}
        </a>
        <a href="{{ route('admin.warehouses.index') }}" class="admin-sidebar__link {{ request()->routeIs('admin.warehouses.*') ? 'is-active' : '' }}">
            <i class="bi bi-house-door" aria-hidden="true"></i>{{ __('Warehouses') }}
        </a>
    </div>
</div>

<div class="admin-nav-group {{ $reportsOpen ? 'is-open' : '' }}" data-nav-group>
    <button type="button" class="admin-nav-group__toggle" aria-expanded="{{ $reportsOpen ? 'true' : 'false' }}">
        <span class="admin-nav-group__title"><i class="bi bi-bar-chart-line" aria-hidden="true"></i>{{ __('Reports') }}</span>
        <i class="bi bi-chevron-down admin-nav-group__chevron" aria-hidden="true"></i>
    </button>
    <div class="admin-nav-group__panel">
        <a href="{{ route('admin.reports.inventory') }}" class="admin-sidebar__link {{ request()->routeIs('admin.reports.inventory') ? 'is-active' : '' }}">
            <i class="bi bi-clipboard-data" aria-hidden="true"></i>{{ __('Inventory reports') }}
        </a>
        <a href="{{ route('admin.inventory.ledger') }}" class="admin-sidebar__link {{ request()->routeIs('admin.inventory.ledger*') ? 'is-active' : '' }}">
            <i class="bi bi-journal-text" aria-hidden="true"></i>{{ __('Stock ledger') }}
        </a>
        <a href="{{ route('admin.reports.variant-sales') }}" class="admin-sidebar__link {{ request()->routeIs('admin.reports.variant-sales') ? 'is-active' : '' }}">
            <i class="bi bi-graph-up-arrow" aria-hidden="true"></i>{{ __('Sales reports') }}
        </a>
    </div>
</div>
