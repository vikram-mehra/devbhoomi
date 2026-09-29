<?php

namespace App\Services;

use App\Jobs\SendOrderConfirmationEmailsJob;
use App\Mail\OrderPlacedAdminMail;
use App\Mail\OrderPlacedCustomerMail;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class OrderConfirmationMailService
{
    public function dispatchForOrder(Order $order): void
    {
        if ($order->payment_status !== 'paid') {
            return;
        }

        try {
            SendOrderConfirmationEmailsJob::dispatch($order->id);
        } catch (Throwable $e) {
            Log::error('Failed to queue order confirmation emails.', [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function send(int $orderId): void
    {
        try {
            DB::transaction(function () use ($orderId) {
                /** @var Order|null $order */
                $order = Order::query()
                    ->lockForUpdate()
                    ->with(['items', 'shippingAddress', 'address', 'user'])
                    ->find($orderId);

                if (! $order || $order->payment_status !== 'paid') {
                    return;
                }

                $dirty = false;

                $customerEmail = $order->customerDisplayEmail();
                if (! $order->customer_confirmation_sent_at && $customerEmail) {
                    Mail::to($customerEmail)->queue(new OrderPlacedCustomerMail($order));
                    $order->customer_confirmation_sent_at = now();
                    $dirty = true;
                }

                $adminEmail = trim((string) config('orders.admin_email', ''));
                if (! $order->admin_notification_sent_at) {
                    if ($adminEmail !== '') {
                        Mail::to($adminEmail)->queue(new OrderPlacedAdminMail($order));
                        $order->admin_notification_sent_at = now();
                        $dirty = true;
                    } else {
                        Log::warning('ADMIN_ORDER_EMAIL is not configured; admin order notification skipped.', [
                            'order_id' => $order->id,
                            'order_number' => $order->order_number,
                        ]);
                    }
                }

                if ($dirty) {
                    $order->save();
                }
            });
        } catch (Throwable $e) {
            Log::error('Order confirmation email dispatch failed.', [
                'order_id' => $orderId,
                'message' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}
