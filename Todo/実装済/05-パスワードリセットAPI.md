# パスワードリセットAPI

## 概要

API仕様書 1.5, 1.6 で定義されているパスワードリセット機能。

- **エンドポイント / 対象**:
  - `POST /api/auth/password/forgot`（リセット要求）
  - `POST /api/auth/password/reset`（リセット実行）
- **権限**: 不要（パブリック）
- **レスポンス / 出力**: 成功メッセージ

## 状態

- [ ] 未実装
- [ ] 実装中
- [x] 完了
- [ ] 保留

**優先度**: 中

## なぜ未実装だったか

1. **実装順序**
   - ログイン・ログアウト・パスワード変更を優先
   - メール送信の設定（Mailpit）が前提

2. **実装の優先度**
   - 開発初期は管理者が直接パスワードリセット可能
   - 本番運用を見据えて後から実装

3. **優先度・判断**
   - 基盤APIの実装完了後に着手

## 実装工数の目安

| 作業 | 見積もり |
|------|----------|
| Migration（password_reset_tokens） | 0h（既存） |
| パスワードリセット通知（Notification） | 0.5h |
| forgot/reset メソッド追加 | 0.5h |
| ルート追加・テスト | 0.5h |
| **合計** | **約 1.5 時間** |

## 実装するとできること

- ユーザーがメールアドレスを入力してパスワードリセットを要求可能
- メール送信されたトークンで新パスワードを設定可能
- 紛失時のセルフサービス復旧

## 実装状況（完了時のみ記入）

- [x] User モデルに CanResetPassword トレイト追加
- [x] ResetPasswordNotification（カスタム・日本語メール）
- [x] AuthController::forgotPassword, resetPassword メソッド
- [x] ルート追加（POST /auth/password/forgot, POST /auth/password/reset）
- [x] テストスクリプト `scripts/test-password-reset-api.ps1`
- [x] config/app.php に frontend_url 追加

## 追加対応（必要な場合のみ）

- メール送信のため、.env の MAIL 設定を確認（開発時は Mailpit で受信可能）
- フロントエンドのリセット画面URL: FRONTEND_URL で設定（デフォルト http://localhost:3000）

## 権限

| エンドポイント / 機能 | 権限 |
|----------------------|------|
| POST /auth/password/forgot | 不要（パブリック） |
| POST /auth/password/reset | 不要（パブリック） |

## 依存関係（ある場合）

- メール送信設定（Mailpit / SMTP）
- password_reset_tokens テーブル（Laravel デフォルトで存在）

## 技術メモ（任意）

- Laravel の Password facade を使用
- トークン有効期限は config/auth.php の expire（デフォルト60分）
- メール内のリセットリンクは FRONTEND_URL/reset-password?token=xxx&email=xxx 形式
