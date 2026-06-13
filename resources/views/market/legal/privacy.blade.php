@extends('layouts.market')

@section('title', 'Privacy Policy | Devbhoomi Naturals')
@section('meta_description', 'Learn how Devbhoomi Naturals collects, uses and protects your personal data when you shop organic Himalayan products online. Cookie and data rights explained.')
@section('canonical', route('legal.privacy'))

@push('breadcrumb')
    @include('market.partials.breadcrumbs', [
        'title' => __('Privacy policy'),
        'items' => [['label' => __('Privacy policy')]],
    ])
@endpush

@section('content')
<section class="zm-card p-4 p-md-5">
    <p class="text-muted mb-4">{{ __('Last Updated: June 2026') }}</p>

    <p>{{ __('At Devbhoomi Naturals, we value your privacy and are committed to protecting your personal information. This Privacy Policy explains how we collect, use, store, and protect your information when you visit our website or place an order.') }}</p>

    <h2 class="h5 mt-4">{{ __('Information We Collect') }}</h2>
    <p>{{ __('We may collect the following information:') }}</p>
    <ul>
        <li>{{ __('Name') }}</li>
        <li>{{ __('Email address') }}</li>
        <li>{{ __('Phone number') }}</li>
        <li>{{ __('Shipping and billing address') }}</li>
        <li>{{ __('Order details') }}</li>
        <li>{{ __('Payment-related information (processed securely through payment providers)') }}</li>
        <li>{{ __('Website usage information through cookies and analytics tools') }}</li>
    </ul>

    <h2 class="h5 mt-4">{{ __('How We Use Your Information') }}</h2>
    <p>{{ __('We use your information to:') }}</p>
    <ul>
        <li>{{ __('Process and deliver your orders') }}</li>
        <li>{{ __('Provide customer support') }}</li>
        <li>{{ __('Send order updates and notifications') }}</li>
        <li>{{ __('Improve our products and services') }}</li>
        <li>{{ __('Prevent fraud and unauthorized transactions') }}</li>
        <li>{{ __('Comply with legal obligations') }}</li>
    </ul>

    <h2 class="h5 mt-4">{{ __('Payment Information') }}</h2>
    <p>{{ __('Payments are processed through trusted third-party payment service providers. We do not store your complete card details or sensitive payment credentials on our servers.') }}</p>

    <h2 class="h5 mt-4">{{ __('Sharing of Information') }}</h2>
    <p>{{ __('We may share necessary information with:') }}</p>
    <ul>
        <li>{{ __('Payment gateway providers') }}</li>
        <li>{{ __('Shipping and logistics partners') }}</li>
        <li>{{ __('Service providers supporting website operations') }}</li>
    </ul>
    <p>{{ __('We do not sell or rent your personal information to third parties.') }}</p>

    <h2 class="h5 mt-4">{{ __('Cookies and Analytics') }}</h2>
    <p>{{ __('We use cookies and similar technologies to improve website functionality, remember preferences, analyze traffic, and enhance user experience.') }}</p>
    <p>{{ __('You may disable cookies through your browser settings, although some website features may not function properly.') }}</p>

    <h2 class="h5 mt-4">{{ __('Data Security') }}</h2>
    <p>{{ __('We implement reasonable security measures to protect your personal information from unauthorized access, misuse, disclosure, or loss. However, no online transmission method can be guaranteed to be completely secure.') }}</p>

    <h2 class="h5 mt-4">{{ __('Data Retention') }}</h2>
    <p>{{ __('We retain your information for as long as necessary to fulfill orders, provide services, comply with legal obligations, and resolve disputes.') }}</p>

    <h2 class="h5 mt-4">{{ __('Your Rights') }}</h2>
    <p>{{ __('You may request access to, correction of, or deletion of your personal information by contacting us through the details provided below.') }}</p>

    <h2 class="h5 mt-4">{{ __('Third-Party Links') }}</h2>
    <p>{{ __('Our website may contain links to third-party websites. We are not responsible for the privacy practices or content of such websites.') }}</p>

    <h2 class="h5 mt-4">{{ __('Changes to This Policy') }}</h2>
    <p>{{ __('We may update this Privacy Policy from time to time. Changes will be posted on this page with the updated effective date.') }}</p>

    <h2 class="h5 mt-4">{{ __('Contact Us') }}</h2>
    <p>{{ __('For privacy-related questions, requests, or concerns, please contact us at:') }}</p>
    <ul class="mb-0">
        <li>{{ __('Email: support@devbhoominaturals.com') }}</li>
        <li>{{ __('Website: https://devbhoominaturals.com') }}</li>
    </ul>
</section>
@endsection
