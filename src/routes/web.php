<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MainController;
use App\Http\Controllers\SongController;

Route::get('/', [MainController::class, 'index'])->name('items.index');
Route::get('/brothers', [MainController::class, 'brothers'])->name('items.brothers');
Route::get('/eldest-son', [MainController::class, 'eldestSon'])->name('items.eldestSon');
Route::get('/second-son', [MainController::class, 'secondSon'])->name('items.secondSon');
Route::get('/third-son', [MainController::class, 'thirdSon'])->name('items.thirdSon');
Route::get('/fourth-son', [MainController::class, 'fourthSon'])->name('items.fourthSon');
Route::get('/items', [MainController::class, 'tabIndex'])->name('items.tabIndex');

Route::get('/contact', function () {
    return view('items.contact');
})->name('items.contact');

// 一覧表示
Route::get('/songs', [SongController::class, 'index'])->name('songs.index');
// 新規作成エディタ
Route::get('/songs/create', [SongController::class, 'create'])->name('songs.create');
// 編集エディタ（ID指定）
Route::get('/songs/{song}/edit', [SongController::class, 'edit'])->name('songs.editor');
// 詳細・プレビュー画面（今回追加するもの）
Route::get('/songs/{song}', [SongController::class, 'show'])->name('songs.show');
// 保存・更新・削除
Route::post('/songs', [SongController::class, 'store'])->name('songs.store');
Route::put('/songs/{song}', [SongController::class, 'update'])->name('songs.update');
Route::delete('/songs/{song}', [SongController::class, 'destroy'])->name('songs.destroy');