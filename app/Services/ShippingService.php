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
     * Parse weight string to kilograms (e.g. "500gm", "1kg", "250g", "1.5 kg").
     */
    public function parseWeightStringToKg(?string $str): ?float
    {
        if ($str === null || trim($str) === '') {
            return null;
        }

        $clean = strtolower(trim($str));

        // Matches grams: "500gm", "500 gm", "500g", "500 g", "500grams", "500 grams"
        if (preg_match('/^([0-9]+(?:\.[0-9]+)?)\s*(?:gm|gms|g|gram|grams)$/i', $clean, $matches)) {
            return round(((float) $matches[1]) / 1000.0, 4);
        }

        // Matches kilograms: "1kg", "1 kg", "1.5kg", "1.5 kgs", "1 kilo", "1 kilogram"
        if (preg_match('/^([0-9]+(?:\.[0-9]+)?)\s*(?:kg|kgs|kilo|kilos|kilogram|kilograms)$/i', $clean, $matches)) {
            return round((float) $matches[1], 4);
        }

        return null;
    }

    /**
     * Get normalized weight in kilograms for an item.
     */
    public function itemWeightKg($item): float
    {
        $variant = optional($item)->variant;
        $product = optional($variant)->product;

        if ($variant && ! empty($variant->size)) {
            $parsed = $this->parseWeightStringToKg((string) $variant->size);
            if ($parsed !== null && $parsed > 0) {
                return $parsed;
            }
        }

        $kg = $product ? (float) ($product->weight_kg ?? 0) : 0.0;

        return max(0.0, $kg);
    }

    /**
     * Total cart weight normalized to kilograms.
     *
     * @param  Collection<int, \App\Models\CartItem>  $items
     */
    public function cartWeightKg(Collection $items): float
    {
        $kg = 0.0;

        foreach ($items as $item) {
            $itemKg = $this->itemWeightKg($item);
            $qty = max(0, (int) ($item->qty ?? 0));
            $kg += $itemKg * $qty;
        }

        return round($kg, 3);
    }

    /**
     * Total cart weight in grams.
     *
     * @param  Collection<int, \App\Models\CartItem>  $items
     */
    public function cartWeightGrams(Collection $items): float
    {
        return round($this->cartWeightKg($items) * 1000, 2);
    }

    /**
     * Determine delivery charge discount percentage based on total cart weight.
     *
     * Discount Rules:
     * - Weight <= 1 kg: 0%
     * - 1 kg < Weight <= 2 kg: 20%
     * - 2 kg < Weight <= 3 kg: 30%
     * - 3 kg < Weight <= 4 kg: 40%
     * - Weight > 4 kg: 50%
     */
    public function deliveryDiscountPercent(float $weightKg): int
    {
        $weight = max(0.0, round($weightKg, 3));

        if ($weight <= 1.0) {
            return 0;
        }

        if ($weight <= 2.0) {
            return 20;
        }

        if ($weight <= 3.0) {
            return 30;
        }

        if ($weight <= 4.0) {
            return 40;
        }

        return 50;
    }

    /**
     * Calculate weight-based delivery discount breakdown.
     *
     * @return array{
     *     weight_kg: float,
     *     weight_grams: float,
     *     original_shipping: float,
     *     discount_percent: int,
     *     discount_amount: float,
     *     final_shipping: float
     * }
     */
    public function calculateWeightDiscount(float $weightKg, float $originalCharge): array
    {
        $original = max(0.0, (float) round($originalCharge));
        $weightKg = max(0.0, round($weightKg, 3));
        $weightGrams = round($weightKg * 1000, 2);

        $percent = $this->deliveryDiscountPercent($weightKg);

        if ($original <= 0.0) {
            return [
                'weight_kg' => $weightKg,
                'weight_grams' => $weightGrams,
                'original_shipping' => 0.0,
                'discount_percent' => $percent,
                'discount_amount' => 0.0,
                'final_shipping' => 0.0,
            ];
        }

        $rawDiscount = $original * ($percent / 100);
        $rawFinal = max(0.0, $original - $rawDiscount);
        $finalShipping = max(0.0, (float) round($rawFinal));
        $discountAmount = max(0.0, round($original - $finalShipping, 2));

        return [
            'weight_kg' => $weightKg,
            'weight_grams' => $weightGrams,
            'original_shipping' => $original,
            'discount_percent' => $percent,
            'discount_amount' => $discountAmount,
            'final_shipping' => $finalShipping,
        ];
    }

    /**
     * Calculate original delivery charge before any weight discount.
     *
     * @param  Collection<int, \App\Models\CartItem>  $items
     */
    public function originalChargeForCart(Collection $items, float $subtotal, ?string $pincode = null): float
    {
        $settings = $this->settings();
        $freeThreshold = (float) ($settings->free_shipping_amount ?? 0);

        if ($freeThreshold > 0 && $subtotal >= $freeThreshold) {
            return 0.0;
        }

        $rates = $this->ratesForPincode($pincode, $settings);
        $weightGrams = $this->cartWeightGrams($items);
        $baseGrams = max(1, (int) ($settings->base_weight_grams ?: DeliveryChargeCalculator::DEFAULT_BASE_WEIGHT_GRAMS));

        $raw = $this->calculator->charge(
            $weightGrams,
            $rates['base'],
            $rates['additional'],
            $baseGrams
        );

        return (float) round($raw);
    }

    /**
     * Get complete delivery calculation breakdown for cart.
     *
     * @param  Collection<int, \App\Models\CartItem>  $items
     * @return array{
     *     weight_kg: float,
     *     weight_grams: float,
     *     original_shipping: float,
     *     discount_percent: int,
     *     discount_amount: float,
     *     final_shipping: float,
     *     is_free_shipping: bool,
     *     shipping_label: string
     * }
     */
    public function deliveryCalculationForCart(Collection $items, float $subtotal, ?string $pincode = null): array
    {
        $weightKg = $this->cartWeightKg($items);
        $originalCharge = $this->originalChargeForCart($items, $subtotal, $pincode);
        $discount = $this->calculateWeightDiscount($weightKg, $originalCharge);

        $isFree = $discount['final_shipping'] <= 0;
        $label = $isFree ? 'FREE' : '₹'.number_format($discount['final_shipping'], 2);

        return $discount + [
            'is_free_shipping' => $isFree,
            'shipping_label' => $label,
        ];
    }

    /**
     * Calculate final delivery charge for cart (with weight discount applied).
     *
     * @param  Collection<int, \App\Models\CartItem>  $items
     */
    public function chargeForCart(Collection $items, float $subtotal, ?string $pincode = null): float
    {
        $breakdown = $this->deliveryCalculationForCart($items, $subtotal, $pincode);

        return $breakdown['final_shipping'];
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
     * @return array{
     *     subtotal: float,
     *     shipping_charge: float,
     *     total: float,
     *     shipping_original: float,
     *     shipping_discount_percent: int,
     *     shipping_discount_amount: float,
     *     weight_kg: float,
     *     weight_grams: float,
     *     is_free_shipping: bool,
     *     shipping_label: string,
     *     shipping_breakdown: array<string, mixed>
     * }
     */
    public function totalsForSubtotal(float $subtotal, ?string $pincode = null, ?Collection $items = null): array
    {
        $items = $items ?? collect();
        $subtotal = round($subtotal, 2);
        $breakdown = $this->deliveryCalculationForCart($items, $subtotal, $pincode);
        $finalShipping = $breakdown['final_shipping'];

        return [
            'subtotal' => $subtotal,
            'shipping_charge' => $finalShipping,
            'total' => round($subtotal + $finalShipping, 2),
            'shipping_original' => $breakdown['original_shipping'],
            'shipping_discount_percent' => $breakdown['discount_percent'],
            'shipping_discount_amount' => $breakdown['discount_amount'],
            'weight_kg' => $breakdown['weight_kg'],
            'weight_grams' => $breakdown['weight_grams'],
            'is_free_shipping' => $breakdown['is_free_shipping'],
            'shipping_label' => $breakdown['shipping_label'],
            'shipping_breakdown' => $breakdown,
        ];
    }

    /**
     * @param  Collection<int, \App\Models\CartItem>  $items
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

        return [
            'subtotal' => $totals['subtotal'],
            'shipping_charge' => $totals['shipping_charge'],
            'total' => $totals['total'],
            'is_free_shipping' => $totals['is_free_shipping'],
            'shipping_label' => $totals['shipping_label'],
            'weight_grams' => $totals['weight_grams'],
            'weight_kg' => $totals['weight_kg'],
            'shipping_original' => $totals['shipping_original'],
            'shipping_discount_percent' => $totals['shipping_discount_percent'],
            'shipping_discount_amount' => $totals['shipping_discount_amount'],
        ];
    }
}
