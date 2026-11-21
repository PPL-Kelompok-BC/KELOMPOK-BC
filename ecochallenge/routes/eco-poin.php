<?php

use App\Http\Controllers\AdminClaimReviewController;
use Illuminate\Support\Facades\Route;

// PBI-19 (SCRUM-31): admin menerima klaim, poin mahasiswa bertambah otomatis. Khusus admin.
Route::middleware(['auth', 'role:admin'])->prefix('admin')->group(function () {
    Route::get('/claims', [AdminClaimReviewController::class, 'index'])->name('admin.claims.index');
    Route::post('/claims/{claim}/approve', [AdminClaimReviewController::class, 'approve'])->name('admin.claims.approve');
});
