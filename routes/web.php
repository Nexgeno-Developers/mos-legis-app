<?php

use App\Http\Controllers\Account;
use App\Http\Controllers\Auth\AuthorLoginController;
use App\Http\Controllers\Auth\OrcidController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\SocialLoginController;
use App\Http\Controllers\Payments\RazorpayWebhookController;
use App\Http\Controllers\Site;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public website (SOW section C)
|--------------------------------------------------------------------------
*/

// CMS pages (About, Submit, Contact, policies…) are served at /{slug} by the fallback route at the end of this file.
Route::get('/', Site\HomeController::class)->name('home');
Route::get('policies/{slug}', [Site\PageController::class, 'legacyPolicy']);
Route::get('verify/{slug}', Site\CertificateVerificationController::class)->name('certificates.verify');

Route::get('archive', [Site\ArchiveController::class, 'index'])->name('archive.index');
Route::get('archive/download', [Site\ArchiveController::class, 'zip'])->middleware('throttle:10,1')->name('archive.zip');
Route::get('archive/{submission}', [Site\ArchiveController::class, 'show'])->name('archive.show');
Route::get('archive/{submission}/download', [Site\ArchiveController::class, 'download'])->name('archive.download');

Route::get('blogs', [Site\BlogController::class, 'index'])->name('blogs.index');
Route::get('blogs/{slug}', [Site\BlogController::class, 'show'])->name('blogs.show');
Route::post('blogs/{slug}/comments', [Site\BlogController::class, 'comment'])->middleware(['auth', 'throttle:10,1'])->name('blogs.comments.store');
Route::delete('blogs/{slug}/comments/{comment}', [Site\BlogController::class, 'destroyComment'])->middleware('auth')->name('blogs.comments.destroy');

Route::post('contact', [Site\EnquiryController::class, 'storeContact'])->middleware('throttle:5,1')->name('contact.store');
Route::post('careers', [Site\EnquiryController::class, 'storeCareer'])->middleware('throttle:5,1')->name('careers.store');

Route::post('plagiarism-checker', [Site\PlagiarismCheckerController::class, 'store'])->middleware(['author', 'throttle:10,1'])->name('plagiarism-checker.store');

/*
|--------------------------------------------------------------------------
| Author authentication (SOW B.01)
|--------------------------------------------------------------------------
*/

Route::middleware('guest')->group(function () {
    Route::get('login', [AuthorLoginController::class, 'create'])->name('login');
    Route::post('login', [AuthorLoginController::class, 'store'])->middleware('throttle:10,1')->name('login.store');
    Route::get('register', [RegisterController::class, 'create'])->name('register');
    Route::post('register', [RegisterController::class, 'store'])->middleware('throttle:6,1')->name('register.store');
    Route::get('register/verify', [RegisterController::class, 'verifyForm'])->name('register.verify');
    Route::post('register/verify', [RegisterController::class, 'verify'])->middleware('throttle:10,1')->name('register.verify.store');
    Route::post('register/resend', [RegisterController::class, 'resend'])->name('register.resend');
    Route::post('register/reset', [RegisterController::class, 'reset'])->name('register.reset');
    // Google sign-in / sign-up (ORCID is not a login method; see the ORCID routes below).
    Route::get('auth/{provider}/redirect', [SocialLoginController::class, 'redirect'])->whereIn('provider', ['google'])->name('social.redirect');
    Route::get('auth/{provider}/callback', [SocialLoginController::class, 'callback'])->whereIn('provider', ['google'])->name('social.callback');

    // Shared password reset link (admin panel and author portal).
    Route::get('forgot-password', [PasswordResetController::class, 'request'])->name('password.request');
    Route::post('forgot-password', [PasswordResetController::class, 'email'])->middleware('throttle:5,1')->name('password.email');
    Route::get('reset-password/{token}', [PasswordResetController::class, 'edit'])->name('password.reset');
    Route::post('reset-password', [PasswordResetController::class, 'update'])->name('password.update');
});

Route::post('logout', [AuthorLoginController::class, 'destroy'])->middleware('auth')->name('logout');

// "Connect your ORCID iD" — during registration (guest) and from the author profile.
Route::get('auth/orcid/redirect', [OrcidController::class, 'redirect'])->middleware('throttle:20,1')->name('orcid.redirect');
Route::get('auth/orcid/callback', [OrcidController::class, 'callback'])->name('orcid.callback');
Route::post('auth/orcid/forget', [OrcidController::class, 'forget'])->middleware('guest')->name('orcid.forget');

/*
|--------------------------------------------------------------------------
| Author Portal (SOW B.02–B.06) and payments
|--------------------------------------------------------------------------
*/

Route::middleware('author')->prefix('account')->name('account.')->group(function () {
    Route::get('/', Account\DashboardController::class)->name('dashboard');

    Route::get('submissions', [Account\SubmissionController::class, 'index'])->name('submissions.index');
    Route::post('submissions', [Account\SubmissionController::class, 'store'])->middleware('throttle:10,1')->name('submissions.store');
    Route::get('submissions/{submission}', [Account\SubmissionController::class, 'show'])->name('submissions.show');
    Route::post('submissions/{submission}/revision', [Account\SubmissionController::class, 'resubmit'])->name('submissions.resubmit');
    Route::get('submissions/{submission}/download', [Account\SubmissionController::class, 'download'])->name('submissions.download');
    Route::get('submissions/{submission}/certificate', [Account\SubmissionController::class, 'certificate'])->name('submissions.certificate');

    Route::get('checkout/submissions/{submission}/{purpose}', [Account\CheckoutController::class, 'submission'])->name('checkout.submission');
    Route::get('checkout/plagiarism-checks/{check}', [Account\CheckoutController::class, 'plagiarismCheck'])->name('checkout.plagiarism');
    Route::post('checkout', [Account\CheckoutController::class, 'store'])->name('checkout.store');
    Route::get('payments/{payment}/pay', [Account\CheckoutController::class, 'pay'])->name('payments.pay');
    Route::post('payments/{payment}/simulate', [Account\CheckoutController::class, 'simulate'])->name('payments.simulate');
    Route::get('payments/{payment}/status', [Account\CheckoutController::class, 'status'])->name('payments.status');
    Route::get('payments/{payment}/status/check', [Account\CheckoutController::class, 'check'])->middleware('throttle:60,1')->name('payments.check');
    Route::get('payments', [Account\PaymentController::class, 'index'])->name('payments.index');
    Route::get('payments/{payment}/invoice', [Account\PaymentController::class, 'invoice'])->name('payments.invoice');

    Route::get('plagiarism-checks', [Account\PlagiarismCheckController::class, 'index'])->name('plagiarism-checks.index');
    Route::get('plagiarism-checks/{check}', [Account\PlagiarismCheckController::class, 'show'])->name('plagiarism-checks.show');
    Route::get('plagiarism-checks/{check}/report', [Account\PlagiarismCheckController::class, 'report'])->name('plagiarism-checks.report');

    Route::resource('blogs', Account\BlogController::class)->except('show');
    Route::resource('jobs', Account\JobPostingController::class)->except('show')->parameters(['jobs' => 'job']);

    Route::get('profile', [Account\ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('profile', [Account\ProfileController::class, 'update'])->name('profile.update');
    Route::put('profile/address', [Account\ProfileController::class, 'updateAddress'])->name('profile.address');
});

Route::post('payments/razorpay/callback', [Account\CheckoutController::class, 'callback'])->middleware('author')->name('payments.razorpay.callback');
Route::post('payments/razorpay/webhook', RazorpayWebhookController::class)->name('payments.razorpay.webhook');

/*
|--------------------------------------------------------------------------
| CMS pages at /{slug} (Admin → Pages); always matched last, so fixed routes win
|--------------------------------------------------------------------------
*/

Route::fallback([Site\PageController::class, 'show'])->name('pages.show');
