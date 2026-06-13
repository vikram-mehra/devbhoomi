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
        if (!$this->image) {
            return '';
        }
        if (Str::startsWith($this->image, ['http://', 'https://'])) {
            return $this->image;
        }

        return asset('storage/'.$this->image);
    }

    public function isStoredFile(): bool
    {
        return $this->image && ! Str::startsWith($this->image, ['http://', 'https://']);
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
