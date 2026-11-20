<?php

namespace App\Services\Courier\Adapters;

use App\Enums\ShipmentStatus;
use App\Models\Shipment;
use App\Services\Courier\Clients\DelhiveryClient;
use App\Services\Courier\DTOs\LabelResult;
use App\Services\Courier\DTOs\ServiceabilityResult;
use App\Services\Courier\DTOs\ShipmentRequest;
use App\Services\Courier\DTOs\ShipmentResult;
use App\Services\Courier\DTOs\TrackingResult;
use App\Services\Courier\Exceptions\CourierApiException;
use App\Services\Courier\Exceptions\ShipmentCreationException;
use App\Services\Courier\Support\CourierHttp;
use App\Services\Courier\Support\StatusMapper;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class DelhiveryAdapter extends AbstractCourierAdapter
{
    protected DelhiveryClient $client;

    protected ?string $resolvedPickupName = null;

    public function __construct(array $config, ?DelhiveryClient $client = null)
    {
        parent::__construct($config);
        $this->client = $client ?: new DelhiveryClient($config);
    }

    public function key(): string
    {
        return 'delhivery';
    }

    public function createShipment(ShipmentRequest $request): ShipmentResult
    {
        try {
            $payload = $this->buildShipmentPayload(collect([$request]));
            $response = $this->client->createShipment($payload);
        } catch (\Throwable $e) {
            return $this->withDebug(ShipmentResult::fail($request->orderId, $request->orderNumber, $this->safeMessage($e), 'api_error'));
        }

        return $this->withDebug($this->mapPackage($request, $response));
    }

    public function createShipments(Collection $requests): \App\Services\Courier\DTOs\BulkShipmentResult
    {
        if ($requests->isEmpty()) {
            return new \App\Services\Courier\DTOs\BulkShipmentResult($this->key(), []);
        }

        try {
            $response = $this->client->createShipment($this->buildShipmentPayload($requests));
        } catch (\Throwable $e) {
            $results = $requests->map(fn (ShipmentRequest $r) => $this->withDebug(ShipmentResult::fail(
                $r->orderId,
                $r->orderNumber,
                $this->safeMessage($e),
                'api_error'
            )))->all();

            return new \App\Services\Courier\DTOs\BulkShipmentResult($this->key(), $results);
        }

        $packages = $this->packagesFrom($response);
        $results = [];
        foreach ($requests as $index => $request) {
            $package = $packages[$index] ?? $this->packageForOrder($packages, $request->orderNumber);
            $results[] = $this->withDebug($this->mapSinglePackage($request, $package, $response));
        }

        return new \App\Services\Courier\DTOs\BulkShipmentResult($this->key(), $results);
    }

    public function cancelShipment(Shipment $shipment): ShipmentResult
    {
        $awb = $shipment->trackingNumber();
        if (! $awb) {
            return ShipmentResult::fail((int) $shipment->order_id, (string) optional($shipment->order)->order_number, 'Missing AWB.', 'missing_awb');
        }

        try {
            $response = $this->client->cancelShipment($awb);
        } catch (\Throwable $e) {
            return ShipmentResult::fail((int) $shipment->order_id, (string) optional($shipment->order)->order_number, $this->safeMessage($e), 'api_error');
        }

        return new ShipmentResult(true, (int) $shipment->order_id, optional($shipment->order)->order_number, $shipment->shipment_id, $awb, null, null, ShipmentStatus::CANCELLED, 'Cancelled', null, $response);
    }

    public function trackShipment(string $awb): TrackingResult
    {
        try {
            $response = $this->client->trackByWaybill($awb);
        } catch (\Throwable $e) {
            return new TrackingResult(false, $awb, null, null, null, $this->safeMessage($e));
        }

        $shipment = $response['ShipmentData'][0]['Shipment'] ?? $response['Shipment'] ?? null;
        if (! is_array($shipment)) {
            return new TrackingResult(false, $awb, null, null, null, 'Shipment not found.', [], $response);
        }

        $statusName = (string) data_get($shipment, 'Status.Status', '');
        $statusType = (string) data_get($shipment, 'Status.StatusType', '');

        return new TrackingResult(
            true,
            (string) ($shipment['AWB'] ?? $awb),
            $this->normalizeTrackingStatus($statusName, $statusType),
            $statusName,
            (string) data_get($shipment, 'Status.StatusLocation', ''),
            null,
            $this->mapScanEvents($shipment),
            $response
        );
    }

    public function generateLabel(Shipment $shipment): LabelResult
    {
        $awb = $shipment->trackingNumber();
        if (! $awb) {
            return new LabelResult(false, null, 'Missing AWB.');
        }

        try {
            $response = $this->client->packingSlip($awb);
        } catch (\Throwable $e) {
            return new LabelResult(false, null, $this->safeMessage($e));
        }

        $url = (string) (data_get($response, 'packages.0.pdf_download_link')
            ?: data_get($response, 'packages.0.pdf_download')
            ?: '');

        return new LabelResult($url !== '', $url !== '' ? $url : null, $url !== '' ? null : 'Label URL missing.', $response);
    }

    public function checkServiceability(string $pincode, float $weight, string $paymentMethod): ServiceabilityResult
    {
        try {
            $response = $this->client->pinCodes($pincode);
        } catch (\Throwable $e) {
            return new ServiceabilityResult(false, $this->safeMessage($e));
        }

        $row = data_get($response, 'delivery_codes.0.postal_code');

        return new ServiceabilityResult(is_array($row), is_array($row) ? 'Serviceable' : 'Not serviceable', $response);
    }

    public function parseWebhook(array $payload): TrackingResult
    {
        $shipment = $payload['Shipment'] ?? data_get($payload, 'ShipmentData.0.Shipment');
        if (! is_array($shipment)) {
            return new TrackingResult(false, null, null, null, null, 'Unrecognized Delhivery webhook.', [], $payload);
        }

        $statusName = (string) data_get($shipment, 'Status.Status', '');
        $statusType = (string) data_get($shipment, 'Status.StatusType', '');

        return new TrackingResult(
            true,
            (string) ($shipment['AWB'] ?? ''),
            $this->normalizeTrackingStatus($statusName, $statusType),
            $statusName,
            (string) data_get($shipment, 'Status.StatusLocation', ''),
            null,
            $this->mapScanEvents($shipment),
            $payload
        );
    }

    public function normalizeTrackingStatus(string $courierStatus, ?string $statusType = null): string
    {
        return StatusMapper::delhivery($courierStatus, $statusType);
    }

    /**
     * @param  Collection<int, ShipmentRequest>  $requests
     * @return array<string, mixed>
     */
    protected function buildShipmentPayload(Collection $requests): array
    {
        /** @var ShipmentRequest $first */
        $first = $requests->first();
        $pickupName = $this->resolvePickupName($first);

        $shipments = $requests->map(function (ShipmentRequest $request) {
            $row = [
                'name' => $request->consignee->name,
                'add' => $request->consignee->line(),
                'pin' => $request->consignee->pincode,
                'city' => $request->consignee->city,
                'state' => $request->consignee->state,
                'country' => $request->consignee->country ?: 'India',
                'phone' => $request->consignee->phone,
                'order' => $request->orderNumber,
                'payment_mode' => $request->isCod() ? 'COD' : 'Pre-paid',
                'products_desc' => $request->productDescription,
                'cod_amount' => $request->isCod() ? number_format($request->codAmount, 2, '.', '') : '0',
                'total_amount' => number_format($request->orderValue, 2, '.', ''),
                'quantity' => (string) $request->package->quantity,
                'weight' => (string) $request->package->weightGrams(),
                'shipment_length' => (string) $request->package->length,
                'shipment_width' => (string) $request->package->width,
                'shipment_height' => (string) $request->package->height,
                'shipping_mode' => 'Surface',
                'address_type' => 'home',
                'order_date' => $request->orderDate ?: now()->format('Y-m-d H:i:s'),
                'seller_inv' => $request->invoiceNumber ?: $request->orderNumber,
                'seller_inv_date' => $request->orderDate ? substr($request->orderDate, 0, 10) : now()->toDateString(),
                'hsn_code' => $request->hsnCode ?: (string) (config('couriers.package_defaults.hsn', '21069099') ?: '21069099'),
                'seller_name' => $request->shipper->name,
                'seller_add' => $request->shipper->line(),
            ];

            $client = trim((string) ($this->config['client'] ?? ''));
            if ($client !== '') {
                $row['client'] = $client;
            }

            if ($request->sellerGst) {
                $row['seller_gst_tin'] = $request->sellerGst;
            }

            $returnPin = preg_replace('/\D+/', '', $request->shipper->pincode) ?? '';
            if (strlen($returnPin) === 6 && $returnPin !== '000000') {
                $row['return_pin'] = $returnPin;
                $row['return_city'] = $request->shipper->city;
                $row['return_phone'] = $request->shipper->phone;
                $row['return_add'] = $request->shipper->line();
                $row['return_state'] = $request->shipper->state;
                $row['return_country'] = $request->shipper->country ?: 'India';
            }

            return $row;
        })->values()->all();

        return [
            'shipments' => $shipments,
            'pickup_location' => [
                'name' => $pickupName,
            ],
        ];
    }

    /**
     * Delhivery looks up ClientWarehouse by pickup name and reads contract.end_date.
     * An unknown name returns None and crashes with the end_date AttributeError.
     */
    protected function resolvePickupName(ShipmentRequest $request): string
    {
        if (is_string($this->resolvedPickupName) && $this->resolvedPickupName !== '') {
            return $this->resolvedPickupName;
        }

        $configured = trim((string) ($this->config['warehouse'] ?? ''));
        if ($configured !== '') {
            return $this->resolvedPickupName = $configured;
        }

        $preferred = $this->warehouseName($request->shipper->name);
        $listed = [];

        try {
            $listed = $this->extractWarehouseNames($this->client->listWarehouses());
        } catch (\Throwable $e) {
            Log::channel('courier')->info('Delhivery warehouse list skipped.', [
                'error' => $e->getMessage(),
            ]);
        }

        if ($preferred !== '') {
            $match = $this->matchListedName($preferred, $listed);
            if ($match !== null) {
                return $this->resolvedPickupName = $match;
            }
        }

        if (count($listed) === 1) {
            Log::channel('courier')->info('Using the only registered Delhivery warehouse.', [
                'warehouse' => $listed[0],
                'preferred' => $preferred,
            ]);

            return $this->resolvedPickupName = $listed[0];
        }

        if ($this->canCreateWarehouse($request, $preferred)) {
            try {
                $created = $this->client->createWarehouse($this->warehouseCreatePayload($request, $preferred));
                $createdName = $this->createdWarehouseName($created, $preferred);
                if ($createdName !== '') {
                    Log::channel('courier')->info('Delhivery warehouse create attempted.', [
                        'warehouse' => $createdName,
                        'already_exists' => ! empty($created['_already_exists']),
                    ]);

                    return $this->resolvedPickupName = $createdName;
                }
            } catch (\Throwable $e) {
                Log::channel('courier')->warning('Delhivery warehouse create skipped.', [
                    'error' => $e->getMessage(),
                ]);
            }
        }

        if (isset($listed[0])) {
            return $this->resolvedPickupName = $listed[0];
        }

        if ($preferred !== '') {
            return $this->resolvedPickupName = $preferred;
        }

        throw new ShipmentCreationException(
            'Delhivery pickup warehouse is not configured. Set DELHIVERY_WAREHOUSE to the exact name registered in the Delhivery dashboard.'
        );
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return list<string>
     */
    protected function extractWarehouseNames(array $payload): array
    {
        $bucket = $payload['data'] ?? $payload['results'] ?? $payload['warehouses'] ?? $payload['client_warehouse'] ?? $payload;
        if (! is_array($bucket)) {
            return [];
        }

        if (isset($bucket['name'])) {
            $bucket = [$bucket];
        }

        $names = [];
        foreach ($bucket as $row) {
            if (is_array($row) && isset($row['name']) && is_string($row['name']) && trim($row['name']) !== '') {
                $names[] = trim($row['name']);
            }
        }

        return array_values(array_unique($names));
    }

    /**
     * @param  list<string>  $names
     */
    protected function matchListedName(string $preferred, array $names): ?string
    {
        foreach ($names as $name) {
            if (strcasecmp($name, $preferred) === 0) {
                return $name;
            }
        }

        return null;
    }

    protected function canCreateWarehouse(ShipmentRequest $request, string $name): bool
    {
        $pin = preg_replace('/\D+/', '', $request->shipper->pincode) ?? '';
        $phone = preg_replace('/\D+/', '', $request->shipper->phone) ?? '';

        return $name !== ''
            && strlen($pin) === 6
            && $pin !== '000000'
            && strlen($phone) >= 10
            && $phone !== '0000000000'
            && trim($request->shipper->line()) !== ''
            && trim($request->shipper->line()) !== 'Warehouse';
    }

    /**
     * @return array<string, string>
     */
    protected function warehouseCreatePayload(ShipmentRequest $request, string $name): array
    {
        $shipper = $request->shipper;
        $pin = preg_replace('/\D+/', '', $shipper->pincode) ?? '';
        $phone = substr(preg_replace('/\D+/', '', $shipper->phone) ?? '', -10);
        $city = $shipper->city !== 'NA' ? $shipper->city : '';
        $state = $shipper->state !== 'NA' ? $shipper->state : '';
        $country = $shipper->country ?: 'India';
        $address = $shipper->line();
        $email = $shipper->email ?: (string) config('mail.from.address', '');

        return [
            'name' => $name,
            'registered_name' => $shipper->name,
            'address' => $address,
            'pin' => $pin,
            'phone' => $phone,
            'email' => $email,
            'city' => $city,
            'country' => $country,
            'return_address' => $address,
            'return_pin' => $pin,
            'return_city' => $city,
            'return_state' => $state,
            'return_country' => $country,
        ];
    }

    /**
     * @param  array<string, mixed>  $response
     */
    protected function createdWarehouseName(array $response, string $fallback): string
    {
        foreach (['name', 'warehouse_name', 'client_warehouse'] as $key) {
            $value = data_get($response, $key) ?: data_get($response, 'data.'.$key);
            if (is_string($value) && trim($value) !== '') {
                return trim($value);
            }
        }

        if (! empty($response['_already_exists']) || ! empty($response['success'])) {
            return $fallback;
        }

        $hint = strtolower(CourierHttp::errorHint($response));
        if (str_contains($hint, 'already') || str_contains($hint, 'exist')) {
            return $fallback;
        }

        return $fallback;
    }

    /**
     * @param  array<string, mixed>  $response
     */
    protected function mapPackage(ShipmentRequest $request, array $response): ShipmentResult
    {
        $packages = $this->packagesFrom($response);
        $package = $this->packageForOrder($packages, $request->orderNumber) ?? ($packages[0] ?? null);

        return $this->mapSinglePackage($request, $package, $response);
    }

    /**
     * @param  array<string, mixed>|null  $package
     * @param  array<string, mixed>  $response
     */
    protected function mapSinglePackage(ShipmentRequest $request, ?array $package, array $response): ShipmentResult
    {
        if (! $package) {
            $remark = (string) ($response['rmk'] ?? $response['remark'] ?? '');

            return ShipmentResult::fail(
                $request->orderId,
                $request->orderNumber,
                $this->humanizeDelhiveryError($remark !== '' ? $remark : 'Delhivery did not return a package.'),
                'empty_response',
                $response
            );
        }

        $status = strtolower((string) ($package['status'] ?? ''));
        $awb = trim((string) ($package['waybill'] ?? $package['Waybill'] ?? ''));
        $remark = (string) ($package['remarks'] ?? $package['remark'] ?? $package['rmk'] ?? '');
        if ($remark === '') {
            $remark = (string) ($response['rmk'] ?? $response['remark'] ?? '');
        }

        if ($awb === '' || in_array($status, ['fail', 'failed', 'error'], true)) {
            return ShipmentResult::fail(
                $request->orderId,
                $request->orderNumber,
                $this->humanizeDelhiveryError($remark !== '' ? $remark : 'Delhivery shipment creation failed.'),
                'create_failed',
                $response
            );
        }

        return ShipmentResult::ok($request->orderId, $request->orderNumber, $awb, $response, $awb);
    }

    /**
     * @param  array<string, mixed>  $response
     * @return list<array<string, mixed>>
     */
    protected function packagesFrom(array $response): array
    {
        $packages = $response['packages'] ?? $response['Packages'] ?? [];

        return is_array($packages) ? array_values($packages) : [];
    }

    /**
     * @param  list<array<string, mixed>>  $packages
     * @return array<string, mixed>|null
     */
    protected function packageForOrder(array $packages, string $orderNumber): ?array
    {
        foreach ($packages as $package) {
            $ref = (string) ($package['refnum'] ?? $package['order'] ?? '');
            if ($ref === $orderNumber) {
                return $package;
            }
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $shipment
     * @return list<array<string, mixed>>
     */
    protected function mapScanEvents(array $shipment): array
    {
        $scans = data_get($shipment, 'Scans.ScanDetail', []);
        if (isset($scans['ScanDateTime'])) {
            $scans = [$scans];
        }

        $events = [];
        foreach (is_array($scans) ? $scans : [] as $scan) {
            if (! is_array($scan)) {
                continue;
            }
            $events[] = [
                'status' => $this->normalizeTrackingStatus((string) ($scan['Scan'] ?? ''), (string) ($scan['ScanType'] ?? '')),
                'courier_status' => (string) ($scan['Scan'] ?? ''),
                'location' => (string) ($scan['ScannedLocation'] ?? ''),
                'description' => (string) ($scan['Instructions'] ?? $scan['Scan'] ?? ''),
                'event_time' => $scan['ScanDateTime'] ?? null,
            ];
        }

        return $events;
    }

    protected function withDebug(ShipmentResult $result): ShipmentResult
    {
        $debug = $this->client->lastDebugBlock();
        if ($debug !== '') {
            $result->debugCurl = $debug;
        }

        return $result;
    }

    protected function warehouseName(string $fallback): string
    {
        $name = trim((string) ($this->config['warehouse'] ?? ''));

        return $name !== '' ? $name : $fallback;
    }

    protected function humanizeDelhiveryError(string $message): string
    {
        if ($message === '') {
            return $message;
        }

        if (stripos($message, 'end_date') !== false || stripos($message, 'NoneType') !== false) {
            $warehouse = $this->resolvedPickupName ?: (string) ($this->config['warehouse'] ?? '');

            return 'Delhivery could not match pickup warehouse'
                .($warehouse !== '' ? ' ['.$warehouse.']' : '')
                .'. Set DELHIVERY_WAREHOUSE to the exact name in the Delhivery dashboard (case-sensitive), or register that warehouse first. Original: '.$message;
        }

        return $message;
    }

    protected function safeMessage(\Throwable $e): string
    {
        if ($e instanceof ShipmentCreationException || $e instanceof CourierApiException) {
            return $this->humanizeDelhiveryError($e->getMessage());
        }

        return 'Courier request failed. Please try again or check courier configuration.';
    }
}
