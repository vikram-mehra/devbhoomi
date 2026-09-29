<?php

namespace App\Services;

use Illuminate\Http\Response;

class ContactCaptcha
{
    public const SESSION_KEY = 'contact_captcha_code';

    public function generateCode(): string
    {
        $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
        $code = '';
        for ($i = 0; $i < 5; $i++) {
            $code .= $chars[random_int(0, strlen($chars) - 1)];
        }

        session([self::SESSION_KEY => $code]);

        return $code;
    }

    public function imageResponse(): Response
    {
        $code = $this->generateCode();

        $width = 150;
        $height = 48;
        $image = imagecreatetruecolor($width, $height);

        $bg = imagecolorallocate($image, 244, 241, 234);
        $textColor = imagecolorallocate($image, 30, 61, 42);
        $lineColor = imagecolorallocate($image, 201, 162, 39);
        $noiseColor = imagecolorallocate($image, 180, 190, 176);

        imagefilledrectangle($image, 0, 0, $width, $height, $bg);

        for ($i = 0; $i < 5; $i++) {
            imageline(
                $image,
                random_int(0, $width),
                random_int(0, $height),
                random_int(0, $width),
                random_int(0, $height),
                $lineColor
            );
        }

        for ($i = 0; $i < 80; $i++) {
            imagesetpixel($image, random_int(0, $width - 1), random_int(0, $height - 1), $noiseColor);
        }

        $font = 5;
        $charWidth = imagefontwidth($font);
        $charHeight = imagefontheight($font);
        $startX = 16;

        foreach (str_split($code) as $index => $char) {
            $x = $startX + ($index * ($charWidth + 8));
            $y = (int) (($height - $charHeight) / 2) + random_int(-4, 4);
            imagestring($image, $font, $x, $y, $char, $textColor);
        }

        ob_start();
        imagepng($image);
        $png = ob_get_clean();
        imagedestroy($image);

        return response($png, 200, [
            'Content-Type' => 'image/png',
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
            'Pragma' => 'no-cache',
        ]);
    }

    public function matches(?string $input): bool
    {
        $expected = session(self::SESSION_KEY);
        if (! is_string($expected) || $expected === '') {
            return false;
        }

        return hash_equals(strtoupper($expected), strtoupper(trim((string) $input)));
    }

    public function forget(): void
    {
        session()->forget(self::SESSION_KEY);
    }
}
