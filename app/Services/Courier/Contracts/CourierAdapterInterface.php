<?php

namespace App\Services\Courier\Contracts;

use App\Models\Shipment;
use App\Services\Courier\DTOs\BulkShipmentResult;
use App\Services\Courier\DTOs\LabelResult;
use App\Services\Courier\DTOs\RateResult;
use App\Services\Courier\DTOs\ServiceabilityResult;
use App\Services\Courier\DTOs\ShipmentRequest;
use App\Services\Courier\DTOs\ShipmentResult;
use App\Services\Courier\DTOs\TrackingResult;
use Illuminate\Support\Collection;

interface CourierAdapterInterface
{
    public function key(): string;

    public function createShipment(ShipmentRequest $request): ShipmentResult;

    /**
     * @param  Collection<int, ShipmentRequest>  $requests
     */
    public function createShipments(Collection $requests): BulkShipmentResult;

    public function cancelShipment(Shipment $shipment): ShipmentResult;

    public function trackShipment(string $awb): TrackingResult;

    public function generateLabel(Shipment $shipment): LabelResult;

    public function getShippingRates(ShipmentRequest $request): RateResult;

    public function checkServiceability(string $pincode, float $weight, string $paymentMethod): ServiceabilityResult;

    /**
     * @param  array<string, mixed>  $payload
     */
    public function parseWebhook(array $payload): TrackingResult;

    public function normalizeTrackingStatus(string $courierStatus, ?string $statusType = null): string;
}
