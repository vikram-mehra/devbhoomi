<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use App\Services\CartService;
use App\Services\GoogleAuthService;
use App\Support\AppUrl;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class GoogleAuthController extends Controller
{
    public function __construct()
    {
        $this->middleware('guest')->only('redirect');
    }

    public function redirect(Request $request, GoogleAuthService $googleAuth)
    {
        if (! $googleAuth->isConfigured()) {
            return redirect()
                ->route('login')
                ->with('error', __('Google sign-in is not configured. Add GOOGLE_CLIENT_ID and GOOGLE_CLIENT_SECRET to your .env file.'));
        }

        $redirectUri = $googleAuth->syncRedirectConfig($request);

        return Socialite::driver('google')
            ->redirectUrl($redirectUri)
            ->scopes(['openid', 'profile', 'email'])
            ->redirect();
    }

    public function callback(Request $request, GoogleAuthService $googleAuth)
    {
        if (! $googleAuth->isConfigured()) {
            return redirect()
                ->route('login')
                ->with('error', __('Google sign-in is not configured.'));
        }

        $redirectUri = $googleAuth->syncRedirectConfig($request);

        try {
            $googleUser = Socialite::driver('google')
                ->redirectUrl($redirectUri)
                ->user();
            $user = $googleAuth->resolveUser($googleUser);
        } catch (\RuntimeException $e) {
            return redirect()->route('login')->with('error', $e->getMessage());
        } catch (\Throwable $e) {
            report($e);

            return redirect()
                ->route('login')
                ->with('error', __('Google sign-in was cancelled or failed. Please try again.'));
        }

        Auth::login($user, true);
        $request->session()->regenerate();
        app(CartService::class)->mergeGuestCart();

        if ($user->isAdmin()) {
            return AppUrl::redirectIntended(route('admin.dashboard'))
                ->with('status', __('Signed in with Google.'));
        }

        if ($user->isVendor()) {
            return AppUrl::redirectIntended(route('vendor.dashboard'))
                ->with('status', __('Signed in with Google.'));
        }

        return AppUrl::redirectIntended(RouteServiceProvider::HOME)
            ->with('status', __('Signed in with Google.'));
    }
}
