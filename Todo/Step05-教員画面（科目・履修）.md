# Step 05: 教員画面（科目・履修）

## 概要

教員のダッシュボード、科目管理、履修管理画面を実装する。

**参照**: [画面一覧](../docs/画面一覧.md) T-001〜T-005、[API仕様書](../docs/API仕様書.md) 4, 5

## 状態

- [ ] 未実装
- [ ] 実装中
- [ ] 完了

**優先度**: 高

## 実装内容

### T-001: 教員ダッシュボード

| 項目 | 内容 |
|------|------|
| URL | `/teacher/dashboard` |
| API | GET /dashboard/teacher |
| 機能 | 担当科目サマリ、未採点件数、クイックアクション |

### T-002: 科目管理一覧

| 項目 | 内容 |
|------|------|
| URL | `/teacher/subjects` |
| API | GET /admin/subjects（教員は自分の科目のみ） |
| 機能 | 科目一覧、検索、フィルター |

### T-003: 科目登録画面

| 項目 | 内容 |
|------|------|
| URL | `/teacher/subjects/create` |
| API | POST /admin/subjects |
| 機能 | 年度・学期・科目名・担当教員 |

### T-004: 科目編集画面

| 項目 | 内容 |
|------|------|
| URL | `/teacher/subjects/:id/edit` |
| API | GET /admin/subjects/{id}、PUT /admin/subjects/{id} |
| 機能 | 編集、担当教員割当・解除 |

### T-005: 履修管理画面

| 項目 | 内容 |
|------|------|
| URL | `/teacher/subjects/:id/enrollments` |
| API | GET /admin/subjects/{id}/students、POST enroll、DELETE unenroll |
| 機能 | 履修学生一覧、履修登録・解除、一括登録 |

## 成果物

- `frontend/src/views/teacher/DashboardView.vue`
- `frontend/src/views/teacher/subjects/SubjectListView.vue`
- `frontend/src/views/teacher/subjects/SubjectCreateView.vue`
- `frontend/src/views/teacher/subjects/SubjectEditView.vue`
- `frontend/src/views/teacher/subjects/EnrollmentView.vue`

## 依存関係

- Step 01〜04 完了

## 実装工数目安

| 作業 | 見積もり |
|------|----------|
| 教員ダッシュボード | 1.5h |
| 科目管理（一覧・登録・編集） | 3h |
| 履修管理 | 2.5h |
| **合計** | **約 7 時間** |
