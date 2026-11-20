<?php

namespace App\Services\Courier\DTOs;

class BulkShipmentResult
{
    public bool $success;
    public bool $queued;
    public int $total;
    public int $successful;
    public int $failed;
    public string $courier;
    /** @var list<ShipmentResult> */
    public array $results;

    /**
     * @param  list<ShipmentResult>  $results
     */
    public function __construct(string $courier, array $results, bool $queued = false)
    {
        $this->courier = $courier;
        $this->results = $results;
        $this->queued = $queued;
        $this->total = count($results);
        $this->successful = count(array_filter($results, fn (ShipmentResult $r) => $r->success));
        $this->failed = $this->total - $this->successful;
        $this->success = $this->failed === 0;
    }

    /**
     * @param  list<int>  $orderIds
     */
    public static function queued(string $courier, array $orderIds): self
    {
        $results = array_map(function ($id) {
            return new ShipmentResult(true, (int) $id, null, null, null, null, null, 'queued', 'Queued for processing');
        }, $orderIds);

        $instance = new self($courier, $results, true);
        $instance->successful = 0;
        $instance->failed = 0;

        return $instance;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'success' => $this->success,
            'queued' => $this->queued,
            'courier' => $this->courier,
            'total' => $this->total,
            'successful' => $this->successful,
            'failed' => $this->failed,
            'results' => array_map(fn (ShipmentResult $r) => $r->toArray(), $this->results),
        ];
    }
}
