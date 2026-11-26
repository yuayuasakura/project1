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
            ['name' => 'ふうが', 'display_order' => 1, 'image_path' => $this->copyImage('members/huga.jpg', 'members')],
            ['name' => 'ゆう',   'display_order' => 2, 'image_path' => $this->copyImage('members/yu.jpg', 'members')],
            ['name' => 'かい',   'display_order' => 3, 'image_path' => $this->copyImage('members/kai.jpg', 'members')],
        ]);

        // ② 種類
        DB::table('categories')->insert([
            ['name' => '缶バッジ',   'image_path' => $this->copyImage('categories/badge.jpg', 'categories')],
            ['name' => 'アクスタ',   'image_path' => $this->copyImage('categories/akusuta.jpg', 'categories')],
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
            ['デビューグッズ 缶バッジ ふうが', 'ふうが', '缶バッジ', 'デビューグッズ', '2026-01-01', 'debut_a.jpg'],
            ['デビューグッズ 缶バッジ ゆう', 'ゆう', '缶バッジ', 'デビューグッズ', '2026-01-01', 'debut_b.jpg'],
            ['デビューグッズ 缶バッジ かい', 'かい', '缶バッジ', 'デビューグッズ', '2026-01-01', 'debut_c.jpg'],
            ['デビューグッズ 缶バッジ 集合', null, '缶バッジ', 'デビューグッズ', '2026-01-01', 'debut_all.jpg'],

            ['デビューグッズ アクリルスタンド ふうが', 'ふうが', 'アクスタ', 'デビューグッズ', '2026-01-01', 'debut_s_a.jpg'],
            ['デビューグッズ アクリルスタンド ゆう', 'ゆう', 'アクスタ', 'デビューグッズ', '2026-01-01', 'debut_s_b.jpg'],
            ['デビューグッズ アクリルスタンド かい', 'かい', 'アクスタ', 'デビューグッズ', '2026-01-01', 'debut_s_c.jpg'],

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
    private function copyImage(?string $fileName, string $folder = 'goods'): ?string
    {
       if (!$fileName) {
            return null;
        }

        $from = database_path('seeders/images/' . $fileName);

        if (!file_exists($from)) {
            return null;
        }

        $to = $folder . '/' . basename($fileName);
        Storage::disk('public')->put($to, file_get_contents($from));

        return $to;
    }
}
