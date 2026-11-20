<?php

namespace App\Jobs\Courier;

use App\Services\Courier\CourierManager;
use App\Services\Courier\Exceptions\CourierAuthenticationException;
use App\Services\Courier\Exceptions\ShipmentCreationException;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CreateCourierShipmentJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;

    /** @var list<int> */
    public $backoff = [10, 30, 60];

    public $timeout = 90;

    public int $orderId;
    public string $courier;
    public bool $allowExisting;

    public function __construct(int $orderId, string $courier, bool $allowExisting = false)
    {
        $this->orderId = $orderId;
        $this->courier = $courier;
        $this->allowExisting = $allowExisting;
    }

    public function handle(CourierManager $manager): void
    {
        $manager->createShipmentForOrder($this->orderId, $this->courier, $this->allowExisting);
    }

    public function retryUntil()
    {
        return now()->addMinutes(15);
    }

    /**
     * @return bool
     */
    public function failed(\Throwable $e)
    {
        report($e);
    }

    /**
     * @param  \Throwable  $e
     */
    public function shouldRetry(\Throwable $e): bool
    {
        if ($e instanceof CourierAuthenticationException || $e instanceof ShipmentCreationException) {
            return false;
        }

        return true;
    }
}
