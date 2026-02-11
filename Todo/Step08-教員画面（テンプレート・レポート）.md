# Step 08: 教員画面（テンプレート・レポート）

## 概要

提出物テンプレートの管理とレポート出力画面を実装する。

**参照**: [画面一覧](../docs/画面一覧.md) T-013〜T-015、[API仕様書](../docs/API仕様書.md) 6, 10

## 状態

- [ ] 未実装
- [ ] 実装中
- [ ] 完了

**優先度**: 中

## 実装内容

### T-013: 提出物テンプレート一覧

| 項目 | 内容 |
|------|------|
| URL | `/teacher/templates` |
| API | GET /assignment-templates |
| 機能 | テンプレート一覧、検索、使用・編集・削除 |

### T-014: テンプレート作成画面

| 項目 | 内容 |
|------|------|
| URL | `/teacher/templates/create` |
| API | POST /admin/assignment-templates |
| 機能 | テンプレート作成・編集（科目・期限は不要） |

### T-015: レポート出力画面

| 項目 | 内容 |
|------|------|
| URL | `/teacher/reports` |
| API | GET /reports/submissions/csv、grades/csv、not-submitted/csv、student-grades/csv、student-grades/pdf |
| 機能 | レポート種類選択、条件（年度・科目・提出物）、CSV/PDF出力 |

## 成果物

- `frontend/src/views/teacher/templates/TemplateListView.vue`
- `frontend/src/views/teacher/templates/TemplateCreateView.vue`
- `frontend/src/views/teacher/ReportView.vue`

## 依存関係

- Step 01〜07 完了

## 実装工数目安

| 作業 | 見積もり |
|------|----------|
| テンプレート一覧・作成 | 2h |
| レポート出力 | 2.5h |
| **合計** | **約 4.5 時間** |
