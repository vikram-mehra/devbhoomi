<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Support\AppUrl;
use Illuminate\Http\Request;

class AdminLoginController extends Controller
{
    public function showLoginForm()
    {
        AppUrl::forgetInvalidIntended();

        if (auth()->check()) {
            if (auth()->user()->isAdmin()) {
                return redirect()->route('admin.dashboard');
            }

            return redirect()->route('market.home');
        }

        return view('admin.auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! auth()->attempt($credentials, $request->boolean('remember'))) {
            return back()->withErrors([
                'email' => __('Invalid admin credentials.'),
            ])->onlyInput('email');
        }

        $request->session()->regenerate();

        if (! $request->user()->isAdmin()) {
            auth()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return back()->withErrors([
                'email' => __('This login page is only for admin accounts.'),
            ])->onlyInput('email');
        }

        return AppUrl::redirectIntended(route('admin.dashboard'));
    }
}
