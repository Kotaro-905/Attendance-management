# テーブル定義書

## 1. usersテーブル

ユーザー情報（スタッフおよび管理者）を管理するテーブル

| カラム名 | データ型 | NULL | キー | デフォルト値 | 説明 |
|---------|---------|------|------|------------|------|
| id | BIGINT UNSIGNED | NO | PK | AUTO_INCREMENT | ユーザーID |
| name | VARCHAR(255) | NO | | | ユーザー名 |
| email | VARCHAR(255) | NO | UNIQUE | | メールアドレス（ログイン用） |
| email_verified_at | TIMESTAMP | YES | | NULL | メール認証日時 |
| password | VARCHAR(255) | NO | | | パスワード（ハッシュ化） |
| role | TINYINT | NO | | 0 | 権限（0: 一般ユーザー, 1: 管理者） |
| remember_token | VARCHAR(100) | YES | | NULL | ログイン保持トークン |
| created_at | TIMESTAMP | YES | | NULL | 作成日時 |
| updated_at | TIMESTAMP | YES | | NULL | 更新日時 |

**リレーション:**
- `attendances`: 1対多（自分の勤怠記録）
- `correctionRequests`: 1対多（自分が出した修正申請）
- `handledCorrectionRequests`: 1対多（管理者として対応した修正申請、外部キー: admin_id）

---

## 2. attendancesテーブル

勤怠記録を管理するテーブル（1ユーザー・1日につき1レコード）

| カラム名 | データ型 | NULL | キー | デフォルト値 | 説明 |
|---------|---------|------|------|------------|------|
| id | BIGINT UNSIGNED | NO | PK | AUTO_INCREMENT | 勤怠ID |
| user_id | BIGINT UNSIGNED | NO | FK, UNIQUE(user_id, work_date) | | ユーザーID |
| work_date | DATE | NO | UNIQUE(user_id, work_date) | | 勤務日 |
| clock_in_at | TIME | YES | | NULL | 出勤時刻 |
| break_start_at | TIME | YES | | NULL | 休憩開始時刻（旧仕様、複数休憩には未対応） |
| break_end_at | TIME | YES | | NULL | 休憩終了時刻（旧仕様、複数休憩には未対応） |
| clock_out_at | TIME | YES | | NULL | 退勤時刻 |
| status | TINYINT | NO | | | ステータス（0: 未出勤, 1: 勤務中, 2: 休憩中, 3: 退勤済） |
| remarks | VARCHAR(255) | YES | | NULL | 備考 |
| created_at | TIMESTAMP | YES | | NULL | 作成日時 |
| updated_at | TIMESTAMP | YES | | NULL | 更新日時 |

**制約:**
- UNIQUE KEY: (user_id, work_date) - 1ユーザーにつき1日1レコード
- FOREIGN KEY: user_id → users(id) ON DELETE CASCADE

**リレーション:**
- `user`: 多対1（打刻したユーザー）
- `correctionRequests`: 1対多（この勤怠に対する修正申請）
- `breaks`: 1対多（この勤怠の休憩記録）

---

## 3. attendance_breaksテーブル

勤怠記録の休憩時間を管理するテーブル（複数回の休憩に対応）

| カラム名 | データ型 | NULL | キー | デフォルト値 | 説明 |
|---------|---------|------|------|------------|------|
| id | BIGINT UNSIGNED | NO | PK | AUTO_INCREMENT | 休憩ID |
| attendance_id | BIGINT UNSIGNED | NO | FK | | 勤怠ID |
| start_at | TIME | NO | | | 休憩開始時刻 |
| end_at | TIME | YES | | NULL | 休憩終了時刻（休憩中の場合はNULL） |
| order | TINYINT UNSIGNED | NO | | 1 | 第何回目の休憩か |
| created_at | TIMESTAMP | YES | | NULL | 作成日時 |
| updated_at | TIMESTAMP | YES | | NULL | 更新日時 |

**制約:**
- FOREIGN KEY: attendance_id → attendances(id) ON DELETE CASCADE

**リレーション:**
- `attendance`: 多対1（所属する勤怠記録）

---

## 4. correction_requestsテーブル

勤怠修正申請を管理するテーブル

| カラム名 | データ型 | NULL | キー | デフォルト値 | 説明 |
|---------|---------|------|------|------------|------|
| id | BIGINT UNSIGNED | NO | PK | AUTO_INCREMENT | 修正申請ID |
| attendance_id | BIGINT UNSIGNED | NO | FK | | 対象の勤怠ID |
| user_id | BIGINT UNSIGNED | NO | FK | | 申請した一般ユーザーID |
| admin_id | BIGINT UNSIGNED | YES | FK | NULL | 対応した管理者ID（未対応の場合はNULL） |
| requested_work_date | DATE | YES | | NULL | 修正希望の勤務日 |
| requested_clock_in_time | TIME | YES | | NULL | 修正希望の出勤時刻 |
| requested_clock_out_time | TIME | YES | | NULL | 修正希望の退勤時刻 |
| reason | VARCHAR(255) | NO | | | 修正理由 |
| status | TINYINT | NO | | 0 | ステータス（0: 承認待ち, 1: 承認, 2: 却下） |
| decided_at | TIMESTAMP | YES | | NULL | 承認/却下を行った日時 |
| created_at | TIMESTAMP | YES | | NULL | 作成日時 |
| updated_at | TIMESTAMP | YES | | NULL | 更新日時 |

**注意:**
- `requested_break_start_time`と`requested_break_end_time`カラムは削除されました（複数休憩対応のため、correction_breaksテーブルに移行）

**制約:**
- FOREIGN KEY: attendance_id → attendances(id) ON DELETE CASCADE
- FOREIGN KEY: user_id → users(id) ON DELETE CASCADE
- FOREIGN KEY: admin_id → users(id) ON DELETE SET NULL

**リレーション:**
- `attendance`: 多対1（対象の勤怠記録）
- `user`: 多対1（申請したユーザー）
- `admin`: 多対1（対応した管理者、外部キー: admin_id）
- `breaks`: 1対多（修正希望の休憩時間）

---

## 5. correction_breaksテーブル

勤怠修正申請における休憩時間の修正内容を管理するテーブル

| カラム名 | データ型 | NULL | キー | デフォルト値 | 説明 |
|---------|---------|------|------|------------|------|
| id | BIGINT UNSIGNED | NO | PK | AUTO_INCREMENT | 修正休憩ID |
| correction_request_id | BIGINT UNSIGNED | NO | FK | | 修正申請ID |
| break_no | TINYINT UNSIGNED | NO | | | 第何回目の休憩か |
| requested_break_start | TIME | NO | | | 修正希望の休憩開始時刻 |
| requested_break_end | TIME | NO | | | 修正希望の休憩終了時刻 |
| created_at | TIMESTAMP | YES | | NULL | 作成日時 |
| updated_at | TIMESTAMP | YES | | NULL | 更新日時 |

**制約:**
- FOREIGN KEY: correction_request_id → correction_requests(id) ON DELETE CASCADE

**リレーション:**
- `correctionRequest`: 多対1（所属する修正申請）

---