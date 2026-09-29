<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class ContactPage extends Model
{
    protected $fillable = [
        'hero_title',
        'hero_subtitle',
        'email',
        'phone',
        'whatsapp',
        'address',
        'map_url',
        'hours_weekdays',
        'hours_weekend',
        'form_heading',
        'form_subtext',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'canonical_url',
        'og_image',
        'is_published',
    ];

    protected $casts = [
        'is_published' => 'boolean',
    ];

    public static function cached(): self
    {
        return Cache::remember('contact_page.v1', 300, function () {
            $page = static::first();

            return $page ?: static::createDefault();
        });
    }

    public static function flushCache(): void
    {
        Cache::forget('contact_page.v1');
    }

    public static function createDefault(): self
    {
        return static::create([
            'hero_title' => 'We would love to hear from you',
            'hero_subtitle' => 'Questions about an order, bulk purchase, or partnership? Reach out — our team usually replies within one business day.',
            'email' => 'support@'.strtolower(preg_replace('/\s+/', '', config('app.name', 'devbhoomi'))).'.com',
            'phone' => '+91 12345 67890',
            'whatsapp' => '+91 12345 67890',
            'address' => "Devbhoomi Foods\nDehradun, Uttarakhand, India",
            'hours_weekdays' => 'Mon – Sat: 10:00 AM – 6:00 PM',
            'hours_weekend' => 'Sunday: Closed',
            'form_heading' => 'Send us a message',
            'form_subtext' => 'Fill in the form and we will get back to you shortly.',
            'meta_description' => 'Contact Devbhoomi for orders, support, and wholesale enquiries.',
            'is_published' => true,
        ]);
    }

    public function mapEmbedSrc(): ?string
    {
        $url = trim((string) $this->map_url);
        if ($url === '') {
            return null;
        }

        if (str_contains($url, 'google.com/maps/embed')) {
            return $url;
        }

        return null;
    }

    public function mapLink(): ?string
    {
        $url = trim((string) $this->map_url);

        return $url !== '' ? $url : null;
    }
}
