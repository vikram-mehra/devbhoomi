<?php

namespace App\Console\Commands;

use App\Jobs\SendAbandonedCartReminderJob;
use App\Models\CartItem;
use App\Models\Order;
use App\Services\AbandonedCartReminderService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Throwable;

class SendAbandonedCartReminders extends Command
{
    protected $signature = 'app:send-abandoned-cart-reminders';

    protected $description = 'Queue reminders for opted-in customers with abandoned carts';

    public function handle(AbandonedCartReminderService $reminders): int
    {
        if (! config('abandoned_cart.enabled')) {
            $this->info('Abandoned-cart reminders are disabled.');

            return self::SUCCESS;
        }

        $cutoff = now()->subMinutes((int) config('abandoned_cart.delay_minutes', 60));
        $claimExpiresBefore = now()->subMinutes((int) config('abandoned_cart.claim_timeout_minutes', 120));
        $userIds = CartItem::query()
            ->whereNotNull('user_id')
            ->where('updated_at', '<=', $cutoff)
            ->distinct()
            ->orderBy('user_id')
            ->pluck('user_id');
        $queued = 0;

        foreach ($userIds as $userId) {
            $claim = $reminders->claim((int) $userId, $cutoff, $claimExpiresBefore);
            if ($claim === null) {
                continue;
            }

            try {
                SendAbandonedCartReminderJob::dispatch((int) $userId, $claim);
                $queued++;
            } catch (Throwable $exception) {
                $reminders->release((int) $userId, $claim);
                Log::error('Unable to dispatch abandoned-cart reminder.', [
                    'user_id' => (int) $userId,
                    'message' => $exception->getMessage(),
                ]);
                $this->error('Failed to dispatch an abandoned-cart reminder. See the application log.');

                return self::FAILURE;
            }
        }

        $orderIds = Order::query()
            ->whereIn('payment_status', ['pending', 'unpaid'])
            ->whereNotIn('status', ['cancelled', 'returned'])
            ->where('created_at', '<=', $cutoff)
            ->whereNull('abandoned_cart_reminder_sent_at')
            ->where(function ($query) use ($claimExpiresBefore) {
                $query->whereNull('abandoned_cart_reminder_claim')
                    ->orWhere('abandoned_cart_reminder_claimed_at', '<=', $claimExpiresBefore);
            })
            ->whereHas('items')
            ->orderBy('id')
            ->pluck('id');

        foreach ($orderIds as $orderId) {
            $claim = $reminders->claimOrder((int) $orderId, $cutoff, $claimExpiresBefore);
            if ($claim === null) {
                continue;
            }

            try {
                $order = Order::query()->findOrFail($orderId);
                SendAbandonedCartReminderJob::dispatch((int) $order->user_id, $claim, (int) $orderId);
                $queued++;
            } catch (Throwable $exception) {
                $reminders->releaseOrder((int) $orderId, $claim);
                Log::error('Unable to dispatch abandoned-checkout reminder.', [
                    'order_id' => (int) $orderId,
                    'message' => $exception->getMessage(),
                ]);
                $this->error('Failed to dispatch an abandoned-checkout reminder. See the application log.');

                return self::FAILURE;
            }
        }

        $this->info("Dispatched {$queued} abandoned-cart reminder(s).");

        return self::SUCCESS;
    }
}
