<?php

namespace App\Services;

use App\Models\CartItem;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\ValidationException;

class CartService
{
    public function sessionKey(): string
    {
        return Session::getId();
    }

    public function count(): int
    {
        return $this->query()->sum('qty');
    }

    public function query()
    {
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
        $guestItems = CartItem::query()
            ->where('session_id', $this->sessionKey())
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
