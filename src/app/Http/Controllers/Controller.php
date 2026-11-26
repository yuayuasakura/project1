<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

abstract class Controller
{
    /**
     * 一覧画面の検索・絞り込み条件（URL の ?keyword=... など）をチェックする
     *
     * 正しくない値があっても前の画面に戻さず、その条件だけを外して一覧を表示する。
     * 戻り値：[正しかった条件, 画面に出すエラーメッセージの配列]
     */
    protected function validateFilters(Request $request, array $rules): array
    {
        $validator = Validator::make($request->query(), $rules);

        // valid() … チェックに通った値だけ。rules にある項目だけを取り出す
        $filters = array_intersect_key($validator->valid(), $rules);

        return [$filters, $validator->errors()->all()];
    }
}
