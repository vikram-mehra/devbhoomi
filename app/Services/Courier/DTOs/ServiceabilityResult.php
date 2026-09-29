<?php

namespace App\Services\Courier\DTOs;

class ServiceabilityResult
{
    public bool $serviceable;
    public ?string $message;
    /** @var array<string, mixed> */
    public array $rawResponse;

    /**
     * @param  array<string, mixed>  $rawResponse
     */
    public function __construct(bool $serviceable, ?string $message = null, array $rawResponse = [])
    {
        $this->serviceable = $serviceable;
        $this->message = $message;
        $this->rawResponse = $rawResponse;
    }
}
