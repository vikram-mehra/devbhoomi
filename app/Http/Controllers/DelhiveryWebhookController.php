<?php

namespace App\Http\Controllers;

use App\Services\DelhiveryTrackingSyncService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class DelhiveryWebhookController extends Controller
{
    public function handle(Request $request, DelhiveryTrackingSyncService $sync): Response
    {
        if (! $this->tokenMatches($request)) {
            return response('Unauthorized', 401);
        }

        $payload = $request->all();
        if ($payload === []) {
            Log::info('Delhivery webhook received an empty payload.');

            return response('Success', 200);
        }

        try {
            $sync->applyWebhook($payload);
        } catch (\Throwable $e) {
            report($e);
        }

        return response('Success', 200);
    }

    protected function tokenMatches(Request $request): bool
    {
        $expected = trim((string) config('tracking.delhivery.webhook_token'));
        if ($expected === '') {
            return true;
        }

        $provided = trim((string) (
            $request->header('X-Delhivery-Token')
            ?: $request->header('X-Webhook-Token')
            ?: $request->query('token')
            ?: ''
        ));

        $authorization = (string) $request->header('Authorization', '');
        if ($provided === '' && stripos($authorization, 'Token ') === 0) {
            $provided = trim(substr($authorization, 6));
        }

        return $provided !== '' && hash_equals($expected, $provided);
    }
}
