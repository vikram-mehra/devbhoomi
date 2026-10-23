@extends('layouts.admin')

@section('title', __('Shipping settings'))

@section('content')
    <div class="mb-4">
        <h1 class="h4 mb-1">{{ __('Shipping settings') }}</h1>
        <p class="text-muted mb-0">{{ __('Fallback delivery rates when a pincode has no charge of its own. Pincode rates are set under Pincodes.') }}</p>
    </div>

    <form method="post" action="{{ route('admin.shipping-settings.update') }}" class="card p-4" style="max-width:560px">@csrf
        <div class="mb-3">
            <label class="form-label">{{ __('Free shipping minimum order amount (₹)') }}</label>
            <input type="number" step="0.01" min="0" name="free_shipping_amount" class="form-control"
                   value="{{ old('free_shipping_amount', $settings->free_shipping_amount) }}" required>
            <div class="form-text">{{ __('Orders at or above this subtotal qualify for free shipping.') }}</div>
        </div>
        <div class="mb-3">
            <label class="form-label">{{ __('Fallback delivery charge (₹) for first :grams g', ['grams' => old('base_weight_grams', $settings->base_weight_grams ?: 500)]) }}</label>
            <input type="number" step="0.01" min="0" name="shipping_charge" class="form-control"
                   value="{{ old('shipping_charge', $settings->shipping_charge) }}" required>
            <div class="form-text">{{ __('Used when the destination pincode has no delivery charge configured.') }}</div>
        </div>
        <div class="mb-3">
            <label class="form-label">{{ __('Base weight slab (grams)') }}</label>
            <input type="number" step="1" min="1" max="20000" name="base_weight_grams" class="form-control"
                   value="{{ old('base_weight_grams', $settings->base_weight_grams ?: 500) }}" required>
            <div class="form-text">{{ __('Pincode charges apply to this weight. Extra weight is billed in the same slab size (default 500 g).') }}</div>
        </div>
        <div class="mb-3">
            <label class="form-label">{{ __('Fallback extra-slab charge (₹)') }}</label>
            <input type="number" step="0.01" min="0" name="additional_500g_charge" class="form-control"
                   value="{{ old('additional_500g_charge', $settings->additional_500g_charge) }}" placeholder="{{ __('Same as first slab if left empty') }}">
            <div class="form-text">{{ __('Charged for each extra slab after the first. Leave empty to reuse the first-slab rate.') }}</div>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <button class="btn btn-primary">{{ __('Save') }}</button>
            <a href="{{ route('admin.pincodes.index') }}" class="btn btn-outline-secondary">{{ __('Configure pincode charges') }}</a>
        </div>
    </form>
@endsection
