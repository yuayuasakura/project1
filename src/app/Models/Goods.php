<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Goods extends Model
{
    protected $table = 'goods';
    public $timestamps = false;

    // 「メンバーから選ぶ」の「全員集合」を表す値（URL の ?member=group）
    public const MEMBER_GROUP = 'group';

    // 「全員集合」のアイコン画像（storage/app/public の中。シーダーでコピーする）
    public const MEMBER_GROUP_IMAGE = 'members/all.jpg';

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
    public function series(): BelongsTo
    {
        return $this->belongsTo(Series::class);
    }
    public function userGoods(): HasMany
    {
        return $this->hasMany(UserGoods::class, 'goods_id');
    }
    public function wishes(): HasMany
    {
        return $this->hasMany(Wish::class, 'goods_id');
    }

    /**
     * 管理方法で絞り込む（ログイン中のユーザーの設定で判断する）
     *  'one'     … 「1つでいい」にしたグッズだけ
     *  'collect' … 「回収」にしたグッズだけ
     *  'none'    … 「未選択」のグッズだけ（まだ所持状況を登録していないグッズも、初期値の「未選択」として含める）
     */
    public function scopeCollectType($query, string $type, int $userId)
    {
        if ($type === UserGoods::TYPE_NONE) {
            return $query->whereDoesntHave('userGoods', fn ($q) => $q->where('user_id', $userId)
                ->whereIn('collect_type', [UserGoods::TYPE_ONE, UserGoods::TYPE_COLLECT]));
        }

        return $query->whereHas('userGoods', fn ($q) => $q->where('user_id', $userId)
            ->where('collect_type', $type));
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
     * グッズ名にキーワードを含むものだけ（部分一致）
     *
     * LIKE では % と _ が「何でも一致する」記号になるので、キーワードの中の % と _ は
     * 前に ! を付けて、ただの文字として探す（ESCAPE '!'）。
     * キーワードは ? のプレースホルダーで渡すので、SQL インジェクションの心配はない。
     */
    public function scopeWhereNameContains($query, string $keyword)
    {
        $escaped = str_replace(['!', '%', '_'], ['!!', '!%', '!_'], $keyword);

        return $query->whereRaw("name LIKE ? ESCAPE '!'", ['%' . $escaped . '%']);
    }

    /**
     * 一覧画面の検索・絞り込み（グッズ一覧と回収一覧で共通）
     */
    public static function searchForUser(array $filters, int $userId)
    {
        return static::with(['member', 'category',
                'userGoods' => fn ($q) => $q->where('user_id', $userId)])
            ->when($filters['keyword'] ?? null, fn ($q, $kw) => $q->whereNameContains($kw))
            // member が 'group'（全員集合）のときは、集合柄（member_id が NULL）のグッズ
            ->when($filters['member'] ?? null, fn ($q, $id) => $id === self::MEMBER_GROUP
                ? $q->whereNull('member_id')
                : $q->where('member_id', $id))
            ->when($filters['category'] ?? null, fn ($q, $id) => $q->where('category_id', $id))
            ->when($filters['series'] ?? null, fn ($q, $id) => $q->where('series_id', $id))
            ->when($filters['collect_type'] ?? null, fn ($q, $type) => $q->collectType($type, $userId))
            ->when($filters['owned'] ?? null, fn ($q, $owned) => $q->owned($owned, $userId))
            ->get();
    }
}
