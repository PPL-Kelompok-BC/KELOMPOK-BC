<?php

use App\Http\Controllers\AdminChallengeController;
use Illuminate\Support\Facades\Route;

// PBI-08: Daftar challenge untuk admin.
Route::middleware('auth')->group(function () {
    Route::get('/admin/challenges', [AdminChallengeController::class, 'index'])
        ->name('admin.challenges.index');
});