<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InventoryVariantSearchController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $q = trim((string) $request->get('q', ''));
        $barcode = trim((string) $request->get('barcode', ''));

        $query = ProductVariant::query()
            ->with(['product:id,name,sku,barcode,base_price'])
            ->where('status', ProductVariant::STATUS_ACTIVE);

        if ($barcode !== '') {
            $query->whereHas('product', fn ($pq) => $pq->where('barcode', $barcode));
        } elseif ($q !== '') {
            $term = '%'.str_replace(['%', '_'], ['\\%', '\\_'], $q).'%';
            $query->where(function ($vq) use ($term) {
                $vq->where('sku', 'like', $term)
                    ->orWhereHas('product', function ($pq) use ($term) {
                        $pq->where('name', 'like', $term)
                            ->orWhere('sku', 'like', $term)
                            ->orWhere('barcode', 'like', $term);
                    });
            });
        } else {
            return response()->json(['data' => []]);
        }

        $variants = $query->orderBy('sku')->limit(20)->get();

        return response()->json([
            'data' => $variants->map(function (ProductVariant $variant) {
                $product = $variant->product;

                return [
                    'id' => $variant->id,
                    'product_id' => $variant->product_id,
                    'sku' => $variant->sku,
                    'label' => trim($product->name.' — '.$variant->sku),
                    'stock_qty' => (int) $variant->stock_qty,
                    'unit_price' => (float) $variant->unitPrice(),
                    'barcode' => $product->barcode ?? null,
                    'product_name' => $product->name,
                    'product_sku' => $product->sku,
                ];
            }),
        ]);
    }
}
