<?php

namespace App\Support;

use Illuminate\Http\RedirectResponse;

class AppUrl
{
    /**
     * Application subdirectory from APP_URL (e.g. "/devbhoomi"), or "" when at domain root.
     */
    public static function basePath(): string
    {
        $root = config('app.url');
        if (! is_string($root) || $root === '') {
            return '';
        }

        $path = parse_url(rtrim($root, '/'), PHP_URL_PATH);
        if (! is_string($path) || $path === '' || $path === '/') {
            return '';
        }

        return rtrim(str_replace('\\', '/', $path), '/');
    }

    /**
     * Full URL for the current request path, including APP_URL subdirectory.
     * Used by paginator links when Laravel runs from a subfolder (e.g. /devbhoomi).
     */
    public static function paginatorPath(): string
    {
        $request = request();
        $path = '/'.ltrim($request->path(), '/');
        $base = static::basePath();
        if ($base !== '') {
            $path = $base.$path;
        }

        return $request->getSchemeAndHttpHost().$path;
    }

    /**
     * Session cookie path so localhost/devbhoomi and localhost/alluringstyle do not share sessions.
     */
    public static function sessionCookiePath(): string
    {
        $base = static::basePath();

        return $base !== '' ? $base.'/' : '/';
    }

    public static function isWithinApp(?string $url): bool
    {
        if ($url === null || $url === '') {
            return false;
        }

        if (strpos($url, '/') === 0 && strpos($url, '//') !== 0) {
            $base = static::basePath();
            if ($base === '') {
                return true;
            }

            return $url === $base || strpos($url, $base.'/') === 0;
        }

        $parsed = parse_url($url);
        if (! is_array($parsed) || ! isset($parsed['path'])) {
            return false;
        }

        $appRoot = rtrim((string) config('app.url'), '/');
        $appParsed = parse_url($appRoot);
        if (! is_array($appParsed)) {
            return false;
        }

        $hostKey = static function (array $parts): string {
            $host = ($parts['scheme'] ?? 'http').'://'.($parts['host'] ?? 'localhost');
            if (isset($parts['port'])) {
                $host .= ':'.$parts['port'];
            }

            return $host;
        };

        if ($hostKey($parsed) !== $hostKey($appParsed)) {
            return false;
        }

        $base = static::basePath();
        $path = $parsed['path'];
        if ($base === '') {
            return true;
        }

        return $path === $base || strpos($path, $base.'/') === 0;
    }

    public static function forgetInvalidIntended(): void
    {
        $intended = session('url.intended');
        if (is_string($intended) && ! static::isWithinApp($intended)) {
            session()->forget('url.intended');
        }
    }

    public static function redirectIntended(string $default, int $status = 302): RedirectResponse
    {
        $intended = session()->pull('url.intended');
        if (is_string($intended) && static::isWithinApp($intended)) {
            return redirect($intended, $status);
        }

        return redirect($default, $status);
    }
}
