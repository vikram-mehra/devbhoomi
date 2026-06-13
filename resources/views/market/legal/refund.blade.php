@extends('layouts.market')

@section('title', 'Refund & Return Policy | Devbhoomi Naturals')
@section('meta_description', 'Devbhoomi Naturals refund and return policy for organic food orders. Learn eligibility, timelines and how to request returns for damaged or incorrect items.')
@section('canonical', route('legal.refund'))

@push('breadcrumb')
    @include('market.partials.breadcrumbs', [
        'title' => __('Refund policy'),
        'items' => [['label' => __('Refund policy')]],
    ])
@endpush

@section('content')
<section class="zm-card p-4 p-md-5">
    <p class="text-muted mb-4">{{ __('Last Updated: June 2026') }}</p>

    <p>{{ __('At Devbhoomi Naturals, we are committed to delivering quality products. Due to the nature of food products, please read our return and refund policy carefully.') }}</p>

    <h2 class="h5 mt-4">{{ __('Order Cancellation') }}</h2>
    <p>{{ __('Orders may be cancelled before they are dispatched. Once an order has been shipped, cancellation requests cannot be accepted.') }}</p>

    <h2 class="h5 mt-4">{{ __('Returns') }}</h2>
    <p>{{ __('As we sell food products, returns are generally not accepted after delivery due to hygiene, safety, and quality considerations.') }}</p>

    <h2 class="h5 mt-4">{{ __('Damaged, Defective, or Incorrect Products') }}</h2>
    <p>{{ __('If you receive:') }}</p>
    <ul>
        <li>{{ __('A damaged product') }}</li>
        <li>{{ __('A defective product') }}</li>
        <li>{{ __('An incorrect item') }}</li>
    </ul>
    <p>{{ __('Please contact us within 48 hours of delivery with:') }}</p>
    <ul>
        <li>{{ __('Order number') }}</li>
        <li>{{ __('Clear photographs of the package and product') }}</li>
        <li>{{ __('Description of the issue') }}</li>
    </ul>
    <p>{{ __('After verification, we may offer a replacement, store credit, or refund at our discretion.') }}</p>

    <h2 class="h5 mt-4">{{ __('Refund Processing') }}</h2>
    <p>{{ __('Approved refunds will be processed to the original payment method used for the purchase.') }}</p>
    <p>{{ __('Refunds are generally completed within 5–10 business days after approval, depending on the payment provider and banking system.') }}</p>

    <h2 class="h5 mt-4">{{ __('Non-Refundable Situations') }}</h2>
    <p>{{ __('Refunds or replacements will not be provided for:') }}</p>
    <ul>
        <li>{{ __('Change of mind after delivery') }}</li>
        <li>{{ __('Incorrect address provided by the customer') }}</li>
        <li>{{ __('Delivery attempts failed due to customer unavailability') }}</li>
        <li>{{ __('Products opened, consumed, or partially used') }}</li>
        <li>{{ __('Quality concerns reported after the specified reporting period') }}</li>
    </ul>

    <h2 class="h5 mt-4">{{ __('Contact Us') }}</h2>
    <p>{{ __('For refund-related assistance, please contact:') }}</p>
    <ul>
        <li>{{ __('Email: support@devbhoominaturals.com') }}</li>
        <li>{{ __('Website: https://devbhoominaturals.com') }}</li>
    </ul>
    <p class="mb-0">{{ __('Please include your order number and relevant details when contacting support.') }}</p>
</section>
@endsection
