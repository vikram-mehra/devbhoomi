@php
    $tab = $activeTab ?? 'details';
@endphp
<nav class="account-tabs mb-4" aria-label="{{ __('Account sections') }}">
    <ul class="nav nav-pills account-tabs__nav flex-wrap gap-2">
        <li class="nav-item">
            <a href="{{ route('account.details') }}" class="nav-link account-tabs__link @if($tab === 'details') active @endif">{{ __('Account details') }}</a>
        </li>
        <li class="nav-item">
            <a href="{{ route('account.addresses.index') }}" class="nav-link account-tabs__link @if($tab === 'addresses') active @endif">{{ __('Address book') }}</a>
        </li>
    </ul>
</nav>
