<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// 管理方法に「未選択（none）」を追加したので、初期値を 'one' から 'none' に変える
// none … 未選択 / one … 1つでいい / collect … 回収
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_goods', function (Blueprint $table) {
            $table->string('collect_type', 10)->default('none')->change();
        });
    }

    public function down(): void
    {
        Schema::table('user_goods', function (Blueprint $table) {
            $table->string('collect_type', 10)->default('one')->change();
        });
    }
};
