# Step 02: 認証・共通画面

## 概要

ログイン、パスワードリセット、パスワード変更、プロフィール編集、通知一覧の共通画面を実装する。

**参照**: [画面一覧](../docs/画面一覧.md) 1. 共通画面（C-001〜C-006）、[API仕様書](../docs/API仕様書.md) 1. 認証API

## 状態

- [ ] 未実装
- [ ] 実装中
- [ ] 完了

**優先度**: 高

## 実装内容

### C-001: ログイン画面

| 項目 | 内容 |
|------|------|
| URL | `/login` |
| API | POST /auth/login |
| 機能 | メール・パスワード入力、ログイン、リセットリンク |
| 初回ログイン | パスワード変更画面へ強制遷移 |

### C-002: パスワードリセット画面

| 項目 | 内容 |
|------|------|
| URL | `/password/reset` |
| API | POST /auth/password/forgot、POST /auth/password/reset |
| 機能 | ステップ1: メール送信、ステップ2: トークン+新パスワード |

### C-003: パスワード変更画面

| 項目 | 内容 |
|------|------|
| URL | `/password/change` |
| API | PUT /auth/password/change |
| 権限 | 認証済みユーザー |

### C-004: プロフィール編集画面

| 項目 | 内容 |
|------|------|
| URL | `/profile` |
| API | GET /auth/me、PUT /users/{id}（自身の更新） |
| 機能 | 氏名・メールアドレス編集 |

### C-005: 通知一覧画面

| 項目 | 内容 |
|------|------|
| URL | `/notifications` |
| API | GET /notifications、PUT /notifications/{id}/read |
| 機能 | 一覧表示、既読、全件既読 |

### C-006: 通知詳細画面

| 項目 | 内容 |
|------|------|
| URL | `/notifications/:id` |
| API | GET /notifications/{id} |
| 機能 | 詳細表示、関連画面へのリンク |

## 成果物

- `frontend/src/views/auth/LoginView.vue`（既存を拡張）
- `frontend/src/views/auth/PasswordResetView.vue`
- `frontend/src/views/auth/PasswordChangeView.vue`
- `frontend/src/views/profile/ProfileEditView.vue`
- `frontend/src/views/notifications/NotificationListView.vue`
- `frontend/src/views/notifications/NotificationDetailView.vue`

## 依存関係

- Step 01（フロントエンド基盤）完了

## 実装工数目安

| 作業 | 見積もり |
|------|----------|
| ログイン・パスワードリセット | 2h |
| パスワード変更・プロフィール | 1.5h |
| 通知一覧・詳細 | 2h |
| **合計** | **約 5.5 時間** |
