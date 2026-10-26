<?php
use Illuminate\Support\Facades\Route;
// GUI demo only. No authentication or backend mutations are implemented here.
Route::view('/', 'eco.app')->name('eco.home');
