<?php

use App\Http\Controllers\BulkOneController;
use App\Http\Controllers\CollectionController;
use App\Http\Controllers\GoodsController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserGoodsController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\WishController; 

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

    // 回収一覧（管理方法が「回収」のグッズだけ）
    Route::get('/collections', [CollectionController::class, 'index'])->name('collections.index');

    // 所持状況（Ajax）
    Route::patch('/goods/{goods}/quantity', [UserGoodsController::class, 'updateQuantity'])->name('user-goods.quantity');
    Route::patch('/goods/{goods}/collect-type', [UserGoodsController::class, 'updateCollectType'])->name('user-goods.collect-type');

    // まとめて各1に登録（Ajax）
    Route::get('/user-goods/bulk-one/candidates', [BulkOneController::class, 'candidates'])->name('user-goods.bulk-one.candidates');
    Route::post('/user-goods/bulk-one', [BulkOneController::class, 'store'])->name('user-goods.bulk-one.store');
    Route::post('/user-goods/bulk-one/undo', [BulkOneController::class, 'undo'])->name('user-goods.bulk-one.undo');

    // 欲しい！（Ajax）
    Route::post('/goods/{goods}/wish', [WishController::class, 'toggle'])->name('wishes.toggle');
});

require __DIR__.'/auth.php';
