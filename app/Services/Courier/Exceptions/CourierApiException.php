<?php

namespace App\Services\Courier\Exceptions;

class CourierApiException extends CourierException
{
    /** @var int|null */
    public $httpStatus;

    public function __construct(string $message, ?int $httpStatus = null, ?\Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
        $this->httpStatus = $httpStatus;
    }
}
