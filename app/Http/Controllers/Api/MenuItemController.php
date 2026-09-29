<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\MenuItemResource;
use App\Services\MenuItemService;
use Illuminate\Http\JsonResponse;

class MenuItemController extends Controller
{
    public function index(MenuItemService $menus): JsonResponse
    {
        return MenuItemResource::collection($menus->apiTree())
            ->response()
            ->header('Cache-Control', $menus->shouldCache()
                ? 'public, max-age='.$menus->cacheSeconds()
                : 'no-store, no-cache, must-revalidate');
    }
}
