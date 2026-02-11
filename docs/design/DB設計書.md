# 学校提出物管理アプリ データベース設計書

## 目次

1. [データベース概要](#データベース概要)
2. [テーブル一覧](#テーブル一覧)
3. [テーブル定義](#テーブル定義)
4. [インデックス定義](#インデックス定義)
5. [制約定義](#制約定義)

---

## データベース概要

**DBMS**: PostgreSQL 15.x以上  
**文字コード**: UTF-8  
**照合順序**: ja_JP.UTF-8（日本語対応）  
**タイムゾーン**: Asia/Tokyo

---

## テーブル一覧

| No | テーブル名 | 論理名 | 行数想定 |
|----|-----------|-------|---------|
| 1 | users | ユーザー | 200 |
| 2 | academic_years | 年度 | 10 |
| 3 | terms | 学期 | 20 |
| 4 | subjects | 科目 | 100 |
| 5 | subject_teachers | 科目担当教員 | 150 |
| 6 | enrollments | 履修 | 2,000 |
| 7 | assignments | 提出物 | 500 |
| 8 | assignment_templates | 提出物テンプレート | 50 |
| 9 | assignment_deadline_histories | 提出期限変更履歴 | 100 |
| 10 | submissions | 提出状況 | 10,000 |
| 11 | submission_files | 提出ファイル | 15,000 |
| 12 | submission_histories | 提出状況変更履歴 | 30,000 |
| 13 | submission_comments | 提出物コメント | 5,000 |
| 14 | notifications | 通知 | 50,000 |
| 15 | notification_settings | 通知設定 | 200 |
| 16 | audit_logs | 監査ログ | 100,000 |
| 17 | system_settings | システム設定 | 50 |

---

## テーブル定義

### 1. users（ユーザー）

全システムユーザーを管理するテーブル。

| カラム名 | データ型 | NULL | デフォルト | 説明 |
|---------|---------|------|-----------|------|
| id | BIGSERIAL | NO | - | ユーザーID（主キー） |
| name | VARCHAR(255) | NO | - | 氏名 |
| email | VARCHAR(255) | NO | - | メールアドレス（ユニーク） |
| password | VARCHAR(255) | NO | - | パスワード（ハッシュ化） |
| role | VARCHAR(20) | NO | - | ロール（admin/teacher/student_admin/student） |
| student_number | VARCHAR(50) | YES | NULL | 学籍番号（学生のみ、ユニーク） |
| is_active | BOOLEAN | NO | true | 有効フラグ |
| is_first_login | BOOLEAN | NO | true | 初回ログインフラグ |
| email_verified_at | TIMESTAMP | YES | NULL | メール認証日時 |
| remember_token | VARCHAR(100) | YES | NULL | Remember Meトークン |
| created_at | TIMESTAMP | NO | CURRENT_TIMESTAMP | 作成日時 |
| updated_at | TIMESTAMP | NO | CURRENT_TIMESTAMP | 更新日時 |
| deleted_at | TIMESTAMP | YES | NULL | 削除日時（論理削除） |

**インデックス**:
- PRIMARY KEY: id
- UNIQUE: email
- UNIQUE: student_number (WHERE student_number IS NOT NULL)
- INDEX: role
- INDEX: is_active
- INDEX: deleted_at

**制約**:
- CHECK: role IN ('admin', 'teacher', 'student_admin', 'student')
- CHECK: (role IN ('student', 'student_admin') AND student_number IS NOT NULL) OR (role IN ('admin', 'teacher'))

---

### 2. academic_years（年度）

学年度を管理するテーブル。

| カラム名 | データ型 | NULL | デフォルト | 説明 |
|---------|---------|------|-----------|------|
| id | BIGSERIAL | NO | - | 年度ID（主キー） |
| year | INTEGER | NO | - | 年度（例: 2024） |
| name | VARCHAR(100) | NO | - | 年度名（例: 2024年度） |
| start_date | DATE | NO | - | 開始日 |
| end_date | DATE | NO | - | 終了日 |
| is_active | BOOLEAN | NO | false | 現在の年度フラグ |
| created_at | TIMESTAMP | NO | CURRENT_TIMESTAMP | 作成日時 |
| updated_at | TIMESTAMP | NO | CURRENT_TIMESTAMP | 更新日時 |

**インデックス**:
- PRIMARY KEY: id
- UNIQUE: year
- INDEX: is_active

**制約**:
- CHECK: start_date < end_date
- CHECK: year BETWEEN 2000 AND 2100

---

### 3. terms（学期）

学期を管理するテーブル。

| カラム名 | データ型 | NULL | デフォルト | 説明 |
|---------|---------|------|-----------|------|
| id | BIGSERIAL | NO | - | 学期ID（主キー） |
| academic_year_id | BIGINT | NO | - | 年度ID（外部キー） |
| name | VARCHAR(100) | NO | - | 学期名（例: 前期、後期） |
| start_date | DATE | NO | - | 開始日 |
| end_date | DATE | NO | - | 終了日 |
| created_at | TIMESTAMP | NO | CURRENT_TIMESTAMP | 作成日時 |
| updated_at | TIMESTAMP | NO | CURRENT_TIMESTAMP | 更新日時 |

**インデックス**:
- PRIMARY KEY: id
- INDEX: academic_year_id
- UNIQUE: academic_year_id, name

**外部キー**:
- academic_year_id → academic_years(id) ON DELETE RESTRICT

**制約**:
- CHECK: start_date < end_date

---

### 4. subjects（科目）

科目情報を管理するテーブル。

| カラム名 | データ型 | NULL | デフォルト | 説明 |
|---------|---------|------|-----------|------|
| id | BIGSERIAL | NO | - | 科目ID（主キー） |
| code | VARCHAR(50) | NO | - | 科目コード |
| name | VARCHAR(255) | NO | - | 科目名 |
| academic_year_id | BIGINT | NO | - | 年度ID（外部キー） |
| term_id | BIGINT | NO | - | 学期ID（外部キー） |
| description | TEXT | YES | NULL | 科目説明 |
| is_active | BOOLEAN | NO | true | 有効フラグ |
| created_at | TIMESTAMP | NO | CURRENT_TIMESTAMP | 作成日時 |
| updated_at | TIMESTAMP | NO | CURRENT_TIMESTAMP | 更新日時 |
| deleted_at | TIMESTAMP | YES | NULL | 削除日時（論理削除） |

**インデックス**:
- PRIMARY KEY: id
- UNIQUE: code, academic_year_id
- INDEX: academic_year_id
- INDEX: term_id
- INDEX: is_active
- INDEX: deleted_at

**外部キー**:
- academic_year_id → academic_years(id) ON DELETE RESTRICT
- term_id → terms(id) ON DELETE RESTRICT

---

### 5. subject_teachers（科目担当教員）

科目と教員の多対多関係を管理するテーブル。

| カラム名 | データ型 | NULL | デフォルト | 説明 |
|---------|---------|------|-----------|------|
| id | BIGSERIAL | NO | - | ID（主キー） |
| subject_id | BIGINT | NO | - | 科目ID（外部キー） |
| teacher_id | BIGINT | NO | - | 教員ID（外部キー） |
| created_at | TIMESTAMP | NO | CURRENT_TIMESTAMP | 作成日時 |
| updated_at | TIMESTAMP | NO | CURRENT_TIMESTAMP | 更新日時 |

**インデックス**:
- PRIMARY KEY: id
- UNIQUE: subject_id, teacher_id
- INDEX: teacher_id

**外部キー**:
- subject_id → subjects(id) ON DELETE CASCADE
- teacher_id → users(id) ON DELETE RESTRICT

---

### 6. enrollments（履修）

学生と科目の履修関係を管理するテーブル。

| カラム名 | データ型 | NULL | デフォルト | 説明 |
|---------|---------|------|-----------|------|
| id | BIGSERIAL | NO | - | 履修ID（主キー） |
| subject_id | BIGINT | NO | - | 科目ID（外部キー） |
| student_id | BIGINT | NO | - | 学生ID（外部キー） |
| enrolled_at | TIMESTAMP | NO | CURRENT_TIMESTAMP | 履修登録日時 |
| is_active | BOOLEAN | NO | true | 有効フラグ |
| created_at | TIMESTAMP | NO | CURRENT_TIMESTAMP | 作成日時 |
| updated_at | TIMESTAMP | NO | CURRENT_TIMESTAMP | 更新日時 |

**インデックス**:
- PRIMARY KEY: id
- UNIQUE: subject_id, student_id
- INDEX: student_id
- INDEX: is_active

**外部キー**:
- subject_id → subjects(id) ON DELETE RESTRICT
- student_id → users(id) ON DELETE RESTRICT

---

### 7. assignments（提出物）

課題・提出物を管理するテーブル。

| カラム名 | データ型 | NULL | デフォルト | 説明 |
|---------|---------|------|-----------|------|
| id | BIGSERIAL | NO | - | 提出物ID（主キー） |
| subject_id | BIGINT | NO | - | 科目ID（外部キー） |
| template_id | BIGINT | YES | NULL | テンプレートID（外部キー） |
| title | VARCHAR(255) | NO | - | タイトル |
| description | TEXT | YES | NULL | 説明 |
| deadline | TIMESTAMP | NO | - | 提出期限 |
| published_at | TIMESTAMP | YES | NULL | 公開日時 |
| is_graded | BOOLEAN | NO | true | 採点対象フラグ |
| grading_type | VARCHAR(20) | NO | 'score' | 評価方式（score/grade） |
| max_score | INTEGER | YES | NULL | 最大点数 |
| submission_type | VARCHAR(20) | NO | 'file' | 提出形式（file/text/both） |
| allowed_file_types | JSON | YES | NULL | 許可するファイル形式 |
| max_file_size | BIGINT | NO | 52428800 | 最大ファイルサイズ（バイト） |
| max_files | INTEGER | NO | 5 | 最大ファイル数 |
| is_active | BOOLEAN | NO | true | 有効フラグ |
| created_at | TIMESTAMP | NO | CURRENT_TIMESTAMP | 作成日時 |
| updated_at | TIMESTAMP | NO | CURRENT_TIMESTAMP | 更新日時 |
| deleted_at | TIMESTAMP | YES | NULL | 削除日時（論理削除） |

**インデックス**:
- PRIMARY KEY: id
- INDEX: subject_id
- INDEX: template_id
- INDEX: deadline
- INDEX: published_at
- INDEX: is_active
- INDEX: deleted_at

**外部キー**:
- subject_id → subjects(id) ON DELETE RESTRICT
- template_id → assignment_templates(id) ON DELETE SET NULL

**制約**:
- CHECK: grading_type IN ('score', 'grade')
- CHECK: submission_type IN ('file', 'text', 'both')
- CHECK: (grading_type = 'score' AND max_score IS NOT NULL) OR (grading_type = 'grade')
- CHECK: max_score > 0
- CHECK: max_file_size > 0
- CHECK: max_files > 0

---

### 8. assignment_templates（提出物テンプレート）

提出物のテンプレートを管理するテーブル。

| カラム名 | データ型 | NULL | デフォルト | 説明 |
|---------|---------|------|-----------|------|
| id | BIGSERIAL | NO | - | テンプレートID（主キー） |
| created_by | BIGINT | NO | - | 作成者ID（外部キー） |
| name | VARCHAR(255) | NO | - | テンプレート名 |
| title | VARCHAR(255) | NO | - | タイトル |
| description | TEXT | YES | NULL | 説明 |
| grading_type | VARCHAR(20) | NO | 'score' | 評価方式 |
| max_score | INTEGER | YES | NULL | 最大点数 |
| submission_type | VARCHAR(20) | NO | 'file' | 提出形式 |
| allowed_file_types | JSON | YES | NULL | 許可するファイル形式 |
| max_file_size | BIGINT | NO | 52428800 | 最大ファイルサイズ |
| max_files | INTEGER | NO | 5 | 最大ファイル数 |
| created_at | TIMESTAMP | NO | CURRENT_TIMESTAMP | 作成日時 |
| updated_at | TIMESTAMP | NO | CURRENT_TIMESTAMP | 更新日時 |

**インデックス**:
- PRIMARY KEY: id
- INDEX: created_by

**外部キー**:
- created_by → users(id) ON DELETE RESTRICT

---

### 9. assignment_deadline_histories（提出期限変更履歴）

提出期限の変更履歴を記録するテーブル。

| カラム名 | データ型 | NULL | デフォルト | 説明 |
|---------|---------|------|-----------|------|
| id | BIGSERIAL | NO | - | 履歴ID（主キー） |
| assignment_id | BIGINT | NO | - | 提出物ID（外部キー） |
| old_deadline | TIMESTAMP | NO | - | 変更前の期限 |
| new_deadline | TIMESTAMP | NO | - | 変更後の期限 |
| changed_by | BIGINT | NO | - | 変更者ID（外部キー） |
| reason | TEXT | YES | NULL | 変更理由 |
| created_at | TIMESTAMP | NO | CURRENT_TIMESTAMP | 作成日時 |

**インデックス**:
- PRIMARY KEY: id
- INDEX: assignment_id
- INDEX: created_at

**外部キー**:
- assignment_id → assignments(id) ON DELETE CASCADE
- changed_by → users(id) ON DELETE RESTRICT

---

### 10. submissions（提出状況）

学生の提出状況を管理するテーブル。

| カラム名 | データ型 | NULL | デフォルト | 説明 |
|---------|---------|------|-----------|------|
| id | BIGSERIAL | NO | - | 提出ID（主キー） |
| assignment_id | BIGINT | NO | - | 提出物ID（外部キー） |
| student_id | BIGINT | NO | - | 学生ID（外部キー） |
| status | VARCHAR(20) | NO | 'not_submitted' | 提出状態 |
| score | INTEGER | YES | NULL | 点数 |
| grade | VARCHAR(10) | YES | NULL | 評価（S/A/B/C/D等） |
| comment_from_teacher | TEXT | YES | NULL | 教員からのコメント |
| comment_from_student | TEXT | YES | NULL | 学生からのコメント |
| submitted_at | TIMESTAMP | YES | NULL | 提出日時 |
| graded_at | TIMESTAMP | YES | NULL | 採点日時 |
| graded_by | BIGINT | YES | NULL | 採点者ID（外部キー） |
| resubmission_deadline | TIMESTAMP | YES | NULL | 再提出期限 |
| resubmission_count | INTEGER | NO | 0 | 再提出回数 |
| is_overdue | BOOLEAN | NO | false | 期限超過フラグ |
| created_at | TIMESTAMP | NO | CURRENT_TIMESTAMP | 作成日時 |
| updated_at | TIMESTAMP | NO | CURRENT_TIMESTAMP | 更新日時 |

**インデックス**:
- PRIMARY KEY: id
- UNIQUE: assignment_id, student_id
- INDEX: student_id
- INDEX: status
- INDEX: submitted_at
- INDEX: graded_at
- INDEX: is_overdue

**外部キー**:
- assignment_id → assignments(id) ON DELETE RESTRICT
- student_id → users(id) ON DELETE RESTRICT
- graded_by → users(id) ON DELETE SET NULL

**制約**:
- CHECK: status IN ('not_submitted', 'submitted', 'graded', 'resubmit_required', 'resubmitted')
- CHECK: score >= 0
- CHECK: resubmission_count >= 0

---

### 11. submission_files（提出ファイル）

提出されたファイルを管理するテーブル。

| カラム名 | データ型 | NULL | デフォルト | 説明 |
|---------|---------|------|-----------|------|
| id | BIGSERIAL | NO | - | ファイルID（主キー） |
| submission_id | BIGINT | NO | - | 提出ID（外部キー） |
| filename | VARCHAR(255) | NO | - | 保存ファイル名 |
| original_filename | VARCHAR(255) | NO | - | 元のファイル名 |
| file_size | BIGINT | NO | - | ファイルサイズ（バイト） |
| mime_type | VARCHAR(100) | NO | - | MIMEタイプ |
| storage_path | VARCHAR(500) | NO | - | ストレージパス |
| version | INTEGER | NO | 1 | バージョン |
| uploaded_at | TIMESTAMP | NO | CURRENT_TIMESTAMP | アップロード日時 |
| deleted_at | TIMESTAMP | YES | NULL | 削除日時（論理削除） |

**インデックス**:
- PRIMARY KEY: id
- INDEX: submission_id
- INDEX: version
- INDEX: deleted_at

**外部キー**:
- submission_id → submissions(id) ON DELETE CASCADE

**制約**:
- CHECK: file_size > 0
- CHECK: version > 0

---

### 12. submission_histories（提出状況変更履歴）

提出状況の変更履歴を記録するテーブル。

| カラム名 | データ型 | NULL | デフォルト | 説明 |
|---------|---------|------|-----------|------|
| id | BIGSERIAL | NO | - | 履歴ID（主キー） |
| submission_id | BIGINT | NO | - | 提出ID（外部キー） |
| status_from | VARCHAR(20) | NO | - | 変更前の状態 |
| status_to | VARCHAR(20) | NO | - | 変更後の状態 |
| changed_by | BIGINT | NO | - | 変更者ID（外部キー） |
| note | TEXT | YES | NULL | 備考 |
| created_at | TIMESTAMP | NO | CURRENT_TIMESTAMP | 作成日時 |

**インデックス**:
- PRIMARY KEY: id
- INDEX: submission_id
- INDEX: changed_by
- INDEX: created_at

**外部キー**:
- submission_id → submissions(id) ON DELETE CASCADE
- changed_by → users(id) ON DELETE RESTRICT

---

### 13. submission_comments（提出物コメント）

提出物に対するコメントを管理するテーブル。

| カラム名 | データ型 | NULL | デフォルト | 説明 |
|---------|---------|------|-----------|------|
| id | BIGSERIAL | NO | - | コメントID（主キー） |
| submission_id | BIGINT | NO | - | 提出ID（外部キー） |
| user_id | BIGINT | NO | - | コメント投稿者ID（外部キー） |
| comment | TEXT | NO | - | コメント本文 |
| attachments | JSON | YES | NULL | 添付ファイル情報 |
| created_at | TIMESTAMP | NO | CURRENT_TIMESTAMP | 作成日時 |
| updated_at | TIMESTAMP | NO | CURRENT_TIMESTAMP | 更新日時 |

**インデックス**:
- PRIMARY KEY: id
- INDEX: submission_id
- INDEX: user_id
- INDEX: created_at

**外部キー**:
- submission_id → submissions(id) ON DELETE CASCADE
- user_id → users(id) ON DELETE RESTRICT

---

### 14. notifications（通知）

ユーザーへの通知を管理するテーブル。

| カラム名 | データ型 | NULL | デフォルト | 説明 |
|---------|---------|------|-----------|------|
| id | BIGSERIAL | NO | - | 通知ID（主キー） |
| user_id | BIGINT | NO | - | ユーザーID（外部キー） |
| type | VARCHAR(50) | NO | - | 通知タイプ |
| title | VARCHAR(255) | NO | - | タイトル |
| message | TEXT | NO | - | メッセージ |
| data | JSON | YES | NULL | 関連データ |
| is_read | BOOLEAN | NO | false | 既読フラグ |
| read_at | TIMESTAMP | YES | NULL | 既読日時 |
| created_at | TIMESTAMP | NO | CURRENT_TIMESTAMP | 作成日時 |

**インデックス**:
- PRIMARY KEY: id
- INDEX: user_id
- INDEX: is_read
- INDEX: created_at
- INDEX: user_id, is_read（複合）

**外部キー**:
- user_id → users(id) ON DELETE CASCADE

**制約**:
- CHECK: type IN ('assignment_created', 'deadline_reminder', 'graded', 'resubmit_required', 'system')

---

### 15. notification_settings（通知設定）

ユーザーごとの通知設定を管理するテーブル。

| カラム名 | データ型 | NULL | デフォルト | 説明 |
|---------|---------|------|-----------|------|
| id | BIGSERIAL | NO | - | 設定ID（主キー） |
| user_id | BIGINT | NO | - | ユーザーID（外部キー、ユニーク） |
| email_enabled | BOOLEAN | NO | true | メール通知ON/OFF |
| assignment_created | BOOLEAN | NO | true | 課題作成通知 |
| deadline_reminder | BOOLEAN | NO | true | 期限リマインダー |
| graded | BOOLEAN | NO | true | 採点完了通知 |
| resubmit_required | BOOLEAN | NO | true | 再提出指示通知 |
| created_at | TIMESTAMP | NO | CURRENT_TIMESTAMP | 作成日時 |
| updated_at | TIMESTAMP | NO | CURRENT_TIMESTAMP | 更新日時 |

**インデックス**:
- PRIMARY KEY: id
- UNIQUE: user_id

**外部キー**:
- user_id → users(id) ON DELETE CASCADE

---

### 16. audit_logs（監査ログ）

システム内の重要な操作を記録するテーブル（削除・更新不可）。

| カラム名 | データ型 | NULL | デフォルト | 説明 |
|---------|---------|------|-----------|------|
| id | BIGSERIAL | NO | - | ログID（主キー） |
| user_id | BIGINT | YES | NULL | 操作者ID（外部キー） |
| action | VARCHAR(20) | NO | - | 操作（create/update/delete） |
| model | VARCHAR(100) | NO | - | モデル名 |
| model_id | BIGINT | NO | - | モデルID |
| changes | JSON | YES | NULL | 変更内容 |
| ip_address | VARCHAR(45) | YES | NULL | IPアドレス |
| user_agent | TEXT | YES | NULL | User Agent |
| created_at | TIMESTAMP | NO | CURRENT_TIMESTAMP | 作成日時 |

**インデックス**:
- PRIMARY KEY: id
- INDEX: user_id
- INDEX: action
- INDEX: model
- INDEX: created_at
- INDEX: model, model_id（複合）

**外部キー**:
- user_id → users(id) ON DELETE SET NULL

**制約**:
- CHECK: action IN ('create', 'update', 'delete', 'login', 'logout')

**注意**: このテーブルは削除・更新を禁止（改ざん防止）

---

### 17. system_settings（システム設定）

システム全体の設定を管理するテーブル。

| カラム名 | データ型 | NULL | デフォルト | 説明 |
|---------|---------|------|-----------|------|
| id | BIGSERIAL | NO | - | 設定ID（主キー） |
| key | VARCHAR(100) | NO | - | 設定キー（ユニーク） |
| value | TEXT | NO | - | 設定値 |
| type | VARCHAR(20) | NO | 'string' | 値の型 |
| description | TEXT | YES | NULL | 説明 |
| created_at | TIMESTAMP | NO | CURRENT_TIMESTAMP | 作成日時 |
| updated_at | TIMESTAMP | NO | CURRENT_TIMESTAMP | 更新日時 |

**インデックス**:
- PRIMARY KEY: id
- UNIQUE: key

**制約**:
- CHECK: type IN ('string', 'integer', 'boolean', 'json')

---

## インデックス定義

### パフォーマンス最適化のための複合インデックス

```sql
-- ユーザー検索
CREATE INDEX idx_users_role_active ON users(role, is_active);

-- 科目検索
CREATE INDEX idx_subjects_year_term ON subjects(academic_year_id, term_id);

-- 提出状況検索
CREATE INDEX idx_submissions_assignment_status ON submissions(assignment_id, status);
CREATE INDEX idx_submissions_student_status ON submissions(student_id, status);

-- 通知検索
CREATE INDEX idx_notifications_user_unread ON notifications(user_id, is_read, created_at DESC);

-- 監査ログ検索
CREATE INDEX idx_audit_logs_user_date ON audit_logs(user_id, created_at DESC);
CREATE INDEX idx_audit_logs_model_date ON audit_logs(model, model_id, created_at DESC);
```

---

## 制約定義

### ユニーク制約

```sql
-- ユーザー
ALTER TABLE users ADD CONSTRAINT uk_users_email UNIQUE (email);
ALTER TABLE users ADD CONSTRAINT uk_users_student_number UNIQUE (student_number) WHERE student_number IS NOT NULL;

-- 年度
ALTER TABLE academic_years ADD CONSTRAINT uk_academic_years_year UNIQUE (year);

-- 学期
ALTER TABLE terms ADD CONSTRAINT uk_terms_year_name UNIQUE (academic_year_id, name);

-- 科目
ALTER TABLE subjects ADD CONSTRAINT uk_subjects_code_year UNIQUE (code, academic_year_id);

-- 科目担当教員
ALTER TABLE subject_teachers ADD CONSTRAINT uk_subject_teachers UNIQUE (subject_id, teacher_id);

-- 履修
ALTER TABLE enrollments ADD CONSTRAINT uk_enrollments UNIQUE (subject_id, student_id);

-- 提出状況
ALTER TABLE submissions ADD CONSTRAINT uk_submissions UNIQUE (assignment_id, student_id);

-- 通知設定
ALTER TABLE notification_settings ADD CONSTRAINT uk_notification_settings_user UNIQUE (user_id);

-- システム設定
ALTER TABLE system_settings ADD CONSTRAINT uk_system_settings_key UNIQUE (key);
```

### CHECK制約

```sql
-- ユーザー
ALTER TABLE users ADD CONSTRAINT chk_users_role CHECK (role IN ('admin', 'teacher', 'student_admin', 'student'));

-- 年度
ALTER TABLE academic_years ADD CONSTRAINT chk_academic_years_dates CHECK (start_date < end_date);
ALTER TABLE academic_years ADD CONSTRAINT chk_academic_years_year CHECK (year BETWEEN 2000 AND 2100);

-- 学期
ALTER TABLE terms ADD CONSTRAINT chk_terms_dates CHECK (start_date < end_date);

-- 提出物
ALTER TABLE assignments ADD CONSTRAINT chk_assignments_grading_type CHECK (grading_type IN ('score', 'grade'));
ALTER TABLE assignments ADD CONSTRAINT chk_assignments_submission_type CHECK (submission_type IN ('file', 'text', 'both'));
ALTER TABLE assignments ADD CONSTRAINT chk_assignments_max_score CHECK (max_score > 0);

-- 提出状況
ALTER TABLE submissions ADD CONSTRAINT chk_submissions_status CHECK (status IN ('not_submitted', 'submitted', 'graded', 'resubmit_required', 'resubmitted'));
ALTER TABLE submissions ADD CONSTRAINT chk_submissions_score CHECK (score >= 0);

-- 提出ファイル
ALTER TABLE submission_files ADD CONSTRAINT chk_submission_files_size CHECK (file_size > 0);

-- 監査ログ
ALTER TABLE audit_logs ADD CONSTRAINT chk_audit_logs_action CHECK (action IN ('create', 'update', 'delete', 'login', 'logout'));

-- システム設定
ALTER TABLE system_settings ADD CONSTRAINT chk_system_settings_type CHECK (type IN ('string', 'integer', 'boolean', 'json'));
```

---

## パーティショニング戦略

大量データが蓄積されるテーブルのパーティショニング戦略。

### audit_logs（監査ログ）

年単位でパーティショニング：

```sql
-- パーティションテーブル作成例（PostgreSQL）
CREATE TABLE audit_logs (
    -- カラム定義
) PARTITION BY RANGE (created_at);

-- 2024年のパーティション
CREATE TABLE audit_logs_2024 PARTITION OF audit_logs
    FOR VALUES FROM ('2024-01-01') TO ('2025-01-01');

-- 2025年のパーティション
CREATE TABLE audit_logs_2025 PARTITION OF audit_logs
    FOR VALUES FROM ('2025-01-01') TO ('2026-01-01');
```

### notifications（通知）

3ヶ月単位でパーティショニングも検討可能。

---

## バックアップ戦略

### 日次バックアップ

- **対象**: 全テーブル
- **方式**: pg_dump
- **保持期間**: 30日

### 週次バックアップ

- **対象**: 全テーブル
- **方式**: pg_dump（圧縮）
- **保持期間**: 12週間

### 月次バックアップ

- **対象**: 全テーブル
- **方式**: pg_dump（圧縮）
- **保持期間**: 5年

---

## データ保持期間

| テーブル | 保持期間 | 備考 |
|---------|---------|------|
| users | 卒業後3年 | 個人情報保護法対応 |
| submissions | 卒業後1年 | 提出物データ |
| submission_files | 卒業後1年 | ストレージコスト考慮 |
| notifications | 6ヶ月 | 既読後は削除可 |
| audit_logs | 5年以上 | 法定保存期間 |
| その他 | 無期限 | マスタデータ |

---

## 初期データ（Seeder）

### 管理者ユーザー

```sql
INSERT INTO users (name, email, password, role, is_active, is_first_login)
VALUES ('システム管理者', 'admin@example.com', '$2y$10$...', 'admin', true, false);
```

### システム設定

```sql
INSERT INTO system_settings (key, value, type, description) VALUES
('smtp_host', 'smtp.example.com', 'string', 'SMTPホスト'),
('smtp_port', '587', 'integer', 'SMTPポート'),
('max_file_size', '52428800', 'integer', '最大ファイルサイズ（バイト）'),
('allowed_file_types', '["pdf","docx","xlsx","jpg","png","zip"]', 'json', '許可するファイル形式'),
('session_timeout', '120', 'integer', 'セッションタイムアウト（分）'),
('password_min_length', '8', 'integer', 'パスワード最小長'),
('notification_timing_days', '3', 'integer', '期限リマインダー日数');
```

---

## マイグレーション順序

テーブル作成の依存関係を考慮した順序：

1. users
2. academic_years
3. terms
4. subjects
5. subject_teachers
6. enrollments
7. assignment_templates
8. assignments
9. assignment_deadline_histories
10. submissions
11. submission_files
12. submission_histories
13. submission_comments
14. notifications
15. notification_settings
16. audit_logs
17. system_settings

---

## セキュリティ考慮事項

### データベースユーザー権限

```sql
-- アプリケーション用ユーザー
CREATE USER school_app WITH PASSWORD 'secure_password';

-- 必要最小限の権限付与
GRANT SELECT, INSERT, UPDATE, DELETE ON ALL TABLES IN SCHEMA public TO school_app;
GRANT USAGE, SELECT ON ALL SEQUENCES IN SCHEMA public TO school_app;

-- audit_logsは削除・更新不可
REVOKE UPDATE, DELETE ON audit_logs FROM school_app;
```

### パスワードポリシー

- 最小8文字
- 英数字混在推奨
- bcryptでハッシュ化（コスト: 10）

### 個人情報の暗号化

必要に応じて以下のカラムを暗号化：

- users.email
- users.student_number

---

## 変更履歴

| 日付 | バージョン | 変更内容 |
|------|-----------|---------|
| 2024-04-15 | 1.0.0 | 初版作成 |
