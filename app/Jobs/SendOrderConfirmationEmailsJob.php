<?php

namespace App\Jobs;

use App\Services\OrderConfirmationMailService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class SendOrderConfirmationEmailsJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 3;

    /** @var array<int, int> */
    public array $backoff = [60, 300, 900];

    public int $orderId;

    public function __construct(int $orderId)
    {
        $this->orderId = $orderId;
    }

    public function handle(OrderConfirmationMailService $service): void
    {
        $service->send($this->orderId);
    }

    public function failed(Throwable $exception): void
    {
        Log::error('Order confirmation email job permanently failed.', [
            'order_id' => $this->orderId,
            'message' => $exception->getMessage(),
        ]);
    }
}
