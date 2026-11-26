<?php

namespace App\Http\Controllers;

use App\Models\Goods;
use Illuminate\Http\Request;
use App\Models\Wish;

class GoodsController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate([
            'keyword'      => ['nullable', 'string', 'max:100'],
            'member'       => ['nullable', 'integer'],
            'category'     => ['nullable', 'integer'],
            'collect_type' => ['nullable', 'in:one'],                    // 下のメニュー「各１」から来たとき
            'owned'        => ['nullable', 'in:yes,no'],                 // 各１のプルダウン：所持済（yes）／未所持（no）
            'filter'       => ['nullable', 'in:collect,owned,unowned'],  // グッズ一覧のプルダウン：回収／所持済／未所持
        ]);

        // 「各１」から来たときだけ、下のメニューの「各１」を太字にする
        $isOne = ($filters['collect_type'] ?? null) === 'one';

        // グッズ一覧のプルダウンの値を、絞り込みの条件に置きかえる
        if (! $isOne) {
            match ($filters['filter'] ?? null) {
                'collect' => $filters['collect_type'] = 'collect',
                'owned'   => $filters['owned'] = 'yes',
                'unowned' => $filters['owned'] = 'no',
                default   => null,
            };
        }

        $goods = Goods::searchForUser($filters, auth()->id());

        // 見出しの右上のプルダウン（name … URL のパラメータ名、options … 値 => 表示名。'' がデフォルトの「一覧」）
        $select = $isOne
            ? ['name' => 'owned',  'options' => ['' => '一覧', 'yes' => '所持済', 'no' => '未所持']]
            : ['name' => 'filter', 'options' => ['' => '一覧', 'collect' => '回収', 'owned' => '所持済', 'unowned' => '未所持']];

        return view('goods.index', [
            'goods'  => $goods,
            'title'  => $isOne ? '各１（1つでいい）' : 'グッズ一覧',
            'active' => $isOne ? 'goods' : '',
            'action' => route('goods.index'),
            'select' => $select,
        ]);
    }

    public function show(Goods $goods)
    {
        $userGoods = $goods->userGoods()->where('user_id', auth()->id())->first();

        $wished = Wish::where('user_id', auth()->id())
            ->where('goods_id', $goods->id)
            ->exists();

        return view('goods.show', compact('goods', 'userGoods', 'wished'));
    }
}
