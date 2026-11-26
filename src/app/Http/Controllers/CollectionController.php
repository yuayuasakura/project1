<?php

namespace App\Http\Controllers;

use App\Models\Goods;
use Illuminate\Http\Request;

// 回収一覧：管理方法を「回収」にしたグッズだけを表示する
class CollectionController extends Controller
{
    public function index(Request $request)
    {
        // 正しくない条件は外して表示し、エラーメッセージを画面に出す
        [$filters, $filterErrors] = $this->validateFilters($request, [
            'keyword'  => ['nullable', 'string', 'max:100'],
            'member'   => ['nullable', 'regex:/^(\d+|group)$/'],   // メンバーの id か、全員集合（group）
            'category' => ['nullable', 'integer'],
            'series'   => ['nullable', 'integer'],
        ]);
        $filters['collect_type'] = 'collect';

        $goods = Goods::searchForUser($filters, auth()->id());

        // グッズ一覧と同じ画面を使う
        return view('goods.index', [
            'goods'  => $goods,
            'title'  => '回収',
            'active' => 'collections',
            'action' => route('collections.index'),
            'select' => null,   // 回収の画面ではプルダウンを出さない
            'filterErrors' => $filterErrors,
        ]);
    }
}
