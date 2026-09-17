<?php

namespace App\Http\Controllers;

use App\Models\Wishlist;
use Illuminate\Http\Request;

class WishlistController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $items = Wishlist::with(['product.images', 'product.vendor', 'product.variants', 'product.flashSale', 'product.menuItem'])
            ->where('user_id', auth()->id())->latest()->get();

        return view('market.wishlist', compact('items'));
    }

    public function store(Request $request)
    {
        $request->validate(['product_id' => 'required|exists:products,id']);

        $existing = Wishlist::where('user_id', auth()->id())
            ->where('product_id', $request->product_id)
            ->first();

        if ($existing) {
            $existing->delete();
            $wishlisted = false;
            $message = __('Successfully removed from wishlist');
        } else {
            Wishlist::create([
                'user_id' => auth()->id(),
                'product_id' => $request->product_id,
            ]);
            $wishlisted = true;
            $message = __('Successfully added to wishlist');
        }

        if ($request->expectsJson()) {
            return response()->json([
                'ok' => true,
                'wishlisted' => $wishlisted,
                'count' => Wishlist::where('user_id', auth()->id())->count(),
                'product_id' => (int) $request->product_id,
                'message' => $message,
            ]);
        }

        return back()->with('status', $message);
    }

    public function destroy(Wishlist $wishlist)
    {
        abort_unless($wishlist->user_id === auth()->id(), 403);
        $wishlist->delete();

        return back()->with('status', 'Removed');
    }
}
