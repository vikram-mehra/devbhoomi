<?php

namespace App\Jobs;

use App\Services\AbandonedCartReminderService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class SendAbandonedCartReminderJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 3;

    /** @var array<int, int> */
    public array $backoff = [60, 300, 900];

    public int $userId;

    public string $claim;

    public ?int $orderId;

    public function __construct(int $userId, string $claim, ?int $orderId = null)
    {
        $this->userId = $userId;
        $this->claim = $claim;
        $this->orderId = $orderId;
    }

    public function handle(AbandonedCartReminderService $reminders): void
    {
        if ($this->orderId !== null) {
            $reminders->sendOrder($this->orderId, $this->claim);

            return;
        }

        $reminders->send($this->userId, $this->claim);
    }

    public function failed(Throwable $exception): void
    {
        $reminders = app(AbandonedCartReminderService::class);
        if ($this->orderId !== null) {
            $reminders->releaseOrder($this->orderId, $this->claim);
        } else {
            $reminders->release($this->userId, $this->claim);
        }

        Log::error('Abandoned-cart reminder job permanently failed.', [
            'user_id' => $this->userId,
            'message' => $exception->getMessage(),
        ]);
    }
}
