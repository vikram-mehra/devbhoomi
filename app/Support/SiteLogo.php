<?php

namespace App\Support;

class SiteLogo
{
    /** @var array<int, string> */
    private const CANDIDATES = [
        'images/logo.png',
        'images/logo.svg',
        'images/logo.webp',
        'images/logo.jpg',
        'images/logo.jpeg',
    ];

    public static function url(): ?string
    {
        foreach (self::CANDIDATES as $relative) {
            if (is_file(public_path($relative))) {
                return asset($relative);
            }
        }

        return null;
    }
}
