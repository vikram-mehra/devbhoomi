<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use App\Services\CartService;
use App\Support\AppUrl;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    protected function attemptLogin(Request $request)
    {
        // Email/password login: no OTP step here — only validate credentials.
        return $this->guard()->attempt(
            $this->credentials($request),
            $request->boolean('remember')
        );
    }

    protected function authenticated(Request $request, $user)
    {
        app(CartService::class)->mergeGuestCart();

        if ($user->role === User::ROLE_ADMIN) {
            return AppUrl::redirectIntended(route('admin.dashboard'));
        }
        if ($user->role === User::ROLE_VENDOR) {
            return AppUrl::redirectIntended(route('vendor.dashboard'));
        }

        return AppUrl::redirectIntended(route('market.home'));
    }
}
