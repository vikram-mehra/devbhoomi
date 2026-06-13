<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class Order extends Model
{
    protected $fillable = [
        'order_number', 'user_id', 'address_id', 'status', 'payment_method', 'payment_status',
        'payment_ref', 'subtotal', 'shipping', 'discount', 'total', 'admin_commission',
        'wallet_used', 'coupon_code', 'notes', 'customer_name', 'customer_phone', 'customer_email',
        'shipping_address_id', 'tax_amount', 'transaction_id', 'razorpay_payment_id', 'courier_name',
        'tracking_id', 'delivery_date', 'confirmed_at', 'shipped_at', 'delivered_at',
        'customer_confirmation_sent_at', 'admin_notification_sent_at',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'shipping' => 'decimal:2',
        'discount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total' => 'decimal:2',
        'admin_commission' => 'decimal:2',
        'wallet_used' => 'decimal:2',
        'delivery_date' => 'date',
        'confirmed_at' => 'datetime',
        'shipped_at' => 'datetime',
        'delivered_at' => 'datetime',
        'customer_confirmation_sent_at' => 'datetime',
        'admin_notification_sent_at' => 'datetime',
        'user_id' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function address(): BelongsTo
    {
        return $this->belongsTo(Address::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function shippingAddress(): HasOne
    {
        return $this->hasOne(ShippingAddress::class);
    }

    /**
     * Admin order status dropdown (value => label). Includes `placed` for new checkout orders.
     *
     * @return array<string, string>
     */
    public static function adminStatusOptions(): array
    {
        return [
            'pending' => __('Pending'),
            'confirmed' => __('Confirmed'),
            'processing' => __('Processing'),
            'shipped' => __('Shipped'),
            'delivered' => __('Delivered'),
            'cancelled' => __('Cancelled'),
            'returned' => __('Returned'),
        ];
    }

    /**
     * @return list<string>
     */
    public static function allowedStatusValues(): array
    {
        return array_keys(static::adminStatusOptions());
    }

    public static function statusLabel(?string $status): string
    {
        $opts = static::adminStatusOptions();

        return $opts[$status] ?? Str::title(str_replace('_', ' ', (string) $status));
    }

    /**
     * Linear fulfillment steps (excludes cancelled) for customer timeline order.
     *
     * @return list<string>
     */
    public static function fulfillmentTimelineKeys(): array
    {
        return ['pending', 'confirmed', 'processing', 'shipped', 'delivered'];
    }

    public static function paymentStatusOptions(): array
    {
        return [
            'paid' => __('Paid'),
            'unpaid' => __('Unpaid'),
            'failed' => __('Failed'),
            'refunded' => __('Refunded'),
        ];
    }

    public function payableAmount(): float
    {
        return max(0, (float) $this->total - (float) $this->wallet_used);
    }

    public function customerDisplayName(): string
    {
        return (string) ($this->customer_name ?: $this->user?->name ?: __('Customer'));
    }

    public function customerDisplayEmail(): ?string
    {
        $email = trim((string) ($this->customer_email ?: $this->user?->email ?: ''));

        return $email !== '' ? $email : null;
    }

    public function customerDisplayPhone(): string
    {
        return (string) ($this->customer_phone
            ?: $this->shippingAddress?->phone
            ?: $this->address?->phone
            ?: '—');
    }

    public function paymentMethodLabel(): string
    {
        $key = strtolower((string) $this->payment_method);

        return match ($key) {
            'razorpay' => __('Razorpay (UPI, Cards, Netbanking)'),
            'cod' => __('Cash on Delivery'),
            default => $key !== '' ? Str::title(str_replace('_', ' ', $key)) : __('N/A'),
        };
    }

    public function paymentStatusLabel(): string
    {
        $opts = static::paymentStatusOptions();

        return $opts[$this->payment_status] ?? Str::title((string) $this->payment_status);
    }

    public function shippingAddressLines(): array
    {
        return $this->formatAddressLines($this->shippingAddress);
    }

    public function billingAddressLines(): array
    {
        if ($this->address) {
            return $this->formatAddressLines($this->address);
        }

        return $this->shippingAddressLines();
    }

    /**
     * @return list<string>
     */
    protected function formatAddressLines($address): array
    {
        if (! $address) {
            return [__('Not available')];
        }

        $lines = array_filter([
            $address->name ?? null,
            $address->line1 ?? null,
            $address->line2 ?? null,
            trim(implode(', ', array_filter([
                $address->city ?? null,
                $address->state ?? null,
                $address->pincode ?? null,
            ]))),
            isset($address->phone) && $address->phone ? __('Phone: :phone', ['phone' => $address->phone]) : null,
        ]);

        return $lines !== [] ? array_values($lines) : [__('Not available')];
    }

    public static function generateOrderNumber(): string
    {
        $startAt = 100001;

        $maxNumeric = (int) DB::table('orders')
            ->whereRaw("order_number REGEXP '^[0-9]+$'")
            ->lockForUpdate()
            ->selectRaw('COALESCE(MAX(CAST(order_number AS UNSIGNED)), 0) as max_num')
            ->value('max_num');

        return (string) max($startAt, $maxNumeric + 1);
    }

    /** Customer account lists: hide failed / unpaid Razorpay attempts. */
    public function scopeVisibleInAccount($query)
    {
        return $query->where(function ($q) {
            $q->where('payment_status', '!=', 'failed')
                ->where(function ($q2) {
                    $q2->whereNotIn('payment_status', ['pending', 'unpaid'])
                        ->orWhere('payment_method', '!=', 'razorpay');
                });
        });
    }
}
