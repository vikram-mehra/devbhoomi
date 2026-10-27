<?php

namespace App\Services;

class DeliveryChargeCalculator
{
    public const DEFAULT_BASE_WEIGHT_GRAMS = 500;

    public function slabs(float $weightGrams, int $baseWeightGrams = self::DEFAULT_BASE_WEIGHT_GRAMS): int
    {
        $base = max(1, $baseWeightGrams);
        $weight = max(0.0, $weightGrams);

        return max(1, (int) ceil($weight / $base));
    }

    public function charge(
        float $weightGrams,
        float $baseCharge,
        ?float $additionalCharge = null,
        int $baseWeightGrams = self::DEFAULT_BASE_WEIGHT_GRAMS
    ): float {
        $slabs = $this->slabs($weightGrams, $baseWeightGrams);
        $first = max(0.0, $baseCharge);
        $extra = max(0.0, $additionalCharge ?? $baseCharge);

        return round($first + (($slabs - 1) * $extra), 2);
    }
}
