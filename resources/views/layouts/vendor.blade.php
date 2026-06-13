<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @include('partials.favicon')
    <title>@yield('title', 'Seller') — {{ config('app.name') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:ital,wght@0,300;0,400;0,600;0,700;1,400&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>body{font-family:"Open Sans",sans-serif;font-weight:300}</style>
</head>
<body class="bg-light">
    @php
        $vendorFlashExtra = [];
        if (! empty($vendorPendingBanner) && isset($vendor)) {
            $vendorFlashExtra[] = [
                'type' => 'warning',
                'body' => __('Your shop :name is :status. An admin will review it shortly.', [
                    'name' => $vendor->shop_name,
                    'status' => $vendor->status,
                ]),
            ];
        }
    @endphp
    @include('partials.flash-toasts', ['extraMessages' => $vendorFlashExtra, 'includeValidationErrors' => true])
    <nav class="navbar navbar-dark mb-4" style="background:#5b4dff;">
        <div class="container-fluid">
            <a class="navbar-brand" href="{{ route('vendor.dashboard') }}">Seller hub</a>
            <div class="d-flex gap-2">
                <a class="btn btn-sm btn-light" href="{{ route('vendor.products.index') }}">Products</a>
                <a class="btn btn-sm btn-light" href="{{ route('vendor.orders.index') }}">Orders</a>
                <a class="btn btn-sm btn-outline-light" href="{{ route('market.home') }}">Store</a>
            </div>
        </div>
    </nav>
    <main class="container pb-5">
        @yield('content')
    </main>
</body>
</html>
