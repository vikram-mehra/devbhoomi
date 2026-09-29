<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\OtpService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class OtpController extends Controller
{
    public function showPhoneForm()
    {
        return view('auth.phone-login');
    }

    public function sendOtp(Request $request, OtpService $otp)
    {
        $request->validate(['phone' => 'required|string|min:8|max:20']);
        $code = $otp->send($request->phone);

        return back()
            ->with('status', 'OTP sent.'.(config('app.debug') ? ' Dev code: '.$code : ''))
            ->with('otp_debug', config('app.debug') ? $code : null);
    }

    public function verifyOtp(Request $request, OtpService $otp)
    {
        $request->validate([
            'phone' => 'required|string|min:8|max:20',
            'code' => 'required|string|size:6',
        ]);

        if (! $otp->verify($request->phone, $request->code)) {
            return back()->with('error', 'Invalid or expired OTP.');
        }

        $user = User::firstOrCreate(
            ['phone' => $request->phone],
            [
                'name' => 'User '.$request->phone,
                'email' => uniqid('phone_', true).'@otp.local',
                'password' => Hash::make(Str::random(32)),
                'role' => User::ROLE_USER,
                'phone_verified_at' => now(),
                'email_verified_at' => now(),
                'account_status' => User::ACCOUNT_ACTIVE,
            ]
        );
        if (! $user->wasRecentlyCreated) {
            $user->update(['phone_verified_at' => now()]);
        }

        Auth::login($user, true);
        app(\App\Services\CartService::class)->mergeGuestCart();

        return redirect()->intended('/');
    }
}
