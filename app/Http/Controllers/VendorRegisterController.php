<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Vendor;
use App\Services\EmailVerificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class VendorRegisterController extends Controller
{
    public function create()
    {
        return view('market.vendor-register');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'shop_name' => 'required|string|max:255',
            'city' => 'nullable|string|max:120',
            'state' => 'nullable|string|max:120',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => strtolower(trim($request->email)),
            'password' => Hash::make($request->password),
            'role' => User::ROLE_VENDOR,
            'account_status' => User::ACCOUNT_INACTIVE,
            'email_verified_at' => null,
            'verification_code' => null,
            'verification_expires_at' => null,
            'google_id' => null,
        ]);

        $mail = app(EmailVerificationService::class);

        Vendor::create([
            'user_id' => $user->id,
            'shop_name' => $request->shop_name,
            'slug' => Str::slug($request->shop_name).'-'.Str::lower(Str::random(4)),
            'status' => 'pending',
            'city' => $request->city,
            'state' => $request->state,
        ]);

        try {
            $mail->sendVerificationEmail($user);
        } catch (\RuntimeException $e) {
            return back()
                ->withInput($request->only('name', 'email', 'shop_name', 'city', 'state'))
                ->with('error', $e->getMessage());
        }

        $message = session('dev_verification_code')
            ? __('Gmail SMTP is not set up yet. Use the verification code below (local testing only).')
            : __('Verification code sent to your email. Please check your inbox and spam folder.');

        return redirect()
            ->route('verification.sent')
            ->with('status', $message)
            ->with('email', $user->email);
    }
}
