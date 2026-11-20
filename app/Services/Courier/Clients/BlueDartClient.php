<?php

namespace App\Services\Courier\Clients;

use App\Services\Courier\Exceptions\CourierAuthenticationException;
use App\Services\Courier\Support\CourierHttp;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class BlueDartClient
{
    /** @var array<string, mixed> */
    protected array $config;

    /** @var string|null */
    protected $token;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function baseUrl(): string
    {
        return rtrim((string) ($this->config['api_url'] ?? 'https://apigateway.bluedart.com'), '/');
    }

    /**
     * Official API Gateway token: POST /in/transportation/token/v1/login
     */
    public function token(): string
    {
        if ($this->token) {
            return $this->token;
        }

        $clientId = trim((string) ($this->config['client_id'] ?? $this->config['username'] ?? ''));
        $clientSecret = trim((string) ($this->config['client_secret'] ?? $this->config['password'] ?? ''));

        if ($clientId === '' || $clientSecret === '') {
            throw new CourierAuthenticationException('Blue Dart credentials are not configured.', 401);
        }

        $response = Http::timeout((int) ($this->config['timeout'] ?? 30))
            ->acceptJson()
            ->withHeaders([
                'ClientID' => $clientId,
                'clientSecret' => $clientSecret,
            ])
            ->get($this->baseUrl().'/in/transportation/token/v1/login');

        CourierHttp::assertSuccessful($response, 'Blue Dart login');

        $json = $response->json();
        $token = is_array($json)
            ? (string) ($json['JWTToken'] ?? $json['token'] ?? $json['JWT_Token'] ?? '')
            : '';

        if ($token === '') {
            throw new CourierAuthenticationException('Blue Dart login did not return a token.', 401);
        }

        return $this->token = $token;
    }

    /**
     * Official GenerateWayBill: POST /in/transportation/waybill/v1/GenerateWayBill
     *
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function generateWayBill(array $payload): array
    {
        $response = $this->authed()->post($this->baseUrl().'/in/transportation/waybill/v1/GenerateWayBill', $payload);
        CourierHttp::assertSuccessful($response, 'Blue Dart waybill');

        return $this->json($response);
    }

    /**
     * Official tracking: POST /in/transportation/tracking/v1/shipment
     *
     * @return array<string, mixed>
     */
    public function track(string $awb): array
    {
        $response = $this->authed()->post($this->baseUrl().'/in/transportation/tracking/v1/shipment', [
            'ScanType' => 'Shipment',
            'ReferenceNumber' => $awb,
        ]);
        CourierHttp::assertSuccessful($response, 'Blue Dart tracking');

        return $this->json($response);
    }

    /**
     * @return array<string, mixed>
     */
    public function cancel(string $awb): array
    {
        $response = $this->authed()->post($this->baseUrl().'/in/transportation/waybill/v1/CancelWaybill', [
            'Request' => [
                'AWBNo' => $awb,
            ],
            'Profile' => $this->profile(),
        ]);
        CourierHttp::assertSuccessful($response, 'Blue Dart cancel');

        return $this->json($response);
    }

    /**
     * @return array<string, mixed>
     */
    public function profile(): array
    {
        return [
            'LoginID' => (string) ($this->config['login_id'] ?? $this->config['username'] ?? ''),
            'LicenceKey' => (string) ($this->config['licence_key'] ?? $this->config['password'] ?? ''),
            'Api_type' => 'S',
        ];
    }

    protected function authed()
    {
        return Http::timeout((int) ($this->config['timeout'] ?? 30))
            ->retry(2, 1000, function ($exception) {
                return $exception instanceof \Illuminate\Http\Client\ConnectionException;
            })
            ->acceptJson()
            ->withToken($this->token());
    }

    /**
     * @return array<string, mixed>
     */
    protected function json(Response $response): array
    {
        $json = $response->json();

        return is_array($json) ? $json : ['body' => $response->body()];
    }
}
