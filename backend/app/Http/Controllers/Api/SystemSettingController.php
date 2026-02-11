<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SystemSetting;
use Illuminate\Http\Request;

class SystemSettingController extends Controller
{
    /**
     * システム設定一覧取得
     */
    public function index()
    {
        $settings = SystemSetting::getAllAsArray();

        return response()->json([
            'success' => true,
            'data' => $settings,
        ]);
    }

    /**
     * システム設定更新
     */
    public function update(Request $request)
    {
        $allowedKeys = array_keys(SystemSetting::DEFAULTS);

        $request->validate([
            'smtp_host' => ['nullable', 'string', 'max:255'],
            'smtp_port' => ['nullable', 'integer', 'min:1', 'max:65535'],
            'smtp_username' => ['nullable', 'string', 'max:255'],
            'notification_timing_days' => ['nullable', 'integer', 'min:1', 'max:30'],
            'max_file_size' => ['nullable', 'integer', 'min:1024', 'max:104857600'],
            'allowed_file_types' => ['nullable', 'array'],
            'allowed_file_types.*' => ['string', 'max:20'],
            'session_timeout' => ['nullable', 'integer', 'min:5', 'max:1440'],
            'password_min_length' => ['nullable', 'integer', 'min:6', 'max:32'],
        ]);

        $data = $request->only($allowedKeys);

        foreach ($data as $key => $value) {
            if ($value === null) {
                continue;
            }

            $default = SystemSetting::DEFAULTS[$key] ?? ['type' => 'string'];
            $type = $default['type'] ?? 'string';

            SystemSetting::set($key, $value, $type);
        }

        return response()->json([
            'success' => true,
            'message' => 'システム設定を更新しました',
            'data' => SystemSetting::getAllAsArray(),
        ]);
    }
}
