@extends('layouts.market')

@php
    $freeShippingThreshold = (float) (\App\Models\ShippingSetting::current()->free_shipping_amount ?? 0);
@endphp

@section('title', 'Shipping Policy')
@section('meta_description')
@if($freeShippingThreshold > 0)
{{ __('Shipping and delivery policy for Devbhoomi Naturals organic products. Pan-India delivery, free shipping on orders above ₹:amount, and estimated delivery timelines explained.', ['amount' => number_format($freeShippingThreshold, 0)]) }}
@else
{{ __('Shipping and delivery policy for Devbhoomi Naturals organic products. Pan-India delivery rates, courier tracking, and estimated delivery timelines explained.') }}
@endif
@endsection
@section('canonical', route('legal.shipping'))

@push('schema')
<script type="application/ld+json">
{!! json_encode(app(\App\Services\SeoService::class)->webPageSchema('Shipping Policy', route('legal.shipping'), 'Shipping and delivery policy for Devbhoomi Naturals organic products.'), JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE) !!}
</script>
@endpush

@push('breadcrumb')
    @include('market.partials.breadcrumbs', [
        'title' => __('Shipping policy'),
        'items' => [['label' => __('Shipping policy')]],
    ])
@endpush

@section('content')
<section class="zm-card p-4 p-md-5">
    <p class="text-muted mb-4">{{ __('Last Updated: June 2026') }}</p>

    <p>{{ __('Thank you for shopping with Devbhoomi Naturals.') }}</p>

    <h2 class="h5 mt-4">{{ __('Order Processing') }}</h2>
    <p>{{ __('Orders are typically processed within 1–2 business days after confirmation. Orders placed on weekends or public holidays will be processed on the next working day.') }}</p>

    <h2 class="h5 mt-4">{{ __('Shipping Coverage') }}</h2>
    <p>{{ __('We currently ship across India through trusted courier and logistics partners.') }}</p>

    <h2 class="h5 mt-4">{{ __('Delivery Time') }}</h2>
    <p>{{ __('Estimated delivery timelines are:') }}</p>
    <ul>
        <li>{{ __('Metro Cities: 3–7 business days') }}</li>
        <li>{{ __('Other Cities and Towns: 5–10 business days') }}</li>
        <li>{{ __('Remote Areas: 7–14 business days') }}</li>
    </ul>
    <p>{{ __('Delivery times are estimates and may vary due to weather conditions, courier delays, public holidays, or other unforeseen circumstances.') }}</p>

    <h2 class="h5 mt-4">{{ __('Shipping Charges') }}</h2>
    @if($freeShippingThreshold > 0)
        <p>{{ __('We offer free standard delivery across India on all orders of ₹:amount and above. For orders below ₹:amount, a nominal delivery charge (starting at ₹49) is calculated at checkout based on your delivery destination and package weight.', ['amount' => number_format($freeShippingThreshold, 0)]) }}</p>
    @else
        <p>{{ __('Shipping charges are calculated at checkout based on your delivery destination and package weight. Nominal delivery rates apply across all serviced pincodes in India.') }}</p>
    @endif

    <h2 class="h5 mt-4">{{ __('Order Tracking') }}</h2>
    <p>{{ __('Once your order is shipped, tracking details will be shared via email, SMS, or WhatsApp (where applicable). After logging in, you can also use Track order on this website with your order number and the email or mobile used at checkout.') }}</p>

    <h2 class="h5 mt-4">{{ __('Damaged or Lost Shipments') }}</h2>
    <p>{{ __('If your package arrives damaged or appears to be lost in transit, please contact us within 48 hours of delivery or expected delivery date. We will work with our logistics partner to resolve the issue.') }}</p>

    <h2 class="h5 mt-4">{{ __('Contact Us') }}</h2>
    <p class="mb-0">{{ __('For shipping-related queries, please contact our customer support team through the contact details available on our website.') }}</p>
</section>
@endsection
