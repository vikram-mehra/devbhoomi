<?php

namespace App\Services\Courier\Adapters;

use App\Enums\ShipmentStatus;
use App\Models\Shipment;
use App\Services\Courier\Clients\BlueDartClient;
use App\Services\Courier\DTOs\ShipmentRequest;
use App\Services\Courier\DTOs\ShipmentResult;
use App\Services\Courier\DTOs\TrackingResult;
use App\Services\Courier\Exceptions\ShipmentCreationException;
use App\Services\Courier\Support\StatusMapper;

class BlueDartAdapter extends AbstractCourierAdapter
{
    protected BlueDartClient $client;

    public function __construct(array $config, ?BlueDartClient $client = null)
    {
        parent::__construct($config);
        $this->client = $client ?: new BlueDartClient($config);
    }

    public function key(): string
    {
        return 'bluedart';
    }

    public function createShipment(ShipmentRequest $request): ShipmentResult
    {
        try {
            $response = $this->client->generateWayBill($this->buildWayBillPayload($request));
        } catch (\Throwable $e) {
            return ShipmentResult::fail($request->orderId, $request->orderNumber, $this->safeMessage($e), 'api_error');
        }

        return $this->mapWayBill($request, $response);
    }

    public function cancelShipment(Shipment $shipment): ShipmentResult
    {
        $awb = $shipment->trackingNumber();
        if (! $awb) {
            return ShipmentResult::fail((int) $shipment->order_id, optional($shipment->order)->order_number, 'Missing AWB.', 'missing_awb');
        }

        try {
            $response = $this->client->cancel($awb);
        } catch (\Throwable $e) {
            return ShipmentResult::fail((int) $shipment->order_id, optional($shipment->order)->order_number, $this->safeMessage($e), 'api_error');
        }

        return new ShipmentResult(true, (int) $shipment->order_id, optional($shipment->order)->order_number, $shipment->shipment_id, $awb, null, null, ShipmentStatus::CANCELLED, 'Cancelled', null, $response);
    }

    public function trackShipment(string $awb): TrackingResult
    {
        try {
            $response = $this->client->track($awb);
        } catch (\Throwable $e) {
            return new TrackingResult(false, $awb, null, null, null, $this->safeMessage($e));
        }

        $scan = data_get($response, 'ShipmentData.0.Shipment.Statustracking.0')
            ?? data_get($response, 'ScanDetail')
            ?? [];

        $statusName = (string) (data_get($scan, 'Scan') ?? data_get($response, 'Status') ?? '');

        return new TrackingResult(
            true,
            $awb,
            $this->normalizeTrackingStatus($statusName),
            $statusName,
            (string) (data_get($scan, 'ScannedLocation') ?? ''),
            null,
            [],
            $response
        );
    }

    public function parseWebhook(array $payload): TrackingResult
    {
        $awb = (string) ($payload['AWBNo'] ?? $payload['awb'] ?? '');
        $statusName = (string) ($payload['Status'] ?? $payload['Scan'] ?? '');

        return new TrackingResult(
            $awb !== '',
            $awb !== '' ? $awb : null,
            $statusName !== '' ? $this->normalizeTrackingStatus($statusName) : null,
            $statusName !== '' ? $statusName : null,
            (string) ($payload['Location'] ?? ''),
            $awb !== '' ? null : 'Unrecognized Blue Dart webhook.',
            [],
            $payload
        );
    }

    public function normalizeTrackingStatus(string $courierStatus, ?string $statusType = null): string
    {
        return StatusMapper::bluedart($courierStatus, $statusType);
    }

    /**
     * @return array<string, mixed>
     */
    protected function buildWayBillPayload(ShipmentRequest $request): array
    {
        $productCode = $request->isCod() ? 'A' : 'D';

        return [
            'Request' => [
                'Consignee' => [
                    'ConsigneeName' => $request->consignee->name,
                    'ConsigneeAddress1' => $request->consignee->addressLine1,
                    'ConsigneeAddress2' => (string) $request->consignee->addressLine2,
                    'ConsigneePincode' => $request->consignee->pincode,
                    'ConsigneeMobile' => $request->consignee->phone,
                    'ConsigneeAttention' => $request->consignee->name,
                ],
                'Services' => [
                    'ProductCode' => $productCode,
                    'ProductType' => 'Dutiables',
                    'SubProductCode' => $request->isCod() ? 'C' : 'P',
                    'PieceCount' => (string) $request->package->quantity,
                    'ActualWeight' => number_format($request->package->weight, 3, '.', ''),
                    'CollectableAmount' => $request->isCod() ? number_format($request->codAmount, 2, '.', '') : '0',
                    'DeclaredValue' => number_format($request->orderValue, 2, '.', ''),
                    'CreditReferenceNo' => $request->orderNumber,
                    'Dimensions' => [
                        [
                            'Length' => $request->package->length,
                            'Breadth' => $request->package->width,
                            'Height' => $request->package->height,
                            'Count' => $request->package->quantity,
                        ],
                    ],
                    'Itemdtl' => [
                        [
                            'ItemID' => $request->orderNumber,
                            'ItemName' => $request->productDescription,
                            'ItemValue' => number_format($request->orderValue, 2, '.', ''),
                        ],
                    ],
                ],
                'Shipper' => [
                    'CustomerName' => $request->shipper->name,
                    'CustomerAddress1' => $request->shipper->addressLine1,
                    'CustomerAddress2' => (string) $request->shipper->addressLine2,
                    'CustomerPincode' => $request->shipper->pincode,
                    'CustomerMobile' => $request->shipper->phone,
                    'OriginArea' => substr($request->shipper->pincode, 0, 3),
                    'CustomerCode' => (string) ($this->config['login_id'] ?? ''),
                    'Sender' => $request->shipper->name,
                    'IsToPayCustomer' => false,
                ],
            ],
            'Profile' => $this->client->profile(),
        ];
    }

    /**
     * @param  array<string, mixed>  $response
     */
    protected function mapWayBill(ShipmentRequest $request, array $response): ShipmentResult
    {
        $result = $response['GenerateWayBillResult'] ?? $response;
        $awb = trim((string) ($result['AWBNo'] ?? $result['AWBNo'] ?? data_get($result, 'AWBNo') ?? ''));
        $status = $result['Status'] ?? $result['IsError'] ?? null;
        $message = (string) ($result['StatusInformation'] ?? $result['ErrorMessage'] ?? '');

        if ($awb === '' || $status === 0 || $status === '0' || $status === false) {
            return ShipmentResult::fail(
                $request->orderId,
                $request->orderNumber,
                $message !== '' ? $message : 'Blue Dart waybill creation failed.',
                'create_failed',
                $response
            );
        }

        return ShipmentResult::ok($request->orderId, $request->orderNumber, $awb, $response, (string) ($result['CCRCRDREF'] ?? $awb));
    }

    protected function safeMessage(\Throwable $e): string
    {
        if ($e instanceof ShipmentCreationException) {
            return $e->getMessage();
        }

        return 'Courier request failed. Please try again or check courier configuration.';
    }
}
