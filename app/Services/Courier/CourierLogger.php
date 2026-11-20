<?php

namespace App\Services\Courier;

use Illuminate\Support\Facades\Log;

class CourierLogger
{
    /**
     * @param  array<string, mixed>  $context
     */
    public function info(string $message, array $context = []): void
    {
        Log::channel('courier')->info($message, $this->safe($context));
    }

    /**
     * @param  array<string, mixed>  $context
     */
    public function warning(string $message, array $context = []): void
    {
        Log::channel('courier')->warning($message, $this->safe($context));
    }

    /**
     * @param  array<string, mixed>  $context
     */
    public function error(string $message, array $context = []): void
    {
        Log::channel('courier')->error($message, $this->safe($context));
    }

    /**
     * @param  array<string, mixed>  $context
     * @return array<string, mixed>
     */
    protected function safe(array $context): array
    {
        unset($context['token'], $context['password'], $context['api_token'], $context['client_secret'], $context['licence_key']);

        return $context;
    }
}
