@if(($promoBanners ?? collect())->isNotEmpty())
    <section class="pro-home-promo cb-reveal" aria-label="{{ __('Featured offers') }}">
        <div class="cb-container">
            <div class="row g-3 g-md-4">
                @foreach($promoBanners as $tile)
                    <div class="col-md-4">
                        <a href="{{ $tile->resolvedLink() }}" class="pro-home-promo__card" @if($tile->target_blank ?? false) target="_blank" rel="noopener noreferrer" @endif>
                            <img src="{{ $tile->imageUrl() }}" alt="{{ $tile->title }}" class="pro-home-promo__img" loading="lazy" width="640" height="480">
                            <span class="pro-home-promo__overlay" aria-hidden="true"></span>
                            <span class="pro-home-promo__content">
                                @if(filled($tile->eyebrow))
                                    <span class="pro-home-promo__eyebrow">{{ $tile->eyebrow }}</span>
                                @endif
                                @if(filled($tile->title))
                                    <span class="pro-home-promo__title">{{ $tile->title }}</span>
                                @endif
                                <span class="pro-home-promo__btn">{{ $tile->button_label ?: __('Shop now') }}</span>
                            </span>
                        </a>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
@endif
