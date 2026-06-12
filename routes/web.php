<?php

use App\Http\Controllers\PredictionController;
use App\Http\Controllers\ResultsController;
use App\Http\Controllers\ScoresController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'Welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'Dashboard')->name('dashboard');

    Route::get('predictions', [PredictionController::class, 'index'])->name('predictions.index');
    Route::post('predictions/{game}', [PredictionController::class, 'store'])->name('predictions.store');

    Route::get('results', [ResultsController::class, 'index'])->name('results.index');

    Route::middleware('scores.admin')->group(function () {
        Route::get('scores', [ScoresController::class, 'index'])->name('scores.index');
        Route::post('scores/{game}', [ScoresController::class, 'update'])->name('scores.update');
    });
});

require __DIR__.'/settings.php';
