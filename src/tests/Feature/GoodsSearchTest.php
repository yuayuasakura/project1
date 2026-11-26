<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

// グッズ一覧の検索・絞り込みと、エラーメッセージの日本語表示のテスト
class GoodsSearchTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        app()->setLocale('ja');

        $this->user = User::factory()->create();

        DB::table('members')->insert([['id' => 1, 'name' => 'A', 'display_order' => 1]]);
        DB::table('categories')->insert([['id' => 1, 'name' => '缶バッジ']]);
        DB::table('goods')->insert([
            ['id' => 1, 'name' => '缶バッジ A', 'member_id' => 1, 'category_id' => 1],
            ['id' => 2, 'name' => '100%缶バッジ', 'member_id' => 1, 'category_id' => 1],
            ['id' => 3, 'name' => 'A_B アクスタ', 'member_id' => null, 'category_id' => 1],
        ]);
    }

    // 一覧に出たグッズの id
    private function ids(string $url): array
    {
        preg_match_all('#/goods/(\d+)"#', $this->actingAs($this->user)->get($url)->assertOk()->getContent(), $m);

        return array_map('intval', $m[1]);
    }

    public function test_キーワードは部分一致で探せる(): void
    {
        $this->assertSame([1, 2], $this->ids('/goods?keyword=' . urlencode('缶バッジ')));
    }

    public function test_キーワードの記号はただの文字として探す(): void
    {
        $this->assertSame([2], $this->ids('/goods?keyword=' . urlencode('%')));
        $this->assertSame([3], $this->ids('/goods?keyword=' . urlencode('_')));
        $this->assertSame([], $this->ids('/goods?keyword=' . urlencode('!')));
    }

    public function test_正しくない条件はエラーを表示して、その条件だけ外す(): void
    {
        $response = $this->actingAs($this->user)->get('/goods?member=xyz&category=1');

        // 前の画面に戻さず、その場で表示する
        $response->assertOk()
            ->assertSee('正しくない条件があったため、その条件を外して表示しています')
            ->assertSee('メンバーの形式が正しくありません。');

        // member は外れ、category=1 の絞り込みは効いたまま（3件とも種類1）
        preg_match_all('#/goods/(\d+)"#', $response->getContent(), $m);
        $this->assertSame(['1', '2', '3'], $m[1]);
    }

    public function test_回収一覧でも正しくない条件はエラーを表示する(): void
    {
        $this->actingAs($this->user)->get('/collections?series=abc')
            ->assertOk()
            ->assertSee('シリーズは整数で入力してください。');
    }

    public function test_正しい条件ならエラーは出ない(): void
    {
        $this->actingAs($this->user)->get('/goods?member=group&keyword=A')
            ->assertOk()
            ->assertDontSee('正しくない条件があったため');
    }

    public function test_Ajaxのエラーメッセージが日本語になる(): void
    {
        $this->actingAs($this->user)->patchJson('/goods/1/quantity', ['quantity' => -1])
            ->assertStatus(422)
            ->assertJsonPath('message', '所持数は0以上にしてください。');

        $this->actingAs($this->user)->patchJson('/goods/1/collect-type', ['collect_type' => 'x'])
            ->assertStatus(422)
            ->assertJsonPath('message', '選んだ管理方法は正しくありません。');
    }

    public function test_ログイン失敗のメッセージが日本語になる(): void
    {
        $this->post('/login', ['email' => $this->user->email, 'password' => 'wrong-password'])
            ->assertSessionHasErrors(['email' => 'メールアドレスまたはパスワードが正しくありません。']);
    }
}
