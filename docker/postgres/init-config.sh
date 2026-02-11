#!/bin/bash
# PostgreSQL設定ファイルをデータディレクトリにコピーするスクリプト
# このスクリプトは docker-entrypoint-initdb.d で実行されます

set -e

# データディレクトリが初期化済みの場合はスキップ
if [ -f "$PGDATA/postgresql.conf" ]; then
    echo "PostgreSQL already initialized, skipping config copy"
    exit 0
fi

# 設定ファイルが存在する場合のみコピー
if [ -f "/docker-entrypoint-initdb.d/postgresql.conf" ]; then
    echo "Copying PostgreSQL configuration file..."
    cp /docker-entrypoint-initdb.d/postgresql.conf "$PGDATA/postgresql.conf"
    chown postgres:postgres "$PGDATA/postgresql.conf"
    echo "PostgreSQL configuration file copied successfully"
fi
