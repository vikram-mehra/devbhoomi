@extends('layouts.admin')

@section('title', __('Shipping settings'))

@section('content')
    <div class="mb-4">
        <h1 class="h4 mb-1">{{ __('Shipping settings') }}</h1>
        <p class="text-muted mb-0">{{ __('Configure free shipping threshold and standard delivery charge.') }}</p>
    </div>

    <form method="post" action="{{ route('admin.shipping-settings.update') }}" class="card p-4" style="max-width:480px">@csrf
        <div class="mb-3">
            <label class="form-label">{{ __('Free shipping minimum order amount (₹)') }}</label>
            <input type="number" step="0.01" min="0" name="free_shipping_amount" class="form-control"
                   value="{{ old('free_shipping_amount', $settings->free_shipping_amount) }}" required>
            <div class="form-text">{{ __('Orders at or above this subtotal qualify for free shipping.') }}</div>
        </div>
        <div class="mb-3">
            <label class="form-label">{{ __('Shipping charge amount (₹)') }}</label>
            <input type="number" step="0.01" min="0" name="shipping_charge" class="form-control"
                   value="{{ old('shipping_charge', $settings->shipping_charge) }}" required>
            <div class="form-text">{{ __('Applied when the cart subtotal is below the free shipping minimum.') }}</div>
        </div>
        <button class="btn btn-primary">{{ __('Save') }}</button>
    </form>
@endsection
