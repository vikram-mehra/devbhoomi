<?php

namespace App\Services;

use App\Models\ShippingSetting;
use Illuminate\Support\Collection;

class ShippingService
{
    public function settings(): ShippingSetting
    {
        return ShippingSetting::current();
    }

    public function chargeForSubtotal(float $subtotal): float
    {
        $settings = $this->settings();

        if ($subtotal >= (float) $settings->free_shipping_amount) {
            return 0.0;
        }

        return round((float) $settings->shipping_charge, 2);
    }

    /**
     * @return array{subtotal: float, shipping_charge: float, total: float}
     */
    public function totalsForSubtotal(float $subtotal): array
    {
        $subtotal = round($subtotal, 2);
        $shippingCharge = $this->chargeForSubtotal($subtotal);

        return [
            'subtotal' => $subtotal,
            'shipping_charge' => $shippingCharge,
            'total' => round($subtotal + $shippingCharge, 2),
        ];
    }

    /**
     * @param  Collection<int, \App\Models\CartItem>  $items
     * @return array{subtotal: float, shipping_charge: float, total: float}
     */
    public function totalsForCart(Collection $items, CheckoutPricingService $pricing): array
    {
        return $this->totalsForSubtotal($pricing->subtotal($items));
    }

    public function isFreeShipping(float $subtotal): bool
    {
        return $this->chargeForSubtotal($subtotal) <= 0;
    }

    /**
     * @return array<string, mixed>
     */
    public function apiResponse(float $subtotal): array
    {
        $totals = $this->totalsForSubtotal($subtotal);

        return $totals + [
            'is_free_shipping' => $totals['shipping_charge'] <= 0,
            'shipping_label' => $totals['shipping_charge'] <= 0 ? 'FREE' : '₹'.number_format($totals['shipping_charge'], 2),
        ];
    }
}
