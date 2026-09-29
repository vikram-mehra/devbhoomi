<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\EmailVerificationService;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class RegisterController extends Controller
{
    use RegistersUsers;

    public function __construct()
    {
        $this->middleware('guest');
        $this->middleware('throttle:registration')->only('register');
    }

    public function register(Request $request)
    {
        $this->validator($request->all())->validate();

        $user = $this->create($request->all());

        try {
            app(EmailVerificationService::class)->sendVerificationEmail($user);
        } catch (\RuntimeException $e) {
            return back()
                ->withInput($request->only('name', 'email'))
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

    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'website' => ['nullable', 'max:0'],
        ]);
    }

    protected function create(array $data)
    {
        return User::create([
            'name' => $data['name'],
            'email' => strtolower(trim($data['email'])),
            'password' => Hash::make($data['password']),
            'role' => User::ROLE_USER,
            'account_status' => User::ACCOUNT_INACTIVE,
            'email_verified_at' => null,
            'verification_code' => null,
            'verification_expires_at' => null,
            'google_id' => null,
        ]);
    }
}
