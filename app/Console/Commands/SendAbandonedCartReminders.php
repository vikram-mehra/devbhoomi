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

    protected $description = 'Queue reminders for customers with eligible abandoned carts';

    public function handle(AbandonedCartReminderService $reminders): int
    {
        if (! config('abandoned_cart.enabled')) {
            $this->info('Abandoned-cart reminders are disabled.');

            return self::SUCCESS;
        }

        $stages = config('abandoned_cart.stages');
        $cutoffs = [];
        $scheduledAt = now();
        foreach ($stages as $stage => $delayMinutes) {
            $cutoffs[$stage] = $scheduledAt->copy()->subMinutes((int) $delayMinutes);
        }
        $cutoff = $cutoffs[1];
        $claimExpiresBefore = $scheduledAt->copy()
            ->subMinutes((int) config('abandoned_cart.claim_timeout_minutes', 120));
        $userIds = CartItem::query()
            ->whereNotNull('user_id')
            ->where('updated_at', '<=', $cutoff)
            ->where(function ($query) {
                $query->whereNull('abandoned_cart_reminder_sent_at')
                    ->orWhereNull('abandoned_cart_reminder_2_sent_at')
                    ->orWhereNull('abandoned_cart_reminder_3_sent_at');
            })
            ->distinct()
            ->orderBy('user_id')
            ->pluck('user_id');
        $queued = 0;

        foreach ($userIds as $userId) {
            foreach (array_keys($stages) as $stage) {
                $claim = $reminders->claim(
                    (int) $userId,
                    $cutoffs[$stage],
                    $claimExpiresBefore,
                    (int) $stage
                );
                if ($claim === null) {
                    continue;
                }

                try {
                    SendAbandonedCartReminderJob::dispatch((int) $userId, $claim, null, (int) $stage);
                    $queued++;
                } catch (Throwable $exception) {
                    $reminders->release((int) $userId, $claim);
                    Log::error('Unable to dispatch abandoned-cart reminder.', [
                        'user_id' => (int) $userId,
                        'stage' => (int) $stage,
                        'message' => $exception->getMessage(),
                    ]);
                    $this->error('Failed to dispatch an abandoned-cart reminder. See the application log.');

                    return self::FAILURE;
                }

                break;
            }
        }

        $orderIds = Order::query()
            ->whereIn('payment_status', ['pending', 'unpaid'])
            ->whereNotIn('status', ['cancelled', 'returned'])
            ->where('created_at', '<=', $cutoff)
            ->where(function ($query) {
                $query->whereNull('abandoned_cart_reminder_sent_at')
                    ->orWhereNull('abandoned_cart_reminder_2_sent_at')
                    ->orWhereNull('abandoned_cart_reminder_3_sent_at');
            })
            ->where(function ($query) use ($claimExpiresBefore) {
                $query->whereNull('abandoned_cart_reminder_claim')
                    ->orWhere('abandoned_cart_reminder_claimed_at', '<=', $claimExpiresBefore);
            })
            ->whereHas('items')
            ->orderBy('id')
            ->pluck('id');

        foreach ($orderIds as $orderId) {
            foreach (array_keys($stages) as $stage) {
                $claim = $reminders->claimOrder(
                    (int) $orderId,
                    $cutoffs[$stage],
                    $claimExpiresBefore,
                    (int) $stage
                );
                if ($claim === null) {
                    continue;
                }

                try {
                    $order = Order::query()->findOrFail($orderId);
                    SendAbandonedCartReminderJob::dispatch(
                        (int) $order->user_id,
                        $claim,
                        (int) $orderId,
                        (int) $stage
                    );
                    $queued++;
                } catch (Throwable $exception) {
                    $reminders->releaseOrder((int) $orderId, $claim);
                    Log::error('Unable to dispatch abandoned-checkout reminder.', [
                        'order_id' => (int) $orderId,
                        'stage' => (int) $stage,
                        'message' => $exception->getMessage(),
                    ]);
                    $this->error('Failed to dispatch an abandoned-checkout reminder. See the application log.');

                    return self::FAILURE;
                }

                break;
            }
        }

        $this->info("Dispatched {$queued} abandoned-cart reminder(s).");

        return self::SUCCESS;
    }
}
