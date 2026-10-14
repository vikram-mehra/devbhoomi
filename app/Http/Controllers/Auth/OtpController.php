<?php

namespace App\Http\Controllers\Auth;

use App\Exceptions\Msg91OtpException;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use App\Services\CartService;
use App\Services\Msg91OtpService;
use App\Support\AppUrl;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class OtpController extends Controller
{
    public function __construct()
    {
        $this->middleware('guest');
        $this->middleware('throttle:otp-send')->only(['sendOtp', 'resendOtp']);
        $this->middleware('throttle:otp-verify')->only('verifyOtp');
    }

    public function showPhoneForm(Msg91OtpService $otp)
    {
        AppUrl::rememberIntendedFromPrevious();
        app(CartService::class)->rememberGuestSession();

        if (! session()->hasOldInput('phone') && ! session()->hasOldInput('code')) {
            session()->forget('otp_login_phone');
        }

        return view('auth.phone-login', $this->otpViewData($otp));
    }

    public function sendOtp(Request $request, Msg91OtpService $otp)
    {
        $this->validateIndianMobile($request);

        try {
            $result = $otp->send($request->input('phone'));
        } catch (Msg91OtpException $e) {
            if ($e->getMessage() === __('Kindly enter a valid mobile number')) {
                throw ValidationException::withMessages(['phone' => $e->getMessage()]);
            }

            return $this->otpFailure($request, $e->getMessage(), $request->input('phone'));
        }

        $request->session()->put('otp_login_phone', $result['phone']);

        $sentMessage = ! empty($result['dummy'])
            ? __('Dummy OTP sent. Use :code to sign in.', ['code' => $otp->dummyCode()])
            : __('OTP sent to :phone.', ['phone' => $this->displayPhone($result['phone'])]);

        return $this->otpSuccess($request, $sentMessage, $result);
    }

    public function resendOtp(Request $request, Msg91OtpService $otp)
    {
        $phone = $request->input('phone', $request->session()->get('otp_login_phone'));
        $request->merge(['phone' => $phone]);
        $this->validateIndianMobile($request);

        try {
            $result = $otp->resend($request->input('phone'));
        } catch (Msg91OtpException $e) {
            if ($e->getMessage() === __('Kindly enter a valid mobile number')) {
                throw ValidationException::withMessages(['phone' => $e->getMessage()]);
            }

            return $this->otpFailure($request, $e->getMessage(), $request->input('phone'));
        }

        $request->session()->put('otp_login_phone', $result['phone']);

        $resentMessage = ! empty($result['dummy'])
            ? __('Dummy OTP resent. Use :code to sign in.', ['code' => $otp->dummyCode()])
            : __('OTP resent to :phone.', ['phone' => $this->displayPhone($result['phone'])]);

        return $this->otpSuccess($request, $resentMessage, $result);
    }

    public function verifyOtp(Request $request, Msg91OtpService $otp)
    {
        $length = $otp->otpLength();
        $this->validateIndianMobile($request);
        $request->validate([
            'code' => 'required|string|min:'.$length.'|max:'.$length,
        ]);

        try {
            $phone = $otp->verify($request->input('phone'), $request->input('code'));
            $user = $this->resolveCustomer($otp, $phone);
        } catch (Msg91OtpException $e) {
            $field = $e->getMessage() === __('Kindly enter a valid mobile number') ? 'phone' : 'code';
            throw ValidationException::withMessages([
                $field => $e->getMessage(),
            ]);
        }

        app(CartService::class)->rememberGuestSession();
        Auth::login($user, $request->boolean('remember', true));
        $request->session()->regenerate();
        $request->session()->forget('otp_login_phone');
        app(CartService::class)->mergeGuestCart();

        $home = $user->isVendor() ? route('vendor.dashboard') : RouteServiceProvider::HOME;
        $redirect = AppUrl::redirectIntended($home)->with('status', __('Signed in with mobile OTP.'));

        if ($request->expectsJson()) {
            return response()->json([
                'ok' => true,
                'message' => __('Mobile number verified. Signing you in…'),
                'redirect' => $redirect->getTargetUrl(),
            ]);
        }

        return $redirect;
    }

    /**
     * @param  array{national: string, mobile: string, variants: list<string>}  $phone
     */
    protected function resolveCustomer(Msg91OtpService $otp, array $phone): User
    {
        $user = $otp->findUserByPhone($phone);

        if ($user) {
            if ($user->isAdmin() || $user->isSeo()) {
                throw Msg91OtpException::privilegedAccount();
            }

            $user->forceFill([
                'phone' => $phone['national'],
                'phone_verified_at' => now(),
                'account_status' => User::ACCOUNT_ACTIVE,
            ])->save();

            return $user->fresh();
        }

        return User::create([
            'name' => __('Customer'),
            'email' => $this->uniqueOtpEmail($phone['national']),
            'password' => Hash::make(Str::random(40)),
            'role' => User::ROLE_USER,
            'phone' => $phone['national'],
            'phone_verified_at' => now(),
            'account_status' => User::ACCOUNT_ACTIVE,
            'email_verified_at' => null,
            'google_id' => null,
            'verification_code' => null,
            'verification_expires_at' => null,
        ]);
    }

    protected function uniqueOtpEmail(string $national): string
    {
        $base = 'otp.'.$national.'@users.invalid';
        if (! User::query()->where('email', $base)->exists()) {
            return $base;
        }

        return 'otp.'.$national.'.'.Str::lower(Str::random(6)).'@users.invalid';
    }

    protected function validateIndianMobile(Request $request): void
    {
        $digits = preg_replace('/\D+/', '', (string) $request->input('phone', '')) ?? '';
        $request->merge(['phone' => $digits]);

        $request->validate([
            'phone' => ['required', 'digits:10', 'regex:/^[6-9][0-9]{9}$/'],
        ], [
            'phone.required' => __('Kindly enter a valid mobile number'),
            'phone.digits' => __('Kindly enter a valid mobile number'),
            'phone.regex' => __('Kindly enter a valid mobile number'),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    protected function otpViewData(Msg91OtpService $otp, ?string $phone = null): array
    {
        $phone = $phone ?: old('phone', session('otp_login_phone'));

        return [
            'otpAvailable' => $otp->isAvailable(),
            'otpLength' => $otp->otpLength(),
            'otpPhone' => $phone,
            'otpCooldown' => $phone ? $otp->cooldownRemaining((string) $phone) : 0,
        ];
    }

    /**
     * @param  array<string, mixed>  $result
     */
    protected function otpSuccess(Request $request, string $message, array $result)
    {
        if ($request->expectsJson()) {
            return response()->json([
                'ok' => true,
                'message' => $message,
                'phone' => $result['phone'],
                'cooldown' => $result['cooldown'],
                'expires_in' => $result['expires_in'],
                'dummy' => ! empty($result['dummy']),
                'display_phone' => $this->displayPhone($result['phone']),
            ]);
        }

        return back()->with('status', $message);
    }

    protected function otpFailure(Request $request, string $message, ?string $phone)
    {
        if ($phone) {
            try {
                $normalized = app(Msg91OtpService::class)->normalizePhone($phone);
                $request->session()->flash('otp_login_phone', $normalized['national']);
            } catch (Msg91OtpException $e) {
                $request->session()->flash('otp_login_phone', $phone);
            }
        }

        if ($request->expectsJson()) {
            return response()->json([
                'ok' => false,
                'message' => $message,
            ], 422);
        }

        return back()->withInput($request->only('phone'))->with('error', $message);
    }

    protected function displayPhone(string $national): string
    {
        return '+91 '.substr($national, 0, 5).' '.substr($national, 5);
    }
}
