{{-- Top Announcement Bar (Left: Fixed Email | Center: Marquee | Right: Fixed Phone) --}}
@php
    $email = trim((string) \App\Models\Setting::getValue('contact_email', config('seo.organization.email', 'support@devbhoominaturals.com')));
    $phone = trim((string) \App\Models\Setting::getValue('contact_phone', '+91 9217732670'));
    $phoneLink = $phone !== '' ? 'tel:' . preg_replace('/[^\d+]/', '', $phone) : 'tel:+919217732670';
    $publicCoupons = \App\Models\Coupon::query()->public()->activeValid()->get();
    $freeShippingThreshold = (float) (\App\Models\ShippingSetting::current()->free_shipping_amount ?? 0);
@endphp

@if(config('holiday.enabled'))
    <div class="pro-announcement pro-announcement--holiday pro-announcement--has-marquee py-1 border-bottom shadow-sm overflow-hidden">
        <div class="container-fluid px-2 px-md-3">
            <div class="pro-announcement__row">
                {{-- Fixed Left: Email (Desktop/Tablet only; hidden on mobile) --}}
                <div class="pro-announcement__left d-none d-md-block">
                    <a href="mailto:{{ $email }}" class="pro-announcement__link text-decoration-none d-inline-flex align-items-center" title="{{ __('Email support') }}">
                        <i class="bi bi-envelope-fill me-1" aria-hidden="true"></i>
                        <span>{{ $email }}</span>
                    </a>
                </div>

                {{-- Center: Holiday Marquee Running Between --}}
                <div class="pro-announcement__center">
                    <marquee behavior="scroll" direction="left" scrollamount="6" onmouseover="this.stop();" onmouseout="this.start();" class="pro-holiday-marquee">
                        <span class="mx-3">
                            <span class="fs-6 me-1">🪔</span><strong>{{ config('holiday.title', __('Holiday Notice')) }}</strong>: {{ config('holiday.message') }}
                        </span>
                        <span class="mx-3 opacity-50">✦</span>
                        <span class="mx-3">
                            <i class="bi bi-cart-check-fill text-success me-1"></i>{{ __('Catalog & Cart Active 24/7') }}
                        </span>
                        <span class="mx-3 opacity-50">✦</span>
                        <span class="mx-3">
                            <i class="bi bi-pause-circle-fill text-dark me-1"></i>{{ __('Checkout & Orders Temporarily Paused for Diwali Holidays') }}
                        </span>
                        <span class="mx-3 opacity-50">✦</span>
                        <span class="mx-3">
                            <i class="bi bi-truck text-dark me-1"></i>{{ __('Order dispatch will resume post-festivities') }}
                        </span>
                        <span class="mx-3 opacity-50">✦</span>
                        <span class="mx-3">
                            <span class="fs-6 me-1">🪔</span><strong>{{ config('holiday.title', __('Holiday Notice')) }}</strong>: {{ config('holiday.message') }}
                        </span>
                    </marquee>
                </div>

                {{-- Fixed Right: Support Phone (Desktop/Tablet only; hidden on mobile) --}}
                <div class="pro-announcement__right d-none d-md-block">
                    <a href="{{ $phoneLink }}" class="pro-announcement__link text-decoration-none d-inline-flex align-items-center" title="{{ __('Call support') }}">
                        <i class="bi bi-telephone-fill me-1" aria-hidden="true"></i>
                        <span>{{ __('Support') }}: </span>{{ $phone }}
                    </a>
                </div>
            </div>
        </div>
    </div>
@elseif($publicCoupons->isNotEmpty())
    <div class="pro-announcement pro-announcement--offer pro-announcement--has-marquee py-1 border-bottom overflow-hidden">
        <div class="container-fluid px-2 px-md-3">
            <div class="pro-announcement__row">
                {{-- Fixed Left: Email (Desktop/Tablet only; hidden on mobile) --}}
                <div class="pro-announcement__left d-none d-md-block">
                    <a href="mailto:{{ $email }}" class="pro-announcement__link text-decoration-none d-inline-flex align-items-center" title="{{ __('Email support') }}">
                        <i class="bi bi-envelope-fill me-1" aria-hidden="true"></i>
                        <span>{{ $email }}</span>
                    </a>
                </div>

                {{-- Center: Offers Marquee Running Between --}}
                <div class="pro-announcement__center">
                    <marquee behavior="scroll" direction="left" scrollamount="5" onmouseover="this.stop();" onmouseout="this.start();" class="pro-offer-marquee">
                        @foreach($publicCoupons as $coupon)
                            <span class="mx-3">
                                <i class="bi bi-tag-fill me-1 text-warning" aria-hidden="true"></i>
                                <strong>{{ __('Offer') }}:</strong> {{ __('Use coupon :code for :discount', ['code' => $coupon->code, 'discount' => $coupon->discountLabel()]) }}
                                @if((float) $coupon->min_cart > 0)
                                    <span class="opacity-75">({{ __('Min. cart ₹:amount', ['amount' => number_format((float) $coupon->min_cart, 0)]) }})</span>
                                @endif
                            </span>
                            @if($freeShippingThreshold > 0)
                                <span class="mx-2 opacity-50">✦</span>
                                <span class="mx-2">
                                    <i class="bi bi-truck me-1" aria-hidden="true"></i>
                                    {{ __('Free shipping on orders above ₹:amount', ['amount' => number_format($freeShippingThreshold, 0)]) }}
                                </span>
                            @endif
                            <span class="mx-3 opacity-50">✦</span>
                        @endforeach
                    </marquee>
                </div>

                {{-- Fixed Right: Support Phone (Desktop/Tablet only; hidden on mobile) --}}
                <div class="pro-announcement__right d-none d-md-block">
                    <a href="{{ $phoneLink }}" class="pro-announcement__link text-decoration-none d-inline-flex align-items-center" title="{{ __('Call support') }}">
                        <i class="bi bi-telephone-fill me-1" aria-hidden="true"></i>
                        <span>{{ __('Support') }}: </span>{{ $phone }}
                    </a>
                </div>
            </div>
        </div>
    </div>
@else
    {{-- No active coupon & no holiday: Show Email on Left and Support Number on Right (Desktop/Tablet only; hidden on mobile) --}}
    <div class="pro-announcement pro-announcement--default d-none d-md-block py-1 border-bottom overflow-hidden">
        <div class="container-fluid px-2 px-md-3">
            <div class="pro-announcement__row">
                {{-- Left: Email --}}
                <div class="pro-announcement__left">
                    <a href="mailto:{{ $email }}" class="pro-announcement__link text-decoration-none d-inline-flex align-items-center" title="{{ __('Email support') }}">
                        <i class="bi bi-envelope-fill me-1" aria-hidden="true"></i>
                        <span class="pro-announcement__email-text">{{ $email }}</span>
                    </a>
                </div>

                {{-- Right: Support Phone --}}
                <div class="pro-announcement__right">
                    <a href="{{ $phoneLink }}" class="pro-announcement__link text-decoration-none d-inline-flex align-items-center" title="{{ __('Call support') }}">
                        <i class="bi bi-telephone-fill me-1" aria-hidden="true"></i>
                        <span class="d-none d-sm-inline">{{ __('Support') }}: </span>{{ $phone }}
                    </a>
                </div>
            </div>
        </div>
    </div>
@endif
