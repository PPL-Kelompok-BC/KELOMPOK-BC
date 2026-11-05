<?php

use App\Http\Controllers\ClaimController;
use Illuminate\Support\Facades\Route;

// Klaim challenge: unggah & verifikasi bukti foto (PBI-15 s.d. PBI-18).
Route::middleware('auth')->group(function () {
    Route::get('/challenges/{challenge}/claims', [ClaimController::class, 'create'])->name('claims.create');
    Route::post('/challenges/{challenge}/claims', [ClaimController::class, 'store'])->name('claims.store');
    Route::get('/claims', [ClaimController::class, 'index'])->name('claims.index');
    Route::get('/claims/{claim}', [ClaimController::class, 'show'])->name('claims.show');
    Route::post('/claims/{claim}/resubmit', [ClaimController::class, 'resubmit'])->name('claims.resubmit');
    Route::get('/claims/{claim}/photo', [ClaimController::class, 'photo'])->name('claims.photo');
});
