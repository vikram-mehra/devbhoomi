<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Services\CheckoutPricingService;
use App\Services\CouponService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class CouponController extends Controller
{
    public function index(CouponService $coupons)
    {
        $items = Coupon::query()
            ->public()
            ->activeValid()
            ->orderByDesc('id')
            ->get()
            ->map(fn (Coupon $coupon) => $coupons->toPublicPayload($coupon));

        return response()->json(['data' => $items]);
    }

    public function apply(Request $request, CouponService $coupons)
    {
        $data = $request->validate([
            'coupon_code' => 'required|string|max:64',
            'subtotal' => 'required|numeric|min:0',
            'shipping' => 'nullable|numeric|min:0',
            'tax' => 'nullable|numeric|min:0',
        ]);

        $subtotal = (float) $data['subtotal'];
        $shipping = (float) ($data['shipping'] ?? 0);
        $tax = (float) ($data['tax'] ?? 0);

        try {
            $resolved = $coupons->resolveForCart($data['coupon_code'], $subtotal);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => collect($e->errors())->flatten()->first() ?? __('Invalid coupon.'),
            ], 422);
        }

        $discount = $resolved['discount'];
        $coupon = $resolved['coupon'];
        $pricing = app(CheckoutPricingService::class);
        $total = $pricing->grandTotal($subtotal, $shipping, $tax, $discount);

        return response()->json([
            'code' => $coupon->code,
            'coupon_type' => $coupon->coupon_type,
            'discount' => $discount,
            'discount_formatted' => '₹'.number_format($discount, 2),
            'subtotal' => $subtotal,
            'shipping' => $shipping,
            'tax' => $tax,
            'total' => $total,
            'total_formatted' => '₹'.number_format($total, 2),
            'message' => $coupon->type === 'percent'
                ? __(':code applied — :value% off', ['code' => $coupon->code, 'value' => rtrim(rtrim(number_format((float) $coupon->value, 2), '0'), '.')])
                : __(':code applied — ₹:value off', ['code' => $coupon->code, 'value' => number_format((float) $coupon->value, 2)]),
        ]);
    }
}
