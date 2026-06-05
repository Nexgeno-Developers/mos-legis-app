<?php

use Illuminate\Support\Facades\Route;

//Backend
use App\Http\Controllers\CommandController;
use App\Http\Controllers\Backend\AuthController;
use App\Http\Controllers\Backend\CompanyController;
use App\Http\Controllers\Backend\DashboardController;
use App\Http\Controllers\Backend\UploadController;
use App\Http\Controllers\Backend\PageController;
use App\Http\Controllers\Backend\MenuController;
use App\Http\Controllers\Backend\VisitorController;
use App\Http\Controllers\Backend\UserController;
use App\Http\Controllers\Backend\RoleController;
use App\Http\Controllers\Backend\PropertyController;
use App\Http\Controllers\Backend\CabinController;
use App\Http\Controllers\Backend\SeatController;
use App\Http\Controllers\Backend\CustomerController;
use App\Http\Controllers\Backend\FormController;


//Command Routes
Route::middleware(['auth.backend'])->prefix('command')->group(function () {
    Route::get('cache-clear', [CommandController::class, 'cacheClear']);
    Route::get('config-clear', [CommandController::class, 'configClear']);
    Route::get('config-cache', [CommandController::class, 'configCache']);
    Route::get('route-cache', [CommandController::class, 'routeCache']);
    Route::get('route-clear', [CommandController::class, 'routeClear']);
    Route::get('view-clear', [CommandController::class, 'viewClear']);
    Route::get('view-cache', [CommandController::class, 'viewCache']);
    Route::get('storage-link', [CommandController::class, 'storageLink']);
    Route::get('key-generate', [CommandController::class, 'keyGenerate']);
    Route::get('optimize-clear', [CommandController::class, 'optimizeClear']);
    Route::get('queue-work', [CommandController::class, 'queueWork']);
    Route::get('queue-retry/{id?}', [CommandController::class, 'queueRetry']); // optional id
    Route::get('queue-failed', [CommandController::class, 'queueFailed']);
    Route::get('queue-forget/{id}', [CommandController::class, 'queueForget']);
    Route::get('queue-flush', [CommandController::class, 'queueFlush']);    
});

// Global password reset routes to support Laravel's default password.* route names
Route::middleware(['auth.guest'])->group(function () {
    Route::get('password/forgot', [AuthController::class, 'showForgotPasswordForm'])->name('password.request');
    Route::post('password/email', [AuthController::class, 'sendResetLinkEmail'])->middleware(['recaptcha','throttle:10,60'])->name('password.email');
    Route::get('password/reset/{token}', [AuthController::class, 'showResetForm'])->name('password.reset');
    Route::post('password/reset', [AuthController::class, 'resetPassword'])->name('password.update');
});

// Group routes under the 'backend' prefix
Route::prefix('backend')->group(function () {

    // Public login/logout & password reset routes
    Route::get('/', [AuthController::class, 'showLoginForm'])->middleware(['auth.guest'])->name('backend.login');
    Route::get('/login', [AuthController::class, 'showLoginForm'])->middleware(['auth.guest'])->name('backend.login');
    Route::post('/login', [AuthController::class, 'login'])->middleware(['recaptcha','throttle:10,60'])->name('backend.login.submit');
    Route::post('/logout', [AuthController::class, 'logout'])->name('backend.logout');

    Route::get('/password/forgot', [AuthController::class, 'showForgotPasswordForm'])->middleware(['auth.guest'])->name('backend.password.request');
    Route::post('/password/email', [AuthController::class, 'sendResetLinkEmail'])->middleware(['recaptcha','throttle:10,60'])->name('backend.password.email');
    Route::get('/password/reset/{token}', [AuthController::class, 'showResetForm'])->middleware(['auth.guest'])->name('backend.password.reset');
    Route::post('/password/reset', [AuthController::class, 'resetPassword'])->name('backend.password.update');

    // Authenticated admin routes
    Route::middleware(['auth.backend'])->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('backend.dashboard');
    });

    // Uploads routes 
    Route::middleware(['auth.backend'])->resource('/uploaded-files', UploadController::class);
    Route::middleware(['auth.backend'])->controller(UploadController::class)->group(function () {
        Route::any('/uploaded-files/file-info', 'file_info')->name('uploaded-files.info');
        Route::get('/uploaded-files/destroy/{id}', 'destroy')->name('uploaded-files.destroy');
        Route::post('/bulk-uploaded-files-delete', 'bulk_uploaded_files_delete')->name('bulk-uploaded-files-delete');
        Route::get('/all-file', 'all_file');
        Route::post('/aiz-uploader', 'show_uploader');
        Route::post('/aiz-uploader/upload', 'upload');
        Route::get('/aiz-uploader/get-uploaded-files', 'get_uploaded_files');
        Route::post('/aiz-uploader/get_file_by_ids', 'get_preview_files');
        Route::get('/aiz-uploader/download/{id}', 'attachment_download')->name('download_attachment');   
        Route::get('/aiz-uploader/generate-all-thumbnail', 'generate_all_thumbnails');     
    }); 
    
    //Companies Routes
    Route::middleware('auth.backend')->group(function () {
        Route::resource('companies', CompanyController::class);
    });  
    
    //Pages Routes
    Route::middleware('auth.backend')->group(function () {
        Route::get('pages/{page}/layout-fields', [PageController::class, 'layoutFields'])->name('pages.layout-fields');
        Route::get('pages/{page}/clone', [PageController::class, 'clone'])->name('pages.clone');
        Route::resource('pages', PageController::class);
    });     
    
    //Forms Routes
    Route::middleware('auth.backend')->group(function () {
        Route::get('forms-by/{form_name}', [BackendFormController::class, 'index'])->name('forms.by');
    });
    
    //Visitors Routes
    Route::middleware('auth.backend')->group(function () {
        Route::resource('visitors', VisitorController::class);
        Route::post('visitors/bulk-delete', [VisitorController::class, 'bulkDelete'])->name('visitors.bulk-delete');
    });
    
    //Menus Routes
    Route::middleware('auth.backend')->group(function () {
        Route::get('/menus', [MenuController::class, 'index'])->name('backend.menus');
        Route::post('/menus/group/save', [MenuController::class, 'saveGroup'])->name('backend.menus.group.save');
        Route::post('/menus/group/delete', [MenuController::class, 'deleteGroup'])->name('backend.menus.group.delete');
        Route::post('/menus/item/save', [MenuController::class, 'saveItem'])->name('backend.menus.item.save');
        Route::post('/menus/item/delete', [MenuController::class, 'deleteItem'])->name('backend.menus.item.delete');
        Route::post('/menus/save-order', [MenuController::class, 'saveOrder'])->name('backend.menus.save-order');
        Route::get('/menus/get-items', [MenuController::class, 'getMenuItems'])->name('backend.menus.get-items');
    });
    
    //User and Role Management Routes
    Route::middleware('auth.backend')->group(function () {
        Route::resource('users', UserController::class);
        Route::resource('customers', CustomerController::class);
        Route::get('customers/login-as/{id}', [CustomerController::class, 'loginAsCustomer'])->name('customers.login-as');
    });

    Route::middleware('auth.backend')->group(function () {
        Route::resource('roles', RoleController::class);        
    });

    Route::middleware('auth.backend')->group(function () {
        Route::resource('properties', PropertyController::class);
    });

    Route::middleware('auth.backend')->group(function () {
        Route::resource('cabins', CabinController::class);
    });

    Route::middleware('auth.backend')->group(function () {
        Route::resource('seats', SeatController::class);
    });
});
