<?php

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

/*
|--------------------------------------------------------------------------
| Check If The Application Is Under Maintenance
|--------------------------------------------------------------------------
|
| If the application is in maintenance / demo mode via the "down" command
| we will load this file so that any pre-rendered content can be shown
| instead of starting the framework, which could cause an exception.
|
*/

if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

/*
|--------------------------------------------------------------------------
| Register The Auto Loader
|--------------------------------------------------------------------------
|
| Composer provides a convenient, automatically generated class loader for
| this application. We just need to utilize it! We'll simply require it
| into the script here so we don't need to manually load our classes.
|
*/

require __DIR__.'/../vendor/autoload.php';

/*
| Load .env before the HTTP kernel — config is not available until bootstrap.
*/
if (file_exists(__DIR__.'/../.env')) {
    Dotenv\Dotenv::createImmutable(dirname(__DIR__))->safeLoad();
}

/*
|--------------------------------------------------------------------------
| Run The Application
|--------------------------------------------------------------------------
|
| Once we have the application, we can handle the incoming request using
| the application's HTTP kernel. Then, we will send the response back
| to this client's browser, allowing them to enjoy our application.
|
*/

$app = require_once __DIR__.'/../bootstrap/app.php';

/*
|--------------------------------------------------------------------------
| Subfolder / XAMPP: make path info "/" for Laravel routes
|--------------------------------------------------------------------------
|
| You may open http://localhost/FOLDER/ (root .htaccess → public/) or
| http://localhost/FOLDER/public/. Strip every relevant prefix so routes match.
|
*/
if (isset($_SERVER['REQUEST_URI'])) {
    $raw = $_SERVER['REQUEST_URI'];
    $path = str_replace('\\', '/', parse_url($raw, PHP_URL_PATH) ?: '/');
    $query = parse_url($raw, PHP_URL_QUERY);
    $qs = ($query !== null && $query !== '') ? '?'.$query : '';

    $stripPrefix = static function (string $path, string $prefix): string {
        $prefix = rtrim(str_replace('\\', '/', $prefix), '/');
        if ($prefix === '' || $prefix === '/') {
            return $path;
        }
        if ($path === $prefix) {
            return '/';
        }
        if (strpos($path, $prefix.'/') !== 0) {
            return $path;
        }
        $next = substr($path, strlen($prefix));
        if ($next === false || $next === '') {
            return '/';
        }

        return $next[0] === '/' ? $next : '/'.$next;
    };

    $prefixes = [];

    $appUrl = (string) ($_ENV['APP_URL'] ?? getenv('APP_URL') ?: '');
    $fromConfig = $appUrl !== '' ? parse_url(rtrim($appUrl, '/'), PHP_URL_PATH) : false;
    $fromConfig = is_string($fromConfig) ? str_replace('\\', '/', $fromConfig) : '';
    if ($fromConfig !== '' && $fromConfig !== '/') {
        $prefixes[] = $fromConfig;
        // APP_URL=.../zionshoping/public but browser uses http://localhost/alluringstyle/
        if (substr($fromConfig, -7) === '/public') {
            $parent = str_replace('\\', '/', dirname($fromConfig));
            if ($parent !== '/' && $parent !== '.' && $parent !== '') {
                $prefixes[] = $parent;
            }
        }
    }

    if (isset($_SERVER['SCRIPT_NAME']) && is_string($_SERVER['SCRIPT_NAME'])) {
        $script = str_replace('\\', '/', $_SERVER['SCRIPT_NAME']);
        if (substr($script, -10) === '/index.php') {
            $base = rtrim(substr($script, 0, -strlen('/index.php')), '/');
            if ($base !== '' && $base !== '/') {
                $prefixes[] = $base;
            }
        }
    }

    $prefixes = array_values(array_unique(array_filter($prefixes)));
    usort($prefixes, static function (string $a, string $b): int {
        return strlen($b) <=> strlen($a);
    });

    foreach ($prefixes as $pfx) {
        $stripped = $stripPrefix($path, $pfx);
        if ($stripped !== $path) {
            $path = $stripped;
            break;
        }
    }

    if ($path === '/index.php') {
        $path = '/';
    } elseif (strpos($path, '/index.php/') === 0) {
        $path = '/'.ltrim(substr($path, 11), '/');
    }

    $_SERVER['REQUEST_URI'] = $path.$qs;
}

unset($_SERVER['PATH_INFO']);

$kernel = $app->make(Kernel::class);

$response = $kernel->handle(
    $request = Request::capture()
)->send();

$kernel->terminate($request, $response);
