<?php

namespace App\Services\Courier\Adapters;

use App\Models\Shipment;
use App\Services\Courier\Contracts\CourierAdapterInterface;
use App\Services\Courier\DTOs\BulkShipmentResult;
use App\Services\Courier\DTOs\LabelResult;
use App\Services\Courier\DTOs\RateResult;
use App\Services\Courier\DTOs\ServiceabilityResult;
use App\Services\Courier\DTOs\ShipmentRequest;
use App\Services\Courier\DTOs\ShipmentResult;
use App\Services\Courier\DTOs\TrackingResult;
use Illuminate\Support\Collection;

abstract class AbstractCourierAdapter implements CourierAdapterInterface
{
    /** @var array<string, mixed> */
    protected array $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function createShipments(Collection $requests): BulkShipmentResult
    {
        $results = [];
        foreach ($requests as $request) {
            $results[] = $this->createShipment($request);
        }

        return new BulkShipmentResult($this->key(), $results);
    }

    public function getShippingRates(ShipmentRequest $request): RateResult
    {
        return new RateResult(false, null, 'INR', 'Rate enquiry is not enabled for '.$this->key().' yet.');
    }

    public function generateLabel(Shipment $shipment): LabelResult
    {
        return new LabelResult(false, $shipment->label_url, 'Label generation is not available for '.$this->key().'.');
    }

    public function checkServiceability(string $pincode, float $weight, string $paymentMethod): ServiceabilityResult
    {
        return new ServiceabilityResult(false, 'Serviceability is not enabled for '.$this->key().' yet.');
    }

    public function parseWebhook(array $payload): TrackingResult
    {
        return new TrackingResult(false, null, null, null, null, 'Webhook parsing is not configured.', [], $payload);
    }
}
