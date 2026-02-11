# Step 04: 管理者画面（バックアップ・監査）

## 概要

管理者のバックアップ管理画面と監査ログ閲覧画面を実装する。

**参照**: [画面一覧](../docs/画面一覧.md) A-008, A-009、[API仕様書](../docs/API仕様書.md) 11.3, 11.4, 12

## 状態

- [ ] 未実装
- [ ] 実装中
- [ ] 完了

**優先度**: 中

## 実装内容

### A-008: バックアップ管理画面

| 項目 | 内容 |
|------|------|
| URL | `/admin/backups` |
| API | GET /admin/system/backups、POST /admin/system/backup |
| 機能 | バックアップ一覧表示、新規バックアップ実行ボタン |
| 拡張（任意） | ダウンロードAPI があればダウンロードボタン |

### A-009: 監査ログ閲覧画面

| 項目 | 内容 |
|------|------|
| URL | `/admin/audit-logs` |
| API | GET /admin/audit-logs（フィルタ：user_id, action, date_from, date_to） |
| 機能 | ログ一覧、フィルター、ページネーション、詳細表示 |

## 成果物

- `frontend/src/views/admin/BackupListView.vue`
- `frontend/src/views/admin/AuditLogView.vue`

## 依存関係

- Step 01, 02, 03 完了

## 実装工数目安

| 作業 | 見積もり |
|------|----------|
| バックアップ管理 | 1.5h |
| 監査ログ閲覧 | 2h |
| **合計** | **約 3.5 時間** |
