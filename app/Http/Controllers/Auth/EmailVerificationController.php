<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ResendEmailOtpRequest;
use App\Http\Requests\Auth\VerifyEmailOtpRequest;
use App\Services\CartService;
use App\Services\EmailVerificationService;
use App\Support\AppUrl;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Email OTP verification after signup: verify, resend, notice pages.
 */
class EmailVerificationController extends Controller
{
    public function __construct()
    {
        $this->middleware('throttle:verification-resend')->only('resend');
        $this->middleware('throttle:verification-verify')->only('verify');
    }

    /** Show OTP entry page (post-signup). */
    public function sent(Request $request, EmailVerificationService $service)
    {
        $email = $request->query('email') ?? session('email');

        if ($request->query('send') && $email) {
            return $this->handleSendQuery($request, $service, (string) $email);
        }

        $emailString = is_string($email) ? $email : null;
        $devCode = null;
        $otpExpiresIn = 0;

        if ($emailString) {
            $user = $service->findUnverifiedByEmail($emailString);
            if ($user && ! $user->registeredViaGoogle()) {
                $otpExpiresIn = $service->otpExpiresInSeconds($user);
                if (! $service->isSmtpConfigured()) {
                    $devCode = $service->ensureLocalDevCodeForDisplay($user);
                    $otpExpiresIn = $service->otpExpiresInSeconds($user);
                }
            }
        }

        return view('auth.verify-otp', [
            'email' => $emailString,
            'devCode' => $devCode,
            'mailNotConfigured' => ! $service->isSmtpConfigured(),
            'resendCooldown' => $emailString ? $service->resendCooldownRemaining($emailString) : 0,
            'otpExpiresIn' => $otpExpiresIn,
            'expireMinutes' => (int) config('verification.token_expire_minutes', 10),
        ]);
    }

    public function notice(Request $request)
    {
        $email = $request->user()?->email
            ?? $request->query('email')
            ?? session('email');

        if ($email) {
            return redirect()->route('verification.sent', ['email' => $email]);
        }

        return view('auth.verify-email', ['email' => null]);
    }

    /** Verify OTP, login, redirect dashboard/home. */
    public function verify(VerifyEmailOtpRequest $request, EmailVerificationService $service)
    {
        $email = strtolower(trim((string) $request->input('email')));
        $user = $service->findUnverifiedByEmail($email);

        if (! $user) {
            return $this->verifyFailed($request, __('Invalid or expired verification code.'));
        }

        try {
            $service->verifyCode($user, (string) $request->input('code'));
        } catch (\InvalidArgumentException $e) {
            return $this->verifyFailed($request, $e->getMessage());
        }

        if (config('verification.auto_login_after_verify', true)) {
            Auth::login($user, false);
            $request->session()->regenerate();
            app(CartService::class)->mergeGuestCart();

            if ($user->isVendor()) {
                return redirect()->route('vendor.dashboard')
                    ->with('status', __('Your email has been verified. Welcome!'));
            }

            return AppUrl::redirectIntended(route('market.home'))
                ->with('status', __('Your email has been verified. Welcome!'));
        }

        return redirect()->route('login')
            ->with('status', __('Your email has been verified. You can sign in now.'));
    }

    /** Resend OTP (JSON) with 60s cooldown. */
    public function resend(ResendEmailOtpRequest $request, EmailVerificationService $service)
    {
        $email = strtolower(trim((string) $request->input('email')));

        if (! $service->canResend($email)) {
            $remaining = $service->resendCooldownRemaining($email);

            return response()->json([
                'ok' => false,
                'message' => __('Please wait :seconds seconds before requesting another code.', ['seconds' => $remaining]),
                'cooldown' => $remaining,
            ], 429);
        }

        $user = $service->findUnverifiedByEmail($email);

        if ($user) {
            try {
                $service->sendVerificationEmail($user);
            } catch (\RuntimeException $e) {
                return response()->json(['ok' => false, 'message' => $e->getMessage()], 500);
            }
        }

        $devCode = $service->getLocalDevCode($email);
        $otpExpiresIn = $user ? $service->otpExpiresInSeconds($user) : 0;

        return response()->json([
            'ok' => true,
            'message' => $devCode
                ? __('New code is shown above (email not sent — configure Gmail SMTP).')
                : __('A new verification code has been sent to your email.'),
            'cooldown' => (int) config('verification.resend_cooldown_seconds', 60),
            'dev_code' => $devCode,
            'otp_expires_in' => $otpExpiresIn,
        ]);
    }

    protected function handleSendQuery(Request $request, EmailVerificationService $service, string $email)
    {
        $user = $service->findUnverifiedByEmail($email);

        if (! $user) {
            return redirect()->route('verification.sent')
                ->with('error', __('No pending verification found for this email.'));
        }

        if (! $service->canResend($email)) {
            return redirect()->route('verification.sent', ['email' => $email])
                ->with('warning', __('Please wait before requesting another code.'))
                ->with('email', $email);
        }

        try {
            $service->sendVerificationEmail($user);
        } catch (\RuntimeException $e) {
            return redirect()->route('verification.sent', ['email' => $email])
                ->with('error', $e->getMessage())
                ->with('email', $email);
        }

        $status = session('dev_verification_code')
            ? __('New verification code generated (shown on this page).')
            : __('A new verification code has been sent to your email.');

        return redirect()->route('verification.sent', ['email' => $email])
            ->with('status', $status)
            ->with('email', $email);
    }

    protected function verifyFailed(VerifyEmailOtpRequest $request, string $message)
    {
        $email = $request->input('email');

        return redirect()
            ->route('verification.sent', ['email' => $email])
            ->withInput($request->only('email', 'code'))
            ->with('error', $message);
    }
}
