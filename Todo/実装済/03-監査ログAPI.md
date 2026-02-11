# 監査ログAPI

## 概要

API仕様書 12 で定義されている監査ログ機能。

- **エンドポイント / 対象**:
  - `GET /api/admin/audit-logs?user_id=1&action=update&date_from=2024-04-01`（一覧取得）
  - `GET /api/admin/audit-logs/{id}`（詳細取得）
- **権限**: 管理者のみ
- **レスポンス / 出力**: ログ一覧（フィルタ・ページネーション対応）、ログ詳細
- **その他**: ログの記録（各操作時に自動記録）

## 状態

- [ ] 未実装
- [ ] 実装中
- [x] 完了
- [ ] 保留

**優先度**: 高

## なぜ未実装だったか

1. **実装順序**
   - ユーザー管理、科目管理、課題管理など、主要な業務 API を優先
   - 監査ログは「記録」と「閲覧」の両方が必要で、範囲が広い

2. **記録ポイントの設計**
   - どの操作をどの粒度で記録するかの設計が必要
   - ログイン/ログアウト、CRUD、権限変更など多数の記録ポイントがある

3. **優先度・判断**
   - 残りの API として最後に残っている状態

## 実装工数の目安

| 作業 | 見積もり |
|------|----------|
| audit_logs テーブルマイグレーション | 0.5h |
| AuditLog モデル・AuditLogService | 1h |
| 各 Controller へのログ記録 | 2-3h |
| 監査ログ一覧取得 API | 1h |
| フィルタ・検索・ページネーション | 1h |
| テスト | 1h |
| **合計** | **約 6.5-7.5 時間** |

## 実装するとできること

- 誰が・いつ・何をしたかの操作履歴を記録・閲覧可能
- セキュリティ要件（誰が何をしたか後から必ず追跡できる）の充足
- 不具合調査や問い合わせ対応時の root cause 分析
- 法令・内部規定に基づく監査対応
- ログイン/ログアウト、ユーザー変更、データ削除などの監査証跡の確保

## 実装状況（完了時のみ記入）

- [x] audit_logs テーブルマイグレーション
- [x] AuditLog モデル・AuditLogService
- [x] 各 Controller へのログ記録（Auth, User, Subject, Assignment, AcademicYear, Term, AssignmentTemplate）
- [x] 監査ログ一覧取得 API（フィルタ・ページネーション対応）
- [x] 監査ログ詳細取得 API
- [x] テストスクリプト `scripts/test-audit-log-api.ps1`

## 追加対応（必要な場合のみ）

（特になし）

## 権限

| エンドポイント / 機能 | 権限 |
|----------------------|------|
| GET /audit-logs | 管理者のみ |
| GET /audit-logs/{id} | 管理者のみ |

## 依存関係（ある場合）

- 認証（Auth）、ユーザー、科目、課題等の CRUD が実装済みであること

## 技術メモ（任意）

- **記録対象**: 認証（login/logout）、CRUD（User, Subject, Assignment, AcademicYear, Term, AssignmentTemplate）
- audit_logs テーブルは更新・削除不可（改ざん防止）
- action の CHECK 制約: create, update, delete, login, logout
