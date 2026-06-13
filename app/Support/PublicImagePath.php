<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

trait PublicImagePath
{
    public function publicImageUrl(?string $path, string $fallbackSeed = 'page'): string
    {
        if (! $path) {
            return 'https://picsum.photos/seed/'.rawurlencode($fallbackSeed).'/1200/800';
        }

        if (Str::startsWith($path, ['http://', 'https://'])) {
            return $path;
        }

        if (Storage::disk('public')->exists($path)) {
            return url('storage/'.$path);
        }

        return asset('storage/'.$path);
    }

    public function isStoredImage(?string $path): bool
    {
        return $path && ! Str::startsWith($path, ['http://', 'https://']);
    }
}
