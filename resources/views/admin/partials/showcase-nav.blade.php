@php
    $showcaseOpen = request()->routeIs('admin.showcase.*');
@endphp

<div class="admin-nav-group {{ $showcaseOpen ? 'is-open' : '' }}" data-nav-group>
    <button type="button" class="admin-nav-group__toggle" aria-expanded="{{ $showcaseOpen ? 'true' : 'false' }}">
        <span class="admin-nav-group__title">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" width="18" height="18" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3A1.5 1.5 0 001.5 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z"/></svg>
            {{ __('Showcase') }}
        </span>
        <i class="bi bi-chevron-down admin-nav-group__chevron" aria-hidden="true"></i>
    </button>
    <div class="admin-nav-group__panel">
        <a href="{{ route('admin.showcase.home-promo.index') }}" class="admin-sidebar__link {{ request()->routeIs('admin.showcase.home-promo.*') ? 'is-active' : '' }}">
            <i class="bi bi-grid-3x3-gap" aria-hidden="true"></i>{{ __('Home promo cards') }}
        </a>
        <a href="{{ route('admin.banners.index') }}" class="admin-sidebar__link {{ request()->routeIs('admin.banners.*') ? 'is-active' : '' }}">
            <i class="bi bi-images" aria-hidden="true"></i>{{ __('Hero slider') }}
        </a>
    </div>
</div>
