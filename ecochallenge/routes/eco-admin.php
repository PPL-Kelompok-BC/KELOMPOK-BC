
<?php

use App\Http\Controllers\AdminChallengeController;
use App\Http\Controllers\AdminClaimReviewController;
use Illuminate\Support\Facades\Route;

// PBI-08 dan PBI-09: Pengelolaan challenge oleh admin.
Route::middleware('auth')->group(function () {

    // PBI-08: Menampilkan daftar challenge.
    Route::get('/admin/challenges', [AdminChallengeController::class, 'index'])
        ->name('admin.challenges.index');

    // PBI-09: Menampilkan form tambah challenge.
    Route::get('/admin/challenges/create', [AdminChallengeController::class, 'create'])
        ->name('admin.challenges.create');

    // PBI-09: Menyimpan challenge baru.
    Route::post('/admin/challenges', [AdminChallengeController::class, 'store'])
        ->name('admin.challenges.store');

    // PBI-36 s.d. PBI-38: Tinjau & batalkan klaim (khusus admin).
    Route::middleware('role:admin')->group(function () {
        Route::get('/admin/claims', [AdminClaimReviewController::class, 'index'])
            ->name('admin.claims.index');

        Route::get('/admin/claims/{claim}', [AdminClaimReviewController::class, 'show'])
            ->name('admin.claims.show');

        Route::post('/admin/claims/{claim}/review', [AdminClaimReviewController::class, 'review'])
            ->name('admin.claims.review');
    });

});
