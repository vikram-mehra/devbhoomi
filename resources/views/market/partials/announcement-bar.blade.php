{{-- Top Announcement Bar / Holiday Marquee --}}
@if(config('holiday.enabled'))
    <div class="pro-announcement pro-announcement--holiday py-2 border-bottom shadow-sm overflow-hidden">
        <div class="container-fluid px-2">
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
    </div>
@else
    @php
        $promoCoupon = \App\Models\Coupon::query()->public()->activeValid()->first();
    @endphp
    <div class="pro-announcement">
        <div class="cb-container">
            <div class="pro-announcement__marquee">
                <span>
                    <i class="bi bi-tag-fill me-1" aria-hidden="true"></i>
                    @if($promoCoupon)
                        {{ __('Use coupon :code for :discount. Free shipping above ₹2,999.', ['code' => $promoCoupon->code, 'discount' => $promoCoupon->discountLabel()]) }}
                    @else
                        {{ __('Free shipping on orders above ₹2,999.') }}
                    @endif
                </span>
                <span><i class="bi bi-telephone me-1" aria-hidden="true"></i>{{ __('Support') }}: <a
                        href="tel:+91 9217732670">+91 9217732670</a></span>
            </div>
        </div>
    </div>
@endif
