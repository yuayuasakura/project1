<?php

namespace App\Http\Controllers;

use App\Models\Goods;
use Illuminate\Http\Request;

// 回収一覧：管理方法を「集める」にしたグッズだけを表示する
class CollectionController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate([
            'keyword'  => ['nullable', 'string', 'max:100'],
            'member'   => ['nullable', 'integer'],
            'category' => ['nullable', 'integer'],
        ]);
        $filters['collect_type'] = 'collect';

        $goods = Goods::searchForUser($filters, auth()->id());

        // グッズ一覧と同じ画面を使う
        return view('goods.index', [
            'goods'  => $goods,
            'title'  => '回収（集める）',
            'active' => 'collections',
            'action' => route('collections.index'),
            'select' => null,   // 回収の画面ではプルダウンを出さない
        ]);
    }
}
