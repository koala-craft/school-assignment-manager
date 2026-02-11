# 学校提出物管理アプリ API仕様書

**Version**: 1.0.0  
**Base URL**: `http://localhost:8000/api`  
**Authentication**: Laravel Sanctum (SPA Authentication)

---

## 目次

1. [認証API](#1-認証api)
2. [ユーザー管理API](#2-ユーザー管理api)
3. [年度・学期管理API](#3-年度学期管理api)
4. [科目管理API](#4-科目管理api)
5. [履修管理API](#5-履修管理api)
6. [提出物管理API](#6-提出物管理api)
7. [提出状況管理API](#7-提出状況管理api)
8. [ファイル管理API](#8-ファイル管理api)
9. [通知管理API](#9-通知管理api)
10. [レポート・エクスポートAPI](#10-レポートエクスポートapi)
11. [システム設定API](#11-システム設定api)
12. [監査ログAPI](#12-監査ログapi)

---

## 共通仕様

### リクエストヘッダー

```http
Content-Type: application/json
Accept: application/json
X-CSRF-TOKEN: {csrf_token}
```

### 認証が必要なエンドポイント

```http
Authorization: Bearer {token}
```

### レスポンス形式

#### 成功時

```json
{
  "success": true,
  "data": { ... },
  "message": "操作が成功しました"
}
```

#### エラー時

```json
{
  "success": false,
  "message": "エラーメッセージ",
  "errors": {
    "field_name": ["エラー詳細"]
  }
}
```

### HTTPステータスコード

- `200 OK`: 成功（取得・更新）
- `201 Created`: 作成成功
- `204 No Content`: 削除成功
- `400 Bad Request`: バリデーションエラー
- `401 Unauthorized`: 認証エラー
- `403 Forbidden`: 権限エラー
- `404 Not Found`: リソースが見つからない
- `422 Unprocessable Entity`: バリデーションエラー（詳細）
- `500 Internal Server Error`: サーバーエラー

### ページネーション

リスト取得APIは以下のページネーション形式を返します：

```json
{
  "success": true,
  "data": {
    "items": [ ... ],
    "pagination": {
      "total": 100,
      "per_page": 15,
      "current_page": 1,
      "last_page": 7,
      "from": 1,
      "to": 15
    }
  }
}
```

---

## 1. 認証API

### 1.1 CSRFクッキー取得

SPA認証のため、最初にCSRFトークンを取得します。

```http
GET /sanctum/csrf-cookie
```

**レスポンス**: Cookie に XSRF-TOKEN が設定される

---

### 1.2 ログイン

```http
POST /auth/login
```

**リクエストボディ**:

```json
{
  "email": "user@example.com",
  "password": "password123"
}
```

**レスポンス** (200):

```json
{
  "success": true,
  "data": {
    "user": {
      "id": 1,
      "name": "山田太郎",
      "email": "user@example.com",
      "role": "teacher",
      "student_number": null,
      "is_first_login": false
    },
    "token": "1|abcdef..."
  },
  "message": "ログインに成功しました"
}
```

**エラー** (401):

```json
{
  "success": false,
  "message": "メールアドレスまたはパスワードが正しくありません"
}
```

---

### 1.3 ログアウト

```http
POST /auth/logout
```

**権限**: 認証済みユーザー

**レスポンス** (200):

```json
{
  "success": true,
  "message": "ログアウトしました"
}
```

---

### 1.4 現在のユーザー情報取得

```http
GET /auth/me
```

**権限**: 認証済みユーザー

**レスポンス** (200):

```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "山田太郎",
    "email": "user@example.com",
    "role": "teacher",
    "student_number": null,
    "created_at": "2024-01-01T00:00:00Z"
  }
}
```

---

### 1.5 パスワードリセット要求

```http
POST /auth/password/forgot
```

**リクエストボディ**:

```json
{
  "email": "user@example.com"
}
```

**レスポンス** (200):

```json
{
  "success": true,
  "message": "パスワードリセットのメールを送信しました"
}
```

---

### 1.6 パスワードリセット実行

```http
POST /auth/password/reset
```

**リクエストボディ**:

```json
{
  "email": "user@example.com",
  "token": "reset_token",
  "password": "newpassword123",
  "password_confirmation": "newpassword123"
}
```

**レスポンス** (200):

```json
{
  "success": true,
  "message": "パスワードをリセットしました"
}
```

---

### 1.7 パスワード変更

```http
PUT /auth/password/change
```

**権限**: 認証済みユーザー

**リクエストボディ**:

```json
{
  "current_password": "oldpassword",
  "password": "newpassword123",
  "password_confirmation": "newpassword123"
}
```

**レスポンス** (200):

```json
{
  "success": true,
  "message": "パスワードを変更しました"
}
```

---

## 2. ユーザー管理API

### 2.1 ユーザー一覧取得

```http
GET /users?page=1&per_page=15&role=teacher&search=山田
```

**権限**: 管理者

**クエリパラメータ**:

- `page` (int): ページ番号（デフォルト: 1）
- `per_page` (int): 1ページあたりの件数（デフォルト: 15）
- `role` (string): ロールでフィルタ（admin, teacher, student_admin, student）
- `search` (string): 氏名またはメールアドレスで検索

**レスポンス** (200):

```json
{
  "success": true,
  "data": {
    "items": [
      {
        "id": 1,
        "name": "山田太郎",
        "email": "yamada@example.com",
        "role": "teacher",
        "student_number": null,
        "is_active": true,
        "created_at": "2024-01-01T00:00:00Z"
      }
    ],
    "pagination": { ... }
  }
}
```

---

### 2.2 ユーザー詳細取得

```http
GET /users/{id}
```

**権限**: 管理者、または自分自身

**レスポンス** (200):

```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "山田太郎",
    "email": "yamada@example.com",
    "role": "teacher",
    "student_number": null,
    "is_active": true,
    "is_first_login": false,
    "created_at": "2024-01-01T00:00:00Z",
    "updated_at": "2024-01-01T00:00:00Z"
  }
}
```

---

### 2.3 ユーザー作成

```http
POST /users
```

**権限**: 管理者

**リクエストボディ**:

```json
{
  "name": "山田太郎",
  "email": "yamada@example.com",
  "password": "password123",
  "role": "teacher",
  "student_number": "S2024001",
  "is_active": true
}
```

**バリデーション**:

- `name`: 必須、最大255文字
- `email`: 必須、メール形式、ユニーク
- `password`: 必須、最小8文字
- `role`: 必須、admin/teacher/student_admin/student
- `student_number`: roleがstudent系の場合は必須、ユニーク
- `is_active`: boolean

**レスポンス** (201):

```json
{
  "success": true,
  "data": {
    "id": 2,
    "name": "山田太郎",
    "email": "yamada@example.com",
    "role": "teacher"
  },
  "message": "ユーザーを作成しました"
}
```

---

### 2.4 ユーザー更新

```http
PUT /users/{id}
```

**権限**: 管理者、または自分自身（ただし role は変更不可）

**リクエストボディ**:

```json
{
  "name": "山田太郎",
  "email": "yamada@example.com",
  "role": "teacher",
  "is_active": true
}
```

**レスポンス** (200):

```json
{
  "success": true,
  "data": { ... },
  "message": "ユーザー情報を更新しました"
}
```

---

### 2.5 ユーザー削除（無効化）

```http
DELETE /users/{id}
```

**権限**: 管理者

**レスポンス** (200):

```json
{
  "success": true,
  "message": "ユーザーを無効化しました"
}
```

**注**: 物理削除ではなく、`is_active` を false にする論理削除

---

## 3. 年度・学期管理API

### 3.1 年度一覧取得

```http
GET /academic-years
```

**権限**: 認証済みユーザー

**レスポンス** (200):

```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "year": 2024,
      "name": "2024年度",
      "start_date": "2024-04-01",
      "end_date": "2025-03-31",
      "is_active": true,
      "created_at": "2024-01-01T00:00:00Z"
    }
  ]
}
```

---

### 3.2 年度作成

```http
POST /academic-years
```

**権限**: 管理者

**リクエストボディ**:

```json
{
  "year": 2024,
  "name": "2024年度",
  "start_date": "2024-04-01",
  "end_date": "2025-03-31",
  "is_active": true
}
```

**レスポンス** (201):

```json
{
  "success": true,
  "data": { ... },
  "message": "年度を作成しました"
}
```

---

### 3.3 学期一覧取得

```http
GET /terms?academic_year_id=1
```

**権限**: 認証済みユーザー

**レスポンス** (200):

```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "academic_year_id": 1,
      "name": "前期",
      "start_date": "2024-04-01",
      "end_date": "2024-09-30",
      "created_at": "2024-01-01T00:00:00Z"
    }
  ]
}
```

---

### 3.4 学期作成

```http
POST /terms
```

**権限**: 管理者

**リクエストボディ**:

```json
{
  "academic_year_id": 1,
  "name": "前期",
  "start_date": "2024-04-01",
  "end_date": "2024-09-30"
}
```

**レスポンス** (201):

```json
{
  "success": true,
  "data": { ... },
  "message": "学期を作成しました"
}
```

---

## 4. 科目管理API

### 4.1 科目一覧取得

```http
GET /subjects?academic_year_id=1&term_id=1&search=プログラミング
```

**権限**: 
- 管理者: 全科目
- 教員: 担当科目
- 学生: 履修科目

**クエリパラメータ**:

- `academic_year_id` (int): 年度ID
- `term_id` (int): 学期ID
- `search` (string): 科目名または科目コードで検索
- `teacher_id` (int): 担当教員IDでフィルタ

**レスポンス** (200):

```json
{
  "success": true,
  "data": {
    "items": [
      {
        "id": 1,
        "code": "CS101",
        "name": "プログラミング基礎",
        "academic_year_id": 1,
        "academic_year_name": "2024年度",
        "term_id": 1,
        "term_name": "前期",
        "description": "プログラミングの基礎を学ぶ",
        "teachers": [
          {
            "id": 2,
            "name": "山田太郎"
          }
        ],
        "enrollment_count": 30,
        "is_active": true,
        "created_at": "2024-01-01T00:00:00Z"
      }
    ],
    "pagination": { ... }
  }
}
```

---

### 4.2 科目詳細取得

```http
GET /subjects/{id}
```

**権限**: 管理者、担当教員、履修学生

**レスポンス** (200):

```json
{
  "success": true,
  "data": {
    "id": 1,
    "code": "CS101",
    "name": "プログラミング基礎",
    "academic_year_id": 1,
    "term_id": 1,
    "description": "プログラミングの基礎を学ぶ",
    "teachers": [ ... ],
    "enrollments": [ ... ],
    "assignments_count": 5,
    "is_active": true,
    "created_at": "2024-01-01T00:00:00Z",
    "updated_at": "2024-01-01T00:00:00Z"
  }
}
```

---

### 4.3 科目作成

```http
POST /subjects
```

**権限**: 管理者

**リクエストボディ**:

```json
{
  "code": "CS101",
  "name": "プログラミング基礎",
  "academic_year_id": 1,
  "term_id": 1,
  "description": "プログラミングの基礎を学ぶ",
  "teacher_ids": [2, 3]
}
```

**バリデーション**:

- `code`: 必須、最大50文字、年度内でユニーク
- `name`: 必須、最大255文字
- `academic_year_id`: 必須、存在する年度ID
- `term_id`: 必須、存在する学期ID
- `description`: 任意、テキスト
- `teacher_ids`: 必須、配列、存在する教員ID

**レスポンス** (201):

```json
{
  "success": true,
  "data": { ... },
  "message": "科目を作成しました"
}
```

---

### 4.4 科目更新

```http
PUT /subjects/{id}
```

**権限**: 管理者

**リクエストボディ**: 科目作成と同じ

**レスポンス** (200):

```json
{
  "success": true,
  "data": { ... },
  "message": "科目を更新しました"
}
```

---

### 4.5 科目削除（無効化）

```http
DELETE /subjects/{id}
```

**権限**: 管理者

**レスポンス** (200):

```json
{
  "success": true,
  "message": "科目を無効化しました"
}
```

---

## 5. 履修管理API

### 5.1 履修情報一覧取得

```http
GET /enrollments?subject_id=1&student_id=5
```

**権限**: 管理者、担当教員、該当学生

**クエリパラメータ**:

- `subject_id` (int): 科目ID
- `student_id` (int): 学生ID
- `academic_year_id` (int): 年度ID

**レスポンス** (200):

```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "subject_id": 1,
      "subject_name": "プログラミング基礎",
      "student_id": 5,
      "student_name": "田中花子",
      "student_number": "S2024001",
      "enrolled_at": "2024-04-01T00:00:00Z",
      "is_active": true
    }
  ]
}
```

---

### 5.2 履修登録（一括）

```http
POST /enrollments/bulk
```

**権限**: 管理者

**リクエストボディ**:

```json
{
  "subject_id": 1,
  "student_ids": [5, 6, 7, 8]
}
```

**レスポンス** (201):

```json
{
  "success": true,
  "data": {
    "success_count": 4,
    "failed_count": 0
  },
  "message": "4件の履修登録が完了しました"
}
```

---

### 5.3 履修解除

```http
DELETE /enrollments/{id}
```

**権限**: 管理者

**レスポンス** (200):

```json
{
  "success": true,
  "message": "履修を解除しました"
}
```

---

## 6. 提出物管理API

### 6.1 提出物一覧取得

```http
GET /assignments?subject_id=1&status=active
```

**権限**: 
- 管理者: 全提出物
- 教員: 担当科目の提出物
- 学生: 履修科目の提出物

**クエリパラメータ**:

- `subject_id` (int): 科目ID
- `status` (string): active/inactive
- `academic_year_id` (int): 年度ID
- `term_id` (int): 学期ID

**レスポンス** (200):

```json
{
  "success": true,
  "data": {
    "items": [
      {
        "id": 1,
        "subject_id": 1,
        "subject_name": "プログラミング基礎",
        "title": "第1回課題",
        "description": "変数と演算子について",
        "deadline": "2024-05-01T23:59:59Z",
        "published_at": "2024-04-15T00:00:00Z",
        "is_graded": true,
        "grading_type": "score",
        "max_score": 100,
        "submission_type": "file",
        "is_active": true,
        "submission_count": 28,
        "total_students": 30,
        "created_at": "2024-04-15T00:00:00Z"
      }
    ],
    "pagination": { ... }
  }
}
```

---

### 6.2 提出物詳細取得

```http
GET /assignments/{id}
```

**権限**: 管理者、担当教員、履修学生

**レスポンス** (200):

```json
{
  "success": true,
  "data": {
    "id": 1,
    "subject_id": 1,
    "subject": { ... },
    "title": "第1回課題",
    "description": "変数と演算子について",
    "deadline": "2024-05-01T23:59:59Z",
    "published_at": "2024-04-15T00:00:00Z",
    "is_graded": true,
    "grading_type": "score",
    "max_score": 100,
    "submission_type": "file",
    "allowed_file_types": ["pdf", "docx", "zip"],
    "max_file_size": 52428800,
    "max_files": 5,
    "is_active": true,
    "submission_stats": {
      "total": 30,
      "submitted": 28,
      "not_submitted": 2,
      "graded": 25,
      "overdue": 1
    },
    "created_at": "2024-04-15T00:00:00Z",
    "updated_at": "2024-04-15T00:00:00Z"
  }
}
```

---

### 6.3 提出物作成

```http
POST /assignments
```

**権限**: 管理者、担当教員

**リクエストボディ**:

```json
{
  "subject_id": 1,
  "title": "第1回課題",
  "description": "変数と演算子について",
  "deadline": "2024-05-01T23:59:59Z",
  "published_at": "2024-04-15T00:00:00Z",
  "is_graded": true,
  "grading_type": "score",
  "max_score": 100,
  "submission_type": "file",
  "allowed_file_types": ["pdf", "docx"],
  "max_file_size": 52428800,
  "max_files": 5
}
```

**バリデーション**:

- `subject_id`: 必須、存在する科目ID
- `title`: 必須、最大255文字
- `description`: 任意、テキスト
- `deadline`: 必須、日時（ISO 8601形式）
- `published_at`: 任意、日時
- `is_graded`: boolean
- `grading_type`: score/grade（点数/評価）
- `max_score`: grading_typeがscoreの場合は必須
- `submission_type`: file/text/both

**レスポンス** (201):

```json
{
  "success": true,
  "data": { ... },
  "message": "提出物を作成しました"
}
```

---

### 6.4 提出物更新

```http
PUT /assignments/{id}
```

**権限**: 管理者、担当教員

**リクエストボディ**: 提出物作成と同じ

**レスポンス** (200):

```json
{
  "success": true,
  "data": { ... },
  "message": "提出物を更新しました"
}
```

---

### 6.5 提出物削除（無効化）

```http
DELETE /assignments/{id}
```

**権限**: 管理者、担当教員

**レスポンス** (200):

```json
{
  "success": true,
  "message": "提出物を無効化しました"
}
```

---

### 6.6 提出物テンプレート一覧取得

```http
GET /assignment-templates?subject_id=1
```

**権限**: 管理者、教員

**レスポンス** (200):

```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "レポート課題テンプレート",
      "title": "第{n}回レポート",
      "description": "...",
      "grading_type": "score",
      "max_score": 100,
      "submission_type": "file",
      "created_at": "2024-01-01T00:00:00Z"
    }
  ]
}
```

---

### 6.7 提出物テンプレートから作成

```http
POST /assignments/from-template
```

**権限**: 管理者、担当教員

**リクエストボディ**:

```json
{
  "template_id": 1,
  "subject_id": 1,
  "deadline": "2024-05-01T23:59:59Z",
  "published_at": "2024-04-15T00:00:00Z"
}
```

**レスポンス** (201):

```json
{
  "success": true,
  "data": { ... },
  "message": "テンプレートから提出物を作成しました"
}
```

---

## 7. 提出状況管理API

### 7.1 提出状況一覧取得（マトリクス形式）

```http
GET /submissions/matrix?assignment_id=1
```

**権限**: 管理者、担当教員、管理者学生

**レスポンス** (200):

```json
{
  "success": true,
  "data": {
    "assignment": {
      "id": 1,
      "title": "第1回課題",
      "deadline": "2024-05-01T23:59:59Z"
    },
    "submissions": [
      {
        "id": 1,
        "student_id": 5,
        "student_name": "田中花子",
        "student_number": "S2024001",
        "status": "graded",
        "score": 85,
        "grade": null,
        "submitted_at": "2024-04-28T15:30:00Z",
        "graded_at": "2024-05-02T10:00:00Z",
        "is_overdue": false,
        "resubmission_count": 0
      }
    ]
  }
}
```

---

### 7.2 提出状況詳細取得

```http
GET /submissions/{id}
```

**権限**: 管理者、担当教員、管理者学生、該当学生

**レスポンス** (200):

```json
{
  "success": true,
  "data": {
    "id": 1,
    "assignment_id": 1,
    "assignment": { ... },
    "student_id": 5,
    "student": { ... },
    "status": "graded",
    "score": 85,
    "grade": null,
    "comment_from_teacher": "よくできています",
    "comment_from_student": "お願いします",
    "submitted_at": "2024-04-28T15:30:00Z",
    "graded_at": "2024-05-02T10:00:00Z",
    "resubmission_deadline": null,
    "resubmission_count": 0,
    "is_overdue": false,
    "files": [
      {
        "id": 1,
        "filename": "report.pdf",
        "original_filename": "レポート第1回.pdf",
        "file_size": 1024000,
        "mime_type": "application/pdf",
        "uploaded_at": "2024-04-28T15:30:00Z"
      }
    ],
    "histories": [
      {
        "id": 1,
        "status_from": "not_submitted",
        "status_to": "submitted",
        "changed_by": 5,
        "changed_by_name": "田中花子",
        "changed_at": "2024-04-28T15:30:00Z"
      }
    ]
  }
}
```

---

### 7.3 学生による提出

```http
POST /submissions
```

**権限**: 学生

**リクエストボディ** (multipart/form-data):

```
assignment_id: 1
comment_from_student: よろしくお願いします
files[]: (file)
files[]: (file)
```

**レスポンス** (201):

```json
{
  "success": true,
  "data": { ... },
  "message": "提出しました"
}
```

---

### 7.4 提出状況更新（管理者学生）

```http
PUT /submissions/{id}/status
```

**権限**: 管理者、担当教員、管理者学生

**リクエストボディ**:

```json
{
  "status": "submitted"
}
```

**許可される状態遷移**:

- 管理者学生: not_submitted → submitted, resubmit_required → resubmitted
- 教員: 全ての状態遷移

**レスポンス** (200):

```json
{
  "success": true,
  "data": { ... },
  "message": "提出状況を更新しました"
}
```

---

### 7.5 採点

```http
PUT /submissions/{id}/grade
```

**権限**: 管理者、担当教員

**リクエストボディ**:

```json
{
  "score": 85,
  "grade": null,
  "comment_from_teacher": "よくできています",
  "status": "graded"
}
```

**バリデーション**:

- `score`: grading_typeがscoreの場合は必須、0～max_score
- `grade`: grading_typeがgradeの場合は必須
- `comment_from_teacher`: 必須、最小1文字
- `status`: graded または resubmit_required

**レスポンス** (200):

```json
{
  "success": true,
  "data": { ... },
  "message": "採点を完了しました"
}
```

---

### 7.6 再提出指示

```http
PUT /submissions/{id}/resubmit
```

**権限**: 管理者、担当教員

**リクエストボディ**:

```json
{
  "comment_from_teacher": "〇〇の部分を修正してください",
  "resubmission_deadline": "2024-05-15T23:59:59Z"
}
```

**レスポンス** (200):

```json
{
  "success": true,
  "data": { ... },
  "message": "再提出を指示しました"
}
```

---

### 7.7 一括採点

```http
POST /submissions/bulk-grade
```

**権限**: 管理者、担当教員

**リクエストボディ**:

```json
{
  "submission_ids": [1, 2, 3, 4],
  "score": 80,
  "grade": null,
  "comment_from_teacher": "よくできています"
}
```

**レスポンス** (200):

```json
{
  "success": true,
  "data": {
    "success_count": 4,
    "failed_count": 0
  },
  "message": "4件の採点が完了しました"
}
```

---

## 8. ファイル管理API

### 8.1 ファイルアップロード

```http
POST /files/upload
```

**権限**: 認証済みユーザー

**リクエストボディ** (multipart/form-data):

```
file: (file)
type: submission
```

**レスポンス** (201):

```json
{
  "success": true,
  "data": {
    "id": 1,
    "filename": "abc123.pdf",
    "original_filename": "レポート.pdf",
    "file_size": 1024000,
    "mime_type": "application/pdf",
    "url": "/storage/submissions/abc123.pdf"
  },
  "message": "ファイルをアップロードしました"
}
```

---

### 8.2 ファイルダウンロード

```http
GET /files/{id}/download
```

**権限**: 管理者、担当教員、該当学生

**レスポンス**: ファイルバイナリ

---

### 8.3 ファイル一括ダウンロード

```http
POST /files/bulk-download
```

**権限**: 管理者、担当教員

**リクエストボディ**:

```json
{
  "file_ids": [1, 2, 3, 4]
}
```

または

```json
{
  "assignment_id": 1
}
```

**レスポンス**: ZIPファイル

---

### 8.4 ファイル削除

```http
DELETE /files/{id}
```

**権限**: 管理者、ファイルの所有者（提出前のみ）

**レスポンス** (200):

```json
{
  "success": true,
  "message": "ファイルを削除しました"
}
```

---

## 9. 通知管理API

### 9.1 通知一覧取得

```http
GET /notifications?unread_only=true
```

**権限**: 認証済みユーザー（自分の通知のみ）

**クエリパラメータ**:

- `unread_only` (boolean): 未読のみ取得

**レスポンス** (200):

```json
{
  "success": true,
  "data": {
    "items": [
      {
        "id": 1,
        "type": "assignment_created",
        "title": "新しい課題が登録されました",
        "message": "「プログラミング基礎」で「第1回課題」が登録されました",
        "data": {
          "assignment_id": 1,
          "subject_id": 1
        },
        "is_read": false,
        "created_at": "2024-04-15T00:00:00Z"
      }
    ],
    "unread_count": 5
  }
}
```

---

### 9.2 通知既読

```http
PUT /notifications/{id}/read
```

**権限**: 認証済みユーザー（自分の通知のみ）

**レスポンス** (200):

```json
{
  "success": true,
  "message": "通知を既読にしました"
}
```

---

### 9.3 全通知既読

```http
PUT /notifications/read-all
```

**権限**: 認証済みユーザー

**レスポンス** (200):

```json
{
  "success": true,
  "message": "全ての通知を既読にしました"
}
```

---

### 9.4 通知設定取得

```http
GET /notification-settings
```

**権限**: 認証済みユーザー

**レスポンス** (200):

```json
{
  "success": true,
  "data": {
    "email_enabled": true,
    "assignment_created": true,
    "deadline_reminder": true,
    "graded": true,
    "resubmit_required": true
  }
}
```

---

### 9.5 通知設定更新

```http
PUT /notification-settings
```

**権限**: 認証済みユーザー

**リクエストボディ**:

```json
{
  "email_enabled": true,
  "assignment_created": true,
  "deadline_reminder": true,
  "graded": true,
  "resubmit_required": true
}
```

**レスポンス** (200):

```json
{
  "success": true,
  "message": "通知設定を更新しました"
}
```

---

## 10. レポート・エクスポートAPI

### 10.1 提出状況CSV出力

```http
GET /reports/submissions/csv?subject_id=1&assignment_id=1
```

**権限**: 管理者、担当教員

**クエリパラメータ**:

- `subject_id` (int): 科目ID
- `assignment_id` (int): 提出物ID（任意）

**レスポンス**: CSVファイル

---

### 10.2 成績一覧CSV出力

```http
GET /reports/grades/csv?subject_id=1
```

**権限**: 管理者、担当教員

**レスポンス**: CSVファイル

---

### 10.3 未提出者リストCSV出力

```http
GET /reports/not-submitted/csv?assignment_id=1
```

**権限**: 管理者、担当教員

**レスポンス**: CSVファイル

---

### 10.4 個人成績表PDF出力

```http
GET /reports/student-grades/pdf?student_id=5&academic_year_id=1
```

**権限**: 管理者、該当学生

**レスポンス**: PDFファイル

---

## 11. システム設定API

### 11.1 システム設定取得

```http
GET /system-settings
```

**権限**: 管理者

**レスポンス** (200):

```json
{
  "success": true,
  "data": {
    "smtp_host": "smtp.example.com",
    "smtp_port": 587,
    "smtp_username": "noreply@example.com",
    "notification_timing_days": 3,
    "max_file_size": 52428800,
    "allowed_file_types": ["pdf", "docx", "xlsx", "jpg", "png", "zip"],
    "session_timeout": 120,
    "password_min_length": 8
  }
}
```

---

### 11.2 システム設定更新

```http
PUT /system-settings
```

**権限**: 管理者

**リクエストボディ**: システム設定取得と同じ

**レスポンス** (200):

```json
{
  "success": true,
  "message": "システム設定を更新しました"
}
```

---

### 11.3 バックアップ実行

```http
POST /system/backup
```

**権限**: 管理者

**レスポンス** (200):

```json
{
  "success": true,
  "data": {
    "backup_file": "backup_20240415_120000.sql",
    "size": 10485760,
    "created_at": "2024-04-15T12:00:00Z"
  },
  "message": "バックアップを作成しました"
}
```

---

### 11.4 バックアップ一覧取得

```http
GET /system/backups
```

**権限**: 管理者

**レスポンス** (200):

```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "filename": "backup_20240415_120000.sql",
      "size": 10485760,
      "created_at": "2024-04-15T12:00:00Z"
    }
  ]
}
```

---

## 12. 監査ログAPI

### 12.1 監査ログ一覧取得

```http
GET /audit-logs?user_id=1&action=update&date_from=2024-04-01
```

**権限**: 管理者

**クエリパラメータ**:

- `user_id` (int): ユーザーID
- `action` (string): create/update/delete
- `model` (string): モデル名（User, Subject, Assignment等）
- `date_from` (date): 開始日
- `date_to` (date): 終了日

**レスポンス** (200):

```json
{
  "success": true,
  "data": {
    "items": [
      {
        "id": 1,
        "user_id": 1,
        "user_name": "管理者",
        "action": "update",
        "model": "Assignment",
        "model_id": 1,
        "changes": {
          "deadline": {
            "old": "2024-05-01T23:59:59Z",
            "new": "2024-05-05T23:59:59Z"
          }
        },
        "ip_address": "192.168.1.1",
        "user_agent": "Mozilla/5.0...",
        "created_at": "2024-04-15T10:00:00Z"
      }
    ],
    "pagination": { ... }
  }
}
```

---

### 12.2 監査ログ詳細取得

```http
GET /audit-logs/{id}
```

**権限**: 管理者

**レスポンス** (200):

```json
{
  "success": true,
  "data": {
    "id": 1,
    "user_id": 1,
    "user": { ... },
    "action": "update",
    "model": "Assignment",
    "model_id": 1,
    "changes": { ... },
    "ip_address": "192.168.1.1",
    "user_agent": "Mozilla/5.0...",
    "created_at": "2024-04-15T10:00:00Z"
  }
}
```

---

## 13. ダッシュボードAPI

### 13.1 管理者ダッシュボード

```http
GET /dashboard/admin
```

**権限**: 管理者

**レスポンス** (200):

```json
{
  "success": true,
  "data": {
    "total_users": 150,
    "total_subjects": 20,
    "total_assignments": 100,
    "active_students": 120,
    "recent_activities": [ ... ],
    "submission_stats": {
      "total": 3000,
      "submitted": 2800,
      "not_submitted": 200,
      "overdue": 50
    }
  }
}
```

---

### 13.2 教員ダッシュボード

```http
GET /dashboard/teacher
```

**権限**: 教員

**レスポンス** (200):

```json
{
  "success": true,
  "data": {
    "my_subjects": 5,
    "total_students": 150,
    "total_assignments": 25,
    "pending_grading": 18,
    "recent_submissions": [ ... ],
    "upcoming_deadlines": [ ... ]
  }
}
```

---

### 13.3 学生ダッシュボード

```http
GET /dashboard/student
```

**権限**: 学生

**レスポンス** (200):

```json
{
  "success": true,
  "data": {
    "enrolled_subjects": 8,
    "total_assignments": 40,
    "not_submitted": 3,
    "graded": 35,
    "upcoming_deadlines": [ ... ],
    "recent_grades": [ ... ]
  }
}
```

---

## エラーコード一覧

| コード | 説明 |
|-------|------|
| `VALIDATION_ERROR` | バリデーションエラー |
| `UNAUTHORIZED` | 認証エラー |
| `FORBIDDEN` | 権限エラー |
| `NOT_FOUND` | リソースが見つからない |
| `CONFLICT` | データの競合 |
| `FILE_TOO_LARGE` | ファイルサイズ超過 |
| `INVALID_FILE_TYPE` | 許可されていないファイル形式 |
| `DEADLINE_PASSED` | 提出期限超過 |
| `ALREADY_SUBMITTED` | 既に提出済み |
| `INVALID_STATUS_TRANSITION` | 不正な状態遷移 |
| `SERVER_ERROR` | サーバーエラー |

---

## 変更履歴

| バージョン | 日付 | 変更内容 |
|-----------|------|---------|
| 1.0.0 | 2024-04-15 | 初版作成 |

---

## 動作確認

各 API の動作確認方法は [動作確認手順書](./動作確認手順書.md) を参照してください。`scripts/` 配下の PowerShell スクリプト（`test-*.ps1`）で自動確認できます。個人成績表PDFやパスワードリセットなど、機能別の設定は [開発時の注意事項](./開発時の注意事項.md) を参照してください。

---

**注意事項**:

1. 全てのAPI呼び出しには、事前に `/sanctum/csrf-cookie` エンドポイントでCSRFトークンを取得する必要があります
2. 認証が必要なエンドポイントには、Bearerトークンまたはセッションクッキーが必要です
3. ファイルアップロードは multipart/form-data 形式で送信してください
4. 日時は全てISO 8601形式（UTC）で送受信されます
5. ページネーションのデフォルトは1ページ15件です
