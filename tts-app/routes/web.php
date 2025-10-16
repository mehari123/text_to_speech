<?php

use App\Http\Controllers\TranslationController;
use App\Http\Controllers\HistoryController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application.
|
*/

// Main translation page
Route::get('/', [TranslationController::class, 'index'])->name('home');

// Translation endpoints
Route::post('/translate', [TranslationController::class, 'translate'])->name('translate');
Route::get('/download/{id}', [TranslationController::class, 'download'])->name('download');

// History endpoints
Route::get('/history', [HistoryController::class, 'index'])->name('history');
Route::get('/history/{id}', [HistoryController::class, 'show'])->name('history.show');
Route::delete('/history/{id}', [HistoryController::class, 'destroy'])->name('history.destroy');
Route::post('/history/clear', [HistoryController::class, 'clear'])->name('history.clear');
