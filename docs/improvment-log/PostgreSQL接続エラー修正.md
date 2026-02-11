# PostgreSQL接続エラー修正ガイド

## 問題

```
SQLSTATE[08006] [7] connection to server at "postgres" (172.23.0.3), port 5432 failed: Connection refused
```

PostgreSQLコンテナが起動に失敗している可能性があります。

## 原因

PostgreSQL 15 Alpineイメージでは、設定ファイルのパスが `/etc/postgresql/postgresql.conf` ではなく、データディレクトリ内（`/var/lib/postgresql/data/postgresql.conf`）を使用します。

`command: postgres -c config_file=/etc/postgresql/postgresql.conf` を指定していたため、コンテナが起動に失敗していました。

## 修正内容

### 1. 設定方法の変更

**修正前:**
- 設定ファイルを `/etc/postgresql/postgresql.conf` にマウント
- `command` で設定ファイルを指定

**修正後:**
- 環境変数で主要な設定を渡す（Alpineイメージで確実に動作）
- 設定ファイルは初期化スクリプトでデータディレクトリにコピー

### 2. 環境変数での設定

以下の環境変数でパフォーマンス設定を渡します：

```yaml
POSTGRES_SHARED_BUFFERS: "256MB"
POSTGRES_EFFECTIVE_CACHE_SIZE: "1GB"
POSTGRES_WORK_MEM: "16MB"
POSTGRES_MAINTENANCE_WORK_MEM: "128MB"
POSTGRES_RANDOM_PAGE_COST: "1.1"
POSTGRES_EFFECTIVE_IO_CONCURRENCY: "200"
```

**注意**: PostgreSQLの公式イメージでは、環境変数名が `POSTGRES_*` 形式では直接設定されません。  
代わりに、初期化スクリプトで設定を適用します。

## 適用方法

### 1. コンテナの状態確認

```bash
# PostgreSQLコンテナの状態を確認
docker compose ps postgres

# ログを確認
docker compose logs postgres
```

### 2. コンテナの再起動

```bash
# コンテナを停止
docker compose down

# コンテナを起動
docker compose up -d postgres

# ログを確認して起動を確認
docker compose logs -f postgres
```

### 3. 接続確認

```bash
# PostgreSQLに接続できるか確認
docker compose exec postgres psql -U school_user -d school_assignment_db -c "SELECT version();"
```

## 代替案：設定ファイルを使う場合

環境変数での設定がうまくいかない場合は、以下の方法で設定ファイルを使用できます：

### 方法1: データディレクトリに直接マウント（推奨）

```yaml
volumes:
  - ./docker/postgres/data:/var/lib/postgresql/data
  - ./docker/postgres/postgresql.conf:/var/lib/postgresql/data/postgresql.conf:ro
```

**注意**: データディレクトリが既に初期化されている場合、設定ファイルのマウントは失敗する可能性があります。

### 方法2: 初期化スクリプトでコピー（現在の実装）

`init-config.sh` スクリプトで設定ファイルをコピーします。  
この方法は、データディレクトリが既に存在する場合でも安全です。

## トラブルシューティング

### コンテナが起動しない場合

1. **ログを確認**:
   ```bash
   docker compose logs postgres
   ```

2. **データディレクトリをクリア**（**注意: データが消えます**）:
   ```bash
   docker compose down -v
   rm -rf docker/postgres/data/*
   docker compose up -d postgres
   ```

3. **設定ファイルの構文エラーを確認**:
   ```bash
   docker compose exec postgres postgres --check-config
   ```

### 接続が拒否される場合

1. **コンテナが起動しているか確認**:
   ```bash
   docker compose ps
   ```

2. **ネットワークを確認**:
   ```bash
   docker network inspect school-assignment-manager_school-app-network
   ```

3. **ポートが使用されているか確認**:
   ```bash
   netstat -an | findstr 5432
   ```

## パフォーマンス設定の確認

設定が正しく適用されているか確認：

```bash
docker compose exec postgres psql -U school_user -d school_assignment_db -c "
SHOW shared_buffers;
SHOW random_page_cost;
SHOW effective_io_concurrency;
"
```

## 参考

- [PostgreSQL Docker公式イメージ](https://hub.docker.com/_/postgres)
- [PostgreSQL設定ファイル](https://www.postgresql.org/docs/15/config-setting.html)
