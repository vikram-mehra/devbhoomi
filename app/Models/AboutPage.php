<?php

namespace App\Models;

use App\Support\PublicImagePath;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;

class AboutPage extends Model
{
    use PublicImagePath;

    protected $fillable = [
        'hero_eyebrow',
        'hero_title',
        'hero_subtitle',
        'hero_image',
        'story_heading',
        'story_body',
        'story_image',
        'mission_title',
        'mission_body',
        'vision_title',
        'vision_body',
        'gallery_heading',
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

    public function highlights(): HasMany
    {
        return $this->hasMany(AboutHighlight::class)->orderBy('sort_order')->orderBy('id');
    }

    public function galleryItems(): HasMany
    {
        return $this->hasMany(AboutGalleryItem::class)->orderBy('sort_order')->orderBy('id');
    }

    public function heroImageUrl(): string
    {
        return $this->publicImageUrl($this->hero_image, 'about-hero');
    }

    public function storyImageUrl(): string
    {
        return $this->publicImageUrl($this->story_image, 'about-story');
    }

    public static function cached(): self
    {
        return Cache::remember('about_page.v1', 300, function () {
            $page = static::with(['highlights', 'galleryItems'])->first();
            if (! $page) {
                $page = static::createDefault();
            }

            return $page;
        });
    }

    public static function flushCache(): void
    {
        Cache::forget('about_page.v1');
    }

    public static function createDefault(): self
    {
        $page = static::create([
            'hero_eyebrow' => 'From the hills of Uttarakhand',
            'hero_title' => 'Pure taste of Devbhoomi',
            'hero_subtitle' => 'We bring farm-fresh pulses, grains, and natural foods from the Himalayas to your kitchen — honest sourcing, careful packing, and flavors you can trust.',
            'story_heading' => 'Our story',
            'story_body' => "Devbhoomi began with a simple promise: share the authentic produce of Uttarakhand with families across India.\n\nWe work closely with local farmers and cooperatives, choose ingredients at their best, and pack them with care so nutrition and taste stay intact from farm to shelf.",
            'mission_title' => 'Our mission',
            'mission_body' => 'To make pure, traceable Himalayan food accessible to every home while supporting sustainable livelihoods in mountain communities.',
            'vision_title' => 'Our vision',
            'vision_body' => 'A future where conscious shoppers choose local, natural foods — and hill farmers thrive because of it.',
            'gallery_heading' => 'Life at Devbhoomi',
            'meta_description' => 'Learn about Devbhoomi — farm-fresh Himalayan pulses, grains, and natural foods sourced from Uttarakhand.',
            'is_published' => true,
        ]);

        $defaults = [
            ['icon' => 'bi-mountain', 'label' => 'Himalayan sourcing', 'value' => '100%'],
            ['icon' => 'bi-people', 'label' => 'Farmer partners', 'value' => '50+'],
            ['icon' => 'bi-box-seam', 'label' => 'Orders delivered', 'value' => '10k+'],
            ['icon' => 'bi-heart', 'label' => 'Happy families', 'value' => '8k+'],
        ];

        foreach ($defaults as $i => $row) {
            $page->highlights()->create($row + ['sort_order' => $i]);
        }

        return $page->load(['highlights', 'galleryItems']);
    }
}
