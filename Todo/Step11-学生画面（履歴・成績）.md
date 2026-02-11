# Step 11: 学生画面（履歴・成績）

## 概要

学生の提出履歴・成績閲覧画面を実装する。

**参照**: [画面一覧](../docs/画面一覧.md) S-004, S-005、[API仕様書](../docs/API仕様書.md) 7, 10

## 状態

- [ ] 未実装
- [ ] 実装中
- [ ] 完了

**優先度**: 中

## 実装内容

### S-004: 提出履歴・成績閲覧

| 項目 | 内容 |
|------|------|
| URL | `/student/submissions` |
| API | GET /my-submissions |
| 機能 | 自身の提出一覧、状態、点数/評価、提出日時 |

### S-005: 科目別課題一覧

| 項目 | 内容 |
|------|------|
| URL | `/student/subjects/:id/assignments` |
| API | GET /assignments（科目フィルタ） |
| 機能 | 特定科目の課題一覧、提出状況 |

### 個人成績表PDF

| 項目 | 内容 |
|------|------|
| 連携 | レポートAPIから GET /reports/student-grades/pdf |
| 機能 | 自身の成績表をPDFダウンロード |

## 成果物

- `frontend/src/views/student/SubmissionHistoryView.vue`
- `frontend/src/views/student/SubjectAssignmentView.vue`

## 依存関係

- Step 01〜10 完了

## 実装工数目安

| 作業 | 見積もり |
|------|----------|
| 提出履歴・成績閲覧 | 2h |
| 科目別課題一覧 | 1h |
| **合計** | **約 3 時間** |
