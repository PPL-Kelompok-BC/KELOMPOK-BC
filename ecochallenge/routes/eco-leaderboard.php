<?php

use App\Http\Controllers\LeaderboardController;
use Illuminate\Support\Facades\Route;

// Leaderboard (PBI-20 s.d. PBI-22). Hanya untuk pengguna yang sudah login.
Route::middleware('auth')->group(function () {
    Route::get('/leaderboard', [LeaderboardController::class, 'index'])->name('leaderboard.index');
});
