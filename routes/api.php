<?php

use App\Http\Controllers\Api\CouponController;
use App\Http\Controllers\Api\MenuItemController;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::prefix('v1')->group(function () {
    Route::get('/menu-items', [MenuItemController::class, 'index']);

    Route::get('/coupons', [CouponController::class, 'index']);
    Route::post('/coupons/apply', [CouponController::class, 'apply']);

    Route::get('/products', function () {
        return Product::with(['images', 'variants', 'vendor:id,shop_name,slug', 'menuItem:id,title,slug'])
            ->where('is_active', true)
            ->latest()
            ->paginate(20);
    });

    Route::get('/products/{product}', function (Product $product) {
        $product->load(['images', 'variants', 'vendor', 'menuItem', 'reviews.user']);

        return $product;
    });
});
