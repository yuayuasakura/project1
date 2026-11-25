<?php

use App\Http\Controllers\GoodsController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserGoodsController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;

// Breeze はログイン後に dashboard へ移動するので、トップページへ転送する
Route::redirect('/dashboard', '/')->name('dashboard');

Route::middleware('auth')->group(function () {
    // トップページ（未ログインなら auth ミドルウェアでログイン画面へ）
    Route::get('/', [HomeController::class, 'index'])->name('home');

    // プロフィール（Breeze）
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // グッズ
    Route::get('/goods', [GoodsController::class, 'index'])->name('goods.index');
    Route::get('/goods/{goods}', [GoodsController::class, 'show'])->name('goods.show');

    // 所持状況（Ajax）
    Route::patch('/goods/{goods}/quantity', [UserGoodsController::class, 'updateQuantity'])->name('user-goods.quantity');
    Route::patch('/goods/{goods}/collect-type', [UserGoodsController::class, 'updateCollectType'])->name('user-goods.collect-type');
});

require __DIR__.'/auth.php';
