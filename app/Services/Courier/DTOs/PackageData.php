<?php

namespace App\Services\Courier\DTOs;

class PackageData
{
    public float $weight;
    public float $length;
    public float $width;
    public float $height;
    public int $quantity;

    public function __construct(float $weight, float $length, float $width, float $height, int $quantity = 1)
    {
        $this->weight = $weight;
        $this->length = $length;
        $this->width = $width;
        $this->height = $height;
        $this->quantity = max(1, $quantity);
    }

    public function weightGrams(): int
    {
        return max(1, (int) round($this->weight * 1000));
    }

    /**
     * @return array<string, float|int>
     */
    public function toArray(): array
    {
        return [
            'weight' => $this->weight,
            'length' => $this->length,
            'width' => $this->width,
            'height' => $this->height,
            'quantity' => $this->quantity,
        ];
    }
}
