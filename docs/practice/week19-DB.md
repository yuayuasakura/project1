# users
| カラム名      | 型           | NULL | デフォルト | 説明              |
|--------------|-------------|------|-----------|-------------------|
| id           | BIGINT      | NO   | AUTO      | ユーザーID（PK）   |
| name         | VARCHAR(50) | NO   | -         | ユーザー名         |
| email        | VARCHAR(100)| NO   | -         | メールアドレス     |
| password     | VARCHAR(255)| NO   | -         | パスワード（ハッシュ）|
| created_at   | TIMESTAMP   | NO   | CURRENT   | 作成日時          |
| updated_at   | TIMESTAMP   | NO   | CURRENT   | 更新日時          |

【インデックス】
- PRIMARY KEY (id)
- UNIQUE KEY (email)

【制約】
- email: UNIQUE制約
- password: 最低8文字


# posts
| カラム名      | 型           | NULL | デフォルト | 説明              |
|--------------|-------------|------|-----------|-------------------|
| id           | BIGINT      | NO   | AUTO      | 投稿ID（PK）       |
| user_id      | BIGINT      | NO   | -         | ユーザーID（FK）   |
| title        | VARCHAR(200)| NO   | -         | タイトル          |
| content      | TEXT        | NO   | -         | 本文              |
| published    | BOOLEAN     | NO   | false     | 公開状態          |
| created_at   | TIMESTAMP   | NO   | CURRENT   | 作成日時          |
| updated_at   | TIMESTAMP   | NO   | CURRENT   | 更新日時          |

【インデックス】
- PRIMARY KEY (id)
- INDEX (user_id)
- INDEX (created_at)

【外部キー】
- user_id REFERENCES users(id) ON DELETE CASCADE


# comments
| カラム名      | 型           | NULL | デフォルト | 説明              |
|--------------|-------------|------|-----------|-------------------|
| id           | BIGINT      | NO   | AUTO      | コメントID（PK）   |
| post_id      | BIGINT      | NO   | -         | 対象記事（FK）     |
| user_id      | BIGINT      | NO   | -         | 投稿者            |
| parent_id    | BIGINT      | YES  | NULL      | 返信先コメント     |
| body         | TEXT        | NO   | -         | コメント本文       |
| is_approved  | BOOLEAN     | NO   | TRUE      | 認証済みか         |
| created_at   | TIMESTAMP   | NO   | CURRENT   | 作成日時          |
| updated_at   | TIMESTAMP   | NO   | CURRENT   | 更新日時          |

【インデックス】
- PRIMARY KEY (id)
- INDEX (user_id)
- INDEX (post_id,created_at)
- INDEX (parent_id)

【制約】
- body:1~1000文字まで


# tags
| カラム名      | 型           | NULL | デフォルト | 説明              |
|--------------|-------------|------|-----------|-------------------|
| id           | BIGINT      | NO   | AUTO      | タグID（PK）       |
| name         | VARCHAR(50) | NO   | -         | タグ名             |
| slug         | VARCHAR(50) | NO   | -         | URL用の識別子       |
| created_at   | TIMESTAMP   | NO   | CURRENT   | 作成日時          |
| updated_at   | TIMESTAMP   | NO   | CURRENT   | 更新日時          |

【インデックス】
- PRIMARY KEY (id)
- UNIQUE KEY (name)
- UNIQUE KEY (slug)

【制約】
- name: UNIQUE制約
- slug: 英字とハイフンのみ