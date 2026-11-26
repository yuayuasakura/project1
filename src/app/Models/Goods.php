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
    public function wishes() { return $this->hasMany(Wish::class, 'goods_id'); }

    /**
     * 管理方法で絞り込む（ログイン中のユーザーの設定で判断する）
     *  'collect' … 「集める」にしたグッズだけ
     *  'one'     … 「1つでいい」のグッズだけ（まだ所持状況を登録していないグッズも、初期値の「1つでいい」として含める）
     */
    public function scopeCollectType($query, string $type, int $userId)
    {
        $isCollect = fn ($q) => $q->where('user_id', $userId)->where('collect_type', 'collect');

        return $type === 'collect'
            ? $query->whereHas('userGoods', $isCollect)
            : $query->whereDoesntHave('userGoods', $isCollect);
    }

    /**
     * 所持で絞り込む（ログイン中のユーザーの所持数で判断する）
     *  'yes' … 1つでも持っているグッズだけ
     *  'no'  … 1つも持っていないグッズだけ（所持状況をまだ登録していないグッズも含める）
     */
    public function scopeOwned($query, string $owned, int $userId)
    {
        $hasAny = fn ($q) => $q->where('user_id', $userId)->where('quantity', '>', 0);

        return $owned === 'yes'
            ? $query->whereHas('userGoods', $hasAny)
            : $query->whereDoesntHave('userGoods', $hasAny);
    }

    /**
     * 一覧画面の検索・絞り込み（グッズ一覧と回収一覧で共通）
     */
    public static function searchForUser(array $filters, int $userId)
    {
        return static::with(['member', 'category',
                'userGoods' => fn ($q) => $q->where('user_id', $userId)])
            ->when($filters['keyword'] ?? null, fn ($q, $kw) => $q->where('name', 'like', "%{$kw}%"))
            ->when($filters['member'] ?? null, fn ($q, $id) => $q->where('member_id', $id))
            ->when($filters['category'] ?? null, fn ($q, $id) => $q->where('category_id', $id))
            ->when($filters['collect_type'] ?? null, fn ($q, $type) => $q->collectType($type, $userId))
            ->when($filters['owned'] ?? null, fn ($q, $owned) => $q->owned($owned, $userId))
            ->get();
    }
}
