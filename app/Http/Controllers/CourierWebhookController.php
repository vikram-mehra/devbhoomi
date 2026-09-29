<?php

namespace App\Http\Controllers;

use App\Models\Shipment;
use App\Services\Courier\CourierManager;
use App\Services\Courier\CourierResolver;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CourierWebhookController extends Controller
{
    public function handle(Request $request, string $courier, CourierResolver $resolver, CourierManager $manager): Response
    {
        $courier = strtolower(trim($courier));

        if (! $this->tokenMatches($request, $courier)) {
            return response('Unauthorized', 401);
        }

        try {
            $adapter = $resolver->resolve($courier);
            $result = $adapter->parseWebhook($request->all());
            if ($result->success && $result->awb) {
                $shipment = Shipment::query()->where('awb', $result->awb)->orWhere('tracking_number', $result->awb)->latest('id')->first();
                if ($shipment) {
                    $manager->applyTracking($shipment, $result);
                }
            }
        } catch (\Throwable $e) {
            report($e);
        }

        return response('Success', 200);
    }

    protected function tokenMatches(Request $request, string $courier): bool
    {
        $expected = trim((string) config('couriers.partners.'.$courier.'.webhook_token'));
        if ($expected === '') {
            return true;
        }

        $provided = trim((string) (
            $request->header('X-Webhook-Token')
            ?: $request->query('token')
            ?: ''
        ));

        return $provided !== '' && hash_equals($expected, $provided);
    }
}
