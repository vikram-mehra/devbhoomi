<?php

namespace App\Services\Courier\DTOs;

class RateResult
{
    public bool $success;
    public ?float $amount;
    public ?string $currency;
    public ?string $message;
    /** @var array<string, mixed> */
    public array $rawResponse;

    /**
     * @param  array<string, mixed>  $rawResponse
     */
    public function __construct(bool $success, ?float $amount = null, string $currency = 'INR', ?string $message = null, array $rawResponse = [])
    {
        $this->success = $success;
        $this->amount = $amount;
        $this->currency = $currency;
        $this->message = $message;
        $this->rawResponse = $rawResponse;
    }
}
