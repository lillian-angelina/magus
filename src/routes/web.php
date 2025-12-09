<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MainController;

Route::get('/', [MainController::class, 'index'] )->name('items.index');
Route::get('/brothers', [MainController::class, 'brothers'] )->name('items.brothers');
Route::get('/eldest-son', [MainController::class, 'eldestSon'] )->name('items.eldestSon');
Route::get('/second-son', [MainController::class, 'secondSon'] )->name('items.secondSon');
Route::get('/third-son', [MainController::class, 'thirdSon'] )->name('items.thirdSon');
Route::get('/fourth-son', [MainController::class, 'fourthSon'] )->name('items.fourthSon');
Route::get('/contact', function () {
    return view('items.contact');
})->name('items.contact');