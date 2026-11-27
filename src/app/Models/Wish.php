<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Wish extends Model
{
    protected $fillable = ['user_id', 'goods_id'];

    public function goods()
    {
        return $this->belongsTo(Goods::class, 'goods_id');
    }
}
