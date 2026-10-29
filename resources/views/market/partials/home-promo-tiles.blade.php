@if(($promoBanners ?? collect())->isNotEmpty())
    <section class="pro-home-promo mk-home-band" aria-label="{{ __('Featured offers') }}">
        <div class="cb-container">
            <div class="row g-3 g-md-4">
                @foreach($promoBanners as $tile)
                    @php
                        $hasPromoText = filled($tile->eyebrow) || filled($tile->title);
                        $promoCardTag = $hasPromoText ? 'a' : 'div';
                    @endphp
                    <div class="col-md-12">
                        <{{ $promoCardTag }} @if($hasPromoText) href="{{ $tile->resolvedLink() }}" @endif class="pro-home-promo__card" @if($hasPromoText && ($tile->target_blank ?? false)) target="_blank" rel="noopener noreferrer" @endif>
                            <picture>
                                @if($tile->mobile_image)
                                    <source media="(max-width: 767.98px)" srcset="{{ \App\Support\OptimizedImage::url($tile->mobileImageUrl(), 640) }}">
                                @endif
                                <img src="{{ \App\Support\OptimizedImage::url($tile->imageUrl(), 1280) }}" alt="{{ $tile->title }}" class="pro-home-promo__img" loading="lazy" width="1280" height="480">
                            </picture>
                            <span class="pro-home-promo__overlay" aria-hidden="true"></span>
                            @if($hasPromoText)
                                <span class="pro-home-promo__content">
                                    @if(filled($tile->eyebrow))
                                        <span class="pro-home-promo__eyebrow">{{ $tile->eyebrow }}</span>
                                    @endif
                                    @if(filled($tile->title))
                                        <span class="pro-home-promo__title">{{ $tile->title }}</span>
                                    @endif
                                    @if(filled($tile->button_label))
                                        <span class="pro-home-promo__btn">{{ $tile->button_label }}</span>
                                    @endif
                                </span>
                            @endif
                        </{{ $promoCardTag }}>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
@endif
