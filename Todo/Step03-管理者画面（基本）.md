# Step 03: 管理者画面（基本）

## 概要

管理者の基本画面：ダッシュボード、ユーザー管理、年度管理、学期管理、システム設定を実装する。

**参照**: [画面一覧](../docs/画面一覧.md) 2. 管理者画面（A-001〜A-007）、[API仕様書](../docs/API仕様書.md) 2, 3, 11

## 状態

- [ ] 未実装
- [ ] 実装中
- [ ] 完了

**優先度**: 高

## 実装内容

### A-001: 管理者ダッシュボード

| 項目 | 内容 |
|------|------|
| URL | `/admin/dashboard` |
| API | GET /dashboard/admin |
| 機能 | サマリーカード、グラフ、最近のアクティビティ、クイックアクション |

### A-002: ユーザー管理一覧

| 項目 | 内容 |
|------|------|
| URL | `/admin/users` |
| API | GET /admin/users、各種操作 |
| 機能 | 一覧、検索、ロール・状態フィルター、ページネーション |

### A-003: ユーザー登録画面

| 項目 | 内容 |
|------|------|
| URL | `/admin/users/create` |
| API | POST /admin/users |
| 機能 | 氏名、メール、パスワード、ロール、学籍番号 |

### A-004: ユーザー編集画面

| 項目 | 内容 |
|------|------|
| URL | `/admin/users/:id/edit` |
| API | GET /admin/users/{id}、PUT /admin/users/{id} |
| 機能 | 編集、パスワード変更（任意） |

### A-005: 年度管理画面

| 項目 | 内容 |
|------|------|
| URL | `/admin/academic-years` |
| API | GET /admin/academic-years、CRUD、set-active |
| 機能 | 年度一覧、作成・編集、有効年度切替 |

### A-006: 学期管理画面

| 項目 | 内容 |
|------|------|
| URL | `/admin/terms` |
| API | GET /admin/terms、CRUD |
| 機能 | 学期一覧、作成・編集 |

### A-007: システム設定画面

| 項目 | 内容 |
|------|------|
| URL | `/admin/system-settings` |
| API | GET /admin/system-settings、PUT /admin/system-settings |
| 機能 | SMTP、通知、ファイル設定、セッション、パスワードポリシー |

## 成果物

- `frontend/src/views/admin/DashboardView.vue`
- `frontend/src/views/admin/users/UserListView.vue`
- `frontend/src/views/admin/users/UserCreateView.vue`
- `frontend/src/views/admin/users/UserEditView.vue`
- `frontend/src/views/admin/AcademicYearView.vue`
- `frontend/src/views/admin/TermView.vue`
- `frontend/src/views/admin/SystemSettingView.vue`

## 依存関係

- Step 01, 02 完了

## 実装工数目安

| 作業 | 見積もり |
|------|----------|
| ダッシュボード | 2h |
| ユーザー管理（一覧・登録・編集） | 3h |
| 年度・学期管理 | 2h |
| システム設定 | 2h |
| **合計** | **約 9 時間** |
