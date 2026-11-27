<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Wish extends Model
{
    protected $fillable = ['user_id', 'goods_id'];

    public function goods(): BelongsTo
    {
        return $this->belongsTo(Goods::class, 'goods_id');
    }
}
