<?php

namespace App\Services;

use App\Mail\AbandonedCartReminderMail;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class AbandonedCartReminderService
{
    public function claim(int $userId, Carbon $cutoff, Carbon $claimExpiresBefore): ?string
    {
        if (! config('abandoned_cart.enabled')) {
            return null;
        }

        return DB::transaction(function () use ($userId, $cutoff, $claimExpiresBefore) {
            $user = User::query()->whereKey($userId)->lockForUpdate()->first();
            if (! $this->canReceive($user)) {
                return null;
            }

            $items = CartItem::query()
                ->where('user_id', $userId)
                ->lockForUpdate()
                ->get();

            if ($items->isEmpty() || $items->contains(function (CartItem $item) use ($cutoff, $claimExpiresBefore) {
                return $item->updated_at === null
                    || $item->updated_at->gt($cutoff)
                    || $item->abandoned_cart_reminder_sent_at !== null
                    || ($item->abandoned_cart_reminder_claim !== null
                        && ($item->abandoned_cart_reminder_claimed_at === null
                            || $item->abandoned_cart_reminder_claimed_at->gt($claimExpiresBefore)));
            })) {
                return null;
            }

            $lastActivity = $items->max('updated_at');
            if ($this->hasCompletedOrderSince($userId, $lastActivity)) {
                return null;
            }

            $claim = (string) Str::uuid();
            DB::table('cart_items')
                ->where('user_id', $userId)
                ->update([
                    'abandoned_cart_reminder_claim' => $claim,
                    'abandoned_cart_reminder_claimed_at' => now(),
                ]);

            return $claim;
        });
    }

    public function send(int $userId, string $claim): bool
    {
        $user = User::query()->find($userId);
        if (! $this->canReceive($user)) {
            $this->release($userId, $claim);

            return false;
        }

        $items = CartItem::query()
            ->where('user_id', $userId)
            ->with(['variant.product.flashSale', 'variant.galleryImages'])
            ->get();

        if ($items->isEmpty()
            || $items->contains(function (CartItem $item) use ($claim) {
                return $item->abandoned_cart_reminder_claim !== $claim
                    || $item->abandoned_cart_reminder_sent_at !== null;
            })) {
            $this->release($userId, $claim);

            return false;
        }

        $lastActivity = $items->max('updated_at');
        $cutoff = now()->subMinutes((int) config('abandoned_cart.delay_minutes', 60));
        if ($lastActivity === null
            || $lastActivity->gt($cutoff)
            || $this->hasCompletedOrderSince($userId, $lastActivity)) {
            $this->release($userId, $claim);

            return false;
        }

        $validItems = $items->filter(function (CartItem $item) {
            return $item->qty > 0 && $item->variant && $item->variant->product;
        })->values();

        if ($validItems->isEmpty()) {
            $this->release($userId, $claim);

            return false;
        }

        $subtotal = round($validItems->sum(function (CartItem $item) {
            return $item->variant->effectivePrice() * $item->qty;
        }), 2);
        $emailItems = $validItems->map(function (CartItem $item) {
            $unitPrice = $item->variant->effectivePrice();

            return [
                'name' => $item->variant->product->name,
                'image_url' => $item->variant->variantImageUrl(),
                'variant_label' => $item->variant->label(),
                'qty' => $item->qty,
                'line_total' => round($unitPrice * $item->qty, 2),
            ];
        });

        Mail::to($user->email)->send(new AbandonedCartReminderMail(
            $user,
            $emailItems,
            $subtotal,
            route('cart.index')
        ));

        DB::table('cart_items')
            ->where('user_id', $userId)
            ->where('abandoned_cart_reminder_claim', $claim)
            ->update([
                'abandoned_cart_reminder_claim' => null,
                'abandoned_cart_reminder_claimed_at' => null,
                'abandoned_cart_reminder_sent_at' => now(),
            ]);

        return true;
    }

    public function claimOrder(int $orderId, Carbon $cutoff, Carbon $claimExpiresBefore): ?string
    {
        if (! config('abandoned_cart.enabled')) {
            return null;
        }

        return DB::transaction(function () use ($orderId, $cutoff, $claimExpiresBefore) {
            $order = Order::query()->whereKey($orderId)->lockForUpdate()->first();
            if (! $order || ! $this->isUnpaidOrderEligible($order) || $order->created_at->gt($cutoff)) {
                return null;
            }

            $user = User::query()->whereKey($order->user_id)->lockForUpdate()->first();
            if (! $this->canReceive($user)
                || $order->abandoned_cart_reminder_sent_at !== null
                || ($order->abandoned_cart_reminder_claim !== null
                    && ($order->abandoned_cart_reminder_claimed_at === null
                        || $order->abandoned_cart_reminder_claimed_at->gt($claimExpiresBefore)))
                || ! $order->items()->exists()) {
                return null;
            }

            $claim = (string) Str::uuid();
            DB::table('orders')
                ->where('id', $orderId)
                ->update([
                    'abandoned_cart_reminder_claim' => $claim,
                    'abandoned_cart_reminder_claimed_at' => now(),
                ]);

            return $claim;
        });
    }

    public function sendOrder(int $orderId, string $claim): bool
    {
        $order = Order::query()->with(['user', 'items'])->find($orderId);
        if (! $order
            || ! $this->canReceive($order->user)
            || ! $this->isUnpaidOrderEligible($order)
            || $order->abandoned_cart_reminder_claim !== $claim
            || $order->abandoned_cart_reminder_sent_at !== null
            || $order->created_at->gt(now()->subMinutes((int) config('abandoned_cart.delay_minutes', 60)))
            || $order->items->isEmpty()) {
            $this->releaseOrder($orderId, $claim);

            return false;
        }

        $emailItems = $order->items->map(function (OrderItem $item) {
            return [
                'name' => $item->product_name,
                'image_url' => $item->product_image,
                'variant_label' => $item->variant_label,
                'qty' => $item->qty,
                'line_total' => (float) $item->line_total,
            ];
        });
        $subtotal = round($emailItems->sum('line_total'), 2);

        Mail::to($order->user->email)->send(new AbandonedCartReminderMail(
            $order->user,
            $emailItems,
            $subtotal,
            route('pay.razorpay', $order),
            (float) $order->total
        ));

        DB::table('orders')
            ->where('id', $orderId)
            ->where('abandoned_cart_reminder_claim', $claim)
            ->update([
                'abandoned_cart_reminder_claim' => null,
                'abandoned_cart_reminder_claimed_at' => null,
                'abandoned_cart_reminder_sent_at' => now(),
            ]);

        return true;
    }

    public function release(int $userId, string $claim): void
    {
        DB::table('cart_items')
            ->where('user_id', $userId)
            ->where('abandoned_cart_reminder_claim', $claim)
            ->update([
                'abandoned_cart_reminder_claim' => null,
                'abandoned_cart_reminder_claimed_at' => null,
            ]);
    }

    public function releaseOrder(int $orderId, string $claim): void
    {
        DB::table('orders')
            ->where('id', $orderId)
            ->where('abandoned_cart_reminder_claim', $claim)
            ->update([
                'abandoned_cart_reminder_claim' => null,
                'abandoned_cart_reminder_claimed_at' => null,
            ]);
    }

    protected function isUnpaidOrderEligible(Order $order): bool
    {
        return $order->payment_method === 'razorpay'
            && in_array($order->payment_status, ['pending', 'unpaid'], true)
            && ! in_array($order->status, ['cancelled', 'returned'], true)
            && ((float) $order->total - (float) $order->wallet_used) > 0;
    }

    protected function canReceive(?User $user): bool
    {
        return config('abandoned_cart.enabled')
            && $user !== null
            && filter_var(trim((string) $user->email), FILTER_VALIDATE_EMAIL) !== false;
    }

    protected function hasCompletedOrderSince(int $userId, ?Carbon $lastActivity): bool
    {
        if ($lastActivity === null) {
            return false;
        }

        return Order::query()
            ->where('user_id', $userId)
            ->where('payment_status', 'paid')
            ->whereNotIn('status', ['cancelled', 'returned'])
            ->where(function ($query) use ($lastActivity) {
                $query->where('created_at', '>=', $lastActivity)
                    ->orWhere('updated_at', '>=', $lastActivity);
            })
            ->exists();
    }
}
