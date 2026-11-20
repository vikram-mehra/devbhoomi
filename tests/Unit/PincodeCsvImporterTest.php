<?php

namespace Tests\Unit;

use App\Services\PincodeCsvImporter;
use Tests\TestCase;

class PincodeCsvImporterTest extends TestCase
{
    public function test_parses_valid_rows_and_skips_bad_ones(): void
    {
        $path = tempnam(sys_get_temp_dir(), 'pin');
        file_put_contents($path, implode("\n", [
            'pincode,city,state,day_offset,courier_name,status',
            '263645,Haldwani,Uttarakhand,3,Delhivery,1',
            '12,Bad,Row,3,Delhivery,1',
            '201307,Noida,Uttar Pradesh,2,Delhivery,0',
        ]));

        $parsed = (new PincodeCsvImporter())->parse($path);
        unlink($path);

        $this->assertCount(2, $parsed['rows']);
        $this->assertSame('263645', $parsed['rows'][0]['pincode']);
        $this->assertSame('Haldwani', $parsed['rows'][0]['city']);
        $this->assertTrue($parsed['rows'][0]['status']);
        $this->assertSame('201307', $parsed['rows'][1]['pincode']);
        $this->assertFalse($parsed['rows'][1]['status']);
        $this->assertNotEmpty($parsed['errors']);
    }

    public function test_parses_delivery_charge_columns(): void
    {
        $path = tempnam(sys_get_temp_dir(), 'pin');
        file_put_contents($path, implode("\n", [
            'pincode,city,state,delivery_charge,additional_500g_charge,status',
            '263645,Haldwani,Uttarakhand,60,40,1',
            '201307,Noida,Uttar Pradesh,80,,1',
        ]));

        $parsed = (new PincodeCsvImporter())->parse($path);
        unlink($path);

        $this->assertSame(60.0, $parsed['rows'][0]['delivery_charge']);
        $this->assertSame(40.0, $parsed['rows'][0]['additional_500g_charge']);
        $this->assertSame(80.0, $parsed['rows'][1]['delivery_charge']);
        $this->assertNull($parsed['rows'][1]['additional_500g_charge']);
    }

    public function test_requires_core_columns(): void
    {
        $path = tempnam(sys_get_temp_dir(), 'pin');
        file_put_contents($path, "foo,bar\n1,2\n");

        $parsed = (new PincodeCsvImporter())->parse($path);
        unlink($path);

        $this->assertSame([], $parsed['rows']);
        $this->assertStringContainsString('pincode, city, and state', $parsed['errors'][0]);
    }
}
