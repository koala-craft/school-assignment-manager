<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class SystemSetting extends Model
{
    use HasFactory;

    protected $fillable = ['key', 'value', 'type', 'description'];

    public const CACHE_KEY = 'system_settings';

    public const DEFAULTS = [
        'smtp_host' => ['value' => 'smtp.example.com', 'type' => 'string', 'description' => 'SMTPホスト'],
        'smtp_port' => ['value' => '587', 'type' => 'integer', 'description' => 'SMTPポート'],
        'smtp_username' => ['value' => 'noreply@example.com', 'type' => 'string', 'description' => 'SMTPユーザー名'],
        'notification_timing_days' => ['value' => '3', 'type' => 'integer', 'description' => '期限リマインダー日数'],
        'max_file_size' => ['value' => '52428800', 'type' => 'integer', 'description' => '最大ファイルサイズ（バイト）'],
        'allowed_file_types' => ['value' => '["pdf","docx","xlsx","jpg","png","zip"]', 'type' => 'json', 'description' => '許可するファイル形式'],
        'session_timeout' => ['value' => '120', 'type' => 'integer', 'description' => 'セッションタイムアウト（分）'],
        'password_min_length' => ['value' => '8', 'type' => 'integer', 'description' => 'パスワード最小長'],
    ];

    public static function getAllAsArray(): array
    {
        return Cache::rememberForever(self::CACHE_KEY, function () {
            $settings = self::all()->keyBy('key');
            $result = [];

            foreach (self::DEFAULTS as $key => $default) {
                $setting = $settings->get($key);
                $value = $setting ? $setting->value : $default['value'];
                $type = $setting ? $setting->type : $default['type'];

                $result[$key] = self::castValue($value, $type);
            }

            return $result;
        });
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        $all = self::getAllAsArray();

        return $all[$key] ?? $default;
    }

    public static function set(string $key, mixed $value, string $type = 'string'): void
    {
        $stringValue = match ($type) {
            'json' => is_string($value) ? $value : json_encode($value),
            'boolean' => $value ? '1' : '0',
            'integer' => (string) $value,
            default => (string) $value,
        };

        $default = self::DEFAULTS[$key] ?? null;
        $description = $default['description'] ?? null;

        self::updateOrCreate(
            ['key' => $key],
            ['value' => $stringValue, 'type' => $type, 'description' => $description]
        );

        Cache::forget(self::CACHE_KEY);
    }

    private static function castValue(string $value, string $type): mixed
    {
        return match ($type) {
            'integer' => (int) $value,
            'boolean' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            'json' => json_decode($value, true) ?? [],
            default => $value,
        };
    }
}
