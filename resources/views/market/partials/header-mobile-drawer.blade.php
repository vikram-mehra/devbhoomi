{{-- Mobile Offcanvas Navigation Drawer --}}
<div class="offcanvas offcanvas-start pro-nav-offcanvas" tabindex="-1" id="marketNav"
    aria-labelledby="marketNavLabel">
    <div class="offcanvas-header pro-mnav-header">
        <a href="{{ route('market.home') }}" class="pro-mnav-brand text-decoration-none" id="marketNavLabel">
            @if(!empty($siteLogoUrl))
                <img src="{{ \App\Support\OptimizedImage::url($siteLogoUrl, 420) }}" alt="{{ config('app.name') }}" class="pro-mnav-brand__logo" width="180"
                    height="50" decoding="async">
            @else
                <span class="pro-mnav-brand__name font-anc-serif">{{ config('app.name') }}</span>
            @endif
        </a>
        <button type="button" class="btn-close pro-mnav-close" data-bs-dismiss="offcanvas"
            aria-label="{{ __('Close') }}"></button>
    </div>
    <div class="offcanvas-body pro-mnav-body p-0">
        <nav class="pro-mnav" aria-label="{{ __('Mobile') }}">
            @include('market.partials.header-menu-mobile')

            <div class="pro-mnav-divider" role="presentation"></div>
            <a class="pro-mnav-rowlink" href="{{ route('shop.search') }}">{{ __('Search') }}</a>
            <a class="pro-mnav-rowlink"
                href="{{ route('shop.search', ['sort' => 'newest']) }}">{{ __('New arrivals') }}</a>

            <div class="pro-mnav-divider pro-mnav-divider--loose" role="presentation"></div>
            <span class="pro-mnav-section-label">{{ __('Account') }}</span>

            @guest
                <a class="pro-mnav-rowlink" href="{{ route('login') }}">{{ __('Sign in') }}</a>
                <a class="pro-mnav-rowlink" href="{{ route('register') }}">{{ __('Create account') }}</a>
            @else
                @php $navUser = auth()->user(); @endphp
                <div class="pro-mnav-profile px-3 py-3">
                    <div class="pro-mnav-profile__icon" aria-hidden="true"><i class="bi bi-person-circle"></i></div>
                    <div class="pro-mnav-profile__meta min-w-0">
                        <div class="pro-mnav-profile__name text-truncate">{{ $navUser->name }}</div>
                        <div class="pro-mnav-profile__email text-truncate">{{ $navUser->email }}</div>
                    </div>
                </div>
                @if($navUser->role === 'admin')
                    <a class="pro-mnav-rowlink" href="{{ route('admin.dashboard') }}">{{ __('Admin') }}</a>
                @elseif($navUser->role === 'seo')
                    <a class="pro-mnav-rowlink" href="{{ route('admin.seo.index') }}">{{ __('SEO workspace') }}</a>
                @elseif($navUser->role === 'vendor')
                    <a class="pro-mnav-rowlink" href="{{ route('vendor.dashboard') }}">{{ __('Seller hub') }}</a>
                @endif
                <a class="pro-mnav-rowlink" href="{{ route('cart.index') }}">{{ __('Cart') }}</a>
                <a class="pro-mnav-rowlink" href="{{ route('wishlist.index') }}">{{ __('Wishlist') }}</a>
                <a class="pro-mnav-rowlink" href="{{ route('account.dashboard') }}">{{ __('Dashboard') }}</a>
                <a class="pro-mnav-rowlink" href="{{ route('account.details') }}">{{ __('Account details') }}</a>
                <a class="pro-mnav-rowlink" href="{{ route('orders.index') }}">{{ __('My orders') }}</a>
                <a class="pro-mnav-rowlink" href="{{ route('orders.track') }}">{{ __('Track order') }}</a>
                <a class="pro-mnav-rowlink" href="{{ route('account.refunds') }}">{{ __('Refund history') }}</a>
                <a class="pro-mnav-rowlink" href="{{ route('account.addresses.index') }}">{{ __('Address book') }}</a>
                <form action="{{ route('logout') }}" method="post" class="pro-mnav-logout">@csrf
                    <button type="submit" class="pro-mnav-logout__btn">
                        <i class="bi bi-box-arrow-right" aria-hidden="true"></i>{{ __('Logout') }}
                    </button>
                </form>
            @endguest
        </nav>
    </div>
</div>
