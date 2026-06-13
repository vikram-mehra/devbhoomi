<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class VerifyEmailMail extends Mailable
{
    use Queueable, SerializesModels;

    public User $user;

    public string $code;

    public int $expireMinutes;

    public int $codeLength;

    public function __construct(User $user, string $code)
    {
        $this->user = $user;
        $this->code = $code;
        $this->expireMinutes = (int) config('verification.token_expire_minutes', 30);
        $this->codeLength = (int) config('verification.code_length', 6);
    }

    public function build()
    {
        return $this->subject(__('Your verification code — :app', ['app' => config('app.name')]))
            ->view('emails.verify-email');
    }
}
