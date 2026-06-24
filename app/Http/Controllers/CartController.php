<?php

namespace App\Http\Controllers;

use App\Models\CartItem;
use App\Models\ProductVariant;
use App\Services\CartService;
use App\Services\CheckoutPricingService;
use App\Services\ShippingService;
use App\Services\GoogleAnalyticsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function index(CartService $cart, CheckoutPricingService $pricing, ShippingService $shipping)
    {
        $items = $cart->query()->get();
        $summary = $shipping->totalsForCart($items, $pricing);

        return view('market.cart', compact('items') + $summary);
    }

    public function summary(CartService $cart, CheckoutPricingService $pricing, ShippingService $shipping): JsonResponse
    {
        $items = $cart->query()->get();

        return response()->json($shipping->apiResponse($pricing->inclusiveSubtotal($items)));
    }

    public function add(
        Request $request,
        CartService $cart,
        CheckoutPricingService $pricing,
        ShippingService $shipping
    ) {
        $request->validate([
            'product_variant_id' => 'required|exists:product_variants,id',
            'qty' => 'integer|min:1|max:99',
            'buy_now' => 'nullable|boolean',
        ]);
        $qty = (int) ($request->qty ?? 1);
        $variant = ProductVariant::query()->findOrFail((int) $request->product_variant_id);
        if (! $variant->isBuyable()) {
            if ($request->wantsJson()) {
                return response()->json(['error' => __('This option is unavailable or out of stock.')], 422);
            }
            return back()->with('error', __('This option is unavailable or out of stock.'));
        }
        if ($qty > (int) $variant->stock_qty) {
            if ($request->wantsJson()) {
                return response()->json(['error' => __('Only :qty in stock for this option.', ['qty' => $variant->stock_qty])], 422);
            }
            return back()->with('error', __('Only :qty in stock for this option.', ['qty' => $variant->stock_qty]));
        }
        $cart->add((int) $request->product_variant_id, $qty);
        GoogleAnalyticsService::flashAddToCart($variant, $qty, $request->boolean('buy_now'));

        if ($request->boolean('buy_now')) {
            if (auth()->check()) {
                return redirect()->route('checkout.index');
            }

            return redirect()->guest(route('login'));
        }

        if ($request->wantsJson()) {
            $items = $cart->query()->get();
            $cartItem = $items->firstWhere('product_variant_id', (int) $request->product_variant_id);
            $html = view('market.partials.cart-drawer-items', [
                'layoutCartItems' => $items,
            ])->render();

            return response()->json([
                'success' => true,
                'cart_item_id' => $cartItem?->id,
                'qty' => $cartItem?->qty ?? $qty,
                'html' => $html,
                'totals' => $shipping->apiResponse($pricing->inclusiveSubtotal($items)),
                'items' => $items->map(fn ($it) => [
                    'id' => $it->id,
                    'product_variant_id' => $it->product_variant_id,
                    'qty' => $it->qty
                ])
            ]);
        }

        return back()->with('mk_cart_toast', true);
    }

    public function update(
        Request $request,
        CartItem $item,
        CartService $cart,
        CheckoutPricingService $pricing,
        ShippingService $shipping
    ) {
        $this->authorizeItem($item, $cart);
        $request->validate(['qty' => 'required|integer|min:1|max:99']);
        $item->update(['qty' => $request->qty]);

        if ($request->wantsJson()) {
            $items = $cart->query()->get();
            $html = view('market.partials.cart-drawer-items', [
                'layoutCartItems' => $items,
            ])->render();

            return response()->json([
                'success' => true,
                'html' => $html,
                'totals' => $shipping->apiResponse($pricing->inclusiveSubtotal($items)),
                'items' => $items->map(fn ($it) => [
                    'id' => $it->id,
                    'product_variant_id' => $it->product_variant_id,
                    'qty' => $it->qty
                ])
            ]);
        }

        return back()->with('status', 'Cart updated');
    }

    public function destroy(
        Request $request,
        CartItem $item,
        CartService $cart,
        CheckoutPricingService $pricing,
        ShippingService $shipping
    ) {
        $this->authorizeItem($item, $cart);
        $item->delete();

        if ($request->wantsJson()) {
            $items = $cart->query()->get();
            $html = view('market.partials.cart-drawer-items', [
                'layoutCartItems' => $items,
            ])->render();

            return response()->json([
                'success' => true,
                'html' => $html,
                'totals' => $shipping->apiResponse($pricing->inclusiveSubtotal($items)),
                'items' => $items->map(fn ($it) => [
                    'id' => $it->id,
                    'product_variant_id' => $it->product_variant_id,
                    'qty' => $it->qty
                ])
            ]);
        }

        return back()->with('status', 'Removed');
    }

    protected function authorizeItem(CartItem $item, CartService $cart): void
    {
         
        if (auth()->check()) {
            abort_unless((int) $item->user_id === (int) auth()->id(), 403);
        } else {
            abort_unless($item->session_id === $cart->sessionKey(), 403);
        }
    }
}
