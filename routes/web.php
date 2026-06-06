<?php

use App\Http\Controllers\PredictionController;
use App\Http\Controllers\ResultsController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'Welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'Dashboard')->name('dashboard');

    Route::get('predictions', [PredictionController::class, 'index'])->name('predictions.index');
    Route::post('predictions/{game}', [PredictionController::class, 'store'])->name('predictions.store');

    Route::get('results', [ResultsController::class, 'index'])->name('results.index');
});

require __DIR__.'/settings.php';
