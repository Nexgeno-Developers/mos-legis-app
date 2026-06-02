<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\MenuController;
use App\Http\Controllers\Api\V1\CompanyController;
use App\Http\Controllers\Api\V1\FormSubmissionController;
use App\Http\Controllers\Api\V1\PageController; 
use App\Http\Controllers\Api\V1\PostController;
use App\Http\Controllers\Api\V1\CategoryController;
use App\Http\Controllers\Api\V1\TagController;
use App\Http\Controllers\Api\V1\AuthorController;
use App\Http\Controllers\Api\V1\SitemapController;
use App\Http\Controllers\Api\V1\SeoSettingController;

Route::prefix('v1')->middleware(['throttle:60,1'])->group(function () {
    // Menu group (and its active item tree)
    Route::get('menus/groups/{id}', [MenuController::class, 'showById']);
    Route::get('menus/groups/by-name/{name}', [MenuController::class, 'showByName']);

    // Company info by company id
    Route::get('companies/{id}', [CompanyController::class, 'showById'])->whereNumber('id');

    // Public form submission API (expects multipart/form-data when uploading files).
    Route::post('forms/submit', [FormSubmissionController::class, 'submit'])
        ->middleware(['protect.forms', 'throttle:10,1']);

    // Page by id OR slug/path slug
    Route::get('page/{id}', [PageController::class, 'showById'])->whereNumber('id');
    Route::get('page/{slug}', [PageController::class, 'showBySlug'])->where('slug', '.*'); 

    // Posts
    Route::get('posts', [PostController::class, 'index']);
    Route::get('posts/{slug}', [PostController::class, 'showBySlug'])->where('slug', '.*');

    // Categories
    Route::get('categories', [CategoryController::class, 'index']);
    // Route::get('categories/{slug}/posts', [CategoryController::class, 'postsBySlug'])->where('slug', '.*');
    Route::get('categories/{slug_or_id}', [CategoryController::class, 'show'])
        ->where('slug_or_id', '^(?!.*\\/posts$).*$');

    // Tags
    // Route::get('tags', [TagController::class, 'index']);
    // Route::get('tags/{slug}/posts', [TagController::class, 'postsBySlug'])->where('slug', '.*');

    // Authors
    // Route::get('authors', [AuthorController::class, 'index']);
    // Route::get('authors/{id}', [AuthorController::class, 'showById'])->whereNumber('id');
    // Route::get('authors/{id}/posts', [AuthorController::class, 'postsById'])->whereNumber('id');

    // Sitemap slugs for frontend sitemap generation
    Route::get('sitemap', [SitemapController::class, 'index']);

    // robots.txt content (for frontend file generation)
    Route::get('robots-txt', [SeoSettingController::class, 'robotsTxt']);
});
