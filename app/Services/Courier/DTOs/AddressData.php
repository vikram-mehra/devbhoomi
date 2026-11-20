<?php

namespace App\Services\Courier\DTOs;

class AddressData
{
    public string $name;
    public string $phone;
    public ?string $email;
    public string $addressLine1;
    public ?string $addressLine2;
    public string $city;
    public string $state;
    public string $country;
    public string $pincode;

    public function __construct(
        string $name,
        string $phone,
        string $addressLine1,
        string $city,
        string $state,
        string $pincode,
        ?string $email = null,
        ?string $addressLine2 = null,
        string $country = 'India'
    ) {
        $this->name = $name;
        $this->phone = $phone;
        $this->email = $email;
        $this->addressLine1 = $addressLine1;
        $this->addressLine2 = $addressLine2;
        $this->city = $city;
        $this->state = $state;
        $this->country = $country;
        $this->pincode = $pincode;
    }

    public function line(): string
    {
        return trim(implode(', ', array_filter([
            $this->addressLine1,
            $this->addressLine2,
        ])));
    }

    /**
     * @return array<string, string|null>
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'phone' => $this->phone,
            'email' => $this->email,
            'address_line_1' => $this->addressLine1,
            'address_line_2' => $this->addressLine2,
            'city' => $this->city,
            'state' => $this->state,
            'country' => $this->country,
            'pincode' => $this->pincode,
        ];
    }
}
