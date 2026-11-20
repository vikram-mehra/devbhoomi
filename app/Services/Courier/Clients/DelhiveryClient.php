<?php

namespace App\Services\Courier\Clients;

use App\Services\Courier\Exceptions\ShipmentCreationException;
use App\Services\Courier\Support\CourierHttp;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DelhiveryClient
{
    /** @var array<string, mixed> */
    protected array $config;

    protected string $debugLog = '';

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function token(): string
    {
        return trim((string) ($this->config['api_token'] ?? ''));
    }

    public function baseUrl(): string
    {
        return rtrim((string) ($this->config['api_url'] ?? ''), '/');
    }

    /**
     * Official CMU create: POST /api/cmu/create.json
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function createShipment(array $data): array
    {
        $this->assertReady();

        $url = $this->baseUrl().'/api/cmu/create.json';
        $encoded = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '{}';
        $curl = $this->curlPostForm($url, [
            'format' => 'json',
            'data' => $encoded,
        ]);

        try {
            $response = $this->http()
                ->asForm()
                ->post($url, [
                    'format' => 'json',
                    'data' => $encoded,
                ]);
        } catch (\Throwable $e) {
            $this->appendDebug('Delhivery create shipment', $curl, $data, null, ['error' => $e->getMessage()]);
            throw $e;
        }

        $json = $this->json($response);
        $this->appendDebug('Delhivery create shipment', $curl, $data, $response->status(), $json);
        $this->logFailure($response, 'create');
        CourierHttp::assertSuccessful($response, 'Delhivery shipment');

        if (array_key_exists('success', $json) && $json['success'] === false) {
            $hint = CourierHttp::errorHint($json) ?: 'Delhivery rejected the shipment.';
            throw new ShipmentCreationException($hint);
        }

        return $json;
    }

    /**
     * Official tracking pull: GET /api/v1/packages/json/
     *
     * @return array<string, mixed>
     */
    public function trackByWaybill(string $waybill): array
    {
        $response = $this->http()->get($this->baseUrl().'/api/v1/packages/json/', [
            'waybill' => $waybill,
            'verbose' => 2,
        ]);

        CourierHttp::assertSuccessful($response, 'Delhivery tracking');

        return $this->json($response);
    }

    /**
     * Official cancel via /api/p/edit
     *
     * @return array<string, mixed>
     */
    public function cancelShipment(string $waybill): array
    {
        $response = $this->http()
            ->asForm()
            ->post($this->baseUrl().'/api/p/edit', [
                'data' => json_encode([
                    'waybill' => $waybill,
                    'cancellation' => 'true',
                ]),
            ]);

        CourierHttp::assertSuccessful($response, 'Delhivery cancel');

        return $this->json($response);
    }

    /**
     * Official packing slip: GET /api/p/packing_slip
     *
     * @return array<string, mixed>
     */
    public function packingSlip(string $waybill): array
    {
        $response = $this->http()->get($this->baseUrl().'/api/p/packing_slip', [
            'wbns' => $waybill,
            'pdf' => 'true',
        ]);

        CourierHttp::assertSuccessful($response, 'Delhivery label');

        return $this->json($response);
    }

    /**
     * Official pincode filter: GET /c/api/pin-codes/json/
     *
     * @return array<string, mixed>
     */
    public function pinCodes(string $pincode): array
    {
        $response = $this->http()->get($this->baseUrl().'/c/api/pin-codes/json/', [
            'filter_codes' => $pincode,
        ]);

        CourierHttp::assertSuccessful($response, 'Delhivery serviceability');

        return $this->json($response);
    }

    /**
     * List registered pickup warehouses. A 404/empty list is not fatal.
     *
     * @return array<string, mixed>
     */
    public function listWarehouses(): array
    {
        $this->assertReady();

        $url = $this->baseUrl().'/api/backend/clientwarehouse/all/';
        $curl = $this->curlGet($url);
        $response = $this->http()->get($url);

        $json = $response->successful() ? $this->json($response) : ['http_status' => $response->status(), 'body' => $response->body()];
        $this->appendDebug('Delhivery warehouse list', $curl, null, $response->status(), $json);

        if ($response->status() === 404 || ! $response->successful()) {
            $this->logFailure($response, 'warehouse-list');

            return [];
        }

        return $json;
    }

    /**
     * Register a pickup warehouse. Already-exists is treated as success.
     *
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function createWarehouse(array $payload): array
    {
        $this->assertReady();

        $url = $this->baseUrl().'/api/backend/clientwarehouse/create/';
        $curl = $this->curlPostJson($url, $payload);
        $response = $this->http()
            ->asJson()
            ->post($url, $payload);

        $json = $this->json($response);
        $this->appendDebug('Delhivery warehouse create', $curl, $payload, $response->status(), $json);
        $body = strtolower($response->body());
        $already = str_contains($body, 'already') || str_contains($body, 'exist');

        if ($already) {
            $json['_already_exists'] = true;

            return $json;
        }

        if (! $response->successful()) {
            $this->logFailure($response, 'warehouse-create');
            $hint = CourierHttp::errorHint($json);
            if ($hint !== '') {
                $json['_error'] = $hint;
            }
        }

        return $json;
    }

    protected function assertReady(): void
    {
        if ($this->token() === '') {
            throw new ShipmentCreationException('Delhivery API token is not configured. Set DELHIVERY_TOKEN or DELHIVERY_API_TOKEN.');
        }

        if ($this->baseUrl() === '') {
            throw new ShipmentCreationException('Delhivery API URL is not configured. Set DELHIVERY_BASE_URL or DELHIVERY_API_URL.');
        }
    }

    protected function http()
    {
        $timeout = (int) ($this->config['timeout'] ?? 30);

        return Http::timeout($timeout)
            ->retry(2, 1000, function ($exception) {
                return $exception instanceof \Illuminate\Http\Client\ConnectionException;
            })
            ->acceptJson()
            ->withHeaders([
                'Authorization' => 'Token '.$this->token(),
            ]);
    }

    protected function logFailure(Response $response, string $action): void
    {
        if ($response->successful()) {
            return;
        }

        Log::channel('courier')->warning('Delhivery HTTP error.', [
            'action' => $action,
            'http_status' => $response->status(),
            'url' => $this->baseUrl(),
            'hint' => CourierHttp::errorHint($response->json()),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    protected function json(Response $response): array
    {
        $json = $response->json();

        return is_array($json) ? $json : ['body' => $response->body()];
    }

    public function lastDebugBlock(): string
    {
        return trim($this->debugLog);
    }

    /**
     * @param  array<string, mixed>|null  $payload
     * @param  mixed  $response
     */
    protected function appendDebug(string $title, string $curl, ?array $payload, ?int $status, $response): void
    {
        $lines = [$title, $curl];

        if ($payload !== null) {
            $lines[] = 'Request payload:';
            $lines[] = $this->pretty($payload);
        }

        $lines[] = $status === null ? 'HTTP (not sent)' : 'HTTP '.$status;

        if ($response !== null) {
            $lines[] = 'Response:';
            $lines[] = is_string($response) ? $response : $this->pretty($response);
        }

        $this->debugLog = trim($this->debugLog."\n\n".implode("\n", $lines));
    }

    /**
     * @param  array<string, string>  $fields
     */
    protected function curlPostForm(string $url, array $fields): string
    {
        $parts = [
            "curl -X POST '".$this->escape($url)."'",
            "  -H 'Accept: application/json'",
            "  -H 'Authorization: Token ".$this->redactedToken()."'",
            "  -H 'Content-Type: application/x-www-form-urlencoded'",
        ];

        foreach ($fields as $key => $value) {
            $parts[] = "  --data-urlencode '".$key.'='.$this->escape($value)."'";
        }

        return implode(" \\\n", $parts);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    protected function curlPostJson(string $url, array $payload): string
    {
        return implode(" \\\n", [
            "curl -X POST '".$this->escape($url)."'",
            "  -H 'Accept: application/json'",
            "  -H 'Authorization: Token ".$this->redactedToken()."'",
            "  -H 'Content-Type: application/json'",
            "  --data '".$this->escape(json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '{}')."'",
        ]);
    }

    protected function curlGet(string $url): string
    {
        return implode(" \\\n", [
            "curl -X GET '".$this->escape($url)."'",
            "  -H 'Accept: application/json'",
            "  -H 'Authorization: Token ".$this->redactedToken()."'",
        ]);
    }

    protected function redactedToken(): string
    {
        $token = $this->token();
        if ($token === '') {
            return 'MISSING';
        }
        if (strlen($token) <= 8) {
            return str_repeat('*', strlen($token));
        }

        return substr($token, 0, 4).str_repeat('*', strlen($token) - 8).substr($token, -4);
    }

    /**
     * @param  mixed  $value
     */
    protected function pretty($value): string
    {
        return json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '';
    }

    protected function escape(string $value): string
    {
        return str_replace("'", "'\\''", $value);
    }
}
