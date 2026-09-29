<?php

use App\Http\Controllers\Auth\PasswordResetController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public website (SOW section C) and Author Portal (SOW section B)
|--------------------------------------------------------------------------
*/

Route::get('/', fn () => view('welcome'))->name('home');

// Shared password reset link (used by both the admin panel and the author portal).
Route::middleware('guest')->group(function () {
    Route::get('forgot-password', [PasswordResetController::class, 'request'])->name('password.request');
    Route::post('forgot-password', [PasswordResetController::class, 'email'])->middleware('throttle:5,1')->name('password.email');
    Route::get('reset-password/{token}', [PasswordResetController::class, 'edit'])->name('password.reset');
    Route::post('reset-password', [PasswordResetController::class, 'update'])->name('password.update');
});

// Placeholders until the public site / Author Portal modules land.
Route::get('blogs/{slug}', fn () => abort(404))->name('blogs.show');
Route::get('login', fn () => redirect()->route('home'))->name('login');
Route::get('account', fn () => redirect()->route('home'))->middleware('auth')->name('account.dashboard');
