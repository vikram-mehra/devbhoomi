<?php

namespace App\Services\Courier\Support;

use App\Services\Courier\Exceptions\CourierApiException;
use App\Services\Courier\Exceptions\CourierAuthenticationException;
use App\Services\Courier\Exceptions\CourierRateLimitException;
use Illuminate\Http\Client\Response;

class CourierHttp
{
    public static function assertSuccessful(Response $response, string $context): void
    {
        $status = $response->status();

        if ($status === 401 || $status === 403) {
            throw new CourierAuthenticationException($context.' authentication failed.', $status);
        }

        if ($status === 429) {
            throw new CourierRateLimitException($context.' rate limited.', $status);
        }

        if ($status >= 500) {
            throw new CourierApiException($context.' is temporarily unavailable.', $status);
        }

        if (! $response->successful()) {
            $hint = self::errorHint($response->json());
            throw new CourierApiException(
                $hint !== '' ? $context.' failed: '.$hint : $context.' request failed (HTTP '.$status.').',
                $status
            );
        }
    }

    /**
     * @param  mixed  $payload
     */
    public static function errorHint($payload): string
    {
        if (! is_array($payload)) {
            return '';
        }

        foreach (['rmk', 'remark', 'remarks', 'Error', 'error', 'message', 'Message'] as $key) {
            $value = trim((string) ($payload[$key] ?? ''));
            if ($value !== '' && ! preg_match('/token|password|secret|authorization/i', $value)) {
                return $value;
            }
        }

        return '';
    }

    public static function shouldRetry(int $status): bool
    {
        return in_array($status, [429, 500, 502, 503, 504], true);
    }
}
