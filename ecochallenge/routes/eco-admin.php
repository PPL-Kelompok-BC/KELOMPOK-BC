<?php

use App\Http\Controllers\AdminCategoryController;
use App\Http\Controllers\AdminChallengeController;
use Illuminate\Support\Facades\Route;

// PBI-08, PBI-09, PBI-10, dan PBI-11: Pengelolaan challenge oleh admin.
Route::middleware('auth')->group(function () {

    // PBI-05: Menampilkan daftar kategori challenge.
    Route::get('/admin/categories', [AdminCategoryController::class, 'index'])
        ->name('admin.categories.index');

       // PBI-06: Menambah kategori challenge baru.
    Route::post('/admin/categories', [AdminCategoryController::class, 'store'])
        ->name('admin.categories.store');

        // PBI-07: Mengubah kategori (Update)
    Route::put('/admin/categories/{category}', [AdminCategoryController::class, 'update'])
        ->name('admin.categories.update');

    // PBI-07: Menghapus kategori (Destroy) dengan proteksi jika masih ada challenge
    Route::delete('/admin/categories/{category}', [AdminCategoryController::class, 'destroy'])
        ->name('admin.categories.destroy');

    // PBI-08: Menampilkan daftar challenge.
    Route::get('/admin/challenges', [AdminChallengeController::class, 'index'])
        ->name('admin.challenges.index');

    // PBI-09: Menampilkan form tambah challenge.
    Route::get('/admin/challenges/create', [AdminChallengeController::class, 'create'])
        ->name('admin.challenges.create');

    // PBI-09: Menyimpan challenge baru.
    Route::post('/admin/challenges', [AdminChallengeController::class, 'store'])
        ->name('admin.challenges.store');

    // PBI-10: Menampilkan form edit challenge.
    Route::get('/admin/challenges/{challenge}/edit', [AdminChallengeController::class, 'edit'])
        ->name('admin.challenges.edit');

    // PBI-10: Menyimpan perubahan challenge.
    Route::put('/admin/challenges/{challenge}', [AdminChallengeController::class, 'update'])
        ->name('admin.challenges.update');

    // PBI-11: Menonaktifkan challenge.
    Route::post('/admin/challenges/{challenge}/deactivate', [AdminChallengeController::class, 'deactivate'])
        ->name('admin.challenges.deactivate');

    // PBI-11: Mengaktifkan kembali challenge.
    Route::post('/admin/challenges/{challenge}/activate', [AdminChallengeController::class, 'activate'])
        ->name('admin.challenges.activate');
});
