<?php

use App\Http\Controllers\ChallengeController;
use Illuminate\Support\Facades\Route;

// Katalog challenge (PBI-12 s.d. PBI-14). Hanya untuk pengguna yang sudah login.
Route::middleware('auth')->group(function () {
    Route::get('/challenges', [ChallengeController::class, 'index'])->name('challenges.index');
    Route::get('/challenges/{challenge}', [ChallengeController::class, 'show'])->name('challenges.show');
});
