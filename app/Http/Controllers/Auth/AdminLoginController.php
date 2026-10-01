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
            if (auth()->user()->isSeo()) {
                return redirect()->route('admin.seo.index');
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

        if (! $request->user()->canAccessAdmin()) {
            auth()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return back()->withErrors([
                'email' => __('This login page is only for admin accounts.'),
            ])->onlyInput('email');
        }

        $home = $request->user()->isSeo() && ! $request->user()->isAdmin()
            ? route('admin.seo.index')
            : route('admin.dashboard');

        return AppUrl::redirectIntended($home);
    }
}
