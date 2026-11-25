<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserGoods extends Model
{
    protected $table = 'user_goods';
    protected $fillable = ['user_id', 'goods_id', 'quantity', 'collect_type'];

    public function user()  { return $this->belongsTo(User::class); }
    public function goods() { return $this->belongsTo(Goods::class, 'goods_id'); }
}
