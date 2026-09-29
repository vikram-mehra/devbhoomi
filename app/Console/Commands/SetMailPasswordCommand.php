<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class SetMailPasswordCommand extends Command
{
    protected $signature = 'mail:set-password
                            {--password= : Gmail App Password (16 characters, no quotes needed)}';

    protected $description = 'Save Gmail App Password to .env (MAIL_PASSWORD) and clear config cache';

    public function handle(): int
    {
        $password = (string) ($this->option('password') ?: $this->secret('Gmail App Password (from https://myaccount.google.com/apppasswords)'));
        $password = str_replace(' ', '', trim($password));

        if (strlen($password) < 16) {
            $this->error('App Password must be 16 characters. Create one at https://myaccount.google.com/apppasswords');

            return self::FAILURE;
        }

        $path = base_path('.env');
        if (! is_file($path)) {
            $this->error('.env file not found at '.$path);

            return self::FAILURE;
        }

        $this->writeEnvValue($path, 'MAIL_PASSWORD', $password);
        $this->call('config:clear');

        $this->info('MAIL_PASSWORD saved and config cache cleared.');
        $this->line('Test: php artisan mail:test-verification your@email.com');

        return self::SUCCESS;
    }

    protected function writeEnvValue(string $path, string $key, string $value): void
    {
        $content = (string) file_get_contents($path);
        $line = $key.'='.$value;
        $pattern = '/^'.preg_quote($key, '/').'=.*$/m';

        if (preg_match($pattern, $content)) {
            $content = (string) preg_replace($pattern, $line, $content);
        } else {
            $content = rtrim($content).PHP_EOL.$line.PHP_EOL;
        }

        file_put_contents($path, $content);
    }
}
