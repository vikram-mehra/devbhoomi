<?php

namespace App\Http\Controllers;

use App\Models\Address;
use App\Models\CartItem;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ShippingAddress;
use App\Models\Setting;
use App\Models\User;
use App\Services\CartService;
use App\Services\CheckoutPricingService;
use App\Services\CouponService;
use App\Services\OrderConfirmationMailService;
use App\Services\ShippingService;
use App\Services\StockLedgerService;
use App\Services\GoogleAnalyticsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class CheckoutController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(CartService $cart, ShippingService $shipping)
    {
        $items = $cart->query()->get();
        if ($items->isEmpty()) {
            return redirect()->route('cart.index')->with('error', __('Your cart is empty.'));
        }

        $items->loadMissing('variant.product');
        $pricing = app(CheckoutPricingService::class);
        $subtotalExclusive = $pricing->subtotal($items);
        $taxAmount = $pricing->taxAmount($items);
        $subtotal = $pricing->inclusiveSubtotal($items);
        $shippingCharge = $shipping->chargeForSubtotal($subtotal);
        $isFreeShipping = $shippingCharge <= 0;
        $addresses = auth()->user()->addresses()->orderByDesc('is_default')->get();

        $promoCoupons = Coupon::query()
            ->public()
            ->activeValid()
            ->orderByDesc('id')
            ->limit(3)
            ->get();

        $razorpayConfigured = (bool) (config('services.razorpay.key') && config('services.razorpay.secret'));

        $couponDiscount = 0.0;
        $appliedCouponCode = null;
        $couponInput = old('coupon_code', session('checkout_coupon_code'));
        if ($couponInput) {
            try {
                $resolved = app(CouponService::class)->resolveForCart($couponInput, $subtotal);
                $couponDiscount = $resolved['discount'];
                $appliedCouponCode = $resolved['coupon']->code;
            } catch (ValidationException $e) {
                session()->forget('checkout_coupon_code');
            }
        }

        $validateCouponPath = parse_url(url('/checkout/validate-coupon'), PHP_URL_PATH) ?: '/checkout/validate-coupon';
        $removeCouponPath = parse_url(url('/checkout/remove-coupon'), PHP_URL_PATH) ?: '/checkout/remove-coupon';

        return view('market.checkout', compact(
            'items', 'subtotal', 'shippingCharge', 'isFreeShipping', 'taxAmount', 'addresses', 'promoCoupons', 'razorpayConfigured',
            'couponDiscount', 'appliedCouponCode', 'validateCouponPath', 'removeCouponPath'
        ));
    }

    public function validateCoupon(Request $request, CartService $cart, CouponService $coupons, ShippingService $shipping)
    {
        $request->validate(['coupon_code' => 'required|string|max:64']);

        $items = $cart->query()->get();
        if ($items->isEmpty()) {
            return response()->json(['message' => __('Your cart is empty.')], 422);
        }

        $items->loadMissing('variant.product');
        $pricing = app(CheckoutPricingService::class);
        $subtotalExclusive = $pricing->subtotal($items);
        $taxAmount = $pricing->taxAmount($items);
        $subtotal = $pricing->inclusiveSubtotal($items);
        $shippingCharge = $shipping->chargeForSubtotal($subtotal);

        try {
            $resolved = $coupons->resolveForCart($request->input('coupon_code'), $subtotal);
        } catch (ValidationException $e) {
            session()->forget('checkout_coupon_code');

            return response()->json([
                'message' => collect($e->errors())->flatten()->first() ?? __('Invalid coupon.'),
            ], 422);
        }

        $discount = $resolved['discount'];
        $coupon = $resolved['coupon'];
        $total = $pricing->grandTotal($subtotalExclusive, $shippingCharge, $taxAmount, $discount);

        session(['checkout_coupon_code' => $coupon->code]);

        return response()->json([
            'code' => $coupon->code,
            'discount' => $discount,
            'discount_formatted' => '₹'.number_format($discount, 2),
            'subtotal' => $subtotal,
            'shipping_charge' => $shippingCharge,
            'tax' => $taxAmount,
            'tax_formatted' => '₹'.number_format($taxAmount, 2),
            'total' => $total,
            'total_formatted' => '₹'.number_format($total, 2),
            'is_free_shipping' => $shippingCharge <= 0,
            'shipping_label' => $shippingCharge <= 0 ? 'FREE' : '₹'.number_format($shippingCharge, 2),
            'message' => $coupon->type === 'percent'
                ? __(':code applied — :value% off', ['code' => $coupon->code, 'value' => rtrim(rtrim(number_format((float) $coupon->value, 2), '0'), '.')])
                : __(':code applied — ₹:value off', ['code' => $coupon->code, 'value' => number_format((float) $coupon->value, 2)]),
        ]);
    }

    public function removeCoupon(CartService $cart, ShippingService $shipping)
    {
        session()->forget('checkout_coupon_code');

        $items = $cart->query()->get();
        if ($items->isEmpty()) {
            return response()->json(['message' => __('Your cart is empty.')], 422);
        }

        $items->loadMissing('variant.product');
        $pricing = app(CheckoutPricingService::class);
        $subtotalExclusive = $pricing->subtotal($items);
        $taxAmount = $pricing->taxAmount($items);
        $subtotal = $pricing->inclusiveSubtotal($items);
        $shippingCharge = $shipping->chargeForSubtotal($subtotal);
        $total = $pricing->grandTotal($subtotalExclusive, $shippingCharge, $taxAmount, 0);

        return response()->json([
            'message' => __('Coupon removed.'),
            'discount' => 0,
            'subtotal' => $subtotal,
            'shipping_charge' => $shippingCharge,
            'tax' => $taxAmount,
            'tax_formatted' => '₹'.number_format($taxAmount, 2),
            'total' => $total,
            'total_formatted' => '₹'.number_format($total, 2),
            'is_free_shipping' => $shippingCharge <= 0,
            'shipping_label' => $shippingCharge <= 0 ? 'FREE' : '₹'.number_format($shippingCharge, 2),
        ]);
    }

    public function store(Request $request, CartService $cart, CouponService $coupons, ShippingService $shipping)
    {
        if ($request->input('address_id') === '' || $request->input('address_id') === null) {
            $request->merge(['address_id' => null]);
        }

        $request->validate([
            'address_id' => [
                'nullable',
                Rule::exists('addresses', 'id')->where(fn ($q) => $q->where('user_id', $request->user()->id)),
            ],
            'name' => ['nullable', 'string', 'max:255', Rule::requiredIf(fn () => ! $request->filled('address_id'))],
            'phone' => ['nullable', 'string', 'max:32', Rule::requiredIf(fn () => ! $request->filled('address_id'))],
            'line1' => ['nullable', 'string', 'max:255', Rule::requiredIf(fn () => ! $request->filled('address_id'))],
            'line2' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:120', Rule::requiredIf(fn () => ! $request->filled('address_id'))],
            'state' => ['nullable', 'string', 'max:120', Rule::requiredIf(fn () => ! $request->filled('address_id'))],
            'pincode' => ['nullable', 'string', 'max:16', Rule::requiredIf(fn () => ! $request->filled('address_id'))],
            'payment_method' => 'required|in:razorpay',
            'coupon_code' => 'nullable|string|max:64',
        ]);

        $items = $cart->query()->get();
        if ($items->isEmpty()) {
            return redirect()->route('cart.index')->with('error', __('Your cart is empty.'));
        }

        /** @var User $user */
        $user = $request->user();

        $items->loadMissing('variant.product');
        foreach ($items as $line) {
            $variant = $line->variant;
            if (! $variant->isActiveStatus()) {
                return back()->with('error', __('An item in your cart is no longer available: :product.', ['product' => $variant->product->name]));
            }
            if ($variant->stock_qty < $line->qty) {
                return back()->with('error', __('Insufficient stock for :product.', ['product' => $variant->product->name]));
            }
        }

        $items->loadMissing('variant.product');
        $pricing = app(CheckoutPricingService::class);
        $subtotalExclusive = $pricing->subtotal($items);
        $taxAmount = $pricing->taxAmount($items);
        $subtotal = $pricing->inclusiveSubtotal($items);
        $shipping = $shipping->chargeForSubtotal($subtotal);
        $discount = 0.0;
        $couponCode = null;

        if ($coupons->normalizeCode($request->input('coupon_code')) !== '') {
            try {
                $resolved = $coupons->resolveForCart($request->input('coupon_code'), $subtotal);
                $discount = $resolved['discount'];
                $couponCode = $resolved['coupon']->code;
            } catch (ValidationException $e) {
                return back()->withInput()->withErrors($e->errors());
            }
        }

        $grand = $pricing->grandTotal($subtotalExclusive, $shipping, $taxAmount, $discount);
        $walletUse = 0.0;
        $payable = round($grand - $walletUse, 2);

        $addressId = $request->address_id;
        if (! $addressId) {
            $addr = Address::create([
                'user_id' => $user->id,
                'label' => 'Shipping',
                'name' => $request->name,
                'phone' => $request->phone,
                'line1' => $request->line1,
                'line2' => $request->line2,
                'city' => $request->city,
                'state' => $request->state,
                'pincode' => $request->pincode,
                'is_default' => $user->addresses()->count() === 0,
            ]);
            $addressId = $addr->id;
        } else {
            abort_unless($user->addresses()->where('id', $addressId)->exists(), 403);
        }

        $defaultCommission = (float) Setting::getValue('default_commission_percent', '12');
        $countCouponNow = $couponCode && ($payable <= 0);

        $order = null;
        $ledger = app(StockLedgerService::class);

        DB::transaction(function () use (
            $items, $user, $addressId, $request, $subtotalExclusive, $shipping, $taxAmount, $discount, $grand,
            $walletUse, $payable, $couponCode, $defaultCommission, &$order, $ledger
        ) {
            $adminCommission = 0.0;

            $order = Order::create([
                'order_number' => Order::generateOrderNumber(),
                'user_id' => $user->id,
                'address_id' => $addressId,
                'status' => 'pending',
                'payment_method' => $request->payment_method,
                'payment_status' => $payable <= 0 ? 'paid' : 'unpaid',
                'subtotal' => $subtotalExclusive,
                'shipping' => $shipping,
                'discount' => $discount,
                'tax_amount' => $taxAmount,
                'total' => $grand,
                'admin_commission' => 0,
                'wallet_used' => $walletUse,
                'coupon_code' => $couponCode,
                'customer_name' => $request->name ?: optional($user->addresses()->find($addressId))->name ?: $user->name,
                'customer_phone' => $request->phone ?: optional($user->addresses()->find($addressId))->phone ?: $user->phone,
                'customer_email' => $user->email,
            ]);

            $selectedAddress = $user->addresses()->find($addressId);
            $shippingAddress = ShippingAddress::create([
                'order_id' => $order->id,
                'name' => $request->name ?: optional($selectedAddress)->name ?: $user->name,
                'email' => $user->email,
                'phone' => $request->phone ?: optional($selectedAddress)->phone ?: $user->phone,
                'line1' => $request->line1 ?: optional($selectedAddress)->line1 ?: '',
                'line2' => $request->line2 ?: optional($selectedAddress)->line2,
                'city' => $request->city ?: optional($selectedAddress)->city ?: '',
                'state' => $request->state ?: optional($selectedAddress)->state ?: '',
                'pincode' => $request->pincode ?: optional($selectedAddress)->pincode ?: '',
            ]);
            $order->shipping_address_id = $shippingAddress->id;
            $order->save();

            foreach ($items as $line) {
                $variant = $line->variant;
                $variant->loadMissing('product.vendor');
                $product = $variant->product;
                $vendor = $product->vendor;
                $unit = $variant->effectivePrice();
                $lineTotal = round($unit * $line->qty, 2);
                $pct = $vendor->commission_percent ?? $defaultCommission;
                $comm = round($lineTotal * ($pct / 100), 2);
                $adminCommission += $comm;

                OrderItem::create([
                    'order_id' => $order->id,
                    'vendor_id' => $vendor->id,
                    'product_variant_id' => $variant->id,
                    'product_name' => $product->name,
                    'variant_label' => $variant->label(),
                    'sku' => $variant->sku,
                    'weight_kg' => $product->weight_kg,
                    'product_image' => $variant->variantImageUrl(),
                    'unit_price' => $unit,
                    'qty' => $line->qty,
                    'line_total' => $lineTotal,
                    'commission_amount' => $comm,
                ]);

                $ledger->deductForOrderLine($variant, (int) $line->qty, $order);
                $product->increment('sales_count', $line->qty);
            }

            $order->update(['admin_commission' => $adminCommission]);
            CartItem::whereIn('id', $items->pluck('id'))->delete();
        });

        if ($countCouponNow) {
            $coupons->markUsed($couponCode);
        }

        GoogleAnalyticsService::flashPlaceOrder($order);

        if ($payable <= 0) {
            GoogleAnalyticsService::flashPurchase($order);
            app(OrderConfirmationMailService::class)->dispatchForOrder($order->fresh());

            return redirect()->route('orders.show', $order)->with('status', __('Order placed successfully.'));
        }

        return redirect()->route('pay.razorpay', $order);
    }
}
