| 画面・操作           | メソッド   | URL                   | ルート名        | コントローラー@アクション     | ログイン   | 権限（Policy）    |
|--------------------|-----------|-----------------------|----------------|---------------------------|----------|------------------|
| トップ(一覧へ転送)   | GET        | /                     | -              | (リダイレクトのみ)          | 不要      | -                |
| タスク一覧          | GET       | /tasks?keyword=...&status=...&sort=... | tasks.index    | TaskController@show   | 必要     | 自分のタスクだけ表示                |
| タスク作成フォーム   | GET        | /tasks/create         | tasks.create   | TaskController@create     | 必要     | -                |
| タスクの保存        | POST       | /tasks                | tasks.store    | TaskController@store      | 必要     | -                |
| タスク詳細          | GET        | /tasks/{task}         | tasks.show     | TaskController@show      | 必要     | 自分のタスクだけ   |
| タスク編集          | GET        | /tasks/{task}/edit    | tasks.edit     | TaskController@edit      | 必要     | 自分のタスクだけ   |
| タスクの更新        | PUT       | /tasks/{task}         | tasks.update   | TaskController@update     | 必要     | 自分のタスクだけ   |
| タスクの削除        | DELETE    | /tasks/{task}         | tasks.destroy  | TaskController@destroy    | 必要     | 自分のタスクだけ   |
| 完了・未完了の切替（Ajax）| PATCH      | /tasks/{task}/toggle    | tasks.toggle     | TaskController@toggle     | 必要     | 自分のタスクだけ    |

※ ログイン・ログアウト・会員登録の画面は Breeze が作るルートを使う
