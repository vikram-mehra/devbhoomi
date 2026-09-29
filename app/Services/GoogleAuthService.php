<?php

namespace App\Services;

use App\Models\User;
use App\Support\AppUrl;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Contracts\User as SocialiteUser;

class GoogleAuthService
{
    public function isConfigured(): bool
    {
        $clientId = trim((string) config('services.google.client_id'));
        $clientSecret = trim((string) config('services.google.client_secret'));

        return $clientId !== '' && $clientSecret !== '';
    }

    public function redirectUri(?Request $request = null): string
    {
        $explicit = trim((string) env('GOOGLE_REDIRECT_URI', ''));
        if ($explicit !== '' && strpos($explicit, '${') === false) {
            return rtrim($explicit, '/');
        }

        $request = $request ?? request();

        if ($request && app()->environment('local')) {
            $base = AppUrl::basePath();

            return rtrim($request->getSchemeAndHttpHost().$base, '/').'/auth/google/callback';
        }

        return rtrim(route('auth.google.callback', [], true), '/');
    }

    public function syncRedirectConfig(?Request $request = null): string
    {
        $uri = $this->redirectUri($request);
        config(['services.google.redirect' => $uri]);

        return $uri;
    }

    /**
     * Find or create a customer/vendor user from Google profile.
     *
     * @throws \RuntimeException
     */
    public function resolveUser(SocialiteUser $googleUser): User
    {
        $googleId = (string) $googleUser->getId();
        $email = strtolower(trim((string) $googleUser->getEmail()));

        if ($googleId === '' || $email === '') {
            throw new \RuntimeException(__('Google did not provide a valid email address.'));
        }

        $byGoogle = User::query()->where('google_id', $googleId)->first();
        if ($byGoogle) {
            return $this->syncProfile($byGoogle, $googleUser);
        }

        $byEmail = User::query()->whereRaw('LOWER(email) = ?', [$email])->first();

        if ($byEmail) {
            if ($byEmail->isAdmin()) {
                throw new \RuntimeException(__('Please use the admin login page for this account.'));
            }

            if ($byEmail->google_id && $byEmail->google_id !== $googleId) {
                throw new \RuntimeException(__('This email is linked to a different Google account.'));
            }

            return $this->linkGoogleAccount($byEmail, $googleUser);
        }

        return $this->createUser($googleUser, $googleId, $email);
    }

    protected function createUser(SocialiteUser $googleUser, string $googleId, string $email): User
    {
        return User::create([
            'name' => $this->resolveName($googleUser),
            'email' => $email,
            'password' => Hash::make(Str::random(32)),
            'role' => User::ROLE_USER,
            'google_id' => $googleId,
            'avatar' => $googleUser->getAvatar(),
            'email_verified_at' => now(),
            'account_status' => User::ACCOUNT_ACTIVE,
            'verification_code' => null,
            'verification_expires_at' => null,
        ]);
    }

    protected function linkGoogleAccount(User $user, SocialiteUser $googleUser): User
    {
        $user->forceFill([
            'google_id' => (string) $googleUser->getId(),
            'avatar' => $googleUser->getAvatar() ?: $user->avatar,
            'email_verified_at' => $user->email_verified_at ?? now(),
            'account_status' => User::ACCOUNT_ACTIVE,
            'verification_code' => null,
            'verification_expires_at' => null,
        ])->save();

        return $this->syncProfile($user, $googleUser);
    }

    protected function syncProfile(User $user, SocialiteUser $googleUser): User
    {
        $name = $this->resolveName($googleUser);

        if ($name && $user->name !== $name) {
            $user->name = $name;
        }

        if ($googleUser->getAvatar() && ! $user->avatar) {
            $user->avatar = $googleUser->getAvatar();
        }

        if ($user->isDirty()) {
            $user->save();
        }

        return $user->fresh();
    }

    protected function resolveName(SocialiteUser $googleUser): string
    {
        $name = trim((string) $googleUser->getName());

        if ($name !== '') {
            return $name;
        }

        $nickname = trim((string) $googleUser->getNickname());

        return $nickname !== '' ? $nickname : __('Google User');
    }
}
