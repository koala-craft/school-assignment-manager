<?php

namespace App\Services;

use App\Models\BackupHistory;
use Illuminate\Support\Facades\Config;
use RuntimeException;

class BackupService
{
    /**
     * バックアップディレクトリ
     */
    private const BACKUP_DIR = 'backups';

    /**
     * PostgreSQL ダンプを実行してバックアップを作成
     */
    public function createBackup(): array
    {
        if (Config::get('database.default') !== 'pgsql') {
            throw new RuntimeException('バックアップは PostgreSQL のみ対応しています。');
        }

        $dir = storage_path('app/' . self::BACKUP_DIR);
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $filename = 'backup_' . now()->format('Ymd_His') . '.sql';
        $path = $dir . '/' . $filename;

        $host = Config::get('database.connections.pgsql.host');
        $port = Config::get('database.connections.pgsql.port');
        $database = Config::get('database.connections.pgsql.database');
        $username = Config::get('database.connections.pgsql.username');
        $password = Config::get('database.connections.pgsql.password');

        $env = [
            'PGPASSWORD' => $password,
        ];

        $cmd = sprintf(
            'pg_dump -h %s -p %s -U %s -F p -f %s %s',
            escapeshellarg($host),
            escapeshellarg($port),
            escapeshellarg($username),
            escapeshellarg($path),
            escapeshellarg($database)
        );

        $descriptors = [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ];

        $proc = proc_open(
            $cmd,
            $descriptors,
            $pipes,
            null,
            array_merge($env, getenv())
        );

        if (! is_resource($proc)) {
            throw new RuntimeException('pg_dump の実行に失敗しました。');
        }

        fclose($pipes[0]);
        $stderr = stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);
        proc_close($proc);

        if (! file_exists($path)) {
            throw new RuntimeException('バックアップファイルの作成に失敗しました。' . ($stderr ?: ''));
        }

        $size = filesize($path);

        $record = BackupHistory::create([
            'filename' => $filename,
            'size' => $size,
            'created_at' => now(),
        ]);

        return [
            'id' => $record->id,
            'backup_file' => $filename,
            'filename' => $filename,
            'size' => $size,
            'created_at' => $record->created_at->toIso8601String(),
        ];
    }

    /**
     * バックアップ一覧を取得
     */
    public function listBackups(): array
    {
        return BackupHistory::query()
            ->orderByDesc('created_at')
            ->get()
            ->map(fn ($b) => [
                'id' => $b->id,
                'filename' => $b->filename,
                'size' => $b->size,
                'created_at' => $b->created_at->toIso8601String(),
            ])
            ->values()
            ->all();
    }
}
