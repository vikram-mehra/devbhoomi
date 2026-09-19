<?php

namespace App\Console\Commands;

use App\Support\OptimizedImage;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class OptimizeStorefrontImages extends Command
{
    protected $signature = 'images:optimize';

    protected $description = 'Pre-generate resized WebP copies of storefront banners, products, and the logo';

    public function handle(): int
    {
        $roots = [
            public_path('storage/banners'),
            public_path('storage/blog'),
            public_path('storage/products'),
            storage_path('app/public/blog'),
            storage_path('app/public/banners'),
            storage_path('app/public/products'),
            public_path('images'),
        ];
        $count = 0;
        foreach ($roots as $root) {
            if (! is_dir($root)) {
                continue;
            }
            foreach (File::allFiles($root) as $file) {
                $ext = strtolower($file->getExtension());
                if (! in_array($ext, ['jpg', 'jpeg', 'png', 'webp'], true)) {
                    continue;
                }
                $inPublicStorage = strpos($file->getPathname(), public_path('storage')) === 0
                    || strpos($file->getPathname(), storage_path('app/public')) === 0;
                $url = $inPublicStorage
                    ? asset('storage/'.ltrim(str_replace('\\', '/', str_replace([public_path('storage'), storage_path('app/public')], '', $file->getPathname())), '/'))
                    : asset('images/'.$file->getFilename());
                $widths = [420, 800];
                if (strpos($file->getPathname(), 'banners') !== false) {
                    $widths = [768, 1400];
                } elseif (strpos($file->getPathname(), 'blog') !== false) {
                    $widths = [640];
                } elseif (strpos($file->getPathname(), 'logo') !== false) {
                    $widths = [300];
                }
                foreach ($widths as $width) {
                    OptimizedImage::url($url, $width);
                    $count++;
                }
            }
        }
        $this->info('Optimized '.$count.' image variants.');

        return 0;
    }
}
