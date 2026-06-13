<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    public const ROLE_USER = 'user';

    public const ROLE_VENDOR = 'vendor';

    public const ROLE_ADMIN = 'admin';

    public const ACCOUNT_INACTIVE = 'inactive';

    public const ACCOUNT_ACTIVE = 'active';

    protected $fillable = [
        'name', 'email', 'password', 'role', 'phone', 'phone_verified_at',
        'otp_code', 'otp_expires_at', 'wallet_balance', 'dark_mode',
        'google_id', 'facebook_id', 'avatar',
        'verification_code', 'verification_expires_at', 'account_status',
    ];

    protected $hidden = [
        'password', 'remember_token', 'otp_code', 'verification_code',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'verification_expires_at' => 'datetime',
        'phone_verified_at' => 'datetime',
        'otp_expires_at' => 'datetime',
        'wallet_balance' => 'decimal:2',
        'dark_mode' => 'boolean',
    ];

    public function vendor(): HasOne
    {
        return $this->hasOne(Vendor::class);
    }

    public function addresses(): HasMany
    {
        return $this->hasMany(Address::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function wishlists(): HasMany
    {
        return $this->hasMany(Wishlist::class);
    }

    public function walletTransactions(): HasMany
    {
        return $this->hasMany(WalletTransaction::class);
    }

    public function returns(): HasMany
    {
        return $this->hasMany(ReturnModel::class);
    }

    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function isVendor(): bool
    {
        return $this->role === self::ROLE_VENDOR;
    }

    public function hasVerifiedEmail(): bool
    {
        return $this->email_verified_at !== null
            && $this->account_status === self::ACCOUNT_ACTIVE;
    }

    public function markEmailAsVerified(): void
    {
        $this->forceFill([
            'email_verified_at' => $this->freshTimestamp(),
            'account_status' => self::ACCOUNT_ACTIVE,
            'verification_code' => null,
            'verification_expires_at' => null,
        ])->save();
    }

    public function clearEmailVerificationCode(): void
    {
        $this->forceFill([
            'verification_code' => null,
            'verification_expires_at' => null,
        ])->save();
    }

    /** Signed up via Google — no email OTP required. */
    public function registeredViaGoogle(): bool
    {
        return ! empty($this->google_id);
    }

    public function isAccountActive(): bool
    {
        return $this->account_status === self::ACCOUNT_ACTIVE;
    }

    public function avatarUrl(): ?string
    {
        if (! $this->avatar) {
            return null;
        }
        if (Str::startsWith($this->avatar, ['http://', 'https://'])) {
            return $this->avatar;
        }

        return asset('storage/'.$this->avatar);
    }
}
