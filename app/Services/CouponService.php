<?php

namespace App\Services;

use App\Models\Coupon;
use App\Models\CouponUsage;
use App\Models\Order;
use App\Models\Sale;
use App\Models\User;
use App\Support\MenuItemTree;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CouponService
{
    public function normalizeCode(?string $raw): string
    {
        if ($raw === null) {
            return '';
        }
        $s = trim($raw);
        if ($s === '') {
            return '';
        }

        return strtoupper(trim(ltrim($s, '#')));
    }

    public function guestKey(?User $user = null, ?string $email = null, ?string $phone = null): ?string
    {
        $email = strtolower(trim((string) ($email ?: $user?->email)));
        if ($email !== '') {
            return 'email:'.$email;
        }

        $phone = preg_replace('/\D+/', '', (string) ($phone ?: $user?->phone));
        if ($phone !== '') {
            return 'phone:'.$phone;
        }

        return null;
    }

    /**
     * @param  array{
     *     user?: ?User,
     *     items?: iterable|null,
     *     exclude_order_id?: int|null,
     *     exclude_sale_id?: int|null,
     *     guest_key?: string|null,
     *     lock?: bool
     * }  $context
     * @return array{coupon: Coupon, discount: float, eligible_amount: float}
     */
    public function resolveForCart(string $rawCode, float $subtotal, array $context = []): array
    {
        $normalized = $this->normalizeCode($rawCode);
        if ($normalized === '') {
            throw ValidationException::withMessages([
                'coupon_code' => __('Please enter a coupon code.'),
            ]);
        }

        $query = Coupon::query()->whereRaw('UPPER(code) = ?', [$normalized]);
        if (! empty($context['lock'])) {
            $query->lockForUpdate();
        }

        $coupon = $query->first();

        if (! $coupon) {
            throw ValidationException::withMessages([
                'coupon_code' => __('This coupon code is not valid.'),
            ]);
        }

        $coupon->loadMissing(['products:id,menu_item_id', 'menuItems:id']);
        $eligible = $this->eligibleAmount($coupon, $subtotal, $context['items'] ?? null);
        $this->assertCouponUsable($coupon, $subtotal, $context, $eligible);

        return [
            'coupon' => $coupon,
            'discount' => $this->calculateDiscount($coupon, $eligible),
            'eligible_amount' => $eligible,
        ];
    }

    /**
     * @param  array<string, mixed>  $context
     */
    public function assertCouponUsable(Coupon $coupon, float $subtotal, array $context = [], ?float $eligibleAmount = null): void
    {
        if (! $coupon->is_active) {
            throw ValidationException::withMessages([
                'coupon_code' => __('This coupon is no longer active.'),
            ]);
        }

        if ($coupon->starts_at && $coupon->starts_at->isFuture()) {
            throw ValidationException::withMessages([
                'coupon_code' => __('This coupon is not valid yet.'),
            ]);
        }

        if ($coupon->ends_at && $coupon->ends_at->isPast()) {
            throw ValidationException::withMessages([
                'coupon_code' => __('This coupon has expired.'),
            ]);
        }

        if ($coupon->usage_limit !== null) {
            $held = $this->globalHeldCount($coupon, $context);
            if ($held >= (int) $coupon->usage_limit) {
                throw ValidationException::withMessages([
                    'coupon_code' => __('This coupon has reached its usage limit.'),
                ]);
            }
        }

        if ($coupon->per_user_limit !== null) {
            $user = $context['user'] ?? null;
            $guestKey = $context['guest_key'] ?? null;
            if (! $user && ! $guestKey) {
                throw ValidationException::withMessages([
                    'coupon_code' => __('Please sign in to use this coupon.'),
                ]);
            }
            if ($coupon->exists && $this->customerHeldCount($coupon, $context) >= (int) $coupon->per_user_limit) {
                throw ValidationException::withMessages([
                    'coupon_code' => __('You have already used this coupon the maximum number of times.'),
                ]);
            }
        }

        if ((float) $coupon->min_cart > $subtotal) {
            throw ValidationException::withMessages([
                'coupon_code' => __('Your cart must be at least ₹:amount to use this coupon.', [
                    'amount' => number_format((float) $coupon->min_cart, 0),
                ]),
            ]);
        }

        if ($coupon->first_order_only) {
            $user = $context['user'] ?? null;
            $guestKey = $context['guest_key'] ?? null;
            if (! $user && ! $guestKey) {
                throw ValidationException::withMessages([
                    'coupon_code' => __('This coupon is only for your first order. Please sign in.'),
                ]);
            }
            if ($coupon->exists && $this->customerHasPriorPaidOrder($context)) {
                throw ValidationException::withMessages([
                    'coupon_code' => __('This coupon is only valid on your first order.'),
                ]);
            }
        }

        $eligible = $eligibleAmount;
        if ($eligible === null && array_key_exists('items', $context)) {
            $eligible = $this->eligibleAmount($coupon, $subtotal, $context['items']);
        }

        if ($eligible !== null && $eligible <= 0 && $coupon->hasProductRestrictions() && ($context['items'] ?? null) !== null) {
            throw ValidationException::withMessages([
                'coupon_code' => __('This coupon is not valid for the products in your cart.'),
            ]);
        }
    }

    public function calculateDiscount(Coupon $coupon, float $subtotal): float
    {
        if ($subtotal <= 0) {
            return 0.0;
        }

        if ($coupon->type === 'percent') {
            $discount = round($subtotal * ((float) $coupon->value / 100), 2);
            if ($coupon->max_discount) {
                $discount = min($discount, (float) $coupon->max_discount);
            }

            return round(min($discount, $subtotal), 2);
        }

        return round(min((float) $coupon->value, $subtotal), 2);
    }

    /**
     * @return array<string, mixed>
     */
    public function toPublicPayload(Coupon $coupon): array
    {
        return [
            'code' => $coupon->code,
            'coupon_type' => $coupon->coupon_type,
            'discount_type' => $coupon->type,
            'value' => (float) $coupon->value,
            'discount_label' => $coupon->discountLabel(),
            'min_cart' => (float) $coupon->min_cart,
            'max_discount' => $coupon->max_discount ? (float) $coupon->max_discount : null,
            'starts_at' => optional($coupon->starts_at)?->toIso8601String(),
            'ends_at' => optional($coupon->ends_at)?->toIso8601String(),
            'usage_limit' => $coupon->usage_limit,
            'used_count' => $coupon->used_count,
            'per_user_limit' => $coupon->per_user_limit,
            'first_order_only' => (bool) $coupon->first_order_only,
        ];
    }

    /**
     * @param  array<string, mixed>  $context
     */
    public function reserveForOrder(Coupon $coupon, Order $order, float $discount, array $context = []): CouponUsage
    {
        return DB::transaction(function () use ($coupon, $order, $discount, $context) {
            $locked = Coupon::query()->where('id', $coupon->id)->lockForUpdate()->firstOrFail();
            $context['exclude_order_id'] = $order->id;
            $context['lock'] = true;
            $context['user'] = $context['user'] ?? $order->user;
            $context['guest_key'] = $context['guest_key'] ?? $this->guestKey(
                $order->user,
                $order->customer_email,
                $order->customer_phone
            );
            if (! array_key_exists('items', $context)) {
                $context['items'] = $order->relationLoaded('items') ? $order->items : null;
            }

            $subtotal = (float) ($context['cart_subtotal'] ?? ((float) $order->subtotal + (float) $order->tax_amount));
            $eligible = $this->eligibleAmount($locked, $subtotal, $context['items'] ?? null);
            $this->assertCouponUsable($locked, $subtotal, $context, $eligible);

            return $this->upsertUsage(
                $locked,
                [
                    'order_id' => $order->id,
                    'sale_id' => null,
                    'user_id' => $order->user_id,
                    'guest_key' => $context['guest_key'],
                    'discount_amount' => $discount,
                ],
                CouponUsage::STATUS_RESERVED
            );
        });
    }

    public function redeemForOrder(Order $order): void
    {
        if (! $order->coupon_code) {
            return;
        }

        DB::transaction(function () use ($order) {
            $coupon = Coupon::query()
                ->whereRaw('UPPER(code) = ?', [$this->normalizeCode($order->coupon_code)])
                ->lockForUpdate()
                ->first();
            if (! $coupon) {
                return;
            }

            $this->upsertUsage(
                $coupon,
                [
                    'order_id' => $order->id,
                    'sale_id' => null,
                    'user_id' => $order->user_id,
                    'guest_key' => $this->guestKey($order->user, $order->customer_email, $order->customer_phone),
                    'discount_amount' => (float) $order->discount,
                ],
                CouponUsage::STATUS_REDEEMED
            );
        });
    }

    public function releaseForOrder(Order $order, bool $releaseRedeemed = false): void
    {
        if (! $order->coupon_code) {
            return;
        }

        DB::transaction(function () use ($order, $releaseRedeemed) {
            $usage = CouponUsage::query()
                ->where('order_id', $order->id)
                ->lockForUpdate()
                ->first();
            if (! $usage) {
                return;
            }

            $this->releaseUsageRow($usage, $releaseRedeemed);
        });
    }

    /**
     * @param  array<string, mixed>  $context
     */
    public function reserveForSale(Coupon $coupon, Sale $sale, float $discount, array $context = []): CouponUsage
    {
        return DB::transaction(function () use ($coupon, $sale, $discount, $context) {
            $locked = Coupon::query()->where('id', $coupon->id)->lockForUpdate()->firstOrFail();
            $context['exclude_sale_id'] = $sale->id;
            $context['guest_key'] = $context['guest_key'] ?? $this->guestKey(
                null,
                $sale->customer_email,
                $sale->customer_phone
            );
            if (! array_key_exists('items', $context)) {
                $context['items'] = $sale->relationLoaded('items') ? $sale->items : null;
            }
            $this->assertCouponUsable($locked, (float) $sale->subtotal, $context);

            return $this->upsertUsage(
                $locked,
                [
                    'order_id' => null,
                    'sale_id' => $sale->id,
                    'user_id' => null,
                    'guest_key' => $context['guest_key'],
                    'discount_amount' => $discount,
                ],
                CouponUsage::STATUS_RESERVED
            );
        });
    }

    public function redeemForSale(Sale $sale): void
    {
        if (! $sale->coupon_code) {
            return;
        }

        DB::transaction(function () use ($sale) {
            $coupon = Coupon::query()
                ->whereRaw('UPPER(code) = ?', [$this->normalizeCode($sale->coupon_code)])
                ->lockForUpdate()
                ->first();
            if (! $coupon) {
                return;
            }

            $this->upsertUsage(
                $coupon,
                [
                    'order_id' => null,
                    'sale_id' => $sale->id,
                    'user_id' => null,
                    'guest_key' => $this->guestKey(null, $sale->customer_email, $sale->customer_phone),
                    'discount_amount' => (float) $sale->discount_amount,
                ],
                CouponUsage::STATUS_REDEEMED
            );
        });
    }

    public function releaseForSale(Sale $sale, bool $releaseRedeemed = false): void
    {
        if (! $sale->coupon_code) {
            return;
        }

        DB::transaction(function () use ($sale, $releaseRedeemed) {
            $usage = CouponUsage::query()
                ->where('sale_id', $sale->id)
                ->lockForUpdate()
                ->first();
            if (! $usage) {
                return;
            }

            $this->releaseUsageRow($usage, $releaseRedeemed);
        });
    }

    public function syncUsageWithOrder(Order $order): void
    {
        if (! $order->coupon_code) {
            return;
        }

        if (
            in_array($order->status, ['cancelled', 'returned'], true)
            || in_array($order->payment_status, ['failed', 'refunded'], true)
        ) {
            $this->releaseForOrder($order, true);

            return;
        }

        if ($order->payment_status === 'paid') {
            $this->redeemForOrder($order);

            return;
        }

        $coupon = Coupon::query()
            ->whereRaw('UPPER(code) = ?', [$this->normalizeCode($order->coupon_code)])
            ->first();
        if (! $coupon) {
            return;
        }

        $this->reserveForOrder($coupon, $order, (float) $order->discount, [
            'user' => $order->user,
            'items' => $order->relationLoaded('items') ? $order->items : $order->items()->with('variant.product')->get(),
            'guest_key' => $this->guestKey($order->user, $order->customer_email, $order->customer_phone),
        ]);
    }

    public function syncUsageWithSale(Sale $sale): void
    {
        if (! $sale->coupon_code) {
            return;
        }

        if ($sale->order_status === 'cancelled' || $sale->payment_status === 'refunded') {
            $this->releaseForSale($sale, true);

            return;
        }

        if ($sale->payment_status === 'paid') {
            $this->redeemForSale($sale);

            return;
        }

        $coupon = Coupon::query()
            ->whereRaw('UPPER(code) = ?', [$this->normalizeCode($sale->coupon_code)])
            ->first();
        if (! $coupon) {
            return;
        }

        $this->reserveForSale($coupon, $sale, (float) $sale->discount_amount, [
            'items' => $sale->relationLoaded('items') ? $sale->items : $sale->items()->with('product')->get(),
            'guest_key' => $this->guestKey(null, $sale->customer_email, $sale->customer_phone),
        ]);
    }

    /**
     * Successful redemption only. Applying a coupon to a cart does not call this.
     */
    public function markUsed(?string $couponCode, $subject = null): void
    {
        if ($subject instanceof Order) {
            $this->redeemForOrder($subject);

            return;
        }

        if ($subject instanceof Sale) {
            $this->redeemForSale($subject);
        }
    }

    public function releaseUsage(?string $couponCode, $subject = null): void
    {
        if ($subject instanceof Order) {
            $this->releaseForOrder($subject, true);

            return;
        }

        if ($subject instanceof Sale) {
            $this->releaseForSale($subject, true);
        }
    }

    /**
     * @param  iterable<mixed>|null  $items
     */
    public function eligibleAmount(Coupon $coupon, float $subtotal, $items = null): float
    {
        if ($items === null || ! $coupon->hasProductRestrictions()) {
            return round(max(0, $subtotal), 2);
        }

        $productIds = $coupon->relationLoaded('products')
            ? $coupon->products->pluck('id')->map(fn ($id) => (int) $id)->all()
            : $coupon->products()->pluck('products.id')->map(fn ($id) => (int) $id)->all();

        $menuIds = [];
        $menus = $coupon->relationLoaded('menuItems') ? $coupon->menuItems : $coupon->menuItems()->get(['id']);
        foreach ($menus as $menu) {
            $menuIds = array_merge($menuIds, MenuItemTree::subtreeIds((int) $menu->id));
        }
        $menuIds = array_values(array_unique(array_map('intval', $menuIds)));

        $eligible = 0.0;
        foreach ($items as $line) {
            $productId = $this->lineProductId($line);
            $menuItemId = $this->lineMenuItemId($line);
            $matchesProduct = $productId && in_array($productId, $productIds, true);
            $matchesMenu = $menuItemId && in_array($menuItemId, $menuIds, true);
            if ($matchesProduct || $matchesMenu) {
                $eligible += $this->lineAmount($line);
            }
        }

        return round($eligible, 2);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    protected function upsertUsage(Coupon $coupon, array $attributes, string $targetStatus): CouponUsage
    {
        $query = CouponUsage::query()->where('coupon_id', $coupon->id);
        if (! empty($attributes['order_id'])) {
            $query->where('order_id', $attributes['order_id']);
        } elseif (! empty($attributes['sale_id'])) {
            $query->where('sale_id', $attributes['sale_id']);
        } else {
            $query->whereNull('id');
        }

        $usage = $query->lockForUpdate()->first();
        $wasRedeemed = $usage && $usage->status === CouponUsage::STATUS_REDEEMED;

        $now = now();
        $payload = [
            'coupon_id' => $coupon->id,
            'user_id' => $attributes['user_id'] ?? null,
            'order_id' => $attributes['order_id'] ?? null,
            'sale_id' => $attributes['sale_id'] ?? null,
            'guest_key' => $attributes['guest_key'] ?? null,
            'discount_amount' => round((float) ($attributes['discount_amount'] ?? 0), 2),
            'status' => $targetStatus,
        ];

        if ($targetStatus === CouponUsage::STATUS_RESERVED) {
            $payload['reserved_at'] = $usage?->reserved_at ?: $now;
            $payload['redeemed_at'] = null;
            $payload['released_at'] = null;
        }

        if ($targetStatus === CouponUsage::STATUS_REDEEMED) {
            $payload['reserved_at'] = $usage?->reserved_at ?: $now;
            $payload['redeemed_at'] = $usage?->redeemed_at ?: $now;
            $payload['released_at'] = null;
        }

        if ($usage) {
            if ($wasRedeemed && $targetStatus === CouponUsage::STATUS_REDEEMED) {
                $usage->fill([
                    'user_id' => $payload['user_id'],
                    'guest_key' => $payload['guest_key'],
                    'discount_amount' => $payload['discount_amount'],
                ]);
                $usage->save();

                return $usage;
            }
            $usage->fill($payload);
            $usage->save();
        } else {
            $usage = CouponUsage::create($payload);
        }

        if ($targetStatus === CouponUsage::STATUS_REDEEMED && ! $wasRedeemed) {
            $coupon->increment('used_count');
        }

        if ($wasRedeemed && $targetStatus === CouponUsage::STATUS_RESERVED) {
            $this->decrementUsedCount($coupon);
        }

        return $usage->fresh();
    }

    protected function releaseUsageRow(CouponUsage $usage, bool $releaseRedeemed): void
    {
        if ($usage->status === CouponUsage::STATUS_RELEASED) {
            return;
        }

        if ($usage->status === CouponUsage::STATUS_REDEEMED && ! $releaseRedeemed) {
            return;
        }

        $wasRedeemed = $usage->status === CouponUsage::STATUS_REDEEMED;
        $usage->update([
            'status' => CouponUsage::STATUS_RELEASED,
            'released_at' => now(),
        ]);

        if ($wasRedeemed) {
            $this->decrementUsedCount($usage->coupon ?? Coupon::query()->find($usage->coupon_id));
        }
    }

    protected function decrementUsedCount(?Coupon $coupon): void
    {
        if (! $coupon) {
            return;
        }

        Coupon::query()
            ->where('id', $coupon->id)
            ->where('used_count', '>', 0)
            ->decrement('used_count');
    }

    /**
     * @param  array<string, mixed>  $context
     */
    protected function globalHeldCount(Coupon $coupon, array $context): int
    {
        $redeemed = (int) $coupon->used_count;
        if (! $coupon->exists) {
            return $redeemed;
        }

        $reserved = CouponUsage::query()
            ->where('coupon_id', $coupon->id)
            ->where('status', CouponUsage::STATUS_RESERVED)
            ->when(! empty($context['exclude_order_id']), function ($q) use ($context) {
                $q->where(function ($inner) use ($context) {
                    $inner->whereNull('order_id')
                        ->orWhere('order_id', '!=', $context['exclude_order_id']);
                });
            })
            ->when(! empty($context['exclude_sale_id']), function ($q) use ($context) {
                $q->where(function ($inner) use ($context) {
                    $inner->whereNull('sale_id')
                        ->orWhere('sale_id', '!=', $context['exclude_sale_id']);
                });
            })
            ->count();

        return $redeemed + $reserved;
    }

    /**
     * @param  array<string, mixed>  $context
     */
    protected function customerHeldCount(Coupon $coupon, array $context): int
    {
        $user = $context['user'] ?? null;
        $guestKey = $context['guest_key'] ?? null;
        if (! $user && ! $guestKey) {
            return 0;
        }

        return CouponUsage::query()
            ->where('coupon_id', $coupon->id)
            ->whereIn('status', [CouponUsage::STATUS_RESERVED, CouponUsage::STATUS_REDEEMED])
            ->when(! empty($context['exclude_order_id']), fn ($q) => $q->where(function ($inner) use ($context) {
                $inner->whereNull('order_id')->orWhere('order_id', '!=', $context['exclude_order_id']);
            }))
            ->when(! empty($context['exclude_sale_id']), fn ($q) => $q->where(function ($inner) use ($context) {
                $inner->whereNull('sale_id')->orWhere('sale_id', '!=', $context['exclude_sale_id']);
            }))
            ->where(function ($q) use ($user, $guestKey) {
                if ($user) {
                    $q->where('user_id', $user->id);
                } elseif ($guestKey) {
                    $q->where('guest_key', $guestKey);
                }
            })
            ->count();
    }

    /**
     * @param  array<string, mixed>  $context
     */
    protected function customerHasPriorPaidOrder(array $context): bool
    {
        $user = $context['user'] ?? null;
        $guestKey = $context['guest_key'] ?? null;
        $excludeOrderId = $context['exclude_order_id'] ?? null;

        $query = Order::query()
            ->where('payment_status', 'paid')
            ->whereNotIn('status', ['cancelled', 'returned']);

        if ($excludeOrderId) {
            $query->where('id', '!=', $excludeOrderId);
        }

        $query->where(function ($q) use ($user, $guestKey) {
            if ($user) {
                $q->where('user_id', $user->id);
            }
            if ($guestKey && str_starts_with($guestKey, 'email:')) {
                $q->orWhere('customer_email', substr($guestKey, 6));
            }
            if ($guestKey && str_starts_with($guestKey, 'phone:')) {
                $q->orWhere('customer_phone', substr($guestKey, 6));
            }
        });

        return $query->exists();
    }

    protected function lineProductId($line): ?int
    {
        if (is_array($line)) {
            return ! empty($line['product_id']) ? (int) $line['product_id'] : null;
        }
        if (isset($line->product_id) && $line->product_id) {
            return (int) $line->product_id;
        }
        $variant = $line->variant ?? null;
        if ($variant && isset($variant->product_id) && $variant->product_id) {
            return (int) $variant->product_id;
        }
        if ($variant && $variant->product) {
            return (int) $variant->product->id;
        }

        return null;
    }

    protected function lineMenuItemId($line): ?int
    {
        if (is_array($line)) {
            return ! empty($line['menu_item_id']) ? (int) $line['menu_item_id'] : null;
        }
        $product = $line->product ?? ($line->variant->product ?? null);
        if ($product && isset($product->menu_item_id) && $product->menu_item_id) {
            return (int) $product->menu_item_id;
        }

        return null;
    }

    protected function lineAmount($line): float
    {
        if (is_array($line)) {
            return (float) ($line['line_total'] ?? $line['amount'] ?? 0);
        }
        if (isset($line->line_total)) {
            return (float) $line->line_total;
        }
        $variant = $line->variant ?? null;
        $qty = (int) ($line->qty ?? $line->quantity ?? 1);
        if ($variant && method_exists($variant, 'effectivePrice')) {
            return round((float) $variant->effectivePrice() * $qty, 2);
        }

        return 0.0;
    }
}
