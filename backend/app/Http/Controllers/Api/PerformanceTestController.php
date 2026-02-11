<?php

namespace App\Http\Controllers\Api;

use App\Helpers\QueryPerformanceHelper;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PerformanceTestController extends Controller
{
    /**
     * ダッシュボードのパフォーマンス測定用エンドポイント
     */
    public function testDashboard(Request $request)
    {
        QueryPerformanceHelper::start();

        // 実際のダッシュボードコントローラーを呼び出し
        $dashboardController = new DashboardController();
        
        $result = null;
        $user = $request->user();
        
        if ($user->isAdmin()) {
            $result = $dashboardController->admin($request);
        } elseif ($user->isTeacher()) {
            $result = $dashboardController->teacher($request);
        } elseif ($user->isStudent() || $user->isStudentAdmin()) {
            $result = $dashboardController->student($request);
        }

        $performance = QueryPerformanceHelper::stop();

        return response()->json([
            'success' => true,
            'performance' => [
                'total_time_ms' => $performance['total_time_ms'],
                'query_count' => $performance['query_count'],
                'query_time_ms' => $performance['query_time_ms'],
                'queries' => array_map(function ($q) {
                    return [
                        'time' => round($q['time'], 2),
                        'query' => $q['query'],
                        'bindings' => $q['bindings'] ?? [],
                    ];
                }, $performance['queries']),
            ],
            'data' => $result?->getData(true)['data'] ?? null,
        ]);
    }
}
