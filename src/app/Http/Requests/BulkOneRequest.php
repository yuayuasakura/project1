<?php

namespace App\Http\Requests;

use App\Models\Goods;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

// まとめて各1に登録するときの入力チェック
class BulkOneRequest extends FormRequest
{
    // ログインしているかは auth ミドルウェアで確認済み。
    // 自分の所持状況だけを触ることは、コントローラーで auth()->id() と Policy を使って守る
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'goods_ids'   => ['required', 'array', 'min:1', 'max:200'],
            'goods_ids.*' => ['integer', 'distinct'],
        ];
    }

    /**
     * goods テーブルに存在する id かを確認する
     * （'exists' ルールを goods_ids.* に付けると1件ずつ SQL が出るので、whereIn の1回でまとめて調べる）
     */
    public function after(): array
    {
        return [
            function (Validator $validator) {
                if ($validator->errors()->isNotEmpty()) {
                    return;   // 形がおかしいときは調べない
                }

                $ids   = array_map('intval', $this->input('goods_ids', []));
                $found = Goods::whereIn('id', $ids)->pluck('id')->all();

                foreach (array_diff($ids, $found) as $index => $id) {
                    $validator->errors()->add("goods_ids.{$index}", '存在しないグッズが含まれています');
                }
            },
        ];
    }

    public function attributes(): array
    {
        return [
            'goods_ids'   => '登録するグッズ',
            'goods_ids.*' => '登録するグッズ',
        ];
    }
}
