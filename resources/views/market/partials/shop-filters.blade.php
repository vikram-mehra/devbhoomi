@php
    $priceCeil = $facets['price_ceil'] ?? 5000;
    $minVal = (int) request('min', 0);
    $maxVal = (int) (request()->filled('max') ? request('max') : $priceCeil);
    $maxVal = min($priceCeil, max(0, $maxVal));
    $minVal = min($maxVal, max(0, $minVal));
    $ratingVal = request('rating_min', request('rating'));
    $discountVal = request('discount_min');
    $discountOptions = [10, 20, 30, 40, 50, 60, 70, 80];
    $selBrands = array_values(array_filter(\Illuminate\Support\Arr::wrap(request('brand', []))));
    $selColors = array_values(array_filter(\Illuminate\Support\Arr::wrap(request('color', []))));
    $brandCounts = $facets['brand_counts'] ?? collect();
    $brands = $facets['brands'] ?? collect();
    $brandVisible = 8;
    $brandExtra = max(0, $brands->count() - $brandVisible);
    $maxAtCeil = $maxVal >= $priceCeil;
    $pcForPct = (int) $priceCeil;
    $minPct = $pcForPct > 0 ? round(($minVal / $pcForPct) * 100, 4) : 0;
    $maxPct = $pcForPct > 0 ? round(($maxVal / $pcForPct) * 100, 4) : 100;
    $hiddenFieldsForPartial = $hiddenFields ?? [];
    $sortForClear = $hiddenFieldsForPartial['sort'] ?? request('sort', 'popular');
    if ($sortForClear === null || $sortForClear === '') {
        $sortForClear = 'popular';
    }
    $clearFiltersUrl = $formAction.'?'.http_build_query(['sort' => $sortForClear]);
@endphp
<div class="mk-shop-filters mk-filter-sheet zm-pro-filters">
    <form method="get" action="{{ $formAction }}" class="mk-shop-filters__form" id="mkShopFilterForm" data-auto-filter="1">
        @if(!empty($hiddenFields))
            @foreach($hiddenFields as $name => $val)
                @if(is_array($val))
                    @foreach($val as $v)
                        <input type="hidden" name="{{ $name }}[]" value="{{ $v }}">
                    @endforeach
                @else
                    <input type="hidden" name="{{ $name }}" value="{{ $val }}">
                @endif
            @endforeach
        @endif

        <div class="mk-filter-section mk-filter-section--top">
            <div class="mk-filter-page-head">
                <p class="mk-filter-page-head__title">{{ __('Filters') }}</p>
                <a href="{{ $clearFiltersUrl }}" class="mk-filter-page-head__clear">{{ __('Clear all') }}</a>
            </div>
        </div>

        @if($brands->isNotEmpty())
            <div class="mk-filter-section">
                <div class="mk-filter-section__head">
                    <h3 class="mk-filter-section__title mb-0">{{ __('Brand') }}</h3>
                    <button type="button" class="mk-filter-icon-btn" id="mkBrandSearchToggle" aria-expanded="false" aria-controls="mkBrandSearchWrap" title="{{ __('Search brands') }}">
                        <i class="bi bi-search" aria-hidden="true"></i>
                    </button>
                </div>
                <div class="mk-filter-brand-search" id="mkBrandSearchWrap" hidden>
                    <label class="visually-hidden" for="mkBrandSearchInput">{{ __('Search brands') }}</label>
                    <input type="search" id="mkBrandSearchInput" class="form-control form-control-sm" placeholder="{{ __('Search brands') }}" autocomplete="off">
                </div>
                <div class="mk-filter-section__body mk-filter-brand-list" id="mkBrandList">
                    @foreach($brands as $i => $v)
                        @php $bct = (int) ($brandCounts[$v->id] ?? 0); @endphp
                        <label class="mk-filter-row @if($i >= $brandVisible) mk-filter-row--brand-extra @endif" data-brand-label="{{ \Illuminate\Support\Str::lower($v->shop_name) }}">
                            <input type="checkbox" name="brand[]" value="{{ $v->id }}" @if(in_array((string) $v->id, array_map('strval', $selBrands), true)) checked @endif>
                            <span class="mk-filter-row__text">{{ $v->shop_name }}</span>
                            <span class="mk-filter-row__count">({{ number_format($bct) }})</span>
                        </label>
                    @endforeach
                </div>
                @if($brandExtra > 0)
                    <button type="button" class="mk-filter-more-link" id="mkBrandExpand" aria-expanded="false"
                        data-label-more="+ {{ number_format($brandExtra) }} {{ __('more') }}"
                        data-label-less="{{ __('Show less') }}">
                        + {{ number_format($brandExtra) }} {{ __('more') }}
                    </button>
                @endif
            </div>
        @endif

        @if($facets['colors']->isNotEmpty())
            <div class="mk-filter-section">
                <h3 class="mk-filter-section__title">{{ __('Colours') }}</h3>
                <div class="mk-filter-section__body">
                    @foreach($facets['colors'] as $col)
                        <label class="mk-filter-row">
                            <input type="checkbox" name="color[]" value="{{ $col }}" @if(in_array($col, $selColors, true)) checked @endif>
                            <span class="mk-filter-row__text">{{ $col }}</span>
                        </label>
                    @endforeach
                </div>
            </div>
        @endif

        <div class="mk-filter-section">
            <h3 class="mk-filter-section__title">{{ __('Rating') }}</h3>
            <div class="mk-filter-section__body">
                @foreach([5, 4, 3, 2, 1] as $rn)
                    <label class="mk-filter-row mk-filter-row--rating">
                        <input type="radio" name="rating_min" value="{{ $rn }}" @if((string) $ratingVal === (string) $rn) checked @endif>
                        <span class="mk-filter-stars" aria-hidden="true">
                            @for($i = 1; $i <= 5; $i++)
                                <i class="bi bi-star-fill {{ $i <= $rn ? 'is-on' : 'is-off' }}"></i>
                            @endfor
                        </span>
                    </label>
                @endforeach
                <label class="mk-filter-row mk-filter-row--rating">
                    <input type="radio" name="rating_min" value="" @if($ratingVal === null || $ratingVal === '') checked @endif>
                    <span class="mk-filter-row__text">{{ __('Any rating') }}</span>
                </label>
            </div>
        </div>

        <div class="mk-filter-section">
            <h3 class="mk-filter-section__title mk-filter-section__title--price">{{ __('Price') }}</h3>
            <div class="mk-filter-section__body">
                <div class="mk-filter-price" id="mkPriceSliderRoot" style="--min-pct: {{ $minPct }}%; --max-pct: {{ $maxPct }}%;">
                    <input type="hidden" name="min" id="mkPriceHMin" value="{{ $minVal }}">
                    <input type="hidden" name="max" id="mkPriceHMax" value="{{ $maxVal }}">
                    <div class="mk-filter-price__track" aria-hidden="true"></div>
                    <div class="mk-filter-price__range-fill" aria-hidden="true"></div>
                    <div class="mk-filter-price__sliders">
                        <input type="range" class="mk-filter-price__range mk-filter-price__range--min" id="mkPriceRMin" min="0" max="{{ $priceCeil }}" value="{{ $minVal }}" step="1" aria-label="{{ __('Minimum price') }}">
                        <input type="range" class="mk-filter-price__range mk-filter-price__range--max" id="mkPriceRMax" min="0" max="{{ $priceCeil }}" value="{{ $maxVal }}" step="1" aria-label="{{ __('Maximum price') }}">
                    </div>
                    <p class="mk-filter-price__line" id="mkPriceRangeLine">
                        ₹{{ number_format($minVal) }} - @if($maxAtCeil)₹{{ number_format($priceCeil) }}+@else₹{{ number_format($maxVal) }}@endif
                    </p>
                </div>
            </div>
        </div>

        <div class="mk-filter-section mk-filter-section--last">
            <h3 class="mk-filter-section__title mk-filter-section__title--discount">{{ __('Discount range') }}</h3>
            <div class="mk-filter-section__body">
                @foreach($discountOptions as $d)
                    <label class="mk-filter-row mk-filter-row--discount">
                        <input type="radio" name="discount_min" value="{{ $d }}" @if((string) $discountVal === (string) $d) checked @endif>
                        <span class="mk-filter-row__text">{{ $d }}% {{ __('and above') }}</span>
                    </label>
                @endforeach
                <label class="mk-filter-row mk-filter-row--discount">
                    <input type="radio" name="discount_min" value="" @if($discountVal === null || $discountVal === '' || ! in_array((int) $discountVal, $discountOptions, true)) checked @endif>
                    <span class="mk-filter-row__text">{{ __('Any discount') }}</span>
                </label>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script>
(function () {
    var form = document.getElementById('mkShopFilterForm');
    if (!form || form.getAttribute('data-auto-filter') !== '1') return;

    function submitFilter() {
        if (typeof form.requestSubmit === 'function') {
            form.requestSubmit();
        } else {
            form.submit();
        }
    }

    var debounceTimers = {};
    function debounceSubmit(key, ms) {
        clearTimeout(debounceTimers[key]);
        debounceTimers[key] = setTimeout(submitFilter, ms);
    }

    form.addEventListener('change', function (e) {
        var t = e.target;
        if (!t || t.form !== form) return;
        if (t.type === 'checkbox' || t.type === 'radio') {
            submitFilter();
        }
    });

    form.querySelectorAll('input[data-autosubmit-debounce]').forEach(function (inp) {
        var ms = parseInt(inp.getAttribute('data-autosubmit-debounce'), 10) || 400;
        inp.addEventListener('input', function () {
            debounceSubmit('filterSearch', ms);
        });
        inp.addEventListener('change', function () {
            submitFilter();
        });
    });

    var ceil = {{ (int) $priceCeil }};
    var rMin = document.getElementById('mkPriceRMin');
    var rMax = document.getElementById('mkPriceRMax');
    var hMin = document.getElementById('mkPriceHMin');
    var hMax = document.getElementById('mkPriceHMax');
    var line = document.getElementById('mkPriceRangeLine');
    var priceRoot = document.getElementById('mkPriceSliderRoot');

    function fmt(n) { return Number(n).toLocaleString(); }

    function syncPrice() {
        if (!rMin || !rMax || !hMin || !hMax) return;
        var a = parseInt(rMin.value, 10) || 0;
        var b = parseInt(rMax.value, 10) || ceil;
        if (a > b) {
            var t = a; a = b; b = t;
            rMin.value = a;
            rMax.value = b;
        }
        hMin.value = a;
        hMax.value = b;
        if (priceRoot && ceil > 0) {
            priceRoot.style.setProperty('--min-pct', ((a / ceil) * 100) + '%');
            priceRoot.style.setProperty('--max-pct', ((b / ceil) * 100) + '%');
        }
        if (line) {
            var atCeil = b >= ceil;
            line.textContent = '₹' + fmt(a) + ' - ' + (atCeil ? '₹' + fmt(ceil) + '+' : '₹' + fmt(b));
        }
    }

    if (rMin && rMax) {
        rMin.addEventListener('input', syncPrice);
        rMax.addEventListener('input', syncPrice);
        rMin.addEventListener('change', function () { syncPrice(); submitFilter(); });
        rMax.addEventListener('change', function () { syncPrice(); submitFilter(); });
        syncPrice();
    }

    var brandList = document.getElementById('mkBrandList');
    var brandExpand = document.getElementById('mkBrandExpand');
    if (brandExpand && brandList) {
        var moreLbl = brandExpand.getAttribute('data-label-more') || '';
        var lessLbl = brandExpand.getAttribute('data-label-less') || '';
        brandExpand.addEventListener('click', function () {
            var open = brandList.classList.toggle('mk-filter-brand-list--expanded');
            brandExpand.setAttribute('aria-expanded', open ? 'true' : 'false');
            brandExpand.textContent = open ? lessLbl : moreLbl;
        });
    }

    var bsToggle = document.getElementById('mkBrandSearchToggle');
    var bsWrap = document.getElementById('mkBrandSearchWrap');
    var bsInput = document.getElementById('mkBrandSearchInput');
    if (bsToggle && bsWrap) {
        bsToggle.addEventListener('click', function () {
            var open = bsWrap.hidden === false;
            bsWrap.hidden = open;
            bsToggle.setAttribute('aria-expanded', open ? 'false' : 'true');
            if (!open && bsInput) {
                setTimeout(function () { bsInput.focus(); }, 10);
            }
        });
    }
    if (bsInput && brandList) {
        bsInput.addEventListener('input', function () {
            var q = bsInput.value.trim().toLowerCase();
            brandList.querySelectorAll('.mk-filter-row[data-brand-label]').forEach(function (row) {
                var lab = row.getAttribute('data-brand-label') || '';
                row.style.display = !q || lab.indexOf(q) !== -1 ? '' : 'none';
            });
        });
    }
})();
</script>
@endpush
