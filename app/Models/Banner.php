<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Banner extends Model
{
    public const PLACEMENT_HOME_SLIDER = 'home_slider';

    public const PLACEMENT_HOME_PROMO = 'home_promo';

    protected $fillable = [
        'title',
        'eyebrow',
        'subtitle',
        'image',
        'mobile_image',
        'link',
        'button_label',
        'secondary_button_label',
        'secondary_link',
        'placement',
        'sort_order',
        'is_active',
    ];

    protected $casts = ['is_active' => 'boolean'];

    public function imageUrl(): string
    {
        return $this->publicUrl($this->image);
    }

    public function mobileImageUrl(): string
    {
        return $this->publicUrl($this->mobile_image);
    }

    public function resolvedMobileImageUrl(): string
    {
        return $this->mobileImageUrl() !== '' ? $this->mobileImageUrl() : $this->imageUrl();
    }

    public function isStoredFile(): bool
    {
        return $this->isStoredPath($this->image);
    }

    public function isStoredMobileFile(): bool
    {
        return $this->isStoredPath($this->mobile_image);
    }

    public function isStoredPath(?string $path): bool
    {
        $path = trim((string) $path);

        return $path !== '' && ! Str::startsWith($path, ['http://', 'https://']);
    }

    protected function publicUrl(?string $path): string
    {
        $path = trim((string) $path);
        if ($path === '') {
            return '';
        }
        if (Str::startsWith($path, ['http://', 'https://'])) {
            return $path;
        }

        return asset('storage/'.$path);
    }

    public function resolvedLink(): string
    {
        $link = trim((string) $this->link);
        if ($link === '') {
            return route('shop.search');
        }
        if (Str::startsWith($link, ['http://', 'https://'])) {
            return $link;
        }
        if (strpos($link, '/') === 0) {
            return url($link);
        }

        return url('/'.$link);
    }
}
