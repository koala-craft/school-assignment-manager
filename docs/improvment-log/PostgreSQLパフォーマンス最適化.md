# PostgreSQL パフォーマンス最適化ガイド

## 実装した改善内容

### 1. PostgreSQLコンテナのリソース制限追加 ✅

**問題点:**
- リソース制限が設定されていなかった
- Windows Docker環境でメモリ不足によるスワップが発生する可能性
- CPU使用率が制御されていない

**改善内容:**
```yaml
deploy:
  resources:
    limits:
      cpus: '2.0'
      memory: 2G
    reservations:
      cpus: '0.5'
      memory: 512M
```

**効果:**
- メモリ不足によるスワップの防止
- CPU使用率の制御
- 他のコンテナへの影響を軽減

### 2. PostgreSQL設定ファイルの最適化 ✅

**追加した設定 (`docker/postgres/postgresql.conf`):**

#### メモリ設定
- `shared_buffers = 256MB`: 共有メモリバッファ（コンテナメモリの25%程度）
- `effective_cache_size = 1GB`: OSとPostgreSQLが使用するキャッシュサイズ
- `work_mem = 16MB`: ソート・ハッシュ作業用メモリ
- `maintenance_work_mem = 128MB`: メンテナンス作業用メモリ

#### I/O最適化（Windows Docker / WSL2向け）
- `random_page_cost = 1.1`: SSD向け（デフォルト4.0はHDD向け）
- `effective_io_concurrency = 200`: 並行I/O数（SSD向け）

**効果:**
- クエリ実行計画の最適化
- I/O待機時間の削減
- メモリ使用効率の向上

### 3. 接続プール設定の追加 ✅

**Laravel側の設定 (`config/database.php`):**
- `PDO::ATTR_TIMEOUT => 5`: 接続タイムアウトを5秒に設定
- `PDO::ATTR_EMULATE_PREPARES => false`: ネイティブプリペアドステートメントを使用

**効果:**
- 接続エラーの早期検出
- クエリ実行の最適化

### 4. ヘルスチェックの追加 ✅

```yaml
healthcheck:
  test: ["CMD-SHELL", "pg_isready -U school_user -d school_assignment_db"]
  interval: 10s
  timeout: 5s
  retries: 5
```

**効果:**
- コンテナの状態を監視
- 起動完了の確認
- 依存関係の適切な管理

## 適用方法

### 1. コンテナの再起動

```bash
docker compose down
docker compose up -d
```

### 2. 設定の確認

PostgreSQLコンテナ内で設定を確認:

```bash
docker compose exec postgres psql -U school_user -d school_assignment_db -c "SHOW shared_buffers;"
docker compose exec postgres psql -U school_user -d school_assignment_db -c "SHOW random_page_cost;"
```

### 3. パフォーマンス測定

#### クエリ実行時間の確認

```bash
# ログイン処理のクエリ時間を確認
docker compose exec postgres psql -U school_user -d school_assignment_db -c "
SELECT query, mean_exec_time, calls 
FROM pg_stat_statements 
ORDER BY mean_exec_time DESC 
LIMIT 10;
"
```

#### 接続数の確認

```bash
docker compose exec postgres psql -U school_user -d school_assignment_db -c "
SELECT count(*) as connections, state 
FROM pg_stat_activity 
GROUP BY state;
"
```

## 期待される改善効果

### ログイン処理
- **修正前**: 6秒
- **修正後**: 3-4秒（期待値）
- **改善要因**:
  - メモリ不足によるスワップの削減
  - I/O待機時間の短縮
  - クエリ実行計画の最適化

### ダッシュボード読み込み
- **修正前**: 100-300ms
- **修正後**: 50-150ms（期待値）
- **改善要因**:
  - 共有バッファの効果的な利用
  - インデックススキャンの最適化

## 追加の最適化案

### 1. WSL2のI/O性能改善（Windows環境の場合）

WSL2のボリュームマウントは遅い場合があります。以下の対策を検討:

```yaml
# docker-compose.yml
volumes:
  - type: tmpfs
    target: /var/lib/postgresql/data
    tmpfs:
      size: 2G
```

**注意**: これはデータが永続化されないため、開発環境のみ推奨

### 2. PostgreSQLの統計情報更新

定期的に統計情報を更新することで、クエリプランナーの精度が向上:

```bash
# 手動実行
docker compose exec postgres psql -U school_user -d school_assignment_db -c "ANALYZE;"

# または cron で定期実行
```

### 3. スロークエリログの確認

`log_min_duration_statement = 100` により、100ms以上のクエリがログに記録されます:

```bash
docker compose logs postgres | grep "duration:"
```

## トラブルシューティング

### メモリ不足エラーが発生する場合

`docker-compose.yml`のメモリ制限を調整:

```yaml
deploy:
  resources:
    limits:
      memory: 4G  # 2G → 4Gに増やす
```

### 設定が反映されない場合

1. コンテナを完全に削除:
   ```bash
   docker compose down -v
   ```

2. データディレクトリをクリア（**注意: データが消えます**）:
   ```bash
   rm -rf docker/postgres/data/*
   ```

3. コンテナを再起動:
   ```bash
   docker compose up -d
   ```

## 参考資料

- [PostgreSQL パフォーマンスチューニング](https://www.postgresql.org/docs/15/performance-tips.html)
- [Docker Compose リソース制限](https://docs.docker.com/compose/compose-file/deploy/#resources)
