<?php

namespace App\Services;

use Illuminate\Support\Collection;

class CheckoutPricingService
{
    /**
     * @param  \Illuminate\Support\Collection<int, \App\Models\CartItem>  $items
     */
    public function subtotal(Collection $items): float
    {
        return round($items->sum(fn ($item) => $item->variant->effectivePrice() * $item->qty), 2);
    }

    /**
     * GST from each product's gst % applied on line subtotal.
     *
     * @param  \Illuminate\Support\Collection<int, \App\Models\CartItem>  $items
     */
    public function taxAmount(Collection $items): float
    {
        $tax = 0.0;
        foreach ($items as $item) {
            $item->variant->loadMissing('product');
            $gstPercent = (float) ($item->variant->product->gst ?? 0);
            if ($gstPercent <= 0) {
                continue;
            }
            $line = $item->variant->effectivePrice() * $item->qty;
            $tax += $line * ($gstPercent / 100);
        }

        return round($tax, 2);
    }

    public function grandTotal(float $subtotal, float $shipping, float $tax, float $discount): float
    {
        return round(max(0, $subtotal + $shipping + $tax - $discount), 2);
    }
}
