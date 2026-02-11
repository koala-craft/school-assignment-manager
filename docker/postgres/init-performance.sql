-- PostgreSQL パフォーマンス設定の初期化SQL
-- このファイルはデータベース初期化時（初回のみ）に自動実行されます

-- 統計情報の収集を有効化（再起動不要な設定）
ALTER DATABASE school_assignment_db SET track_io_timing = on;
ALTER DATABASE school_assignment_db SET track_activity_query_size = 2048;

-- 注意: shared_buffers, work_mem などの設定は postgresql.conf で設定する必要があります
-- これらの設定はコンテナ起動後に手動で適用するか、設定ファイルをマウントしてください
