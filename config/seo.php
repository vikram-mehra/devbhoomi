<?php

return [

    'title_min' => 50,
    'title_max' => 60,
    'description_min' => 150,
    'description_max' => 160,

    'default_title_suffix' => 'Devbhoomi Naturals',

    'default_description' => 'Shop pure Himalayan organic products — millets, pahadi pulses, spices & grains. Direct from Uttarakhand farmers. Free delivery above ₹499.',

    'default_keywords' => 'organic food, Himalayan products, Uttarakhand, millets, pahadi pulses, natural spices, Devbhoomi Naturals',

    'default_og_image' => '/images/logo.png',

    'organization' => [
        'name' => 'Devbhoomi Naturals',
        'legal_name' => 'Dev Bhoomi Naturals',
        'url' => 'https://devbhoominaturals.com',
        'logo' => '/images/logo.png',
        'email' => 'support@devbhoominaturals.com',
        'phone' => '+919217732670',
        'address' => [
            'street' => 'Ranikhet',
            'city' => 'Ranikhet',
            'region' => 'Uttarakhand',
            'postal_code' => '263645',
            'country' => 'IN',
        ],
        'geo' => [
            'latitude' => 29.6408,
            'longitude' => 79.4322,
        ],
        'same_as' => [
            'https://www.facebook.com/share/1D7KtFBEGi/',
            'https://www.instagram.com/dev_bhoominaturals',
        ],
    ],

    'noindex_route_patterns' => [
        'cart.*',
        'checkout.*',
        'pay.*',
        'orders.*',
        'account.*',
        'wishlist.*',
        'chat.*',
        'login',
        'register',
        'password.*',
        'verification.*',
        'auth.*',
        'admin.*',
        'vendor.dashboard',
        'vendor.orders.*',
        'vendor.products.*',
        'vendor.register*',
    ],

    'sitemap' => [
        'static_pages' => [
            ['route' => 'market.home', 'priority' => '1.0', 'changefreq' => 'daily'],
            ['route' => 'shop.search', 'priority' => '0.9', 'changefreq' => 'daily'],
            ['route' => 'blog.index', 'priority' => '0.8', 'changefreq' => 'weekly'],
            ['route' => 'pages.about', 'priority' => '0.7', 'changefreq' => 'monthly'],
            ['route' => 'pages.contact', 'priority' => '0.7', 'changefreq' => 'monthly'],
            ['route' => 'offers.index', 'priority' => '0.6', 'changefreq' => 'weekly'],
            ['route' => 'legal.terms', 'priority' => '0.3', 'changefreq' => 'yearly'],
            ['route' => 'legal.privacy', 'priority' => '0.3', 'changefreq' => 'yearly'],
            ['route' => 'legal.refund', 'priority' => '0.3', 'changefreq' => 'yearly'],
            ['route' => 'legal.shipping', 'priority' => '0.3', 'changefreq' => 'yearly'],
            ['route' => 'pages.sitemap', 'priority' => '0.4', 'changefreq' => 'monthly'],
        ],
    ],

    'robots_disallow' => [
        '/admin',
        '/vendor',
        '/cart',
        '/checkout',
        '/account',
        '/orders',
        '/wishlist',
        '/login',
        '/register',
        '/pay/',
        '/api/',
    ],

];
