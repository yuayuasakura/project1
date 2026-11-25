<?php

namespace App\Http\Controllers;

use App\Models\Goods;
use App\Models\UserGoods;

class HomeController extends Controller
{
    public function index()
    {
        // グッズ一覧（新しい順に12個）
        $goods = Goods::orderByDesc('release_date')->take(12)->get();

        // 回収状況（自分が「集める」にしたグッズ）
        $collecting = UserGoods::with('goods')
            ->where('user_id', auth()->id())
            ->where('collect_type', 'collect')
            ->get();

        return view('home', compact('goods', 'collecting'));
    }
}
