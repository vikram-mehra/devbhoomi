<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    protected $fillable = ['key', 'value'];

    public static function getValue(string $key, $default = null)
    {
        return Cache::remember('setting.'.$key, 3600, function () use ($key, $default) {
            $row = static::query()->where('key', $key)->first();

            return $row ? $row->value : $default;
        });
    }

    public static function setValue(string $key, ?string $value): void
    {
        static::query()->updateOrCreate(['key' => $key], ['value' => $value]);
        Cache::forget('setting.'.$key);
    }
}
