<?php

namespace App\Http\Controllers;

use App\Models\Coupon;

class CouponController extends Controller
{
    public function index()
    {
        $coupons = Coupon::query()
            ->public()
            ->activeValid()
            ->orderByDesc('id')
            ->paginate(12);

        return view('market.offers', compact('coupons'));
    }
}
