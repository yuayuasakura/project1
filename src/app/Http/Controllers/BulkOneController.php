<?php

namespace App\Http\Controllers;

use App\Http\Requests\BulkOneRequest;
use App\Models\Goods;
use App\Models\UserGoods;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

// 各1ページの「まとめて各1に登録」
//  candidates … メンバー × 種類のグッズを返す
//  store      … チェックしたグッズを、まとめて「各1（1つでいい）」にする
//  undo       … 直前の store で変えたグッズだけ、元の状態に戻す
class BulkOneController extends Controller
{
    // 「元に戻す」ための情報をセッションに入れるときのキー
    private const UNDO_KEY = 'bulk_one_undo';

    /**
     * 対象のグッズ（メンバー × 種類）を JSON で返す
     */
    public function candidates(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'member'   => ['required', 'integer', 'exists:members,id'],
            'category' => ['required', 'integer', 'exists:categories,id'],
        ]);

        // グッズと、自分の所持状況をまとめて取得する（SQL は2回だけ）
        $goods = Goods::with(['userGoods' => fn ($q) => $q->where('user_id', auth()->id())])
            ->where('member_id', $validated['member'])
            ->where('category_id', $validated['category'])
            ->orderBy('id')
            ->get();

        return response()->json([
            'goods' => $goods->map(fn (Goods $item) => [
                'id'           => $item->id,
                'name'         => $item->name,
                'image_url'    => $item->image_path ? Storage::url($item->image_path) : null,
                // 所持状況がまだなければ「未選択」
                'collect_type' => $item->userGoods->first()->collect_type ?? UserGoods::TYPE_NONE,
            ])->values(),
        ]);
    }

    /**
     * チェックしたグッズを、まとめて「各1」にする
     */
    public function store(BulkOneRequest $request): JsonResponse
    {
        $userId   = auth()->id();
        $goodsIds = array_map('intval', $request->validated('goods_ids'));

        $result = DB::transaction(function () use ($userId, $goodsIds) {
            // 自分の所持状況をまとめて取得する（他の人の行は取らない）
            // lockForUpdate … 処理中に別のリクエストで同じ行が変わらないようにする
            $existing = UserGoods::where('user_id', $userId)
                ->whereIn('goods_id', $goodsIds)
                ->lockForUpdate()
                ->get()
                ->keyBy('goods_id');

            $createGoodsIds = [];   // 行がないので新しく作るグッズ
            $updateRowIds   = [];   // 「未選択」から「各1」に変える行
            $changes        = [];   // 変更前の状態（画面に返す）
            $skipped        = [];   // 「回収」なので変えなかったグッズ

            foreach ($goodsIds as $goodsId) {
                $row = $existing->get($goodsId);

                if (! $row) {
                    $createGoodsIds[] = $goodsId;
                    $changes[] = ['goods_id' => $goodsId, 'before' => null];   // null … 行がなかった
                    continue;
                }

                // 自分の行かどうかを Policy でも確認する
                Gate::authorize('update', $row);

                if ($row->collect_type === UserGoods::TYPE_COLLECT) {
                    // 「回収」は、リクエストに入っていても変えない
                    $skipped[] = $goodsId;
                } elseif ($row->collect_type !== UserGoods::TYPE_ONE) {
                    $updateRowIds[] = $row->id;
                    $changes[] = ['goods_id' => $goodsId, 'before' => $row->collect_type];
                }
                // すでに「各1」のものは何もしない
            }

            $now = now();

            // 行がないグッズは、まとめて新しく作る（所持数は0）
            if ($createGoodsIds) {
                UserGoods::insert(array_map(fn ($goodsId) => [
                    'user_id'      => $userId,
                    'goods_id'     => $goodsId,
                    'quantity'     => 0,
                    'collect_type' => UserGoods::TYPE_ONE,
                    'created_at'   => $now,
                    'updated_at'   => $now,
                ], $createGoodsIds));
            }

            // 行があるグッズは、所持数は変えずに管理方法だけまとめて変える
            if ($updateRowIds) {
                UserGoods::where('user_id', $userId)
                    ->whereIn('id', $updateRowIds)
                    ->update(['collect_type' => UserGoods::TYPE_ONE, 'updated_at' => $now]);
            }

            return compact('createGoodsIds', 'changes', 'skipped')
                + ['updatedGoodsIds' => array_column(array_filter($changes, fn ($c) => $c['before'] !== null), 'goods_id')];
        });

        // 「元に戻す」で使う情報はサーバー側に持っておく（画面から送られた値は信じない）
        session()->put(self::UNDO_KEY, [
            'created' => $result['createGoodsIds'],    // 新しく作った行 → 削除する
            'updated' => $result['updatedGoodsIds'],   // 「未選択」から変えた行 → 「未選択」に戻す
        ]);

        return response()->json([
            'count'   => count($result['changes']),   // 実際に「各1」にした件数
            'changes' => $result['changes'],
            'skipped' => $result['skipped'],
        ]);
    }

    /**
     * 直前の「まとめて各1に登録」を元に戻す
     */
    public function undo(): JsonResponse
    {
        $undo = session()->pull(self::UNDO_KEY);   // 取り出したら消す（2回は戻せない）

        if (! $undo) {
            return response()->json(['message' => '元に戻せる操作がありません'], 422);
        }

        $userId = auth()->id();

        $count = DB::transaction(function () use ($userId, $undo) {
            $count = 0;

            // 登録したあとに別の画面で管理方法を変えた行は、そのままにする（今も「各1」の行だけ戻す）
            $stillOne = fn ($ids) => UserGoods::where('user_id', $userId)
                ->whereIn('goods_id', $ids)
                ->where('collect_type', UserGoods::TYPE_ONE);

            if ($undo['created']) {
                // 新しく作った行は削除する
                // ただし、そのあと所持数を増やした行は消すと所持数がなくなるので「未選択」に戻すだけにする
                $count += $stillOne($undo['created'])->where('quantity', 0)->delete();
                $count += $stillOne($undo['created'])->update(['collect_type' => UserGoods::TYPE_NONE, 'updated_at' => now()]);
            }

            if ($undo['updated']) {
                // もともとあった行は、元の「未選択」に戻す
                $count += $stillOne($undo['updated'])->update(['collect_type' => UserGoods::TYPE_NONE, 'updated_at' => now()]);
            }

            return $count;
        });

        return response()->json(['count' => $count]);
    }
}
