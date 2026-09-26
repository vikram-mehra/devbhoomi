<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DelhiveryService
{
    public function token(): string
    {
        return trim((string) config('tracking.delhivery.token'));
    }

    public function baseUrl(): string
    {
        return rtrim((string) config('tracking.delhivery.base_url', 'https://staging-express.delhivery.com'), '/');
    }

    public function enabled(): bool
    {
        return $this->token() !== '';
    }

    public function environment(): string
    {
        $env = strtolower((string) config('tracking.delhivery.env', 'test'));

        return $env === 'live' ? 'live' : 'test';
    }

    public function isTest(): bool
    {
        return $this->environment() === 'test';
    }

    /**
     * Pull one shipment by AWB / waybill.
     *
     * @return array{ok: bool, shipment: ?array<string, mixed>, error: ?string, http_status: ?int}
     */
    public function fetchShipmentByWaybill(string $waybill): array
    {
        return $this->request(['waybill' => trim($waybill)]);
    }

    /**
     * Pull one shipment by client reference (our order number).
     *
     * @return array{ok: bool, shipment: ?array<string, mixed>, error: ?string, http_status: ?int}
     */
    public function fetchShipmentByReference(string $reference): array
    {
        return $this->request(['ref_nos' => trim($reference)]);
    }

    /**
     * Normalize a webhook or pull payload to the Shipment object.
     *
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>|null
     */
    public function extractShipment(array $payload): ?array
    {
        if (isset($payload['Shipment']) && is_array($payload['Shipment'])) {
            return $payload['Shipment'];
        }

        $nested = $payload['ShipmentData'][0]['Shipment'] ?? null;

        return is_array($nested) ? $nested : null;
    }

    public function mapStatus(string $statusType, string $statusName): string
    {
        $name = strtolower($statusName);

        if (in_array($statusType, ['DL'], true) && ! str_contains($name, 'rto')) {
            return 'delivered';
        }

        if (in_array($statusType, ['RT'], true) || str_contains($name, 'rto') || str_contains($name, 'return')) {
            return 'returned';
        }

        if (in_array($statusType, ['CN'], true) || str_contains($name, 'cancel')) {
            return 'cancelled';
        }

        if (str_contains($name, 'manifest') || str_contains($name, 'not picked')) {
            return 'processing';
        }

        if ($name !== '') {
            return 'shipped';
        }

        return 'processing';
    }

    /**
     * @param  array<string, mixed>  $query
     * @return array{ok: bool, shipment: ?array<string, mixed>, error: ?string, http_status: ?int}
     */
    protected function request(array $query): array
    {
        $token = $this->token();
        $waybill = trim((string) ($query['waybill'] ?? ''));
        $reference = trim((string) ($query['ref_nos'] ?? ''));

        if ($token === '') {
            return $this->failure('missing_token');
        }

        if ($waybill === '' && $reference === '') {
            return $this->failure('missing_awb');
        }

        try {
            $response = Http::timeout((int) config('tracking.delhivery.timeout', 12))
                ->acceptJson()
                ->withHeaders([
                    'Authorization' => 'Token '.$token,
                ])
                ->get($this->baseUrl().'/api/v1/packages/json/', array_filter([
                    'token' => $token,
                    'waybill' => $waybill !== '' ? $waybill : null,
                    'ref_nos' => $reference !== '' ? $reference : null,
                    'verbose' => 2,
                ]));

            if (! $response->successful()) {
                Log::warning('Delhivery tracking request failed.', [
                    'http_status' => $response->status(),
                    'env' => $this->environment(),
                ]);

                return $this->failure(
                    $response->status() === 401 ? 'unauthorized' : 'http_error',
                    $response->status()
                );
            }

            $json = $response->json();
            if (! is_array($json)) {
                return $this->failure('invalid_response', $response->status());
            }

            if (! empty($json['Error'])) {
                return $this->failure('invalid_awb', $response->status());
            }

            $shipment = $this->extractShipment($json);
            if (! $shipment) {
                return $this->failure('not_found', $response->status());
            }

            return [
                'ok' => true,
                'shipment' => $shipment,
                'error' => null,
                'http_status' => $response->status(),
            ];
        } catch (\Throwable $e) {
            Log::warning('Delhivery tracking request exception.', [
                'message' => $e->getMessage(),
                'env' => $this->environment(),
            ]);

            return $this->failure('timeout_or_network');
        }
    }

    /**
     * @return array{ok: bool, shipment: null, error: string, http_status: ?int}
     */
    protected function failure(string $error, ?int $httpStatus = null): array
    {
        return [
            'ok' => false,
            'shipment' => null,
            'error' => $error,
            'http_status' => $httpStatus,
        ];
    }
}
