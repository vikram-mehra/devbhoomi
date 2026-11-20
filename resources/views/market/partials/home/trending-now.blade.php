@php
    $newTab = ($newProducts ?? collect())->isNotEmpty() ? $newProducts : ($trending ?? collect())->take(8);
    $featTab = ($featured ?? collect())->filter(fn ($product) => (bool) $product->is_featured);
    if ($featTab->isEmpty()) {
        $featTab = ($trending ?? collect())->take(8);
    }
    $bestTab = ($trending ?? collect())->take(8);
@endphp

<style>
    .cb-market-pro .pro-trend-tab-content .tab-pane .pro-trend-skel { display: none; }
    .cb-market-pro .pro-trend-tab-content .tab-pane.is-pending .pro-trend-skel { display: block !important; }
    .cb-market-pro .pro-trend-tab-content .tab-pane.is-pending .pro-product-swiper,
    .cb-market-pro .pro-trend-tab-content .tab-pane.is-pending > p { display: none !important; }
</style>

<section class="mk-home-band mk-section" aria-labelledby="trend-heading">
    <div class="cb-container">
        <div class="pro-section-head">
            <p class="pro-section-head__eyebrow">{{ __('Trending now') }}</p>
            <h2 class="pro-section-head__title" id="trend-heading">{{ __("Products you'll love") }}</h2>
        </div>
        <div class="pro-tabs-trend-wrap">
            <ul class="nav nav-pills pro-tabs pro-tabs--trend" id="proTrendTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="tab-new" data-bs-toggle="pill" data-bs-target="#pane-new" type="button" role="tab">
                        <i class="bi bi-stars" aria-hidden="true"></i>{{ __('New') }}
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="tab-feat" data-bs-toggle="pill" data-bs-target="#pane-feat" type="button" role="tab">
                        <i class="bi bi-award" aria-hidden="true"></i>{{ __('Featured') }}
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="tab-best" data-bs-toggle="pill" data-bs-target="#pane-best" type="button" role="tab">
                        <i class="bi bi-graph-up-arrow" aria-hidden="true"></i>{{ __('Best selling') }}
                    </button>
                </li>
            </ul>
        </div>
        <div class="tab-content pro-trend-tab-content">
            <div class="tab-pane show active" id="pane-new" role="tabpanel" aria-labelledby="tab-new">
                @include('market.partials.product-card-skel-row')
                @if($newTab->isNotEmpty())
                    <div class="swiper pro-product-swiper">
                        <div class="swiper-wrapper">
                            @foreach($newTab as $product)
                                <div class="swiper-slide">
                                    <div class="swiper-slide__inner h-100">@include('market.partials.product-card', ['product' => $product])</div>
                                </div>
                            @endforeach
                        </div>
                        <button type="button" class="swiper-button-prev pro-product-swiper__nav" aria-label="{{ __('Previous products') }}"></button>
                        <button type="button" class="swiper-button-next pro-product-swiper__nav" aria-label="{{ __('Next products') }}"></button>
                    </div>
                @else
                    <p class="text-center text-muted py-4 mb-0">{{ __('No products in this tab yet.') }}</p>
                @endif
            </div>
            <div class="tab-pane" id="pane-feat" role="tabpanel" aria-labelledby="tab-feat">
                @include('market.partials.product-card-skel-row')
                @if($featTab->isNotEmpty())
                    <div class="swiper pro-product-swiper">
                        <div class="swiper-wrapper">
                            @foreach($featTab as $product)
                                <div class="swiper-slide">
                                    <div class="swiper-slide__inner h-100">@include('market.partials.product-card', ['product' => $product])</div>
                                </div>
                            @endforeach
                        </div>
                        <button type="button" class="swiper-button-prev pro-product-swiper__nav" aria-label="{{ __('Previous products') }}"></button>
                        <button type="button" class="swiper-button-next pro-product-swiper__nav" aria-label="{{ __('Next products') }}"></button>
                    </div>
                @else
                    <p class="text-center text-muted py-4 mb-0">{{ __('No products in this tab yet.') }}</p>
                @endif
            </div>
            <div class="tab-pane" id="pane-best" role="tabpanel" aria-labelledby="tab-best">
                @include('market.partials.product-card-skel-row')
                @if($bestTab->isNotEmpty())
                    <div class="swiper pro-product-swiper">
                        <div class="swiper-wrapper">
                            @foreach($bestTab as $product)
                                <div class="swiper-slide">
                                    <div class="swiper-slide__inner h-100">@include('market.partials.product-card', ['product' => $product])</div>
                                </div>
                            @endforeach
                        </div>
                        <button type="button" class="swiper-button-prev pro-product-swiper__nav" aria-label="{{ __('Previous products') }}"></button>
                        <button type="button" class="swiper-button-next pro-product-swiper__nav" aria-label="{{ __('Next products') }}"></button>
                    </div>
                @else
                    <p class="text-center text-muted py-4 mb-0">{{ __('No products in this tab yet.') }}</p>
                @endif
            </div>
        </div>
    </div>
</section>
