<?php

namespace App\Services;

use App\Models\PincodeServiceability;
use App\Models\ShippingSetting;
use Illuminate\Support\Collection;

class ShippingService
{
    public function __construct(protected DeliveryChargeCalculator $calculator)
    {
    }

    public function settings(): ShippingSetting
    {
        return ShippingSetting::current();
    }

    public function chargeForSubtotal(float $subtotal, ?string $pincode = null, ?Collection $items = null): float
    {
        return $this->chargeForCart($items ?? collect(), $subtotal, $pincode);
    }

    /**
     * @param  Collection<int, \App\Models\CartItem>  $items
     */
    public function chargeForCart(Collection $items, float $subtotal, ?string $pincode = null): float
    {
        $settings = $this->settings();

        if ($subtotal >= (float) $settings->free_shipping_amount) {
            return 0.0;
        }

        $rates = $this->ratesForPincode($pincode, $settings);
        $weightGrams = $this->cartWeightGrams($items);
        $baseGrams = max(1, (int) ($settings->base_weight_grams ?: DeliveryChargeCalculator::DEFAULT_BASE_WEIGHT_GRAMS));

        return $this->calculator->charge(
            $weightGrams,
            $rates['base'],
            $rates['additional'],
            $baseGrams
        );
    }

    /**
     * @param  Collection<int, \App\Models\CartItem>  $items
     */
    public function cartWeightGrams(Collection $items): float
    {
        $grams = 0.0;

        foreach ($items as $item) {
            $product = optional(optional($item)->variant)->product;
            $kg = $product ? (float) ($product->weight_kg ?? 0) : 0.0;
            $grams += max(0.0, $kg) * 1000 * max(0, (int) ($item->qty ?? 0));
        }

        return $grams;
    }

    /**
     * @return array{base: float, additional: float, source: string}
     */
    public function ratesForPincode(?string $pincode, ?ShippingSetting $settings = null): array
    {
        $settings = $settings ?: $this->settings();
        $fallback = max(0.0, (float) $settings->shipping_charge);
        $fallbackExtra = $settings->additional_500g_charge !== null
            ? max(0.0, (float) $settings->additional_500g_charge)
            : $fallback;

        $pin = PincodeServiceability::normalizePincode($pincode);
        if (strlen($pin) === 6) {
            $row = PincodeServiceability::query()->where('pincode', $pin)->first();
            if ($row && $row->delivery_charge !== null) {
                $base = max(0.0, (float) $row->delivery_charge);
                $extra = $row->additional_500g_charge !== null
                    ? max(0.0, (float) $row->additional_500g_charge)
                    : $base;

                return [
                    'base' => $base,
                    'additional' => $extra,
                    'source' => 'pincode',
                ];
            }
        }

        return [
            'base' => $fallback,
            'additional' => $fallbackExtra,
            'source' => 'fallback',
        ];
    }

    /**
     * @return array{subtotal: float, shipping_charge: float, total: float}
     */
    public function totalsForSubtotal(float $subtotal, ?string $pincode = null, ?Collection $items = null): array
    {
        $subtotal = round($subtotal, 2);
        $shippingCharge = $this->chargeForSubtotal($subtotal, $pincode, $items);

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
    public function totalsForCart(Collection $items, CheckoutPricingService $pricing, ?string $pincode = null): array
    {
        return $this->totalsForSubtotal($pricing->inclusiveSubtotal($items), $pincode, $items);
    }

    public function isFreeShipping(float $subtotal, ?string $pincode = null, ?Collection $items = null): bool
    {
        return $this->chargeForSubtotal($subtotal, $pincode, $items) <= 0;
    }

    /**
     * @param  Collection<int, \App\Models\CartItem>|null  $items
     * @return array<string, mixed>
     */
    public function apiResponse(float $subtotal, ?string $pincode = null, ?Collection $items = null): array
    {
        $totals = $this->totalsForSubtotal($subtotal, $pincode, $items);

        return $totals + [
            'is_free_shipping' => $totals['shipping_charge'] <= 0,
            'shipping_label' => $totals['shipping_charge'] <= 0 ? 'FREE' : '₹'.number_format($totals['shipping_charge'], 2),
            'weight_grams' => $items ? $this->cartWeightGrams($items) : 0.0,
        ];
    }
}
