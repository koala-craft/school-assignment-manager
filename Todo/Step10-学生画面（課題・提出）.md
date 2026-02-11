# Step 10: 学生画面（課題・提出）

## 概要

学生のダッシュボード、課題一覧、課題詳細・提出画面を実装する。

**参照**: [画面一覧](../docs/画面一覧.md) 5. 一般学生画面（S-001〜S-003）、[API仕様書](../docs/API仕様書.md) 6, 7, 8

## 状態

- [ ] 未実装
- [ ] 実装中
- [ ] 完了

**優先度**: 高

## 実装内容

### S-001: 学生ダッシュボード

| 項目 | 内容 |
|------|------|
| URL | `/student/dashboard` |
| API | GET /dashboard/student |
| 機能 | 履修科目数、総課題数、未提出数、平均点、未対応課題リスト、最近採点された課題 |

### S-002: 課題一覧画面

| 項目 | 内容 |
|------|------|
| URL | `/student/assignments` |
| API | GET /assignments |
| 機能 | 自身の課題一覧、未対応/対応済みフィルター、提出期限順ソート |

### S-003: 課題詳細・提出画面

| 項目 | 内容 |
|------|------|
| URL | `/student/assignments/:id` |
| API | GET /assignments/{id}、POST /assignments/{id}/submit |
| 機能 | 課題詳細表示、ファイルアップロード、テキスト入力、提出ボタン |

## 成果物

- `frontend/src/views/student/DashboardView.vue`
- `frontend/src/views/student/AssignmentListView.vue`
- `frontend/src/views/student/AssignmentDetailView.vue`

## 依存関係

- Step 01〜07 完了

## 実装工数目安

| 作業 | 見積もり |
|------|----------|
| 学生ダッシュボード | 2h |
| 課題一覧 | 1.5h |
| 課題詳細・提出 | 2.5h |
| **合計** | **約 6 時間** |
