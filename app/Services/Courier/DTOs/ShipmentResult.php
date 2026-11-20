<?php

namespace App\Services\Courier\DTOs;

use App\Enums\ShipmentStatus;

class ShipmentResult
{
    public bool $success;
    public ?int $orderId;
    public ?string $orderNumber;
    public ?string $shipmentId;
    public ?string $awb;
    public ?string $trackingUrl;
    public ?string $labelUrl;
    public ?string $status;
    public ?string $message;
    public ?string $errorCode;
    /** @var array<string, mixed> */
    public array $rawResponse;
    public ?string $debugCurl = null;

    /**
     * @param  array<string, mixed>  $rawResponse
     */
    public function __construct(
        bool $success,
        ?int $orderId = null,
        ?string $orderNumber = null,
        ?string $shipmentId = null,
        ?string $awb = null,
        ?string $trackingUrl = null,
        ?string $labelUrl = null,
        ?string $status = null,
        ?string $message = null,
        ?string $errorCode = null,
        array $rawResponse = []
    ) {
        $this->success = $success;
        $this->orderId = $orderId;
        $this->orderNumber = $orderNumber;
        $this->shipmentId = $shipmentId;
        $this->awb = $awb;
        $this->trackingUrl = $trackingUrl;
        $this->labelUrl = $labelUrl;
        $this->status = $status ?: ($success ? ShipmentStatus::CREATED : ShipmentStatus::FAILED);
        $this->message = $message;
        $this->errorCode = $errorCode;
        $this->rawResponse = $rawResponse;
    }

    /**
     * @param  array<string, mixed>  $raw
     */
    public static function ok(int $orderId, string $orderNumber, string $awb, array $raw = [], ?string $shipmentId = null, ?string $labelUrl = null): self
    {
        return new self(true, $orderId, $orderNumber, $shipmentId, $awb, null, $labelUrl, ShipmentStatus::CREATED, null, null, $raw);
    }

    /**
     * @param  array<string, mixed>  $raw
     */
    public static function fail(int $orderId, string $orderNumber, string $message, ?string $errorCode = null, array $raw = []): self
    {
        return new self(false, $orderId, $orderNumber, null, null, null, null, ShipmentStatus::FAILED, $message, $errorCode, $raw);
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'success' => $this->success,
            'order_id' => $this->orderId,
            'order_number' => $this->orderNumber,
            'shipment_id' => $this->shipmentId,
            'awb' => $this->awb,
            'tracking_url' => $this->trackingUrl,
            'label_url' => $this->labelUrl,
            'status' => $this->status,
            'message' => $this->message,
            'error_code' => $this->errorCode,
            'debug_curl' => $this->debugCurl,
        ];
    }
}
