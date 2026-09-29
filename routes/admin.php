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

    // Manuscript pipeline
    Route::controller(Admin\SubmissionController::class)->prefix('submissions/{submission}')->name('submissions.')->group(function () {
        Route::get('download', 'download')->name('download');
        Route::get('revisions/{revision}/download/{version}', 'download')->whereIn('version', ['reviewed', 'resubmitted'])->name('revision-download');
        Route::get('certificate', 'certificate')->name('certificate');
    });
    Route::controller(Admin\SubmissionWorkflowController::class)->prefix('submissions/{submission}')->name('submissions.')->group(function () {
        Route::post('assign', 'assign')->name('assign');
        Route::post('decision', 'decide')->name('decide');
        Route::patch('stage', 'changeStage')->name('change-stage');
        Route::post('recheck', 'recheck')->name('recheck');
        Route::post('awards', 'awardBestPaper')->name('awards.store');
        Route::delete('awards/{award}', 'removeAward')->name('awards.destroy');
    });
    Route::resource('submissions', Admin\SubmissionController::class);
    Route::get('payments/{payment}/invoice', [Admin\PaymentController::class, 'invoice'])->name('payments.invoice');
    Route::resource('payments', Admin\PaymentController::class)->only(['index', 'show']);
    Route::controller(Admin\PlagiarismCheckController::class)->prefix('plagiarism-checks/{plagiarism_check}')->name('plagiarism-checks.')->group(function () {
        Route::post('recheck', 'recheck')->name('recheck');
        Route::get('report', 'report')->name('report');
        Route::get('file', 'file')->name('file');
    });
    Route::resource('plagiarism-checks', Admin\PlagiarismCheckController::class)->only(['index', 'show']);

    // Manuscript catalogue
    Route::patch('author-categories/{author_category}/status', [Admin\AuthorCategoryController::class, 'toggleStatus'])->name('author-categories.toggle-status');
    Route::resource('author-categories', Admin\AuthorCategoryController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::patch('content-categories/{content_category}/status', [Admin\ContentCategoryController::class, 'toggleStatus'])->name('content-categories.toggle-status');
    Route::resource('content-categories', Admin\ContentCategoryController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::resource('themes', Admin\ContentCategoryThemeController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::get('fees', [Admin\ManuscriptFeeController::class, 'index'])->name('fees.index');
    Route::put('fees', [Admin\ManuscriptFeeController::class, 'update'])->name('fees.update');

    // Content
    Route::patch('blogs/{blog}/status', [Admin\BlogController::class, 'toggleStatus'])->name('blogs.toggle-status');
    Route::post('blogs/{blog}/duplicate', [Admin\BlogController::class, 'duplicate'])->name('blogs.duplicate');
    Route::resource('blogs', Admin\BlogController::class)->except('show');
    Route::patch('blog-categories/{blog_category}/status', [Admin\BlogCategoryController::class, 'toggleStatus'])->name('blog-categories.toggle-status');
    Route::post('blog-categories/{blog_category}/duplicate', [Admin\BlogCategoryController::class, 'duplicate'])->name('blog-categories.duplicate');
    Route::resource('blog-categories', Admin\BlogCategoryController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::patch('blog-tags/{blog_tag}/status', [Admin\BlogTagController::class, 'toggleStatus'])->name('blog-tags.toggle-status');
    Route::post('blog-tags/{blog_tag}/duplicate', [Admin\BlogTagController::class, 'duplicate'])->name('blog-tags.duplicate');
    Route::resource('blog-tags', Admin\BlogTagController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::patch('blog-comments/{blog_comment}/moderate', [Admin\BlogCommentController::class, 'moderate'])->name('blog-comments.moderate');
    Route::post('blog-comments/{blog_comment}/reply', [Admin\BlogCommentController::class, 'reply'])->name('blog-comments.reply');
    Route::resource('blog-comments', Admin\BlogCommentController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::patch('pages/{page}/status', [Admin\PageController::class, 'toggleStatus'])->name('pages.toggle-status');
    Route::post('pages/{page}/duplicate', [Admin\PageController::class, 'duplicate'])->name('pages.duplicate');
    Route::resource('pages', Admin\PageController::class)->except('show');

    Route::patch('job-postings/{job_posting}/status', [Admin\JobPostingController::class, 'toggleStatus'])->name('job-postings.toggle-status');
    Route::resource('job-postings', Admin\JobPostingController::class);

    // Administration
    Route::patch('users/{user}/status', [Admin\UserController::class, 'toggleStatus'])->name('users.toggle-status');
    Route::resource('users', Admin\UserController::class)->except('show');
    Route::resource('roles', Admin\RoleController::class)->except('show');
    Route::get('settings', [Admin\SettingController::class, 'edit'])->name('settings.edit');
    Route::put('settings', [Admin\SettingController::class, 'update'])->name('settings.update');

    // Operations
    Route::get('activity-logs', [Admin\ActivityLogController::class, 'index'])->name('activity-logs.index');
    Route::get('enquiries/{enquiry}/resume', [Admin\EnquiryController::class, 'resume'])->name('enquiries.resume');
    Route::resource('enquiries', Admin\EnquiryController::class)->only(['index', 'show', 'destroy']);
    Route::delete('activity-logs', [Admin\ActivityLogController::class, 'purge'])->name('activity-logs.purge');
});
