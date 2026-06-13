<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReviewController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function store(Request $request, Product $product)
    {
        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'title' => 'nullable|string|max:120',
            'body' => 'nullable|string|max:2000',
        ]);

        if (Review::where('product_id', $product->id)->where('user_id', auth()->id())->exists()) {
            return back()->with('error', 'You already reviewed this product.');
        }

        DB::transaction(function () use ($request, $product) {
            Review::create([
                'product_id' => $product->id,
                'user_id' => auth()->id(),
                'order_id' => $request->order_id,
                'rating' => $request->rating,
                'title' => $request->title,
                'body' => $request->body,
                'is_approved' => true,
            ]);

            $avg = Review::where('product_id', $product->id)->where('is_approved', true)->avg('rating') ?? 0;
            $cnt = Review::where('product_id', $product->id)->where('is_approved', true)->count();
            $product->update([
                'rating_avg' => round((float) $avg, 2),
                'rating_count' => $cnt,
            ]);
        });

        return back()->with('status', 'Thanks for your review!');
    }
}
