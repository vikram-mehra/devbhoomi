@extends('layouts.market')

@section('title', 'Terms & Conditions | Devbhoomi Naturals')
@section('meta_description', 'Read the terms and conditions for shopping organic Himalayan products on Devbhoomi Naturals. Orders, payments, shipping and user responsibilities explained.')
@section('canonical', route('legal.terms'))

@push('breadcrumb')
    @include('market.partials.breadcrumbs', [
        'title' => __('Terms and conditions'),
        'items' => [['label' => __('Terms and conditions')]],
    ])
@endpush

@section('content')
<section class="zm-card p-4 p-md-5">
    <p class="text-muted mb-4">{{ __('Last Updated: June 2026') }}</p>

    <p>{{ __('Welcome to Devbhoomi Naturals. By accessing or using our website, you agree to comply with and be bound by the following Terms and Conditions.') }}</p>

    <h2 class="h5 mt-4">{{ __('Use of the Website') }}</h2>
    <p>{{ __('You agree to use this website only for lawful purposes and in a manner that does not infringe the rights of others or restrict their use of the website. You must provide accurate and complete information while creating an account or placing an order.') }}</p>

    <h2 class="h5 mt-4">{{ __('Products and Pricing') }}</h2>
    <p>{{ __('We strive to ensure that all product descriptions, images, and prices are accurate. However, errors may occasionally occur. We reserve the right to correct any errors, update information, or cancel orders if information is inaccurate.') }}</p>

    <h2 class="h5 mt-4">{{ __('Orders and Payments') }}</h2>
    <p>{{ __('Orders are subject to acceptance and availability. Payment must be successfully authorized before order processing. We reserve the right to refuse or cancel any order suspected of fraud, unauthorized activity, or product unavailability.') }}</p>

    <h2 class="h5 mt-4">{{ __('Shipping and Delivery') }}</h2>
    <p>{{ __('Estimated delivery timelines are provided for convenience and may vary depending on location, weather conditions, courier operations, and other factors beyond our control. Devbhoomi Naturals is not responsible for delays caused by third-party logistics providers.') }}</p>

    <h2 class="h5 mt-4">{{ __('Cancellation and Refunds') }}</h2>
    <p>{{ __('Order cancellations, returns, and refunds are governed by our Return and Refund Policy. Please review the policy before placing an order.') }}</p>

    <h2 class="h5 mt-4">{{ __('Account Responsibility') }}</h2>
    <p>{{ __('You are responsible for maintaining the confidentiality of your account credentials and for all activities conducted under your account. Please notify us immediately of any unauthorized use of your account.') }}</p>

    <h2 class="h5 mt-4">{{ __('Intellectual Property') }}</h2>
    <p>{{ __('All content on this website, including text, images, logos, graphics, product descriptions, and website design, is the property of Devbhoomi Naturals and may not be copied, reproduced, or distributed without prior written permission.') }}</p>

    <h2 class="h5 mt-4">{{ __('Limitation of Liability') }}</h2>
    <p>{{ __('To the maximum extent permitted by law, Devbhoomi Naturals shall not be liable for any indirect, incidental, special, or consequential damages arising from the use of our website, products, or services.') }}</p>

    <h2 class="h5 mt-4">{{ __('Privacy') }}</h2>
    <p>{{ __('Your use of this website is also governed by our Privacy Policy, which explains how we collect, use, and protect your information.') }}</p>

    <h2 class="h5 mt-4">{{ __('Governing Law') }}</h2>
    <p>{{ __('These Terms and Conditions shall be governed by and interpreted in accordance with the laws of India. Any disputes arising from the use of this website shall be subject to the jurisdiction of the competent courts.') }}</p>

    <h2 class="h5 mt-4">{{ __('Contact Us') }}</h2>
    <p>{{ __('If you have any questions regarding these Terms and Conditions, please contact us through the contact details provided on our website.') }}</p>

    <p class="mt-4 mb-0">{{ __('By using this website, you acknowledge that you have read, understood, and agreed to these Terms and Conditions.') }}</p>
</section>
@endsection
