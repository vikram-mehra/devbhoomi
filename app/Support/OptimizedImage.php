<?php

namespace App\Support;

class OptimizedImage
{
    /** @var array<string, string> */
    protected static $memo = [];

    /**
     * Return a cached, resized WebP (or JPEG) URL for a local public/storage image.
     */
    public static function url(?string $url, int $width = 1200, int $quality = 72): string
    {
        $url = trim((string) $url);
        if ($url === '') {
            return '';
        }

        $key = $url.'|'.$width.'|'.$quality;
        if (isset(self::$memo[$key])) {
            return self::$memo[$key];
        }

        $local = self::localPath($url);
        if ($local === null || ! is_file($local) || ! function_exists('imagecreatetruecolor')) {
            return self::$memo[$key] = $url;
        }

        $ext = strtolower((string) pathinfo($local, PATHINFO_EXTENSION));
        if (in_array($ext, ['svg', 'gif'], true)) {
            return self::$memo[$key] = $url;
        }

        $mtime = (int) @filemtime($local);
        $hash = substr(sha1($local.'|'.$mtime.'|'.$width.'|'.$quality), 0, 16);
        $useWebp = function_exists('imagewebp');
        $rel = '_opt/'.$hash.'w'.$width.'.'.($useWebp ? 'webp' : 'jpg');
        $dest = public_path('storage/'.$rel);

        if (! is_file($dest)) {
            if (! self::writeResized($local, $dest, $width, $quality, $useWebp)) {
                return self::$memo[$key] = $url;
            }
        }

        return self::$memo[$key] = asset('storage/'.$rel);
    }

    public static function localPath(string $url): ?string
    {
        $path = $url;
        $appUrl = rtrim((string) config('app.url'), '/');
        if ($appUrl !== '' && stripos($path, $appUrl) === 0) {
            $path = substr($path, strlen($appUrl));
        }
        $path = parse_url($path, PHP_URL_PATH) ?: $path;
        $path = '/'.ltrim(str_replace('\\', '/', (string) $path), '/');

        if (strpos($path, '/storage/') === 0) {
            $relative = ltrim(substr($path, strlen('/storage/')), '/');
            $full = storage_path('app/public/'.$relative);
            if (is_file($full)) {
                return $full;
            }
            $public = public_path('storage/'.$relative);
            if (is_file($public)) {
                return $public;
            }

            return null;
        }

        $public = public_path(ltrim($path, '/'));

        return is_file($public) ? $public : null;
    }

    protected static function writeResized(string $src, string $dest, int $maxWidth, int $quality, bool $webp): bool
    {
        $info = @getimagesize($src);
        if ($info === false) {
            return false;
        }

        [$w, $h, $type] = $info;
        if ($w < 1 || $h < 1) {
            return false;
        }

        switch ($type) {
            case IMAGETYPE_JPEG:
                $im = @imagecreatefromjpeg($src);
                break;
            case IMAGETYPE_PNG:
                $im = @imagecreatefrompng($src);
                break;
            case IMAGETYPE_WEBP:
                $im = function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($src) : false;
                break;
            default:
                return false;
        }

        if (! $im) {
            return false;
        }

        $newW = $w;
        $newH = $h;
        if ($w > $maxWidth) {
            $newW = $maxWidth;
            $newH = max(1, (int) round($h * ($maxWidth / $w)));
        }

        $out = imagecreatetruecolor($newW, $newH);
        imagealphablending($out, false);
        imagesavealpha($out, true);
        $transparent = imagecolorallocatealpha($out, 0, 0, 0, 127);
        imagefilledrectangle($out, 0, 0, $newW, $newH, $transparent);
        imagecopyresampled($out, $im, 0, 0, 0, 0, $newW, $newH, $w, $h);

        $dir = dirname($dest);
        if (! is_dir($dir) && ! @mkdir($dir, 0775, true) && ! is_dir($dir)) {
            imagedestroy($im);
            imagedestroy($out);

            return false;
        }

        $ok = $webp ? @imagewebp($out, $dest, $quality) : @imagejpeg($out, $dest, $quality);
        imagedestroy($im);
        imagedestroy($out);

        return $ok && is_file($dest);
    }
}
