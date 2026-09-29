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
            if ($nextQty > (int) $variant->stock_qty) {
                throw ValidationException::withMessages([
                    'qty' => [__('Not enough stock. You can add up to :n for this option.', ['n' => $variant->stock_qty])],
                ]);
            }
            $row->update(['qty' => $nextQty]);
        } else {
            CartItem::create([
                'user_id' => $userId,
                'session_id' => $sessionId,
                'product_variant_id' => $variantId,
                'qty' => $qty,
            ]);
        }
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
                $existing->update(['qty' => $existing->qty + $guest->qty]);
                $guest->delete();
            } else {
                $guest->update(['user_id' => $userId, 'session_id' => null]);
            }
        }
    }
}
