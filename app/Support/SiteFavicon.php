<?php

namespace App\Support;

class SiteFavicon
{
    /** @var array<int, array{path: string, type: string}> */
    private const CANDIDATES = [
        ['path' => 'images/favicon.jpg', 'type' => 'image/jpeg'],
        ['path' => 'images/favicon.png', 'type' => 'image/png'],
        ['path' => 'images/favicon.webp', 'type' => 'image/webp'],
        ['path' => 'images/favicon.ico', 'type' => 'image/x-icon'],
        ['path' => 'favicon.ico', 'type' => 'image/x-icon'],
    ];

    /**
     * @return array{url: string, type: string}|null
     */
    public static function asset(): ?array
    {
        foreach (self::CANDIDATES as $candidate) {
            if (is_file(public_path($candidate['path']))) {
                return [
                    'url' => asset($candidate['path']),
                    'type' => $candidate['type'],
                ];
            }
        }

        return null;
    }
}
