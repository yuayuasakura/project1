<?php

namespace App\Http\Controllers;

use App\Models\Goods;
use App\Models\UserGoods;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class UserGoodsController extends Controller
{
    // 所持数の更新（Ajax）
    public function updateQuantity(Request $request, Goods $goods)
    {
        $validated = $request->validate([
            'quantity' => ['required', 'integer', 'min:0'],
        ]);

        $userGoods = $this->findOrNew($goods);
        $userGoods->quantity = $validated['quantity'];
        $userGoods->save();

        return response()->json($this->toJson($userGoods));
    }

    // 管理方法の切り替え（Ajax）
    public function updateCollectType(Request $request, Goods $goods)
    {
        $validated = $request->validate([
            'collect_type' => ['required', Rule::in(array_keys(UserGoods::TYPE_LABELS))],
        ]);

        $userGoods = $this->findOrNew($goods);
        $userGoods->collect_type = $validated['collect_type'];
        $userGoods->save();

        return response()->json($this->toJson($userGoods));
    }

    // 自分の所持状況を探す。まだなければ新しく用意する
    private function findOrNew(Goods $goods): UserGoods
    {
        $userGoods = UserGoods::firstOrNew(
            ['user_id' => auth()->id(), 'goods_id' => $goods->id],
            ['quantity' => 0, 'collect_type' => UserGoods::TYPE_NONE]   // 新しく作るときの初期値（未選択）
        );

        if ($userGoods->exists) {
            Gate::authorize('update', $userGoods);       // ⑥の Policy で確認
        }

        return $userGoods;
    }

    // 返す JSON の形（routing.md に書いたものと同じ）
    private function toJson(UserGoods $userGoods): array
    {
        return [
            'goods_id'     => $userGoods->goods_id,
            'quantity'     => $userGoods->quantity,
            'collect_type' => $userGoods->collect_type,
            'owned'        => $userGoods->quantity > 0,
        ];
    }
}
