@php
    $whyUsImage = asset('images/about/gallery-pulses.jpg');
    $shippingSettings = \App\Models\ShippingSetting::current();
    $freeShippingThreshold = (float) ($shippingSettings->free_shipping_amount ?? 0);

    $deliveryTitle = $freeShippingThreshold > 0
        ? __('Pan-India delivery, free above ₹:amount', ['amount' => number_format($freeShippingThreshold, 0)])
        : __('Pan-India delivery to your doorstep');

    $deliveryText = $freeShippingThreshold > 0
        ? __('We ship across India. Free standard delivery on orders above ₹:amount, so you can stock your home pantry without extra shipping worry.', ['amount' => number_format($freeShippingThreshold, 0)])
        : __('We ship across India through trusted logistics partners, with real-time tracking from Uttarakhand to your doorstep.');

    $whyUsPoints = [
        [
            'icon' => 'bi-flower1',
            'title' => __('Grown in the hills, packed with care'),
            'text' => __('We work with Uttarakhand farmers for millets, pahadi pulses, red rice, and honey — food that still tastes like the mountain kitchen.'),
        ],
        [
            'icon' => 'bi-bag-check',
            'title' => __('Simple, honest pantry staples'),
            'text' => __('No fuss, no fillers. Jhangora, Mandua, Gahat, Kala Bhatt, and Himalayan grains arrive clean, sealed, and ready for everyday cooking.'),
        ],
        [
            'icon' => 'bi-truck',
            'title' => $deliveryTitle,
            'text' => $deliveryText,
        ],
        [
            'icon' => 'bi-people',
            'title' => __('A small team you can talk to'),
            'text' => __('Questions on cooking, bulk orders, or tracking? Write or call us. Real people help you choose the right Himalayan staple.'),
        ],
    ];
@endphp
<section class="pro-why-us mk-home-band mk-home-band--ownbg mk-section cb-reveal" aria-labelledby="why-us-heading">
    <div class="cb-container">
        <div class="pro-why-us__grid">
            <figure class="pro-why-us__media">
                <img
                    src="{{ \App\Support\OptimizedImage::url($whyUsImage, 900) }}"
                    alt="{{ __('Pahadi pulses and Himalayan grains from Devbhoomi Naturals') }}"
                    class="pro-why-us__img"
                    width="900"
                    height="720"
                    loading="lazy"
                    decoding="async"
                >
            </figure>
            <div class="pro-why-us__copy">
                <p class="pro-section-head__eyebrow mb-2">{{ __('Why us') }}</p>
                <h2 class="pro-why-us__title" id="why-us-heading">{{ __('Why families choose Devbhoomi Naturals') }}</h2>
                <p class="pro-why-us__lead">
                    {{ __('We started this shop so homes across India could cook with the same Himalayan staples we grew up with — millets, pahadi dals, and hill spices that feel honest on the plate.') }}
                </p>
                <ul class="pro-why-us__list">
                    @foreach($whyUsPoints as $point)
                        <li class="pro-why-us__item">
                            <span class="pro-why-us__icon" aria-hidden="true"><i class="bi {{ $point['icon'] }}"></i></span>
                            <div>
                                <h3 class="pro-why-us__item-title">{{ $point['title'] }}</h3>
                                <p class="pro-why-us__item-text">{{ $point['text'] }}</p>
                            </div>
                        </li>
                    @endforeach
                </ul>
                <a href="{{ \App\Models\MenuItem::ourProductsUrl() }}" class="pro-why-us__cta">{{ __('Shop Himalayan products') }} <i class="bi bi-arrow-right" aria-hidden="true"></i></a>
            </div>
        </div>
    </div>
</section>
