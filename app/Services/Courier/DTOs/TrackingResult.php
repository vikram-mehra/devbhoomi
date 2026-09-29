<?php

namespace App\Services\Courier\DTOs;

class TrackingResult
{
    public bool $success;
    public ?string $awb;
    public ?string $status;
    public ?string $courierStatus;
    public ?string $location;
    public ?string $message;
    /** @var list<array<string, mixed>> */
    public array $events;
    /** @var array<string, mixed> */
    public array $rawResponse;

    /**
     * @param  list<array<string, mixed>>  $events
     * @param  array<string, mixed>  $rawResponse
     */
    public function __construct(
        bool $success,
        ?string $awb = null,
        ?string $status = null,
        ?string $courierStatus = null,
        ?string $location = null,
        ?string $message = null,
        array $events = [],
        array $rawResponse = []
    ) {
        $this->success = $success;
        $this->awb = $awb;
        $this->status = $status;
        $this->courierStatus = $courierStatus;
        $this->location = $location;
        $this->message = $message;
        $this->events = $events;
        $this->rawResponse = $rawResponse;
    }
}
