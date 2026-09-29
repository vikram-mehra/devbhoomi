<?php

namespace App\Http\Controllers;

use App\Services\PincodeServiceabilityService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PincodeController extends Controller
{
    public function check(Request $request, PincodeServiceabilityService $service): JsonResponse
    {
        $result = $service->check($request->input('pincode'));
        $status = $result['ok'] ? 200 : 422;

        return response()->json($result, $status);
    }
}
