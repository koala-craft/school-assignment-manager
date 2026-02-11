# Step 07: 教員画面（提出状況・採点）

## 概要

提出状況マトリクス表示、提出物詳細、採点画面を実装する。

**参照**: [画面一覧](../docs/画面一覧.md) T-009〜T-012、[API仕様書](../docs/API仕様書.md) 7, 8

## 状態

- [ ] 未実装
- [ ] 実装中
- [ ] 完了

**優先度**: 高

## 実装内容

### T-009: 提出状況一覧（マトリクス）

| 項目 | 内容 |
|------|------|
| URL | `/teacher/assignments/:id/submissions` |
| API | GET /admin/submissions、GET /admin/assignments/{id}/submissions/statistics |
| 機能 | 学生×提出物マトリクス、状態フィルター、CSV出力、一括ダウンロード |

### T-010: 提出物詳細画面

| 項目 | 内容 |
|------|------|
| URL | `/submissions/:id` |
| API | GET /submissions/{id}、POST /admin/submissions/{id}/grade |
| 機能 | 提出内容、ファイル、採点、再提出指示、履歴タブ |

### T-011: 採点画面

| 項目 | 内容 |
|------|------|
| URL | `/teacher/grading` |
| API | GET /admin/submissions（未採点フィルタ） |
| 機能 | 未採点一覧、採点エリア、連続採点（次へ） |

### T-012: 学生別提出状況詳細

| 項目 | 内容 |
|------|------|
| URL | `/teacher/students/:id/submissions` |
| API | GET /admin/submissions?user_id=xxx |
| 機能 | 学生の提出状況サマリ、科目フィルター |

## 成果物

- `frontend/src/views/teacher/submissions/SubmissionMatrixView.vue`
- `frontend/src/views/submissions/SubmissionDetailView.vue`
- `frontend/src/views/teacher/GradingView.vue`
- `frontend/src/views/teacher/students/StudentSubmissionView.vue`

## 依存関係

- Step 01〜06 完了

## 実装工数目安

| 作業 | 見積もり |
|------|----------|
| 提出状況マトリクス | 3h |
| 提出物詳細・採点 | 3h |
| 採点画面・学生別詳細 | 2h |
| **合計** | **約 8 時間** |
