# 学校提出物管理アプリ

教育現場での提出物管理を効率化するWebアプリケーション

## 概要

学校での課題・提出物の管理を、教員・学生双方が安心して使える形で提供するシステムです。

- **責任境界の明確化**: 誰が何を見て、誰がどこまで更新できるかを明確に定義
- **履歴管理**: いつ・誰が・何を変更したかを完全に記録
- **権限管理**: 管理者、教員、管理者学生、一般学生の4つのロールで細かく制御

## 技術スタック

### バックエンド
- **Laravel 11.x** (PHP 8.2)
- **PostgreSQL 15.x**
- **Redis 7.x**
- **Docker + Docker Compose**

### フロントエンド
- **Vue.js 3.x** (TypeScript)
- **Vuetify 3.x**
- **Vite**

## セットアップ

### 前提条件

以下のソフトウェアがインストールされていること：

- Docker Desktop
- Node.js v20 以上
- Git

### クイックスタート

#### 1. リポジトリのクローン

```bash
git clone <repository-url>
cd school-assignment-manager
```

#### 2. バックエンドのセットアップ

**Windows PowerShell（推奨）:**
```powershell
.\scripts\setup-backend.ps1
```

**Windows コマンドプロンプト:**
```cmd
scripts\setup-backend.bat
```

#### 3. フロントエンドのセットアップ

```bash
cd frontend
npm install
npm run dev
```

### アクセスURL

セットアップ完了後、以下のURLでアクセスできます：

- **フロントエンド**: http://localhost:3000
- **バックエンドAPI**: http://localhost:8000
- **Mailpit（メールテスト）**: http://localhost:8025
- **Telescope（デバッグツール）**: http://localhost:8000/telescope

## ドキュメント

詳細なドキュメントは`docs`フォルダ内を参照してください：

- [要件定義書](./docs/要件定義書.md)
- [環境構築手順書](./docs/環境構築手順書.md)
- [開発時の注意事項](./docs/開発時の注意事項.md)
- [動作確認手順書](./docs/動作確認手順書.md)
- [DB設計書](./docs/DB設計書.md)
- [API仕様書](./docs/API仕様書.md)
- [画面一覧](./docs/画面一覧.md)
- [画面遷移図](./docs/画面遷移図.md)
- [ER図](./docs/ER図.md)

## プロジェクト構造

```
school-assignment-manager/
├── backend/              # Laravel アプリケーション
├── frontend/             # Vue.js アプリケーション
├── docker/               # Docker設定ファイル
│   ├── nginx/           # Nginx設定
│   ├── php/             # PHP-FPM設定
│   ├── postgres/        # PostgreSQLデータ
│   └── redis/           # Redisデータ
├── docs/                 # プロジェクトドキュメント
│   ├── 要件定義書.md
│   ├── 環境構築手順書.md
│   ├── 動作確認手順書.md
│   ├── DB設計書.md
│   ├── API仕様書.md
│   ├── 画面一覧.md
│   ├── 画面遷移図.md
│   └── ER図.md
├── scripts/             # セットアップ・テストスクリプト
│   ├── setup-backend.bat    # バックエンドセットアップ（Windows）
│   ├── setup-backend.ps1    # バックエンドセットアップ（PowerShell）
│   └── test-*.ps1           # 各APIの動作確認スクリプト
├── docker-compose.yml   # Docker Compose設定
└── README.md            # このファイル
```

## 開発コマンド

### バックエンド

```bash
# コンテナの起動
docker compose up -d

# コンテナの停止
docker compose down

# ログの確認
docker compose logs -f app

# Laravelコマンドの実行
docker compose exec app php artisan <command>

# マイグレーション
docker compose exec app php artisan migrate

# キャッシュクリア
docker compose exec app php artisan cache:clear
```

### フロントエンド

```bash
# 開発サーバー起動
npm run dev

# ビルド
npm run build

# リンター実行
npm run lint
```

## トラブルシューティング

詳細は [環境構築手順書](./docs/環境構築手順書.md) の「トラブルシューティング」セクションを参照してください。

### よくある問題

#### ポートが既に使用されている

```bash
# 使用中のポートを確認（Windows）
netstat -ano | findstr :8000
netstat -ano | findstr :5432
```

docker-compose.ymlでポート番号を変更してください。

#### Dockerコンテナが起動しない

```bash
# コンテナの状態確認
docker compose ps

# ログの確認
docker compose logs
```

## ライセンス

MIT License

Copyright (c) 2026

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.
