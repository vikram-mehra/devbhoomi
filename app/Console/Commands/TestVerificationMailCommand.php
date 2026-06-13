<?php

namespace App\Console\Commands;

use App\Mail\VerifyEmailMail;
use App\Models\User;
use App\Support\MailConfig;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class TestVerificationMailCommand extends Command
{
    protected $signature = 'mail:test-verification {email : Recipient email address}';

    protected $description = 'Send a test verification email to check SMTP configuration';

    public function handle(): int
    {
        $email = strtolower(trim((string) $this->argument('email')));

        $this->info('Mailer: '.config('mail.default'));
        $this->info('Host: '.config('mail.mailers.smtp.host'));

        if (! MailConfig::isSmtpReady()) {
            $this->error('SMTP not configured. Run: php artisan mail:set-password');

            return self::FAILURE;
        }

        $user = User::query()->whereRaw('LOWER(email) = ?', [$email])->first()
            ?? new User(['name' => 'Test User', 'email' => $email]);

        try {
            Mail::to($email)->send(new VerifyEmailMail($user, '123456'));
            $this->info('Test verification email sent to '.$email);

            if (config('mail.default') === 'log') {
                $this->warn('MAIL_MAILER=log — check storage/logs/laravel.log for the message (not sent to inbox).');
            }

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error('Failed: '.$e->getMessage());

            return self::FAILURE;
        }
    }
}
