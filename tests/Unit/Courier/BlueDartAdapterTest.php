<?php

namespace Tests\Unit\Courier;

use App\Services\Courier\Adapters\BlueDartAdapter;
use App\Services\Courier\DTOs\AddressData;
use App\Services\Courier\DTOs\PackageData;
use App\Services\Courier\DTOs\ShipmentRequest;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class BlueDartAdapterTest extends TestCase
{
    public function test_create_shipment_maps_awb(): void
    {
        Http::fake([
            '*/token/v1/login' => Http::response(['JWTToken' => 'jwt-test'], 200),
            '*/GenerateWayBill' => Http::response([
                'GenerateWayBillResult' => [
                    'AWBNo' => 'BD12345678',
                    'Status' => 1,
                    'CCRCRDREF' => '100001',
                ],
            ], 200),
        ]);

        $adapter = new BlueDartAdapter([
            'api_url' => 'https://apigateway.bluedart.com',
            'client_id' => 'id',
            'client_secret' => 'secret',
            'login_id' => 'login',
            'licence_key' => 'key',
            'timeout' => 5,
        ]);

        $result = $adapter->createShipment($this->request());

        $this->assertTrue($result->success);
        $this->assertSame('BD12345678', $result->awb);
    }

    protected function request(): ShipmentRequest
    {
        $consignee = new AddressData('Ravi', '9876543210', 'Lane 1', 'Dehradun', 'Uttarakhand', '248001');
        $shipper = new AddressData('Warehouse', '9000000000', 'Depot', 'Dehradun', 'Uttarakhand', '248001');

        return new ShipmentRequest(
            1,
            '100001',
            'COD',
            499,
            499,
            'DEV-ORDER-1-BLUEDART',
            $consignee,
            $shipper,
            new PackageData(0.5, 14, 10, 6, 1),
            'Gahat Dal'
        );
    }
}
