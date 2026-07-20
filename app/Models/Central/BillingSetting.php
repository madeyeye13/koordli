<?php

namespace App\Models\Central;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class BillingSetting extends Model
{
    protected $fillable = ['key', 'value', 'type', 'label', 'description'];

    public static function get(string $key, mixed $default = null): mixed
    {
        return Cache::remember("billing_setting_{$key}", 3600, function () use ($key, $default) {
            $setting = static::where('key', $key)->first();
            if (!$setting) return $default;

            return match($setting->type) {
                'boolean' => (bool) $setting->value,
                'integer' => (int)  $setting->value,
                'json'    => json_decode($setting->value, true),
                default   => $setting->value,
            };
        });
    }

    public static function set(string $key, mixed $value): void
    {
        if (is_array($value)) $value = json_encode($value);
        if (is_bool($value))  $value = $value ? '1' : '0';

        static::updateOrCreate(['key' => $key], ['value' => (string) $value]);
        Cache::forget("billing_setting_{$key}");
    }

    public static function all_settings(): array
    {
        return static::orderBy('key')->get()->keyBy('key')->toArray();
    }
}