@php
    $coupon = $coupon ?? null;
    $fieldId = $fieldId ?? 'create';
    $formKey = $formKey ?? $fieldId;
    $useOld = $useOld ?? (old('_form') === $formKey);
    $selectedProductIds = $useOld ? old('product_ids', $coupon ? $coupon->products->pluck('id')->all() : []) : ($coupon ? $coupon->products->pluck('id')->all() : []);
    $selectedMenuIds = $useOld ? old('menu_item_ids', $coupon ? $coupon->menuItems->pluck('id')->all() : []) : ($coupon ? $coupon->menuItems->pluck('id')->all() : []);
    $val = function (string $key, $fallback = '') use ($useOld) {
        return $useOld ? old($key, $fallback) : $fallback;
    };
@endphp
<div class="row g-2">
    <div class="col-md-3">
        <label class="form-label small">{{ __('Code') }} *</label>
        <input name="code" class="form-control text-uppercase {{ $useOld && $errors->has('code') ? 'is-invalid' : '' }}" placeholder="SAVE20" required value="{{ $val('code', $coupon->code ?? '') }}">
        @if($useOld) @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror @endif
    </div>
    <div class="col-md-3">
        <label class="form-label small">{{ __('Coupon type') }} *</label>
        <select name="coupon_type" class="form-select">
            <option value="public" @selected($val('coupon_type', $coupon->coupon_type ?? 'public') === 'public')>{{ __('Public') }}</option>
            <option value="internal" @selected($val('coupon_type', $coupon->coupon_type ?? '') === 'internal')>{{ __('Internal') }}</option>
        </select>
    </div>
    <div class="col-md-3">
        <label class="form-label small">{{ __('Discount type') }} *</label>
        <select name="type" class="form-select">
            <option value="percent" @selected($val('type', $coupon->type ?? '') === 'percent')>%</option>
            <option value="fixed" @selected($val('type', $coupon->type ?? '') === 'fixed')>{{ __('Fixed ₹') }}</option>
        </select>
    </div>
    <div class="col-md-3">
        <label class="form-label small">{{ __('Value') }} *</label>
        <input name="value" type="number" step="0.01" min="0" class="form-control {{ $useOld && $errors->has('value') ? 'is-invalid' : '' }}" required value="{{ $val('value', $coupon->value ?? '') }}">
        @if($useOld) @error('value')<div class="invalid-feedback">{{ $message }}</div>@enderror @endif
    </div>
    <div class="col-md-3">
        <label class="form-label small">{{ __('Minimum order amount') }}</label>
        <input name="min_cart" type="number" step="0.01" min="0" class="form-control" value="{{ $val('min_cart', $coupon->min_cart ?? '') }}" placeholder="{{ __('Blank = no minimum') }}">
    </div>
    <div class="col-md-3">
        <label class="form-label small">{{ __('Maximum discount amount') }}</label>
        <input name="max_discount" type="number" step="0.01" min="0" class="form-control" value="{{ $val('max_discount', $coupon->max_discount ?? '') }}" placeholder="{{ __('For % coupons') }}">
    </div>
    <div class="col-md-3">
        <label class="form-label small">{{ __('Total usage limit') }}</label>
        <input name="usage_limit" type="number" min="1" class="form-control" value="{{ $val('usage_limit', $coupon->usage_limit ?? '') }}" placeholder="{{ __('Blank = unlimited') }}">
    </div>
    <div class="col-md-3">
        <label class="form-label small">{{ __('Per-user usage limit') }}</label>
        <input name="per_user_limit" type="number" min="1" class="form-control" value="{{ $val('per_user_limit', $coupon->per_user_limit ?? '') }}" placeholder="{{ __('Blank = unlimited') }}">
    </div>
    <div class="col-md-3">
        <label class="form-label small">{{ __('Starts at') }} *</label>
        <input name="starts_at" type="datetime-local" class="form-control" required value="{{ $val('starts_at', optional($coupon->starts_at ?? null)->format('Y-m-d\TH:i') ?: now()->format('Y-m-d\TH:i')) }}">
    </div>
    <div class="col-md-3">
        <label class="form-label small">{{ __('Expiry date') }} *</label>
        <input name="ends_at" type="datetime-local" class="form-control" required value="{{ $val('ends_at', optional($coupon->ends_at ?? null)->format('Y-m-d\TH:i') ?: now()->addMonths(3)->format('Y-m-d\TH:i')) }}">
    </div>
    <div class="col-md-6">
        <label class="form-label small">{{ __('Applicable products') }}</label>
        <select name="product_ids[]" class="form-select" multiple size="5">
            @foreach($products as $product)
                <option value="{{ $product->id }}" @selected(in_array($product->id, $selectedProductIds, false))>{{ $product->name }}</option>
            @endforeach
        </select>
        <div class="form-text">{{ __('Leave empty to apply to all products.') }}</div>
    </div>
    <div class="col-md-6">
        <label class="form-label small">{{ __('Applicable categories') }}</label>
        <select name="menu_item_ids[]" class="form-select" multiple size="5">
            @foreach($menuOptions as $option)
                <option value="{{ $option['id'] }}" @selected(in_array($option['id'], $selectedMenuIds, false))>{{ $option['label'] }}</option>
            @endforeach
        </select>
        <div class="form-text">{{ __('Leave empty for all categories. Child categories are included.') }}</div>
    </div>
    <div class="col-md-6">
        <input type="hidden" name="is_active" value="0">
        <div class="form-check mt-2">
            <input type="checkbox" name="is_active" value="1" class="form-check-input" id="active{{ $fieldId }}" @checked($useOld ? old('is_active', $coupon->is_active ?? true) : ($coupon->is_active ?? true))>
            <label class="form-check-label" for="active{{ $fieldId }}">{{ __('Enable coupon') }}</label>
        </div>
    </div>
    <div class="col-md-6">
        <input type="hidden" name="first_order_only" value="0">
        <div class="form-check mt-2">
            <input type="checkbox" name="first_order_only" value="1" class="form-check-input" id="firstOrder{{ $fieldId }}" @checked($useOld ? old('first_order_only', $coupon->first_order_only ?? false) : ($coupon->first_order_only ?? false))>
            <label class="form-check-label" for="firstOrder{{ $fieldId }}">{{ __('First order only') }}</label>
        </div>
    </div>
</div>
