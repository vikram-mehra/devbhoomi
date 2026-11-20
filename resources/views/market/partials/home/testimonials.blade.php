@php
    $homeTestimonials = [
        [__('The mandua flour tastes like home in the hills — fresh, earthy, and packed with care.'), 'Priya S.', 'Mumbai', 5],
        [__('Love comparing sellers in one place. The pahadi pulses arrived sealed and genuinely organic.'), 'Arjun K.', 'Bengaluru', 5],
        [__('Support helped with a return quickly. I trust this store for weekly Himalayan staples.'), 'Neha R.', 'Delhi', 4],
        [__('Spices are fragrant, not dusty. You can tell they came straight from Uttarakhand farms.'), 'Kavita M.', 'Dehradun', 5],
        [__('Fast delivery and genuine products. The millets have become a regular in our kitchen.'), 'Rohan P.', 'Pune', 5],
        [__('Finally organic grains I can trust for my family — clean packing and honest flavour.'), 'Meera T.', 'Jaipur', 5],
        [__('Ordered red rice and gahat dal together — both arrived fresh and well packed.'), 'Sanjay D.', 'Chandigarh', 5],
        [__('A clean, honest store for pahadi groceries. I keep coming back for the millets.'), 'Ananya L.', 'Noida', 5],
    ];
@endphp

<section class="pro-testi-section mk-home-band mk-home-band--ownbg mk-section cb-reveal" aria-labelledby="testi-heading">
    <div class="pro-testi-section__deco" aria-hidden="true">
        <span class="pro-testi-section__blob pro-testi-section__blob--a"></span>
        <span class="pro-testi-section__blob pro-testi-section__blob--b"></span>
        <span class="pro-testi-section__blob pro-testi-section__blob--c"></span>
        <span class="pro-testi-section__ring"></span>
    </div>
    <div class="cb-container position-relative">
        <div class="pro-section-head">
            <p class="pro-section-head__eyebrow">{{ __('Reviews') }}</p>
            <h2 class="pro-section-head__title" id="testi-heading">{{ __('What buyers say') }}</h2>
            <p class="pro-section-head__sub">{{ __('Real notes from customers who shop Himalayan staples with us.') }}</p>
        </div>
        <div class="pro-testi-swiper-wrap">
            <div class="swiper pro-testi-swiper" id="proTestimonialSwiper">
                <div class="swiper-wrapper">
                    @foreach($homeTestimonials as $t)
                        @php $rating = min(5, max(0, (int) $t[3])); @endphp
                        <div class="swiper-slide">
                            <article class="pro-testi-card">
                                <i class="bi bi-quote pro-testi-card__mark" aria-hidden="true"></i>
                                <div class="pro-testi-card__stars" role="img" aria-label="{{ $rating }} {{ __('out of 5 stars') }}">
                                    @for($i = 1; $i <= 5; $i++)
                                        <i class="bi {{ $i <= $rating ? 'bi-star-fill' : 'bi-star' }}" aria-hidden="true"></i>
                                    @endfor
                                </div>
                                <p class="pro-testi-card__quote">&ldquo;{{ $t[0] }}&rdquo;</p>
                                <div class="pro-testi-card__author">{{ $t[1] }}</div>
                                <div class="small text-muted">{{ $t[2] }}</div>
                            </article>
                        </div>
                    @endforeach
                </div>
                <div class="swiper-pagination pro-testi-swiper__dots"></div>
            </div>
            <button type="button" class="swiper-button-prev pro-testi-swiper__nav" aria-label="{{ __('Previous testimonials') }}"></button>
            <button type="button" class="swiper-button-next pro-testi-swiper__nav" aria-label="{{ __('Next testimonials') }}"></button>
        </div>
    </div>
</section>
