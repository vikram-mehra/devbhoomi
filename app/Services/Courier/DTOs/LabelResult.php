<?php

namespace App\Services\Courier\DTOs;

class LabelResult
{
    public bool $success;
    public ?string $labelUrl;
    public ?string $message;
    /** @var array<string, mixed> */
    public array $rawResponse;

    /**
     * @param  array<string, mixed>  $rawResponse
     */
    public function __construct(bool $success, ?string $labelUrl = null, ?string $message = null, array $rawResponse = [])
    {
        $this->success = $success;
        $this->labelUrl = $labelUrl;
        $this->message = $message;
        $this->rawResponse = $rawResponse;
    }
}
