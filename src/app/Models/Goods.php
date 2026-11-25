<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Goods extends Model
{
    protected $table = 'goods';
    public $timestamps = false;

    public function member()   { return $this->belongsTo(Member::class); }
    public function category() { return $this->belongsTo(Category::class); }
    public function series()   { return $this->belongsTo(Series::class); }
    public function userGoods(){ return $this->hasMany(UserGoods::class, 'goods_id'); }
}
