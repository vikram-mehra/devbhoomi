<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Stores hashed email verification OTPs (one active row per issuance batch).
 */
class EmailVerificationOtp extends Model
{
    protected $fillable = [
        'user_id',
        'otp_hash',
        'expires_at',
        'attempts',
        'consumed_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'consumed_at' => 'datetime',
        'attempts' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeActive($query)
    {
        return $query
            ->whereNull('consumed_at')
            ->where('expires_at', '>', now());
    }

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    public function isConsumed(): bool
    {
        return $this->consumed_at !== null;
    }

    public function hasExceededAttempts(int $maxAttempts): bool
    {
        return $this->attempts >= $maxAttempts;
    }

    public function markConsumed(): void
    {
        $this->forceFill(['consumed_at' => now()])->save();
    }
}
