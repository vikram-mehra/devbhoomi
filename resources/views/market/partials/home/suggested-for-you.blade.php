@if(isset($suggestedForYou) && $suggestedForYou->isNotEmpty())
<section class="mk-home-band mk-section cb-reveal" aria-labelledby="suggested-heading">
    <div class="cb-container">
        <div class="pro-section-head">
            <p class="pro-section-head__eyebrow">{{ __('Based on your browsing') }}</p>
            <h2 class="pro-section-head__title" id="suggested-heading">{{ __('Suggested for you') }}</h2>
            <p class="pro-section-head__sub">{{ __('Products you recently viewed') }}</p>
        </div>
        <div class="row g-3 g-md-4">
            @foreach($suggestedForYou as $product)
                <div class="col-6 col-md-4 col-lg-3">
                    @include('market.partials.product-card', ['product' => $product])
                </div>
            @endforeach
        </div>
    </div>
</section>
@endif
