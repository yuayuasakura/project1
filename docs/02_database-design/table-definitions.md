# user
番号/名前/物理名/型/備考
1/ユーザーID/id/int/--
2/メールアドレス/email/char/ログインに使用
3/パスワード/password_hash/char/ハッシュ化して保存
4/登録日時/created_at/datetime/--
5/更新日時/updated_at/datetime/--

# members
番号/名前/物理名/型/備考
1/メンバーID/id/int/--
2/メンバー名/name/char/--
3/表示順/display_order/int/一覧に並べる際の順番

# categories
番号/名前/物理名/型/備考
1/種類ID/id/int/--
2/種類名/name/char/アクスタ、缶バッジなど

# series
番号/名前/物理名/型/備考
1/シリーズID/id/int/--
2/シリーズ名/name/char/2026年夏ツアーグッズ

# goods
番号/名前/物理名/型/備考
1/グッズID/id/int/--
2/グッズ名/name/char/--
3/メンバーID/member_id/int/members.idより・全員が写った画像はNULL
4/種類ID/category_id/int/categories.idより
5/シリーズID/series_id/int/series.idより・シリーズがないものはNULL
6/発売日/release_date/date/--

# user_goods
番号/名前/物理名/型/備考
1/所持状況ID/id/int/--
2/ユーザーID/user_id/int/user.idより
3/グッズID/goods_id/int/goods.idより
4/所持数/quantity/int/0以上
5/管理方法/collect_type/char/none（未選択・初期値）/ one（1つでいい）/ collect（回収）
6/更新日時/updated_at/datetime/--