<?php

namespace App\Http\Middleware;

use App\Services\HolidayModeService;
use Closure;
use Illuminate\Http\Request;

class EnsureNotInHolidayMode
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        if (HolidayModeService::isEnabled()) {
            if ($request->expectsJson() || $request->wantsJson()) {
                return response()->json([
                    'error' => 'holiday_mode',
                    'message' => HolidayModeService::message(),
                ], 403);
            }

            return redirect()->route('cart.index')
                ->with('error', HolidayModeService::message());
        }

        return $next($request);
    }
}
