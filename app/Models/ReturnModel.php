<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class ReturnModel extends Model
{
    public const STATUS_PENDING = 'pending';

    public const STATUS_UNDER_REVIEW = 'under_review';

    public const STATUS_APPROVED = 'approved';

    public const STATUS_REJECTED = 'rejected';

    public const STATUS_REFUNDED = 'refunded';

    public const STATUS_CANCELLED = 'cancelled';

    protected $table = 'returns';

    protected $fillable = ['order_id', 'user_id', 'reason', 'status', 'admin_note'];

    /** @return array<string, string> */
    public static function statusOptions(): array
    {
        return [
            self::STATUS_PENDING => __('Pending'),
            self::STATUS_UNDER_REVIEW => __('Under Review'),
            self::STATUS_APPROVED => __('Approved'),
            self::STATUS_REJECTED => __('Rejected'),
            self::STATUS_REFUNDED => __('Refunded'),
            self::STATUS_CANCELLED => __('Cancelled'),
        ];
    }

    /** @return list<string> */
    public static function statusKeys(): array
    {
        return array_keys(self::statusOptions());
    }

    public static function normalizeStatus(?string $status): string
    {
        $key = strtolower(trim(str_replace([' ', '-'], '_', (string) $status)));

        $aliases = [
            'requested' => self::STATUS_PENDING,
            'request' => self::STATUS_PENDING,
            'underreview' => self::STATUS_UNDER_REVIEW,
            'in_review' => self::STATUS_UNDER_REVIEW,
            'accepted' => self::STATUS_APPROVED,
            'completed' => self::STATUS_APPROVED,
            'declined' => self::STATUS_REJECTED,
            'canceled' => self::STATUS_CANCELLED,
        ];

        $key = $aliases[$key] ?? $key;

        return array_key_exists($key, self::statusOptions()) ? $key : self::STATUS_PENDING;
    }

    public static function statusLabelFor(?string $status): string
    {
        $key = self::normalizeStatus($status);

        return self::statusOptions()[$key] ?? Str::title(str_replace('_', ' ', $key));
    }

    public static function statusChipClassFor(?string $status): string
    {
        return match (self::normalizeStatus($status)) {
            self::STATUS_PENDING => 'admin-chip--warning',
            self::STATUS_UNDER_REVIEW => 'admin-chip--muted',
            self::STATUS_APPROVED => 'admin-chip--success',
            self::STATUS_REJECTED => 'admin-chip--danger',
            self::STATUS_REFUNDED => 'admin-chip--success',
            self::STATUS_CANCELLED => 'admin-chip--muted',
            default => 'admin-chip--muted',
        };
    }

    public static function restoresStock(?string $status): bool
    {
        return in_array(self::normalizeStatus($status), [self::STATUS_APPROVED, self::STATUS_REFUNDED], true);
    }

    public function normalizedStatus(): string
    {
        return self::normalizeStatus($this->status);
    }

    public function statusLabel(): string
    {
        return self::statusLabelFor($this->status);
    }

    public function statusChipClass(): string
    {
        return self::statusChipClassFor($this->status);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
