画面・操作|メソッド|URL|ルート名|コントローラー@アクション|ログイン|権限

トップページ|GET|/|home|HomeController@index|必要|--
グッズ一覧(検索・絞り込み)|GET|/goods?keyword=...&member=...&category=...&owned=...|goods.index|GoodsController@index|必要|--
グッズ詳細|GET|/goods/{goods}|goods.show|GoodsController@show|必要|--
回収柄一覧|GET|/collections?series...|collections.index|CollectionController@index|必要|--
所持数の更新|PATCH|/goods/{goods}/quantity|user-goods.quantity|UserGoodsController@updateQuantity|必要|自分の所持状況だけ
管理方法の切り替え|PATCH|/goods/{goods}/collect-type|user-goods.collect-type|UserGoodsController@updateCollectType|必要|自分の状況だけ

※ ログイン・ログアウト・会員登録・パスワード再設定の画面は Breeze が作るルートを使う
※ 自分の所持状況だけを見るアプリなので、すべての画面でログインが必要（未ログインならログイン画面へ移動）
※ グッズ情報はあらかじめ登録済みのため、グッズの作成・編集・削除のルートは作らない
※ 所持数の更新（Ajax）は画面を返さず、JSON（所持数と管理方法）を返す
※ 管理方法の切り替え（Ajax）は画面を返さず、JSON（所持数と管理方法）を返す