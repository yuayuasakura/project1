<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\UserGoods;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

// 「まとめて各1に登録」のテスト
class BulkOneTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private User $other;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user  = User::factory()->create();
        $this->other = User::factory()->create();

        // メンバー2人・種類2つ・グッズ5個を用意する
        DB::table('members')->insert([
            ['id' => 1, 'name' => 'A', 'display_order' => 1],
            ['id' => 2, 'name' => 'B', 'display_order' => 2],
        ]);
        DB::table('categories')->insert([
            ['id' => 1, 'name' => '缶バッジ'],
            ['id' => 2, 'name' => 'アクスタ'],
        ]);
        DB::table('goods')->insert([
            ['id' => 1, 'name' => 'A缶1', 'member_id' => 1, 'category_id' => 1],
            ['id' => 2, 'name' => 'A缶2', 'member_id' => 1, 'category_id' => 1],
            ['id' => 3, 'name' => 'A缶3', 'member_id' => 1, 'category_id' => 1],
            ['id' => 4, 'name' => 'A缶4', 'member_id' => 1, 'category_id' => 1],
            ['id' => 5, 'name' => 'Bアクスタ', 'member_id' => 2, 'category_id' => 2],
        ]);
    }

    // 所持状況の行を作る
    private function row(User $user, int $goodsId, string $type, int $quantity = 0): void
    {
        UserGoods::create(['user_id' => $user->id, 'goods_id' => $goodsId, 'collect_type' => $type, 'quantity' => $quantity]);
    }

    private function typeOf(User $user, int $goodsId): ?string
    {
        return UserGoods::where('user_id', $user->id)->where('goods_id', $goodsId)->value('collect_type');
    }

    public function test_ログインしていないと使えない(): void
    {
        $this->getJson('/user-goods/bulk-one/candidates?member=1&category=1')->assertUnauthorized();
        $this->postJson('/user-goods/bulk-one', ['goods_ids' => [1]])->assertUnauthorized();
        $this->postJson('/user-goods/bulk-one/undo')->assertUnauthorized();
    }

    public function test_メンバー×種類のグッズを自分の管理方法つきで返す(): void
    {
        $this->row($this->user, 2, 'collect');
        $this->row($this->other, 3, 'collect');   // 他の人の設定は関係ない

        $this->actingAs($this->user)
            ->getJson('/user-goods/bulk-one/candidates?member=1&category=1')
            ->assertOk()
            ->assertJsonCount(4, 'goods')
            ->assertJsonPath('goods.0.collect_type', 'none')
            ->assertJsonPath('goods.1.collect_type', 'collect')
            ->assertJsonPath('goods.2.collect_type', 'none');
    }

    public function test_まとめて各1にする_行がなければ作り_あれば管理方法だけ変え_回収は変えない(): void
    {
        $this->row($this->user, 2, 'none', 3);     // 未選択・所持数3
        $this->row($this->user, 3, 'collect', 1);  // 回収（リクエストに入っていても変えない）
        $this->row($this->user, 4, 'one', 2);      // すでに各1
        $this->row($this->other, 1, 'collect');    // 他の人の行

        $this->actingAs($this->user)
            ->postJson('/user-goods/bulk-one', ['goods_ids' => [1, 2, 3, 4]])
            ->assertOk()
            ->assertJsonPath('count', 2)
            ->assertJsonPath('changes', [
                ['goods_id' => 1, 'before' => null],
                ['goods_id' => 2, 'before' => 'none'],
            ])
            ->assertJsonPath('skipped', [3]);

        // 新しく作った行は 所持数0・各1
        $this->assertDatabaseHas('user_goods', ['user_id' => $this->user->id, 'goods_id' => 1, 'quantity' => 0, 'collect_type' => 'one']);
        // 所持数は変わらない
        $this->assertDatabaseHas('user_goods', ['user_id' => $this->user->id, 'goods_id' => 2, 'quantity' => 3, 'collect_type' => 'one']);
        $this->assertSame('collect', $this->typeOf($this->user, 3));
        $this->assertDatabaseHas('user_goods', ['user_id' => $this->user->id, 'goods_id' => 4, 'quantity' => 2, 'collect_type' => 'one']);
        // 他の人の行は変わらない
        $this->assertSame('collect', $this->typeOf($this->other, 1));
    }

    public function test_元に戻すと今回変えたものだけ戻る(): void
    {
        $this->row($this->user, 2, 'none', 3);
        $this->row($this->user, 4, 'one', 2);

        $this->actingAs($this->user)->postJson('/user-goods/bulk-one', ['goods_ids' => [1, 2, 4]])->assertOk();

        $this->postJson('/user-goods/bulk-one/undo')->assertOk()->assertJsonPath('count', 2);

        // 新しく作った行は削除、もともとあった行は「未選択」に戻る。もともと各1のものはそのまま
        $this->assertDatabaseMissing('user_goods', ['user_id' => $this->user->id, 'goods_id' => 1]);
        $this->assertDatabaseHas('user_goods', ['user_id' => $this->user->id, 'goods_id' => 2, 'quantity' => 3, 'collect_type' => 'none']);
        $this->assertSame('one', $this->typeOf($this->user, 4));

        // 2回目は戻せない
        $this->postJson('/user-goods/bulk-one/undo')->assertStatus(422);
    }

    public function test_登録のあとに別の画面で変えたものは元に戻さない(): void
    {
        $this->actingAs($this->user)->postJson('/user-goods/bulk-one', ['goods_ids' => [1, 2]])->assertOk();

        // グッズ1は回収に変えた、グッズ2は所持数を増やした
        UserGoods::where('user_id', $this->user->id)->where('goods_id', 1)->update(['collect_type' => 'collect']);
        UserGoods::where('user_id', $this->user->id)->where('goods_id', 2)->update(['quantity' => 1]);

        $this->postJson('/user-goods/bulk-one/undo')->assertOk();

        $this->assertSame('collect', $this->typeOf($this->user, 1));
        // 所持数が消えないように、削除せず「未選択」に戻す
        $this->assertDatabaseHas('user_goods', ['user_id' => $this->user->id, 'goods_id' => 2, 'quantity' => 1, 'collect_type' => 'none']);
    }

    public function test_goods_idsのバリデーション(): void
    {
        $this->actingAs($this->user);

        $this->postJson('/user-goods/bulk-one', [])->assertJsonValidationErrors('goods_ids');
        $this->postJson('/user-goods/bulk-one', ['goods_ids' => 'abc'])->assertJsonValidationErrors('goods_ids');
        $this->postJson('/user-goods/bulk-one', ['goods_ids' => [1, 999]])->assertJsonValidationErrors('goods_ids.1');

        $this->assertDatabaseCount('user_goods', 0);
    }

    public function test_各1ページにだけ表示される(): void
    {
        $this->actingAs($this->user);

        $this->get('/goods?collect_type=one')->assertOk()->assertSee('まとめて各1に登録');
        $this->get('/goods')->assertOk()->assertDontSee('まとめて各1に登録');
        $this->get('/collections')->assertOk()->assertDontSee('まとめて各1に登録');
    }
}
