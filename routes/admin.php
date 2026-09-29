<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\Auth\PasswordResetController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Superadmin panel (SOW section A) — prefix /admin, route names admin.*
|--------------------------------------------------------------------------
| Every route behind the `admin` middleware is limited to active
| Superadmin/Reviewer accounts; each controller then authorizes the
| specific permission through policies or Gate checks.
*/

Route::middleware('guest')->group(function () {
    Route::get('login', [Admin\Auth\LoginController::class, 'create'])->name('login');
    Route::post('login', [Admin\Auth\LoginController::class, 'store'])->middleware('throttle:10,1')->name('login.store');
    Route::get('forgot-password', [PasswordResetController::class, 'request'])->name('password.request');
    Route::post('forgot-password', [PasswordResetController::class, 'email'])->middleware('throttle:5,1')->name('password.email');
});

Route::middleware('admin')->group(function () {
    Route::post('logout', [Admin\Auth\LoginController::class, 'destroy'])->name('logout');

    Route::get('/', Admin\DashboardController::class)->name('dashboard');

    Route::get('profile', [Admin\ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('profile', [Admin\ProfileController::class, 'update'])->name('profile.update');

    // Manuscript catalogue
    Route::patch('author-categories/{author_category}/status', [Admin\AuthorCategoryController::class, 'toggleStatus'])->name('author-categories.toggle-status');
    Route::resource('author-categories', Admin\AuthorCategoryController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::patch('content-categories/{content_category}/status', [Admin\ContentCategoryController::class, 'toggleStatus'])->name('content-categories.toggle-status');
    Route::resource('content-categories', Admin\ContentCategoryController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::resource('themes', Admin\ContentCategoryThemeController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::get('fees', [Admin\ManuscriptFeeController::class, 'index'])->name('fees.index');
    Route::put('fees', [Admin\ManuscriptFeeController::class, 'update'])->name('fees.update');

    // Administration
    Route::patch('users/{user}/status', [Admin\UserController::class, 'toggleStatus'])->name('users.toggle-status');
    Route::resource('users', Admin\UserController::class)->except('show');
    Route::resource('roles', Admin\RoleController::class)->except('show');
});
