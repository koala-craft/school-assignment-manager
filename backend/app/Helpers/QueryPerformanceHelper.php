<?php

namespace App\Helpers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class QueryPerformanceHelper
{
    private static array $queryLogs = [];
    private static float $startTime = 0;

    /**
     * クエリ測定を開始
     */
    public static function start(): void
    {
        self::$startTime = microtime(true);
        self::$queryLogs = [];
        DB::enableQueryLog();
    }

    /**
     * クエリ測定を終了し、結果を返す
     */
    public static function stop(): array
    {
        $endTime = microtime(true);
        $queries = DB::getQueryLog();
        DB::disableQueryLog();

        $totalTime = ($endTime - self::$startTime) * 1000; // ミリ秒
        $queryCount = count($queries);
        $queryTime = array_sum(array_column($queries, 'time'));

        $result = [
            'total_time_ms' => round($totalTime, 2),
            'query_count' => $queryCount,
            'query_time_ms' => round($queryTime, 2),
            'queries' => $queries,
        ];

        // ログに記録
        Log::info('Query Performance', [
            'total_time_ms' => $result['total_time_ms'],
            'query_count' => $result['query_count'],
            'query_time_ms' => $result['query_time_ms'],
        ]);

        return $result;
    }

    /**
     * クエリ結果をフォーマットして返す
     */
    public static function formatResults(array $results): string
    {
        $output = "=== Query Performance Results ===\n";
        $output .= "Total Time: {$results['total_time_ms']}ms\n";
        $output .= "Query Count: {$results['query_count']}\n";
        $output .= "Query Time: {$results['query_time_ms']}ms\n\n";
        $output .= "Queries:\n";

        foreach ($results['queries'] as $index => $query) {
            $output .= sprintf(
                "[%d] Time: %.2fms | %s\n",
                $index + 1,
                $query['time'],
                $query['query']
            );
        }

        return $output;
    }
}
