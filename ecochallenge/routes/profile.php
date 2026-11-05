<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
    Route::get('/profil', [ProfileController::class, 'show'])->name('profile.show');
    Route::get('/profil/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profil', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profil/foto', [ProfileController::class, 'updatePhoto'])->name('profile.photo.update');
    Route::get('/pengguna/{user}/foto', [ProfileController::class, 'photo'])->name('profile.photo');
});
