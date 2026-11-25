<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class MasterDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // ① メンバー
        DB::table('members')->insert([
            ['name' => 'ふうが', 'display_order' => 1],
            ['name' => 'ゆう', 'display_order' => 2],
            ['name' => 'かい', 'display_order' => 3],
        ]);

        // ② 種類
        DB::table('categories')->insert([
            ['name' => '缶バッジ'],
        ]);

        // ③ シリーズ
        DB::table('series')->insert([
            ['name' => 'デビューグッズ'],
            ['name' => '2026ツアーグッズ'],
        ]);

        // 名前からIDを取り出せるようにする（例：$members['メンバーA'] → 1）
        $members    = DB::table('members')->pluck('id', 'name');
        $categories = DB::table('categories')->pluck('id', 'name');
        $series     = DB::table('series')->pluck('id', 'name');

        // ④ グッズ
        $goods = [
            // [グッズ名, メンバー, 種類, シリーズ, 発売日, 画像ファイル名]
            ['デビューグッズ ふうが', 'ふうが', '缶バッジ', 'デビューグッズ', '2026-01-01', 'debut_a.jpg'],
            ['デビューグッズ ゆう', 'ゆう', '缶バッジ', 'デビューグッズ', '2026-01-01', 'debut_b.jpg'],
            ['デビューグッズ かい', 'かい', '缶バッジ', 'デビューグッズ', '2026-01-01', 'debut_c.jpg'],
            ['デビューグッズ 集合', null, '缶バッジ', 'デビューグッズ', '2026-01-01', 'debut_all.jpg'],
            ['2026ツアー缶バッジ ふうが', 'ふうが', '缶バッジ', '2026ツアーグッズ', '2026-04-01', 'sum26_a.jpg'],
            ['2026ツアー缶バッジ ゆう', 'ゆう', '缶バッジ', '2026ツアーグッズ', '2026-04-01', 'sum26_b.jpg'],
            ['2026ツアー缶バッジ かい', 'かい', '缶バッジ', '2026ツアーグッズ', '2026-04-01', 'sum26_c.jpg'],
            ['2026ツアー缶バッジ 集合', null, '缶バッジ', '2026ツアーグッズ', '2026-04-01', 'sum26_all.jpg'],
        ];

        foreach ($goods as [$name, $member, $category, $seriesName, $releaseDate, $image]) {
            DB::table('goods')->insert([
                'name'         => $name,
                'member_id'    => $member ? $members[$member] : null,          // 集合柄はNULL
                'category_id'  => $categories[$category],
                'series_id'    => $seriesName ? $series[$seriesName] : null,   // 単品はNULL
                'release_date' => $releaseDate,
                'image_path'   => $this->copyImage($image),
            ]);
        }
    }
     // database/seeders/images/ の写真を storage/app/public/goods/ にコピーして、保存先のパスを返す
    private function copyImage(?string $fileName): ?string
    {
        if (!$fileName) {
            return null;
        }

        $from = database_path('seeders/images/' . $fileName);

        if (!file_exists($from)) {
            return null;  // 写真がまだ用意できていなくてもエラーにしない
        }

        Storage::disk('public')->put('goods/' . $fileName, file_get_contents($from));

        return 'goods/' . $fileName;
    }
}
