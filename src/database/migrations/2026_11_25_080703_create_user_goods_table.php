<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('user_goods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('goods_id')->constrained('goods')->cascadeOnDelete();
            $table->unsignedInteger('quantity')->default(0);
            $table->string('collect_type', 10)->default('one');
            $table->timestamps();

            $table->unique(['user_id', 'goods_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_goods');
    }
};
