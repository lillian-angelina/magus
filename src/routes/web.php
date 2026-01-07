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

Route::get('/songs', [SongController::class, 'index'])->name('songs.index');
Route::post('/songs', [SongController::class, 'store'])->name('songs.store'); // 新規保存
Route::get('/songs/{id}/edit', [SongController::class, 'editor'])->name('songs.editor');
Route::put('/songs/{id}', [SongController::class, 'update'])->name('songs.update'); // 更新
Route::delete('/songs/{id}', [SongController::class, 'destroy'])->name('songs.destroy'); // 削除
Route::get('/songs/create', function () {
    return view('songs.create');
})->name('songs.create');