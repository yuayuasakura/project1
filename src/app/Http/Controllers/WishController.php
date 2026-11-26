<?php

namespace App\Http\Controllers;

use App\Models\Goods;
use App\Models\Wish;

class WishController extends Controller
{
    // 欲しい！の追加・解除を切り替える（Ajax）
    public function toggle(Goods $goods)
    {
        $wish = Wish::where('user_id', auth()->id())
            ->where('goods_id', $goods->id)
            ->first();

        if ($wish) {
            $wish->delete();          // すでに追加済み → 解除
            $wished = false;
        } else {
            Wish::create([            // まだ → 追加
                'user_id'  => auth()->id(),
                'goods_id' => $goods->id,
            ]);
            $wished = true;
        }

        return response()->json([
            'goods_id' => $goods->id,
            'wished'   => $wished,
        ]);
    }
}