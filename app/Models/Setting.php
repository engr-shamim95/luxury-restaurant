<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'value',
        'type',
    ];

    /**
     * Retrieve a setting value by key with optional fallback.
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        try {
            $setting = static::where('key', $key)->first();

            if (! $setting) {
                return $default;
            }

            return static::castValue($setting->value, $setting->type);
        } catch (\Throwable $e) {
            return $default;
        }
    }

    /**
     * Set/update a setting value by key.
     */
    public static function set(string $key, mixed $value, string $type = 'string'): static
    {
        $rawValue = is_array($value) ? json_encode($value) : (string) $value;

        return static::updateOrCreate(
            ['key' => $key],
            ['value' => $rawValue, 'type' => $type]
        );
    }

    /**
     * Cast raw string value to its specified type.
     */
    public static function castValue(?string $value, string $type): mixed
    {
        if ($value === null) {
            return null;
        }

        return match ($type) {
            'boolean', 'bool' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            'integer', 'int' => (int) $value,
            'float', 'decimal', 'double' => (float) $value,
            'json', 'array' => json_decode($value, true) ?? [],
            default => $value,
        };
    }
}
