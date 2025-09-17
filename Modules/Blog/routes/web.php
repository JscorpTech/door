<?php

use Illuminate\Support\Facades\Route;
use Modules\Blog\App\Http\Controllers\Web\AppBlogController;
use Modules\Blog\App\Http\Controllers\Web\FrontendBlogController;
use Modules\Blog\App\Http\Middleware\BlogActiveStatusMiddleware;
use App\Http\Controllers\Admin\Order\OrderController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
*/

Route::middleware(BlogActiveStatusMiddleware::class)->group(function () {

    // Frontend blog routes
    Route::controller(FrontendBlogController::class)->group(function () {
        Route::get('/blog', 'index')->name('frontend.blog.index');
        Route::get('/popular-blog', 'getPopularBlogs')->name('frontend.blog.popular-blog');
        Route::get('/blog/{slug}', 'getDetailsView')->name('frontend.blog.details');
    });

    // App-prefixed blog routes
    Route::prefix('app')->group(function () {
        Route::controller(FrontendBlogController::class)->group(function () {
            Route::get('/blog', 'index')->name('app.blog.index');
            Route::get('/popular-blog', 'getPopularBlogs')->name('app.blog.popular-blog');
            Route::get('/blog/{slug}', 'getDetailsView')->name('app.blog.details');
        });
    });
});

// Admin order routes
Route::prefix('admin')->group(function () {
    Route::controller(OrderController::class)->group(function () {
        Route::get('/orders', 'index')->name('admin.orders.index');
        Route::get('/orders/{id}', 'show')->name('admin.orders.show');
        Route::post('/orders', 'store')->name('admin.orders.store');
        Route::put('/orders/{id}', 'update')->name('admin.orders.update');
        Route::delete('/orders/{id}', 'destroy')->name('admin.orders.destroy');
    });
});

    
Route::get('/order/product/detail/{id}', [OrderController::class, 'productDetail'])
    ->name('admin.order.product.detail');