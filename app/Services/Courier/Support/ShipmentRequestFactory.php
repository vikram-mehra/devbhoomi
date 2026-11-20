<?php

namespace App\Services\Courier\Support;

use App\Models\Order;
use App\Models\Setting;
use App\Services\Courier\DTOs\AddressData;
use App\Services\Courier\DTOs\PackageData;
use App\Services\Courier\DTOs\ShipmentRequest;
use App\Services\Courier\Exceptions\ShipmentCreationException;

class ShipmentRequestFactory
{
    public function make(Order $order, string $courier): ShipmentRequest
    {
        $order->loadMissing(['items.variant.product', 'shippingAddress', 'address']);

        return new ShipmentRequest(
            (int) $order->id,
            (string) $order->order_number,
            $this->paymentMode($order),
            (float) $order->total,
            $this->codAmount($order),
            $this->reference($order, $courier),
            $this->consignee($order),
            $this->shipper(),
            $this->package($order),
            $this->productDescription($order),
            $this->orderDate($order),
            $this->sellerGst(),
            $this->hsnCode($order),
            (string) $order->order_number
        );
    }

    public function reference(Order $order, string $courier): string
    {
        return 'DEV-ORDER-'.$order->id.'-'.strtoupper($courier);
    }

    public function paymentMode(Order $order): string
    {
        return strtolower((string) $order->payment_method) === 'cod' ? 'COD' : 'PREPAID';
    }

    public function codAmount(Order $order): float
    {
        return $this->paymentMode($order) === 'COD' ? (float) $order->payableAmount() : 0.0;
    }

    public function consignee(Order $order): AddressData
    {
        $addr = $order->shippingAddress ?: $order->address;
        if (! $addr) {
            throw new ShipmentCreationException('Order '.$order->order_number.' is missing a shipping address.');
        }

        $name = trim((string) ($addr->name ?: $order->customerDisplayName()));
        $phone = preg_replace('/\D+/', '', (string) ($addr->phone ?: $order->customerDisplayPhone())) ?? '';
        $line1 = trim((string) ($addr->line1 ?? ''));
        $city = trim((string) ($addr->city ?? ''));
        $state = trim((string) ($addr->state ?? ''));
        $pin = preg_replace('/\D+/', '', (string) ($addr->pincode ?? '')) ?? '';

        if ($name === '' || $phone === '' || $line1 === '' || $city === '' || $pin === '') {
            throw new ShipmentCreationException('Order '.$order->order_number.' is missing required shipping fields.');
        }

        return new AddressData(
            $name,
            $phone,
            $line1,
            $city,
            $state !== '' ? $state : 'NA',
            $pin,
            $order->customerDisplayEmail(),
            $addr->line2 ?? null
        );
    }

    public function shipper(): AddressData
    {
        $pickup = config('couriers.pickup', []);
        $companyAddress = trim((string) Setting::getValue('company_address', ''));
        $parsed = $this->parseLocation($companyAddress);

        $name = trim((string) (($pickup['name'] ?? '') ?: Setting::getValue('company_name', config('app.name'))));
        $phone = preg_replace('/\D+/', '', (string) (($pickup['phone'] ?? '') ?: Setting::getValue('company_phone', ''))) ?? '';
        $line1 = trim((string) (($pickup['address_line_1'] ?? '') ?: $companyAddress));
        $city = trim((string) (($pickup['city'] ?? '') ?: $parsed['city']));
        $state = trim((string) (($pickup['state'] ?? '') ?: $parsed['state']));
        $pin = preg_replace('/\D+/', '', (string) (($pickup['pincode'] ?? '') ?: $parsed['pincode'])) ?? '';
        $email = (string) (($pickup['email'] ?? '') ?: Setting::getValue('company_email', ''));
        $country = trim((string) (($pickup['country'] ?? '') ?: $parsed['country'] ?: 'India'));

        return new AddressData(
            $name !== '' ? $name : 'Warehouse',
            $phone !== '' ? $phone : '0000000000',
            $line1 !== '' ? $line1 : 'Warehouse',
            $city !== '' ? $city : 'NA',
            $state !== '' ? $state : 'NA',
            $pin,
            $email !== '' ? $email : null,
            $pickup['address_line_2'] ?? null,
            $country !== '' ? $country : 'India'
        );
    }

    public function package(Order $order): PackageData
    {
        $defaults = config('couriers.package_defaults', []);
        $weight = 0.0;
        $qty = 0;

        foreach ($order->items as $item) {
            $lineWeight = (float) ($item->weight_kg ?: optional(optional($item->variant)->product)->weight_kg ?: 0);
            $weight += $lineWeight * (int) $item->qty;
            $qty += (int) $item->qty;
        }

        if ($weight <= 0) {
            $weight = (float) ($defaults['weight'] ?? 0.5);
        }

        return new PackageData(
            $weight,
            (float) ($defaults['length'] ?? 14),
            (float) ($defaults['width'] ?? 10),
            (float) ($defaults['height'] ?? 6),
            max(1, $qty)
        );
    }

    public function productDescription(Order $order): string
    {
        $names = $order->items->pluck('product_name')->filter()->unique()->values();

        return $names->isEmpty()
            ? 'Order '.$order->order_number
            : $names->take(4)->implode(', ');
    }

    public function orderDate(Order $order): string
    {
        $dt = $order->created_at ?? now();

        return $dt->format('Y-m-d H:i:s');
    }

    public function sellerGst(): ?string
    {
        $gst = strtoupper(preg_replace('/\s+/', '', (string) Setting::getValue('company_gst', '')) ?? '');

        if (! preg_match('/^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z][1-9A-Z]Z[0-9A-Z]$/', $gst)) {
            return null;
        }

        return $gst;
    }

    public function hsnCode(Order $order): string
    {
        foreach ($order->items as $item) {
            $hsn = trim((string) (optional(optional($item->variant)->product)->hsn ?? $item->hsn ?? ''));
            if ($hsn !== '') {
                return $hsn;
            }
        }

        return (string) (config('couriers.package_defaults.hsn', '21069099') ?: '21069099');
    }

    /**
     * @return array{pincode: string, city: string, state: string, country: string}
     */
    public function parseLocation(string $address): array
    {
        $result = [
            'pincode' => '',
            'city' => '',
            'state' => '',
            'country' => 'India',
        ];

        if ($address === '') {
            return $result;
        }

        if (preg_match('/\b(\d{6})\b/', $address, $match)) {
            $result['pincode'] = $match[1];
        }

        if (preg_match('/,\s*([^,]+),\s*([^,]+)\s*,\s*India/i', $address, $match)) {
            $result['city'] = trim($match[1]);
            $result['state'] = trim($match[2]);
        } elseif (preg_match('/,\s*([^,]+)\s*,\s*India/i', $address, $match)) {
            $result['city'] = trim($match[1]);
        }

        return $result;
    }
}
