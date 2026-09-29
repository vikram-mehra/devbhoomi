<?php

namespace App\Services\Courier\DTOs;

class ShipmentRequest
{
    public int $orderId;
    public string $orderNumber;
    public string $paymentMode;
    public float $orderValue;
    public float $codAmount;
    public string $reference;
    public AddressData $consignee;
    public AddressData $shipper;
    public PackageData $package;
    public string $productDescription;
    public ?string $orderDate;
    public ?string $sellerGst;
    public ?string $hsnCode;
    public ?string $invoiceNumber;

    public function __construct(
        int $orderId,
        string $orderNumber,
        string $paymentMode,
        float $orderValue,
        float $codAmount,
        string $reference,
        AddressData $consignee,
        AddressData $shipper,
        PackageData $package,
        string $productDescription,
        ?string $orderDate = null,
        ?string $sellerGst = null,
        ?string $hsnCode = null,
        ?string $invoiceNumber = null
    ) {
        $this->orderId = $orderId;
        $this->orderNumber = $orderNumber;
        $this->paymentMode = $paymentMode;
        $this->orderValue = $orderValue;
        $this->codAmount = $codAmount;
        $this->reference = $reference;
        $this->consignee = $consignee;
        $this->shipper = $shipper;
        $this->package = $package;
        $this->productDescription = $productDescription;
        $this->orderDate = $orderDate;
        $this->sellerGst = $sellerGst;
        $this->hsnCode = $hsnCode;
        $this->invoiceNumber = $invoiceNumber;
    }

    public function isCod(): bool
    {
        return $this->paymentMode === 'COD';
    }
}
