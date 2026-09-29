<?php

namespace App\Http\Middleware;

use App\Models\User;
use App\Providers\RouteServiceProvider;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RedirectIfAuthenticated
{
    public function handle(Request $request, Closure $next, ...$guards)
    {
        $guards = empty($guards) ? [null] : $guards;

        foreach ($guards as $guard) {
            if (! Auth::guard($guard)->check()) {
                continue;
            }

            $user = Auth::guard($guard)->user();
            if ($user instanceof User && $user->isAdmin()) {
                return redirect()->route('admin.dashboard');
            }
            if ($user instanceof User && $user->role === User::ROLE_VENDOR) {
                return redirect()->route('vendor.dashboard');
            }

            return redirect(RouteServiceProvider::HOME);
        }

        return $next($request);
    }
}
