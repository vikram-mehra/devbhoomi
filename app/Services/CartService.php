<?php

namespace App\Services;

use App\Models\CartItem;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\ValidationException;

class CartService
{
    public const GUEST_SID_KEY = 'guest_cart_sid';
    public const MAX_QTY_PER_PRODUCT = 10;

    public function sessionKey(): string
    {
        return Session::getId();
    }

    public function rememberGuestSession(): void
    {
        if (Auth::check()) {
            return;
        }

        if (! Session::has(self::GUEST_SID_KEY)) {
            Session::put(self::GUEST_SID_KEY, $this->sessionKey());
        }
    }

    public function guestSessionId(): ?string
    {
        $sid = Session::get(self::GUEST_SID_KEY);

        return is_string($sid) && $sid !== '' ? $sid : null;
    }

    public function count(): int
    {
        return $this->query()->sum('qty');
    }

    public function query()
    {
        if (! Auth::check()) {
            $this->rememberGuestSession();
        }

        $q = CartItem::query()->with([
            'variant' => function ($vq) {
                $vq->with([
                    'product' => function ($pq) {
                        $pq->with(['images', 'flashSale']);
                    },
                ]);
            },
        ]);
        if (Auth::check()) {
            $q->where('user_id', Auth::id());
        } else {
            $q->where('session_id', $this->sessionKey());
        }

        return $q;
    }

    public function add(int $variantId, int $qty = 1): void
    {
        $variant = ProductVariant::findOrFail($variantId);
        $userId = Auth::id();
        if (! $userId) {
            $this->rememberGuestSession();
        }
        $sessionId = $userId ? null : $this->sessionKey();

        $row = CartItem::query()
            ->where('product_variant_id', $variantId)
            ->when($userId, fn ($q) => $q->where('user_id', $userId))
            ->when(! $userId, fn ($q) => $q->where('session_id', $sessionId))
            ->first();

        if ($row) {
            $nextQty = $row->qty + $qty;
            if ($nextQty > self::MAX_QTY_PER_PRODUCT) {
                $remaining = max(0, self::MAX_QTY_PER_PRODUCT - (int) $row->qty);
                $msg = $remaining > 0
                    ? __('You can only add :n more unit(s) of this product (maximum :max allowed).', ['n' => $remaining, 'max' => self::MAX_QTY_PER_PRODUCT])
                    : __('You already have the maximum limit of :max units of this product in your cart.', ['max' => self::MAX_QTY_PER_PRODUCT]);
                throw ValidationException::withMessages(['qty' => [$msg]]);
            }
            if ($nextQty > (int) $variant->stock_qty) {
                throw ValidationException::withMessages([
                    'qty' => [__('Not enough stock. You can add up to :n for this option.', ['n' => $variant->stock_qty])],
                ]);
            }
            $row->update(['qty' => $nextQty]);
        } else {
            if ($qty > self::MAX_QTY_PER_PRODUCT) {
                throw ValidationException::withMessages([
                    'qty' => [__('You can add a maximum of :max units of this product.', ['max' => self::MAX_QTY_PER_PRODUCT])],
                ]);
            }
            CartItem::create([
                'user_id' => $userId,
                'session_id' => $sessionId,
                'product_variant_id' => $variantId,
                'qty' => $qty,
            ]);
        }

        if ($userId) {
            $this->recordUserActivity((int) $userId);
        }
    }

    public function recordUserActivity(int $userId): void
    {
        CartItem::query()
            ->where('user_id', $userId)
            ->update([
                'updated_at' => now(),
                'abandoned_cart_reminder_claim' => null,
                'abandoned_cart_reminder_claimed_at' => null,
                'abandoned_cart_reminder_sent_at' => null,
                'abandoned_cart_reminder_2_sent_at' => null,
                'abandoned_cart_reminder_3_sent_at' => null,
            ]);
    }

    public function mergeGuestCart(): void
    {
        if (! Auth::check()) {
            return;
        }

        $userId = Auth::id();
        $guestSids = array_values(array_unique(array_filter([
            $this->guestSessionId(),
            $this->sessionKey(),
        ])));
        Session::forget(self::GUEST_SID_KEY);

        if ($guestSids === []) {
            return;
        }

        $guestItems = CartItem::query()
            ->whereIn('session_id', $guestSids)
            ->whereNull('user_id')
            ->get();

        foreach ($guestItems as $guest) {
            $existing = CartItem::query()
                ->where('user_id', $userId)
                ->where('product_variant_id', $guest->product_variant_id)
                ->first();

            if ($existing) {
                $mergedQty = min(self::MAX_QTY_PER_PRODUCT, $existing->qty + $guest->qty);
                $existing->update(['qty' => $mergedQty]);
                $guest->delete();
            } else {
                $initialQty = min(self::MAX_QTY_PER_PRODUCT, (int) $guest->qty);
                $guest->update(['user_id' => $userId, 'session_id' => null, 'qty' => $initialQty]);
            }
        }

        if ($guestItems->isNotEmpty()) {
            $this->recordUserActivity((int) $userId);
        }
    }
}
