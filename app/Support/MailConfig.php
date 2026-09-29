<?php

namespace App\Support;

class MailConfig
{
    public static function smtpPassword(): string
    {
        $password = trim((string) config('mail.mailers.smtp.password'));
        $password = str_replace(' ', '', $password);

        if ($password === '' || self::isPlaceholder($password)) {
            return '';
        }

        return $password;
    }

    public static function isSmtpReady(): bool
    {
        if ((string) config('mail.default') !== 'smtp') {
            return false;
        }

        $username = trim((string) config('mail.mailers.smtp.username'));
        $from = trim((string) config('mail.from.address'));

        return $username !== ''
            && self::smtpPassword() !== ''
            && $from !== ''
            && $from !== 'noreply@localhost';
    }

    protected static function isPlaceholder(string $value): bool
    {
        $lower = strtolower($value);

        return in_array($lower, [
            'null',
            'change_me',
            'your-16-char-app-password',
            'your@gmail.com',
        ], true);
    }
}
