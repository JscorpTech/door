<?php

use Illuminate\Support\Facades\Route;
use Modules\Blog\app\Http\Controllers\Web\AppBlogController;
use Modules\Blog\app\Http\Controllers\Web\FrontendBlogController;
use Modules\Blog\app\Http\Middleware\BlogActiveStatusMiddleware;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
*/

Route::middleware(BlogActiveStatusMiddleware::class)->group(function () {
    Route::controller(FrontendBlogController::class)->group(function () {
        Route::get('/blog', 'index')->name('frontend.blog.index');
        Route::get('/popular-blog', 'getPopularBlogs')->name('frontend.blog.popular-blog');
        Route::get('/blog/{slug}', 'getDetailsView')->name('frontend.blog.details');
    });

    Route::prefix('app')->group(function () {
        Route::controller(FrontendBlogController::class)->group(function () {
            Route::get('/blog', 'index')->name('app.blog.index');
            Route::get('/popular-blog', 'getPopularBlogs')->name('app.blog.popular-blog');
            Route::get('/blog/{slug}', 'getDetailsView')->name('app.blog.details');
        });
    });
});
