<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\BackupService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;

class BackupController extends Controller
{
    public function __construct(
        private BackupService $backupService
    ) {}

    /**
     * バックアップ実行
     *
     * POST /api/admin/system/backup
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $data = $this->backupService->createBackup();
        } catch (RuntimeException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'backup_file' => $data['backup_file'],
                'size' => $data['size'],
                'created_at' => $data['created_at'],
            ],
            'message' => 'バックアップを作成しました',
        ]);
    }

    /**
     * バックアップ一覧取得
     *
     * GET /api/admin/system/backups
     */
    public function index(Request $request): JsonResponse
    {
        $data = $this->backupService->listBackups();

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }
}
