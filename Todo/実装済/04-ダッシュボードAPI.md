# ダッシュボードAPI

## 概要

API仕様書 13 で定義されているダッシュボード機能。

- **エンドポイント / 対象**:
  - `GET /api/dashboard/admin`（管理者ダッシュボード）
  - `GET /api/dashboard/teacher`（教員ダッシュボード）
  - `GET /api/dashboard/student`（学生ダッシュボード）
- **権限**: 各ロールに応じて異なる（下表参照）
- **レスポンス / 出力**: ダッシュボード用の集計データ・一覧

## 状態

- [ ] 未実装
- [ ] 実装中
- [x] 完了
- [ ] 保留

**優先度**: 中

## なぜ未実装だったか

1. **実装順序**
   - 認証・CRUD・レポート等の基盤 API を優先
   - ダッシュボードは集計・表示のため、元データが揃ってから実装

2. **実装の優先度**
   - 各画面で個別にデータ取得可能
   - 一覧表示の効率化として後回しにした

3. **優先度・判断**
   - 主要機能の実装完了後に着手

## 実装工数の目安

| 作業 | 見積もり |
|------|----------|
| DashboardController 作成 | 1h |
| 管理者ダッシュボード | 1h |
| 教員ダッシュボード | 1h |
| 学生ダッシュボード | 1h |
| ルート追加・テスト | 0.5h |
| **合計** | **約 4.5 時間** |

## 実装するとできること

- 管理者がシステム全体の状況を一覧で把握可能
- 教員が担当科目の課題・採点状況を一覧で把握可能
- 学生が自分の履修・提出状況を一覧で把握可能
- 各ロールに応じたホーム画面の実装基盤

## 実装状況（完了時のみ記入）

- [x] DashboardController 作成
- [x] 管理者ダッシュボード（total_users, total_subjects, total_assignments, active_students, recent_activities, submission_stats）
- [x] 教員ダッシュボード（my_subjects, total_students, total_assignments, pending_grading, recent_submissions, upcoming_deadlines）
- [x] 学生ダッシュボード（enrolled_subjects, total_assignments, not_submitted, graded, upcoming_deadlines, recent_grades）
- [x] テストスクリプト `scripts/test-dashboard-api.ps1`

## 追加対応（必要な場合のみ）

（特になし）

## 権限

| エンドポイント / 機能 | 権限 |
|----------------------|------|
| GET /dashboard/admin | 管理者のみ |
| GET /dashboard/teacher | 教員・管理者 |
| GET /dashboard/student | 学生・管理者 |

## 依存関係（ある場合）

- 監査ログAPI（recent_activities の取得）
- ユーザー、科目、課題、提出状況、履修などのモデル・データ

## 技術メモ（任意）

- 教員ダッシュボードは subject_teachers 経由で担当科目を取得
- 学生ダッシュボードは enrollments 経由で履修科目を取得
- recent_activities は AuditLog の直近10件
