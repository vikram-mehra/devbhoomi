@php
    $u = auth()->user();
    $initial = strtoupper(mb_substr(trim($u->name ?: $u->email), 0, 1));
@endphp
<div class="account-dash__sidebar shadow-sm">
    <div class="account-dash__profile account-dash__profile--top">
        <div class="account-dash__profile-avatar">
            @if($u->avatarUrl())
                <img src="{{ $u->avatarUrl() }}" alt="" class="account-dash__avatar-img" width="72" height="72">
            @else
                <div class="account-dash__avatar" aria-hidden="true">{{ $initial }}</div>
            @endif
        </div>
        <div class="account-dash__profile-meta min-w-0">
            <div class="account-dash__profile-name text-truncate" title="{{ $u->name }}">{{ $u->name }}</div>
            <div class="account-dash__profile-email text-break">{{ $u->email }}</div>
            @if($u->phone)
                <div class="account-dash__profile-phone">{{ $u->phone }}</div>
            @endif
        </div>
    </div>
    <nav class="account-dash__nav" aria-label="{{ __('Account menu') }}">
        <a href="{{ route('account.dashboard') }}" class="account-dash__nav-link @if(request()->routeIs('account.dashboard')) is-active @endif">
            <span class="account-dash__nav-ico" aria-hidden="true"><i class="bi bi-house-door"></i></span>
            <span class="account-dash__nav-label">{{ __('Dashboard') }}</span>
        </a>
        <a href="{{ route('account.details') }}" class="account-dash__nav-link @if(request()->routeIs('account.details')) is-active @endif">
            <span class="account-dash__nav-ico" aria-hidden="true"><i class="bi bi-person-vcard"></i></span>
            <span class="account-dash__nav-label">{{ __('Account details') }}</span>
        </a>
        <a href="{{ route('orders.index') }}" class="account-dash__nav-link @if(request()->routeIs('orders.*')) is-active @endif">
            <span class="account-dash__nav-ico" aria-hidden="true"><i class="bi bi-file-earmark-text"></i></span>
            <span class="account-dash__nav-label">{{ __('My orders') }}</span>
        </a>
        <a href="{{ route('account.refunds') }}" class="account-dash__nav-link @if(request()->routeIs('account.refunds')) is-active @endif">
            <span class="account-dash__nav-ico" aria-hidden="true"><i class="bi bi-currency-dollar"></i></span>
            <span class="account-dash__nav-label">{{ __('Refund history') }}</span>
        </a>
        <a href="{{ route('account.addresses.index') }}" class="account-dash__nav-link @if(request()->routeIs('account.addresses.*')) is-active @endif">
            <span class="account-dash__nav-ico" aria-hidden="true"><i class="bi bi-geo-alt"></i></span>
            <span class="account-dash__nav-label">{{ __('Address book') }}</span>
        </a>
    </nav>
    <form action="{{ route('logout') }}" method="post" class="account-dash__logout-wrap">
        @csrf
        <button type="submit" class="account-dash__logout btn w-100">
            <i class="bi bi-box-arrow-right me-2" aria-hidden="true"></i>{{ __('Logout') }}
        </button>
    </form>
</div>
