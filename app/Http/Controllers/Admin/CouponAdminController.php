<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Services\CouponService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class CouponAdminController extends Controller
{
    public function index()
    {
        $coupons = Coupon::latest()->paginate(25);

        return view('admin.coupons', compact('coupons'));
    }

    public function store(Request $request)
    {
        $data = $this->validatedCoupon($request);
        Coupon::create($data + ['used_count' => 0, 'is_active' => true]);

        return back()->with('status', __('Coupon created.'));
    }

    public function update(Request $request, Coupon $coupon)
    {
        $data = $this->validatedCoupon($request, $coupon);
        $coupon->update($data);

        return back()->with('status', __('Coupon updated.'));
    }

    public function destroy(Coupon $coupon)
    {
        $coupon->delete();

        return back()->with('status', __('Coupon deleted.'));
    }

    public function toggle(Coupon $coupon)
    {
        $coupon->update(['is_active' => ! $coupon->is_active]);

        return back()->with('status', __('Coupon toggled.'));
    }

    public function validateForOrder(Request $request, CouponService $coupons)
    {
        $data = $request->validate([
            'coupon_code' => 'required|string|max:64',
            'subtotal' => 'required|numeric|min:0',
        ]);

        try {
            $resolved = $coupons->resolveForCart($data['coupon_code'], (float) $data['subtotal']);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => collect($e->errors())->flatten()->first() ?? __('Invalid coupon.'),
            ], 422);
        }

        $coupon = $resolved['coupon'];

        return response()->json([
            'code' => $coupon->code,
            'coupon_type' => $coupon->coupon_type,
            'discount' => $resolved['discount'],
            'discount_formatted' => '₹'.number_format($resolved['discount'], 2),
            'message' => $coupon->type === 'percent'
                ? __(':code applied — :value% off', ['code' => $coupon->code, 'value' => rtrim(rtrim(number_format((float) $coupon->value, 2), '0'), '.')])
                : __(':code applied — ₹:value off', ['code' => $coupon->code, 'value' => number_format((float) $coupon->value, 2)]),
        ]);
    }

    protected function validatedCoupon(Request $request, ?Coupon $coupon = null): array
    {
        $codeRule = ['required', 'string', 'max:64'];
        if ($coupon) {
            $codeRule[] = Rule::unique('coupons', 'code')->ignore($coupon->id);
        } else {
            $codeRule[] = 'unique:coupons,code';
        }

        $data = $request->validate([
            'code' => $codeRule,
            'coupon_type' => 'required|in:public,internal',
            'type' => 'required|in:percent,fixed',
            'value' => 'required|numeric|min:0',
            'min_cart' => 'nullable|numeric|min:0',
            'max_discount' => 'nullable|numeric|min:0',
            'usage_limit' => 'nullable|integer|min:1',
            'starts_at' => 'required|date',
            'ends_at' => 'required|date|after:starts_at',
            'is_active' => 'nullable|boolean',
        ]);

        return [
            'code' => strtoupper(trim($data['code'])),
            'coupon_type' => $data['coupon_type'],
            'type' => $data['type'],
            'value' => $data['value'],
            'min_cart' => $data['min_cart'] ?? 0,
            'max_discount' => $data['max_discount'] ?: null,
            'starts_at' => $data['starts_at'],
            'ends_at' => $data['ends_at'],
            'usage_limit' => $data['usage_limit'] ?? null,
            'is_active' => $request->boolean('is_active', $coupon ? $coupon->is_active : true),
        ];
    }
}
