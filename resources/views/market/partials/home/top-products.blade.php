@php
    $displayTop = ($topProducts ?? collect())->take(12);
    if ($displayTop->isEmpty()) {
        $displayTop = ($trending ?? collect())->take(12);
    }
@endphp

@if($displayTop->isNotEmpty())
<section class="mk-home-band mk-section cb-reveal" aria-labelledby="top-products-heading">
    <div class="cb-container">
        <div class="pro-section-head">
            <p class="pro-section-head__eyebrow">{{ __('Best of Devbhoomi') }}</p>
            <h2 class="pro-section-head__title" id="top-products-heading">{{ __('Top Products') }}</h2>
            <p class="pro-section-head__sub">{{ __('Our most popular Himalayan staples and organic favorites.') }}</p>
        </div>
        <div class="row g-3 g-md-4">
            @foreach($displayTop as $product)
                <div class="col-6 col-md-4 col-lg-3">
                    @include('market.partials.product-card', ['product' => $product])
                </div>
            @endforeach
        </div>
    </div>
</section>
@endif
