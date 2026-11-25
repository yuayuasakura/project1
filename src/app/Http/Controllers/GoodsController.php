<?php

namespace App\Http\Controllers;

use App\Models\Goods;
use Illuminate\Http\Request;

class GoodsController extends Controller
{
    public function index(Request $request)
    {
        $goods = Goods::with(['member', 'category',
                'userGoods' => fn ($q) => $q->where('user_id', auth()->id())])
            ->when($request->keyword, fn ($q, $kw) => $q->where('name', 'like', "%{$kw}%"))
            ->get();

        return view('goods.index', compact('goods'));
    }

    public function show(Goods $goods)
    {
        $userGoods = $goods->userGoods()->where('user_id', auth()->id())->first();

        return view('goods.show', compact('goods', 'userGoods'));
    }
}
