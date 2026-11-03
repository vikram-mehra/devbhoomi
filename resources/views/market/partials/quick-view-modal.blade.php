{{-- Product Quick View Modal --}}
<div class="modal fade" id="quickViewModal" tabindex="-1" aria-labelledby="quickViewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
        <div class="modal-content pro-modal">
            <div class="modal-header border-0 pb-0">
                <h2 class="modal-title h5" id="quickViewModalLabel">{{ __('Quick view') }}</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal"
                    aria-label="{{ __('Close') }}"></button>
            </div>
            <div class="modal-body pt-2">
                <div class="row g-3">
                    <div class="col-md-5">
                        <div class="qv-gallery js-qv-gallery qv-gallery--single">
                            <div class="qv-gallery__track js-qv-track" tabindex="0" aria-label="{{ __('Product images') }}">
                                <div class="qv-gallery__slide is-active">
                                    <img src="" alt="" class="qv-gallery__img js-qv-img" width="400" height="480">
                                </div>
                            </div>
                            <div class="qv-gallery__dots js-qv-dots" role="tablist" aria-label="{{ __('Image navigation') }}"></div>
                        </div>
                    </div>
                    <div class="col-md-7">
                        <p class="small text-primary fw-semibold mb-1 js-qv-brand"></p>
                        <h3 class="h5 js-qv-name"></h3>
                        <div class="fs-5 fw-bold mb-2 js-qv-price"></div>
                        <p class="js-qv-description small text-muted" hidden></p>
                        
                        <!-- Dynamic Variant Selectors -->
                        <div class="js-qv-variants-container mb-3 d-none">
                            <label class="form-label small fw-bold text-muted uppercase tracking-wider mb-2 js-qv-variants-label">{{ __('Select Option') }}</label>
                            <div class="d-flex flex-wrap gap-2 js-qv-variants-list"></div>
                        </div>
                        
                        <!-- Add to Cart Form inside Quick View -->
                        <div class="js-qv-cart-action mb-4">
                            <form action="{{ route('cart.add') }}" method="post" class="js-ajax-add-to-cart zm-pro-add-form d-flex gap-2">
                                @csrf
                                <input type="hidden" name="product_variant_id" class="js-qv-variant-id-input" value="">
                                <div class="d-flex align-items-center border rounded-pill bg-light px-2 js-qv-qty-wrap" style="height: 38px; width: 110px;">
                                    <button type="button" class="btn btn-sm p-0 border-0 text-primary js-qv-qty-minus" style="font-size: 1.1rem; width: 25px; line-height: 1;"><i class="bi bi-dash"></i></button>
                                    <input type="number" name="qty" class="form-control form-control-sm text-center border-0 bg-transparent fw-semibold js-qv-qty-input" value="1" min="1" max="99" readonly style="box-shadow:none; padding:0; width:40px;">
                                    <button type="button" class="btn btn-sm p-0 border-0 text-primary js-qv-qty-plus" style="font-size: 1.1rem; width: 25px; line-height: 1;"><i class="bi bi-plus"></i></button>
                                </div>
                                <button type="submit" class="btn btn-primary rounded-pill px-4 js-qv-add-to-cart-btn">{{ __('Add to cart') }}</button>
                                <button type="submit" name="buy_now" value="1" class="btn btn-outline-primary rounded-pill px-4 js-qv-buy-now-btn">{{ __('Buy now') }}</button>
                            </form>
                        </div>

                        <a href="#" class="qv-details-link js-qv-link">
                            <span>{{ __('View full details') }}</span>
                            <i class="bi bi-arrow-right" aria-hidden="true"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
