<?php

namespace App\Http\Controllers;

use App\Models\Product;
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
        Wishlist::firstOrCreate([
            'user_id' => auth()->id(),
            'product_id' => $request->product_id,
        ]);

        return back()->with('status', 'Saved to wishlist');
    }

    public function destroy(Wishlist $wishlist)
    {
        abort_unless($wishlist->user_id === auth()->id(), 403);
        $wishlist->delete();

        return back()->with('status', 'Removed');
    }
}
