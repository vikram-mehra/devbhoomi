<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class BlogPost extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'canonical_url',
        'og_image',
        'excerpt',
        'body',
        'image',
        'published_at',
        'is_published',
        'sort_order',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'published_at' => 'datetime',
    ];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query
            ->where('is_published', true)
            ->where(function (Builder $q) {
                $q->whereNull('published_at')
                    ->orWhere('published_at', '<=', now());
            });
    }

    public function imageUrl(): string
    {
        if ($this->image) {
            if (Str::startsWith($this->image, ['http://', 'https://'])) {
                return $this->image;
            }

            $path = ltrim(str_replace('\\', '/', $this->image), '/');

            if (Storage::disk('public')->exists($path)) {
                return Storage::disk('public')->url($path);
            }

            // Legacy fallback: files saved before public disk pointed at public/storage
            $legacy = storage_path('app/public/'.$path);
            if (is_file($legacy)) {
                return asset('storage/'.$path);
            }
        }

        return 'https://picsum.photos/seed/'.rawurlencode($this->slug ?: 'blog').'/640/400';
    }

    public function isStoredFile(): bool
    {
        if (! $this->image || Str::startsWith($this->image, ['http://', 'https://'])) {
            return false;
        }

        $path = ltrim(str_replace('\\', '/', $this->image), '/');

        return Storage::disk('public')->exists($path)
            || is_file(storage_path('app/public/'.$path));
    }

    public static function uniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $base = Str::slug($title) ?: 'post';
        $slug = $base;
        $n = 2;
        while (static::where('slug', $slug)->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))->exists()) {
            $slug = $base.'-'.$n;
            $n++;
        }

        return $slug;
    }
}
