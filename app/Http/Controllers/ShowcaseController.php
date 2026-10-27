<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Contracts\View\View;

class ShowcaseController extends Controller
{
    public function show(string $slug): View
    {
        $product = Product::query()
            ->storefront()
            ->with([
                'images',
                'menuItem',
                'variants' => function ($query) {
                    $query->where('status', ProductVariant::STATUS_ACTIVE)
                        ->orderBy('id')
                        ->with('galleryImages');
                },
                'flashSale',
            ])
            ->where('slug', $slug)
            ->firstOrFail();

        $galleryImages = $product->images
            ->map(function ($image) {
                return Product::publicImageUrl($image->path);
            })
            ->filter()
            ->values();

        foreach ($product->variants as $variant) {
            $variantImage = $variant->variantImageUrl();
            if ($variantImage) {
                $galleryImages->push($variantImage);
            }
        }

        $galleryImages = $galleryImages->unique()->values();
        if ($galleryImages->isEmpty()) {
            $galleryImages->push($product->namedPlaceholderUrl());
        }

        $otherProducts = Product::query()
            ->storefront()
            ->whereKeyNot($product->getKey())
            ->with('images')
            ->orderBy('name')
            ->paginate(8, ['*'], 'other_page')
            ->withQueryString();

        return view('market.showcase', compact('product', 'galleryImages', 'otherProducts'));
    }
}
