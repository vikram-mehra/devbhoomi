<?php

namespace Tests\Unit\Courier;

use App\Enums\ShipmentStatus;
use App\Services\Courier\Adapters\DelhiveryAdapter;
use App\Services\Courier\DTOs\AddressData;
use App\Services\Courier\DTOs\PackageData;
use App\Services\Courier\DTOs\ShipmentRequest;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class DelhiveryAdapterTest extends TestCase
{
    public function test_missing_token_returns_clear_error(): void
    {
        $adapter = new DelhiveryAdapter([
            'api_url' => 'https://staging-express.delhivery.com',
            'api_token' => '',
            'timeout' => 5,
        ]);

        $result = $adapter->createShipment($this->request());

        $this->assertFalse($result->success);
        $this->assertStringContainsString('Delhivery API token is not configured', $result->message);
    }

    public function test_create_shipment_maps_waybill(): void
    {
        $this->fakeDelhivery([
            'packages' => [[
                'status' => 'Success',
                'waybill' => '123456789012',
                'refnum' => '100001',
            ]],
        ]);

        $adapter = $this->adapter();
        $result = $adapter->createShipment($this->request());

        $this->assertTrue($result->success);
        $this->assertSame('123456789012', $result->awb);
        $this->assertSame(ShipmentStatus::CREATED, $result->status);
    }

    public function test_create_shipment_maps_failure(): void
    {
        $this->fakeDelhivery([
            'packages' => [[
                'status' => 'Fail',
                'remarks' => 'Invalid pincode',
                'refnum' => '100001',
            ]],
        ]);

        $result = $this->adapter()->createShipment($this->request());

        $this->assertFalse($result->success);
        $this->assertSame('Invalid pincode', $result->message);
    }

    public function test_payload_uses_standardized_package_and_address(): void
    {
        $this->fakeDelhivery(['packages' => [['status' => 'Success', 'waybill' => '1', 'refnum' => '100001']]]);

        $this->adapter()->createShipment($this->request(
            '05AAZFD6097K1ZK',
            '0713',
            '2026-09-29 10:00:00'
        ));

        Http::assertSent(function ($request) {
            if (! str_contains($request->url(), '/api/cmu/create.json')) {
                return false;
            }

            $data = $this->cmuData($request);

            return is_array($data)
                && $data['shipments'][0]['pin'] === '248001'
                && $data['shipments'][0]['weight'] === '500'
                && $data['shipments'][0]['payment_mode'] === 'Pre-paid'
                && $data['shipments'][0]['seller_gst_tin'] === '05AAZFD6097K1ZK'
                && $data['shipments'][0]['hsn_code'] === '0713'
                && $data['shipments'][0]['order_date'] === '2026-09-29 10:00:00'
                && $data['shipments'][0]['return_pin'] === '248001'
                && $data['pickup_location']['name'] === 'Warehouse';
        });
    }

    public function test_payload_omits_dummy_return_pin(): void
    {
        $this->fakeDelhivery(['packages' => [['status' => 'Success', 'waybill' => '1', 'refnum' => '100001']]]);

        $shipper = new AddressData('Warehouse', '9000000000', 'Depot', 'Dehradun', 'Uttarakhand', '000000');
        $this->adapter()->createShipment($this->request(null, null, null, $shipper));

        Http::assertSent(function ($request) {
            if (! str_contains($request->url(), '/api/cmu/create.json')) {
                return false;
            }

            $data = $this->cmuData($request);

            return is_array($data) && ! array_key_exists('return_pin', $data['shipments'][0]);
        });
    }

    public function test_configured_warehouse_is_used_exactly(): void
    {
        $this->fakeDelhivery(
            ['packages' => [['status' => 'Success', 'waybill' => '1', 'refnum' => '100001']]],
            [['name' => 'Noida HQ', 'pin' => '201307']]
        );

        $adapter = new DelhiveryAdapter([
            'api_url' => 'https://staging-express.delhivery.com',
            'api_token' => 'test-token',
            'warehouse' => 'DEVBHOOMI NATURALS B2C',
            'timeout' => 5,
        ]);
        $adapter->createShipment($this->request());

        Http::assertSent(function ($request) {
            if (! str_contains($request->url(), '/api/cmu/create.json')) {
                return false;
            }

            $data = $this->cmuData($request);

            return is_array($data) && $data['pickup_location']['name'] === 'DEVBHOOMI NATURALS B2C';
        });
    }

    public function test_uses_registered_warehouse_when_preferred_name_is_missing(): void
    {
        $this->fakeDelhivery(
            ['packages' => [['status' => 'Success', 'waybill' => '1', 'refnum' => '100001']]],
            [['name' => 'Noida HQ', 'pin' => '201307']]
        );

        $this->adapter()->createShipment($this->request());

        Http::assertSent(function ($request) {
            if (! str_contains($request->url(), '/api/cmu/create.json')) {
                return false;
            }

            $data = $this->cmuData($request);

            return is_array($data) && $data['pickup_location']['name'] === 'Noida HQ';
        });
    }

    public function test_result_includes_curl_debug(): void
    {
        $this->fakeDelhivery([
            'success' => false,
            'rmk' => 'Invalid pincode',
        ]);

        $result = $this->adapter()->createShipment($this->request());

        $this->assertFalse($result->success);
        $this->assertNotEmpty($result->debugCurl);
        $this->assertStringContainsString('curl -X POST', $result->debugCurl);
        $this->assertStringContainsString('/api/cmu/create.json', $result->debugCurl);
        $this->assertStringContainsString('HTTP 200', $result->debugCurl);
        $this->assertStringContainsString('Invalid pincode', $result->debugCurl);
    }

    public function test_end_date_error_is_humanized(): void
    {
        $this->fakeDelhivery([
            'success' => false,
            'rmk' => "Package creation API error.Package might be saved.Please contact tech.admin@delhivery.com. Error message is 'NoneType' object has no attribute 'end_date' . Quote this error message while reporting.",
        ]);

        $result = $this->adapter()->createShipment($this->request());

        $this->assertFalse($result->success);
        $this->assertStringContainsString('could not match pickup warehouse', $result->message);
        $this->assertStringContainsString('DELHIVERY_WAREHOUSE', $result->message);
    }

    protected function adapter(): DelhiveryAdapter
    {
        return new DelhiveryAdapter([
            'api_url' => 'https://staging-express.delhivery.com',
            'api_token' => 'test-token',
            'timeout' => 5,
        ]);
    }

    /**
     * @param  array<string, mixed>  $createBody
     * @param  list<array<string, mixed>>  $warehouses
     */
    protected function fakeDelhivery(array $createBody, array $warehouses = [['name' => 'Warehouse', 'pin' => '248001']]): void
    {
        Http::fake([
            '*/api/backend/clientwarehouse/all/' => Http::response(['data' => $warehouses], 200),
            '*/api/backend/clientwarehouse/create/' => Http::response(['success' => true], 200),
            '*/api/cmu/create.json' => Http::response($createBody, 200),
        ]);
    }

    /**
     * @return array<string, mixed>|null
     */
    protected function cmuData($request): ?array
    {
        $data = json_decode($request['data'] ?? '', true);
        if (! is_array($data)) {
            parse_str($request->body(), $form);
            $data = json_decode($form['data'] ?? '', true);
        }

        return is_array($data) ? $data : null;
    }

    protected function request(
        ?string $gst = null,
        ?string $hsn = null,
        ?string $orderDate = null,
        ?AddressData $shipper = null
    ): ShipmentRequest {
        $consignee = new AddressData('Ravi', '9876543210', 'Lane 1', 'Dehradun', 'Uttarakhand', '248001');
        $shipper = $shipper ?: new AddressData('Warehouse', '9000000000', 'Depot', 'Dehradun', 'Uttarakhand', '248001');

        return new ShipmentRequest(
            1,
            '100001',
            'PREPAID',
            499,
            0,
            'DEV-ORDER-1-DELHIVERY',
            $consignee,
            $shipper,
            new PackageData(0.5, 14, 10, 6, 1),
            'Gahat Dal',
            $orderDate,
            $gst,
            $hsn
        );
    }
}
