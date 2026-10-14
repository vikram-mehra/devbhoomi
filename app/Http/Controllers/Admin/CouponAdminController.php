<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Models\CouponUsage;
use App\Models\Product;
use App\Services\CouponService;
use App\Support\MenuItemTree;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class CouponAdminController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->get('q', ''));
        $status = (string) $request->get('status', '');

        $coupons = Coupon::query()
            ->with(['products:id,name', 'menuItems:id,title'])
            ->withCount([
                'usages as reserved_count' => fn ($query) => $query->where('status', CouponUsage::STATUS_RESERVED),
                'usages as redeemed_count' => fn ($query) => $query->where('status', CouponUsage::STATUS_REDEEMED),
            ])
            ->when($q !== '', fn ($query) => $query->where('code', 'like', '%'.$q.'%'))
            ->when($status === 'active', function ($query) {
                $query->where('is_active', true)
                    ->where(function ($inner) {
                        $inner->whereNull('ends_at')->orWhere('ends_at', '>=', now());
                    })
                    ->where(function ($inner) {
                        $inner->whereNull('starts_at')->orWhere('starts_at', '<=', now());
                    });
            })
            ->when($status === 'inactive', fn ($query) => $query->where('is_active', false))
            ->when($status === 'expired', function ($query) {
                $query->whereNotNull('ends_at')->where('ends_at', '<', now());
            })
            ->latest()
            ->paginate(25)
            ->withQueryString();

        $discountTotals = CouponUsage::query()
            ->selectRaw('coupon_id, SUM(discount_amount) as total')
            ->where('status', CouponUsage::STATUS_REDEEMED)
            ->whereIn('coupon_id', $coupons->pluck('id')->filter()->all() ?: [0])
            ->groupBy('coupon_id')
            ->pluck('total', 'coupon_id');

        $coupons->getCollection()->each(function (Coupon $coupon) use ($discountTotals) {
            $coupon->setAttribute('total_discount_granted', (float) ($discountTotals[$coupon->id] ?? 0));
        });

        $products = Product::query()->orderBy('name')->limit(500)->get(['id', 'name']);
        $menuOptions = MenuItemTree::selectOptions(false);

        return view('admin.coupons', compact('coupons', 'products', 'menuOptions', 'q', 'status'));
    }

    public function show(Coupon $coupon)
    {
        $coupon->load(['products:id,name', 'menuItems:id,title']);
        $coupon->loadCount([
            'usages as reserved_count' => fn ($query) => $query->where('status', CouponUsage::STATUS_RESERVED),
            'usages as redeemed_count' => fn ($query) => $query->where('status', CouponUsage::STATUS_REDEEMED),
        ]);
        $coupon->setAttribute(
            'total_discount_granted',
            (float) $coupon->usages()->where('status', CouponUsage::STATUS_REDEEMED)->sum('discount_amount')
        );

        $usages = $coupon->usages()
            ->with(['user:id,name,email', 'order:id,order_number,customer_name,customer_email', 'sale:id,invoice_number,customer_name,customer_email'])
            ->latest()
            ->paginate(25);

        $perCustomer = $coupon->usages()
            ->selectRaw('user_id, guest_key, COUNT(*) as uses, SUM(discount_amount) as discount_total')
            ->where('status', CouponUsage::STATUS_REDEEMED)
            ->groupBy('user_id', 'guest_key')
            ->orderByDesc('uses')
            ->limit(50)
            ->get();

        return view('admin.coupon-show', compact('coupon', 'usages', 'perCustomer'));
    }

    public function store(Request $request)
    {
        [$data, $productIds, $menuItemIds] = $this->validatedCoupon($request);
        $coupon = Coupon::create($data + ['used_count' => 0]);
        $coupon->products()->sync($productIds);
        $coupon->menuItems()->sync($menuItemIds);

        return back()->with('status', __('Coupon created.'));
    }

    public function update(Request $request, Coupon $coupon)
    {
        [$data, $productIds, $menuItemIds] = $this->validatedCoupon($request, $coupon);
        $coupon->update($data);
        $coupon->products()->sync($productIds);
        $coupon->menuItems()->sync($menuItemIds);

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

    /**
     * @return array{0: array<string, mixed>, 1: list<int>, 2: list<int>}
     */
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
            'per_user_limit' => 'nullable|integer|min:1',
            'starts_at' => 'required|date',
            'ends_at' => 'required|date|after:starts_at',
            'is_active' => 'nullable|boolean',
            'first_order_only' => 'nullable|boolean',
            'product_ids' => 'nullable|array',
            'product_ids.*' => 'integer|exists:products,id',
            'menu_item_ids' => 'nullable|array',
            'menu_item_ids.*' => 'integer|exists:menu_items,id',
        ]);

        $attrs = [
            'code' => strtoupper(trim($data['code'])),
            'coupon_type' => $data['coupon_type'],
            'type' => $data['type'],
            'value' => $data['value'],
            'min_cart' => $data['min_cart'] ?? 0,
            'max_discount' => $data['max_discount'] ?: null,
            'starts_at' => $data['starts_at'],
            'ends_at' => $data['ends_at'],
            'usage_limit' => $data['usage_limit'] ?? null,
            'per_user_limit' => $data['per_user_limit'] ?? null,
            'is_active' => $request->boolean('is_active'),
            'first_order_only' => $request->boolean('first_order_only'),
        ];

        $productIds = array_values(array_unique(array_map('intval', $data['product_ids'] ?? [])));
        $menuItemIds = array_values(array_unique(array_map('intval', $data['menu_item_ids'] ?? [])));

        return [$attrs, $productIds, $menuItemIds];
    }
}
