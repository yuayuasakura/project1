<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Member;
use App\Models\UserGoods;
use App\Models\Wish;

class HomeController extends Controller
{
    public function index()
    {
        // メンバーから選ぶ
        $members = Member::orderBy('display_order')->get();

        // グッズの種類から選ぶ
        $categories = Category::orderBy('id')->get();

        // 欲しい！に追加したもの（新しく追加した順に8個）
        $wishes = Wish::with('goods')
            ->where('user_id', auth()->id())
            ->latest()
            ->take(8)
            ->get();

        // 回収状況（変更なし）
        $collecting = UserGoods::with('goods')
            ->where('user_id', auth()->id())
            ->where('collect_type', 'collect')
            ->get();

        return view('home', compact('members', 'categories', 'wishes', 'collecting'));
    }
}
