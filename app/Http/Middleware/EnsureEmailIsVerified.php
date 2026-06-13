<?php

namespace App\Http\Middleware;

use App\Services\EmailVerificationService;
use Closure;
use Illuminate\Http\Request;

class EnsureEmailIsVerified
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        if (! app(EmailVerificationService::class)->requiresVerification($user)) {
            return $next($request);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'message' => __('Please verify your email address to continue.'),
            ], 403);
        }

        return redirect()
            ->route('verification.sent', ['email' => $user->email])
            ->with('warning', __('Please verify your email address to continue.'));
    }
}
