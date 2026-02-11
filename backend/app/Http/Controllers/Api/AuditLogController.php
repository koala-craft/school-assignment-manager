<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AuditLogResource;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    /**
     * 監査ログ一覧取得
     */
    public function index(Request $request)
    {
        $query = AuditLog::query()->with('user');

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        if ($request->filled('model')) {
            $query->where('model', $request->model);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $query->orderBy('created_at', 'desc');

        $perPage = $request->input('per_page', 15);
        $logs = $query->paginate($perPage);

        $items = AuditLogResource::collection($logs->items());
        $items->withoutWrapping();

        return response()->json([
            'success' => true,
            'data' => [
                'items' => $items->resolve(),
                'pagination' => [
                    'total' => $logs->total(),
                    'per_page' => $logs->perPage(),
                    'current_page' => $logs->currentPage(),
                    'last_page' => $logs->lastPage(),
                    'from' => $logs->firstItem(),
                    'to' => $logs->lastItem(),
                ],
            ],
        ]);
    }

    /**
     * 監査ログ詳細取得
     */
    public function show(string $id)
    {
        $log = AuditLog::with('user')->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => new AuditLogResource($log),
        ]);
    }
}
