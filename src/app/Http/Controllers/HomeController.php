<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Member;
use App\Models\Series;
use App\Models\UserGoods;

class HomeController extends Controller
{
    public function index()
    {
        // メンバーから選ぶ
        $members = Member::orderBy('display_order')->get();

        // グッズの種類から選ぶ
        $categories = Category::orderBy('id')->get();

        // シリーズから選ぶ
        $series = Series::orderBy('id')->get();

        // 回収状況（変更なし）
        $collecting = UserGoods::with('goods')
            ->where('user_id', auth()->id())
            ->where('collect_type', 'collect')
            ->get();

        return view('home', compact('members', 'categories', 'series', 'collecting'));
    }
}
