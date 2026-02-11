<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuditLogService
{
    /**
     * 監査ログを記録する
     *
     * @param string $action create|update|delete|login|logout
     * @param string $model モデル名（例: User, Subject, Assignment）
     * @param int $modelId モデルID（login/logoutの場合はユーザーID）
     * @param array|null $changes 変更内容 [field => ['old' => x, 'new' => y]]
     * @param int|null $userId 操作者ID（省略時はAuth::id()、login時は必須）
     */
    public static function log(
        string $action,
        string $model,
        int $modelId = 0,
        ?array $changes = null,
        ?int $userId = null
    ): void {
        $request = request();

        AuditLog::create([
            'user_id' => $userId ?? Auth::id(),
            'action' => $action,
            'model' => $model,
            'model_id' => $modelId,
            'changes' => $changes,
            'ip_address' => $request?->ip(),
            'user_agent' => $request?->userAgent(),
        ]);
    }

    /**
     * create アクションのログを記録
     */
    public static function logCreate(Model $model, ?array $attributes = null): void
    {
        $changes = $attributes ?? $model->getAttributes();
        self::log('create', class_basename($model), (int) $model->getKey(), $changes);
    }

    /**
     * update アクションのログを記録（変更前後の差分）
     */
    public static function logUpdate(Model $model, array $oldAttributes, array $newAttributes): void
    {
        $changes = [];
        foreach ($newAttributes as $key => $new) {
            $old = $oldAttributes[$key] ?? null;
            if ($old !== $new) {
                $changes[$key] = ['old' => $old, 'new' => $new];
            }
        }
        if (!empty($changes)) {
            self::log('update', class_basename($model), (int) $model->getKey(), $changes);
        }
    }

    /**
     * delete アクションのログを記録
     */
    public static function logDelete(Model $model): void
    {
        self::log('delete', class_basename($model), (int) $model->getKey(), $model->getAttributes());
    }

    /**
     * login アクションのログを記録
     */
    public static function logLogin(int $userId): void
    {
        self::log('login', 'Auth', $userId, null, $userId);
    }

    /**
     * logout アクションのログを記録
     */
    public static function logLogout(int $userId): void
    {
        self::log('logout', 'Auth', $userId, null, $userId);
    }
}
