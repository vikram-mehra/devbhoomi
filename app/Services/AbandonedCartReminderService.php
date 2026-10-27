<?php

namespace App\Services;

use App\Mail\AbandonedCartReminderMail;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ProductVariant;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class AbandonedCartReminderService
{
    public function claim(
        int $userId,
        Carbon $cutoff,
        Carbon $claimExpiresBefore,
        int $stage = 1
    ): ?string {
        if (! config('abandoned_cart.enabled')) {
            return null;
        }

        $sentColumn = $this->sentColumn($stage);

        return DB::transaction(function () use ($userId, $cutoff, $claimExpiresBefore, $sentColumn) {
            $user = User::query()->whereKey($userId)->lockForUpdate()->first();
            if (! $this->canReceive($user)) {
                return null;
            }

            $items = CartItem::query()
                ->where('user_id', $userId)
                ->with('variant.product')
                ->lockForUpdate()
                ->get();

            if ($items->isEmpty() || $items->contains(function (CartItem $item) use ($cutoff, $claimExpiresBefore, $sentColumn) {
                return $item->updated_at === null
                    || $item->updated_at->gt($cutoff)
                    || $item->{$sentColumn} !== null
                    || ($item->abandoned_cart_reminder_claim !== null
                        && ($item->abandoned_cart_reminder_claimed_at === null
                            || $item->abandoned_cart_reminder_claimed_at->gt($claimExpiresBefore)));
            })) {
                return null;
            }

            $validItems = $items->filter(function (CartItem $item) {
                return $this->isEligibleProduct($item->variant, (int) $item->qty);
            });
            if ($validItems->isEmpty()) {
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

    public function send(int $userId, string $claim, int $stage = 1): bool
    {
        $sentColumn = $this->sentColumn($stage);

        return DB::transaction(function () use ($userId, $claim, $stage, $sentColumn) {
            return $this->sendClaimedCart($userId, $claim, $stage, $sentColumn);
        });
    }

    protected function sendClaimedCart(int $userId, string $claim, int $stage, string $sentColumn): bool
    {
        $user = User::query()->whereKey($userId)->lockForUpdate()->first();
        if (! $this->canReceive($user)) {
            $this->release($userId, $claim);

            return false;
        }

        $items = CartItem::query()
            ->where('user_id', $userId)
            ->with(['variant.product.flashSale', 'variant.galleryImages'])
            ->lockForUpdate()
            ->get();

        if ($items->isEmpty()
            || $items->contains(function (CartItem $item) use ($claim, $sentColumn) {
                return $item->abandoned_cart_reminder_claim !== $claim
                    || $item->{$sentColumn} !== null;
            })) {
            $this->release($userId, $claim);

            return false;
        }

        $lastActivity = $items->max('updated_at');
        $cutoff = now()->subMinutes($this->delayMinutes($stage));
        if ($lastActivity === null
            || $lastActivity->gt($cutoff)
            || $this->hasCompletedOrderSince($userId, $lastActivity)) {
            $this->release($userId, $claim);

            return false;
        }

        $validItems = $items->filter(function (CartItem $item) {
            return $this->isEligibleProduct($item->variant, (int) $item->qty);
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

        if (! $this->canReceive($user)
            || $this->hasCompletedOrderSince($userId, $lastActivity)
            || CartItem::query()
                ->where('user_id', $userId)
                ->where('abandoned_cart_reminder_claim', $claim)
                ->whereNull($sentColumn)
                ->count() !== $items->count()) {
            $this->release($userId, $claim);

            return false;
        }

        Mail::to($user->email)->send(new AbandonedCartReminderMail(
            $user,
            $emailItems,
            $subtotal,
            route('cart.index'),
            null,
            $stage
        ));

        DB::table('cart_items')
            ->where('user_id', $userId)
            ->where('abandoned_cart_reminder_claim', $claim)
            ->whereNull($sentColumn)
            ->update([
                'abandoned_cart_reminder_claim' => null,
                'abandoned_cart_reminder_claimed_at' => null,
                $sentColumn => now(),
            ]);

        return true;
    }

    public function claimOrder(
        int $orderId,
        Carbon $cutoff,
        Carbon $claimExpiresBefore,
        int $stage = 1
    ): ?string {
        if (! config('abandoned_cart.enabled')) {
            return null;
        }

        $sentColumn = $this->sentColumn($stage);

        return DB::transaction(function () use ($orderId, $cutoff, $claimExpiresBefore, $sentColumn) {
            $order = Order::query()
                ->with('items.variant.product')
                ->whereKey($orderId)
                ->lockForUpdate()
                ->first();
            if (! $order || ! $this->isUnpaidOrderEligible($order) || $order->created_at->gt($cutoff)) {
                return null;
            }

            $user = User::query()->whereKey($order->user_id)->lockForUpdate()->first();
            if (! $this->canReceive($user)
                || $order->{$sentColumn} !== null
                || ($order->abandoned_cart_reminder_claim !== null
                    && ($order->abandoned_cart_reminder_claimed_at === null
                        || $order->abandoned_cart_reminder_claimed_at->gt($claimExpiresBefore)))
                || $order->items->filter(function (OrderItem $item) {
                    return $this->isEligibleProduct($item->variant, (int) $item->qty);
                })->isEmpty()
                || $this->hasCompletedOrderSince((int) $order->user_id, $order->created_at)) {
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

    public function sendOrder(int $orderId, string $claim, int $stage = 1): bool
    {
        $sentColumn = $this->sentColumn($stage);

        return DB::transaction(function () use ($orderId, $claim, $stage, $sentColumn) {
            return $this->sendClaimedOrder($orderId, $claim, $stage, $sentColumn);
        });
    }

    protected function sendClaimedOrder(int $orderId, string $claim, int $stage, string $sentColumn): bool
    {
        $order = Order::query()
            ->with('items.variant.product')
            ->whereKey($orderId)
            ->lockForUpdate()
            ->first();
        $user = $order
            ? User::query()->whereKey($order->user_id)->lockForUpdate()->first()
            : null;
        if (! $order
            || ! $this->canReceive($user)
            || ! $this->isUnpaidOrderEligible($order)
            || $order->abandoned_cart_reminder_claim !== $claim
            || $order->{$sentColumn} !== null
            || $order->created_at->gt(now()->subMinutes($this->delayMinutes($stage)))
            || $order->items->filter(function (OrderItem $item) {
                return $this->isEligibleProduct($item->variant, (int) $item->qty);
            })->isEmpty()
            || $this->hasCompletedOrderSince((int) $order->user_id, $order->created_at)) {
            $this->releaseOrder($orderId, $claim);

            return false;
        }

        $emailItems = $order->items->filter(function (OrderItem $item) {
            return $this->isEligibleProduct($item->variant, (int) $item->qty);
        })->map(function (OrderItem $item) {
            return [
                'name' => $item->product_name,
                'image_url' => $item->product_image,
                'variant_label' => $item->variant_label,
                'qty' => $item->qty,
                'line_total' => (float) $item->line_total,
            ];
        });
        $subtotal = round($emailItems->sum('line_total'), 2);

        if (! $this->canReceive($user)
            || ! $this->isUnpaidOrderEligible($order)
            || $order->abandoned_cart_reminder_claim !== $claim
            || $order->{$sentColumn} !== null
            || $this->hasCompletedOrderSince((int) $order->user_id, $order->created_at)) {
            $this->releaseOrder($orderId, $claim);

            return false;
        }

        Mail::to($user->email)->send(new AbandonedCartReminderMail(
            $user,
            $emailItems,
            $subtotal,
            route('pay.razorpay', $order),
            (float) $order->total,
            $stage
        ));

        DB::table('orders')
            ->where('id', $orderId)
            ->where('abandoned_cart_reminder_claim', $claim)
            ->whereNull($sentColumn)
            ->update([
                'abandoned_cart_reminder_claim' => null,
                'abandoned_cart_reminder_claimed_at' => null,
                $sentColumn => now(),
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

    protected function isEligibleProduct(?ProductVariant $variant, int $qty): bool
    {
        return $qty > 0
            && $variant !== null
            && $variant->product !== null
            && $variant->product->is_active
            && $variant->isBuyable()
            && $qty <= (int) $variant->stock_qty;
    }

    protected function delayMinutes(int $stage): int
    {
        $delays = config('abandoned_cart.stages', []);
        if (! isset($delays[$stage])) {
            throw new \InvalidArgumentException("Unknown abandoned-cart reminder stage: {$stage}");
        }

        return (int) $delays[$stage];
    }

    protected function sentColumn(int $stage): string
    {
        $columns = [
            1 => 'abandoned_cart_reminder_sent_at',
            2 => 'abandoned_cart_reminder_2_sent_at',
            3 => 'abandoned_cart_reminder_3_sent_at',
        ];
        if (! isset($columns[$stage])) {
            throw new \InvalidArgumentException("Unknown abandoned-cart reminder stage: {$stage}");
        }

        return $columns[$stage];
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
