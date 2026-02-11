# Step 06: 教員画面（提出物）

## 概要

提出物管理一覧・登録・編集画面を実装する。

**参照**: [画面一覧](../docs/画面一覧.md) T-006〜T-008、[API仕様書](../docs/API仕様書.md) 6

## 状態

- [ ] 未実装
- [ ] 実装中
- [ ] 完了

**優先度**: 高

## 実装内容

### T-006: 提出物管理一覧

| 項目 | 内容 |
|------|------|
| URL | `/teacher/subjects/:id/assignments` |
| API | GET /assignments（科目フィルタ） |
| 機能 | 提出物カード一覧、テンプレートから作成、公開・非公開、提出状況サマリ |

### T-007: 提出物登録画面

| 項目 | 内容 |
|------|------|
| URL | `/teacher/assignments/create` |
| API | POST /admin/assignments |
| 機能 | 科目選択、タイトル、説明（Markdown）、期限、採点設定、提出形式 |

### T-008: 提出物編集画面

| 項目 | 内容 |
|------|------|
| URL | `/teacher/assignments/:id/edit` |
| API | GET /assignments/{id}、PUT /admin/assignments/{id} |
| 機能 | 編集、公開・非公開切替 |

## 成果物

- `frontend/src/views/teacher/assignments/AssignmentListView.vue`
- `frontend/src/views/teacher/assignments/AssignmentCreateView.vue`
- `frontend/src/views/teacher/assignments/AssignmentEditView.vue`

## 依存関係

- Step 01〜05 完了

## 実装工数目安

| 作業 | 見積もり |
|------|----------|
| 提出物一覧 | 2h |
| 提出物登録 | 2.5h |
| 提出物編集 | 1.5h |
| **合計** | **約 6 時間** |
