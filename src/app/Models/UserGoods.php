<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserGoods extends Model
{
    protected $table = 'user_goods';
    protected $fillable = ['user_id', 'goods_id', 'quantity', 'collect_type'];

    // 管理方法（collect_type）の値
    public const TYPE_NONE    = 'none';     // 未選択（初期値）
    public const TYPE_ONE     = 'one';      // 1つでいい
    public const TYPE_COLLECT = 'collect';  // 回収

    // 画面に出す名前（プルダウンの並び順もこの順）
    public const TYPE_LABELS = [
        self::TYPE_NONE    => '未選択',
        self::TYPE_ONE     => '1つでいい',
        self::TYPE_COLLECT => '回収',
    ];

    public function user()  { return $this->belongsTo(User::class); }
    public function goods() { return $this->belongsTo(Goods::class, 'goods_id'); }
}
