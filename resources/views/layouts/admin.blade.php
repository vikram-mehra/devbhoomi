<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-admin-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @include('partials.favicon')
    <title>@yield('title', 'Admin') — {{ config('app.name') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,400&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="{{ asset('css/admin-dashboard.css') }}?v=10" rel="stylesheet">
    @stack('styles')
</head>
@php
    $adminUser = auth()->user();
    $adminInitial = strtoupper(substr($adminUser->name ?? $adminUser->email ?? 'A', 0, 1));
@endphp
<body class="admin-body">
    <div class="admin-app">
        <div id="adminSidebarBackdrop" class="admin-sidebar-backdrop" aria-hidden="true"></div>

        <aside id="adminSidebar" class="admin-sidebar" aria-label="{{ __('Admin navigation') }}">
            <a href="{{ route('admin.dashboard') }}" class="admin-sidebar__brand">
                @if(!empty($siteLogoUrl))
                    <img src="{{ $siteLogoUrl }}" alt="{{ config('app.name') }}" class="admin-sidebar__brand-logo" width="130" height="36" decoding="async">
                @else
                    <span class="admin-sidebar__brand-mark">{{ strtoupper(substr(config('app.name', 'Z'), 0, 1)) }}</span>
                    <span>{{ config('app.name', 'Admin') }}</span>
                @endif
            </a>

            <nav class="admin-sidebar__nav">
                <div class="admin-sidebar__label">{{ __('Main') }}</div>
                <a href="{{ route('admin.dashboard') }}" class="admin-sidebar__link {{ request()->routeIs('admin.dashboard') ? 'is-active' : '' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z"/></svg>
                    {{ __('Dashboard') }}
                </a>
                <a href="{{ route('admin.vendors.index') }}" class="admin-sidebar__link {{ request()->routeIs('admin.vendors.*') ? 'is-active' : '' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 21v-7.5a.75.75 0 01.75-.75h3a.75.75 0 01.75.75V21m-4.5 0H2.36m11.14 0H18m0 0h3.64m-1.39 0V9.349m-16.5 11.65V9.35m0 0a3.001 3.001 0 003.75-.615A2.993 2.993 0 009.75 9.75c.896 0 1.7-.393 2.25-1.016a2.993 2.993 0 002.25 1.016c.896 0 1.7-.393 2.25-1.016a3.001 3.001 0 003.75.614m-16.5 0a3.004 3.004 0 01-.621-4.72L4.318 3.44A1.5 1.5 0 015.378 3h13.243a1.5 1.5 0 011.06.44l1.19 1.189a3 3 0 01-.621 4.72m-6.83 18h2.5a2.25 2.25 0 002.25-2.25V15a2.25 2.25 0 00-2.25-2.25h-2.5m-5 0H4.5A2.25 2.25 0 002.25 15v3.75A2.25 2.25 0 004.5 21h2.5"/></svg>
                    {{ __('Vendors') }}
                </a>
                <a href="{{ route('admin.users.index') }}" class="admin-sidebar__link {{ request()->routeIs('admin.users.*') ? 'is-active' : '' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z"/></svg>
                    {{ __('Users') }}
                </a>
                <a href="{{ route('admin.menu-items.index') }}" class="admin-sidebar__link {{ request()->routeIs('admin.menu-items.*') ? 'is-active' : '' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25H12"/></svg>
                    {{ __('Menu / Services') }}
                </a>

                <div class="admin-sidebar__label">{{ __('Inventory & ERP') }}</div>
                @include('admin.partials.inventory-nav')

                <div class="admin-sidebar__label">{{ __('Storefront') }}</div>
                <a href="{{ route('admin.orders.index') }}" class="admin-sidebar__link {{ request()->routeIs('admin.orders.*') ? 'is-active' : '' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 00-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 00-16.536-1.84M7.5 14.25L5.106 5.272M6 20.25a.75.75 0 11-1.5 0 .75.75 0 011.5 0zm12.75 0a.75.75 0 11-1.5 0 .75.75 0 011.5 0z"/></svg>
                    {{ __('Orders') }}
                </a>
                @include('admin.partials.showcase-nav')
                <a href="{{ route('admin.coupons.index') }}" class="admin-sidebar__link {{ request()->routeIs('admin.coupons.*') ? 'is-active' : '' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 14.25l6-6m4.5-3.493V21.75l-3.75-1.5-3.75 1.5-3.75-1.5-3.75 1.5V4.757c0-1.108.806-2.057 1.907-2.185a48.507 48.507 0 0111.186 0c1.1.128 1.907 1.077 1.907 2.185z"/></svg>
                    {{ __('Coupons') }}
                </a>
                <a href="{{ route('admin.blog-posts.index') }}" class="admin-sidebar__link {{ request()->routeIs('admin.blog-posts.*') ? 'is-active' : '' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v15.512A9 9 0 0112 17.25a8.965 8.965 0 016 2.292v-15.5a8.965 8.965 0 00-6-2.292zM12 6.042v15.5"/></svg>
                    {{ __('Blog') }}
                </a>

                <a href="{{ route('admin.about-page.edit') }}" class="admin-sidebar__link {{ request()->routeIs('admin.about-page.*') ? 'is-active' : '' }}">{{ __('About page') }}</a>
                <a href="{{ route('admin.contact-page.edit') }}" class="admin-sidebar__link {{ request()->routeIs('admin.contact-page.*', 'admin.contact-inquiries.*') ? 'is-active' : '' }}">{{ __('Contact page') }}</a>

                <div class="admin-sidebar__label">{{ __('System') }}</div>
                <a href="{{ route('admin.shipping-settings.edit') }}" class="admin-sidebar__link {{ request()->routeIs('admin.shipping-settings.*') ? 'is-active' : '' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a49.902 49.902 0 00-2.654-.816A12.023 12.023 0 0118.75 16.5c0-2.136.547-4.142 1.523-5.952M17.25 16.5A12.023 12.023 0 0012 18.75c-2.136 0-4.142-.547-5.952-1.523M17.25 16.5v1.875c0 .621-.504 1.125-1.125 1.125H7.875c-.621 0-1.125-.504-1.125-1.125V16.5m12 0V9.75a2.25 2.25 0 00-2.25-2.25H6.75A2.25 2.25 0 004.5 9.75v6.75"/></svg>
                    {{ __('Shipping') }}
                </a>
                <a href="{{ route('admin.seo.index') }}" class="admin-sidebar__link {{ request()->routeIs('admin.seo.*') ? 'is-active' : '' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/></svg>
                    {{ __('SEO') }}
                </a>
                <a href="{{ route('admin.settings.edit') }}" class="admin-sidebar__link {{ request()->routeIs('admin.settings.*') ? 'is-active' : '' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a6.759 6.759 0 010 .255c-.007.378.138.75.43.99l1.005.828c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.43-.992a6.932 6.932 0 010-.255c.007-.378-.138-.75-.43-.99l-1.004-.828a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.644-.869l.214-1.281z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    {{ __('Settings') }}
                </a>
            </nav>

            <div class="admin-sidebar__footer">
                <a href="{{ route('market.home') }}" class="btn btn-outline-secondary btn-sm" target="_blank" rel="noopener">{{ __('View storefront') }}</a>
                <form action="{{ route('logout') }}" method="post" class="admin-sidebar__logout-form">
                    @csrf
                    <button type="submit" class="btn btn-outline-danger btn-sm w-100">{{ __('Log out') }}</button>
                </form>
            </div>
        </aside>

        <div class="admin-main">
            <header class="admin-topbar">
                <button type="button" class="admin-topbar__toggle" id="adminSidebarToggle" aria-controls="adminSidebar" aria-expanded="false" aria-label="{{ __('Open menu') }}">
                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75"><path stroke-linecap="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"/></svg>
                </button>
                <div class="admin-topbar__search">
                    <span class="admin-topbar__search-icon" aria-hidden="true">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/></svg>
                    </span>
                    <input type="search" name="admin_q" placeholder="{{ __('Type to search…') }}" autocomplete="off" aria-label="{{ __('Search') }}">
                </div>
                <div class="admin-topbar__actions">
                    <button type="button" class="admin-topbar__icon-btn" id="adminThemeToggle" title="{{ __('Toggle theme') }}" aria-label="{{ __('Toggle theme') }}">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.718 9.718 0 0118 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 003 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 009.002-5.998z"/></svg>
                    </button>
                    <a href="{{ route('market.home') }}" class="admin-topbar__icon-btn" title="{{ __('Store') }}" aria-label="{{ __('Store') }}">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 00-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 00-16.536-1.84M7.5 14.25L5.106 5.272M6 20.25a.75.75 0 11-1.5 0 .75.75 0 011.5 0zm12.75 0a.75.75 0 11-1.5 0 .75.75 0 011.5 0z"/></svg>
                    </a>
                    <form action="{{ route('logout') }}" method="post" class="admin-topbar__logout-form">
                        @csrf
                        <button type="submit" class="admin-topbar__icon-btn admin-topbar__icon-btn--logout" title="{{ __('Log out') }}" aria-label="{{ __('Log out') }}">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75"/></svg>
                        </button>
                    </form>
                    <a href="{{ route('admin.dashboard') }}" class="admin-topbar__user">
                        <span class="admin-topbar__avatar" aria-hidden="true">{{ $adminInitial }}</span>
                        <span class="admin-topbar__user-text">
                            <span class="admin-topbar__user-email">{{ $adminUser->email ?? 'Admin' }}</span>
                            <span class="admin-topbar__user-role d-block">{{ __('Administrator') }}</span>
                        </span>
                    </a>
                </div>
            </header>

            <main class="admin-content">
                @if(session('status'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('status') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="{{ __('Close') }}"></button>
                    </div>
                @endif
                <div class="admin-page-head">
                    <div>
                        <h1>@yield('title', __('Dashboard'))</h1>
                        <p class="admin-page-head__sub">@hasSection('page_subtitle')@yield('page_subtitle')@else{{ __('Welcome back! Continue your journey.') }}@endif</p>
                    </div>
                    @hasSection('breadcrumbs')
                        <div>@yield('breadcrumbs')</div>
                    @else
                        <nav class="admin-breadcrumb" aria-label="Breadcrumb">
                            <a href="{{ route('admin.dashboard') }}">{{ __('Home') }}</a>
                            <span class="admin-breadcrumb__sep" aria-hidden="true">/</span>
                            <span>@yield('title', __('Dashboard'))</span>
                        </nav>
                    @endif
                </div>

                @yield('content')
            </main>
        </div>
    </div>

    @php
        $showTransactionFab = request()->routeIs(
            'admin.inventory.*',
            'admin.purchases.*',
            'admin.sales.*',
            'admin.returns.*',
            'admin.suppliers.*',
            'admin.warehouses.*',
            'admin.reports.inventory',
            'admin.inventory.ledger*',
            'admin.reports.variant-sales'
        );
    @endphp

    @if($showTransactionFab)
        <div class="admin-txn-fab-wrap" id="adminTxnFabWrap">
            <div class="admin-txn-fab__menu" id="adminTxnFabMenu" hidden>
                <a href="{{ route('admin.purchases.create') }}" class="admin-txn-fab__item">
                    <i class="bi bi-cart-plus" aria-hidden="true"></i>{{ __('Add purchase') }}
                </a>
                <a href="{{ route('admin.sales.create') }}" class="admin-txn-fab__item">
                    <i class="bi bi-receipt" aria-hidden="true"></i>{{ __('Add sale') }}
                </a>
            </div>
            <button type="button" class="admin-fab admin-txn-fab" id="adminTxnFab" aria-expanded="false" aria-controls="adminTxnFabMenu" title="{{ __('Add transaction') }}" aria-label="{{ __('Add transaction') }}">
                <i class="bi bi-plus-lg" aria-hidden="true"></i>
            </button>
        </div>
    @else
        <a href="{{ route('admin.settings.edit') }}" class="admin-fab" title="{{ __('Settings') }}" aria-label="{{ __('Settings') }}">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a6.759 6.759 0 010 .255c-.007.378.138.75.43.99l1.005.828c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.43-.992a6.932 6.932 0 010-.255c.007-.378-.138-.75-.43-.99l-1.004-.828a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.644-.869l.214-1.281z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
        </a>
    @endif

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        (function () {
            var sb = document.getElementById('adminSidebar');
            var bd = document.getElementById('adminSidebarBackdrop');
            var tg = document.getElementById('adminSidebarToggle');
            function closeSb() {
                sb.classList.remove('is-open');
                bd.classList.remove('is-visible');
                if (tg) tg.setAttribute('aria-expanded', 'false');
            }
            function openSb() {
                sb.classList.add('is-open');
                bd.classList.add('is-visible');
                if (tg) tg.setAttribute('aria-expanded', 'true');
            }
            if (tg && sb && bd) {
                tg.addEventListener('click', function () {
                    if (sb.classList.contains('is-open')) closeSb(); else openSb();
                });
                bd.addEventListener('click', closeSb);
                window.addEventListener('resize', function () {
                    if (window.matchMedia('(min-width: 992px)').matches) closeSb();
                });
            }
            var root = document.documentElement;
            var themeBtn = document.getElementById('adminThemeToggle');
            try {
                var saved = localStorage.getItem('adminTheme');
                if (saved === 'dark' || saved === 'light') root.setAttribute('data-admin-theme', saved);
            } catch (e) {}
            if (themeBtn) {
                themeBtn.addEventListener('click', function () {
                    var next = root.getAttribute('data-admin-theme') === 'dark' ? 'light' : 'dark';
                    root.setAttribute('data-admin-theme', next);
                    try { localStorage.setItem('adminTheme', next); } catch (e) {}
                });
            }

            document.querySelectorAll('[data-nav-group]').forEach(function (group) {
                var toggle = group.querySelector('.admin-nav-group__toggle');
                if (!toggle) return;
                toggle.addEventListener('click', function () {
                    var open = group.classList.toggle('is-open');
                    toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
                });
            });

            var txnFab = document.getElementById('adminTxnFab');
            var txnMenu = document.getElementById('adminTxnFabMenu');
            var txnWrap = document.getElementById('adminTxnFabWrap');
            if (txnFab && txnMenu && txnWrap) {
                txnFab.addEventListener('click', function (event) {
                    event.stopPropagation();
                    var open = txnWrap.classList.toggle('is-open');
                    txnMenu.hidden = !open;
                    txnFab.setAttribute('aria-expanded', open ? 'true' : 'false');
                });
                document.addEventListener('click', function (event) {
                    if (!txnWrap.contains(event.target)) {
                        txnWrap.classList.remove('is-open');
                        txnMenu.hidden = true;
                        txnFab.setAttribute('aria-expanded', 'false');
                    }
                });
            }
        })();
    </script>
    @include('partials.flash-toasts', ['includeValidationErrors' => true])
    @stack('scripts')
</body>
</html>
