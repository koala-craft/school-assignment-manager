# バックアップAPI

## 概要

API仕様書 11.3, 11.4 で定義されているシステムバックアップ機能。

- **エンドポイント / 対象**:
  - `POST /api/admin/system/backup`（バックアップ実行）
  - `GET /api/admin/system/backups`（バックアップ一覧取得）
- **権限**: 管理者のみ
- **レスポンス / 出力**: バックアップ作成結果、一覧データ

## 状態

- [ ] 未実装
- [ ] 実装中
- [x] 完了
- [ ] 保留

**優先度**: 中

## なぜ未実装だったか

1. **実行環境への依存**
   - `pg_dump` などのコマンド実行が必要
   - Docker 構成では DB が別コンテナにあり、実行場所・ネットワーク設定の検討が必要

2. **セキュリティ上の考慮**
   - シェルコマンド実行はリスクが高い
   - 管理者権限が必要であり、誤用時の影響が大きい

3. **運用設計の検討**
   - バックアップファイルの保存先（ローカル or S3 等）
   - 古いバックアップの自動削除・保持期間のポリシー
   - ディスク容量の管理

4. **優先度・判断**
   - まずは「設定の取得・更新」を実装し、バックアップは別フェーズと判断

## 実装工数の目安

| 作業 | 見積もり |
|------|----------|
| マイグレーション（バックアップ履歴テーブル） | 0.5h |
| pg_dump 実行ロジック | 1-2h |
| 保存先・ディレクトリ設計 | 0.5h |
| バックアップ一覧取得 API | 0.5h |
| ダウンロード API（任意） | 0.5h |
| 保持期間・自動削除ロジック | 1h |
| テスト | 1h |
| **合計** | **約 5-6 時間** |

## 実装するとできること

- 管理者がボタン操作で DB バックアップを取得可能
- 定期的なバックアップの手動実行
- 障害復旧時のリストア用データの作成
- バックアップ一覧の確認・管理
- 運用ルールに応じたバックアップのバージョン管理

## 実装状況（完了時のみ記入）

- [x] backup_history テーブルマイグレーション
- [x] BackupHistory モデル
- [x] BackupService（pg_dump 実行ロジック）
- [x] BackupController（store, index）
- [x] 保存先: storage/app/backups/
- [x] postgresql-client を Dockerfile に追加
- [x] テストスクリプト `scripts/test-backup-api.ps1`

## 追加対応（必要な場合のみ）

Docker イメージを再ビルドする必要があります（postgresql-client 追加のため）。

```bash
docker compose build app --no-cache
docker compose up -d
```

## 権限

| エンドポイント / 機能 | 権限 |
|----------------------|------|
| POST /api/admin/system/backup | 管理者のみ |
| GET /api/admin/system/backups | 管理者のみ |

## 依存関係（ある場合）

- システム設定API（SystemSettingController）の実装完了
- PostgreSQL が Docker コンテナで稼働していること

## 技術メモ（任意）

- Docker 構成では app コンテナから postgres コンテナへの `pg_dump` 実行が必要
- 保存先は `storage/app/backups/`
