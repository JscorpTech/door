<?php

use Modules\Blog\app\Http\Controllers\Admin\BlogCategoryController;
use Modules\Blog\app\Http\Controllers\Admin\BlogController;
use Modules\Blog\app\Http\Controllers\Admin\BlogDownloadAppController;
use Modules\Blog\app\Http\Controllers\Admin\BlogPrioritySetupController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
|
*/
Route::group(['prefix' => 'admin', 'as' => 'admin.', 'middleware' => ['admin']], function () {
    Route::group(['prefix' => 'blog', 'as' => 'blog.'], function () {

        Route::controller(BlogCategoryController::class)->group(function () {
            Route::group(['prefix' => 'category', 'as' => 'category.'], function () {
                Route::post('add', 'add')->name('add');
                Route::post('category-info', 'getCategoryInfo')->name('info');
                Route::post('update', 'update')->name('update');
                Route::get('status', 'updateStatus')->name('status-update');
                Route::delete('delete', 'deleteCategory')->name('delete');
                Route::post('search', 'search')->name('search');
                Route::get('get-list', 'getList')->name('get-list');
            });
        });

        Route::controller(BlogController::class)->group(function () {
            Route::get('view', 'index')->name('view');
            Route::post('intro', 'updateIntro')->name('intro');
            Route::get('add', 'getAddView')->name('add');
            Route::post('add', 'addBlog')->name('store');
            Route::get('edit', 'getUpdateView')->name('edit');
            Route::post('update', 'update')->name('update');
            Route::post('status-update', 'updateStatus')->name('status-update');
            Route::post('blog-status-update'. '/{id}', 'updateBlogStatus')->name('blog-status-update');
            Route::get('draft-edit' . '/{id}', 'draftEdit')->name('draft-edit');
            Route::post('delete', 'delete')->name('delete');
            Route::post('section-view', 'sectionView')->name('section-view');
        });

        Route::controller(BlogDownloadAppController::class)->group(function () {
            Route::get('app-download-setup', 'appDownloadSetup')->name('app-download-setup');
            Route::post('app-download-setup', 'updateDownloadAppButton');
            Route::post('app-download-setup-status', 'updateStatus')->name('app-download-setup-status');
            Route::post('delete-image', 'deleteImage')->name('delete-image');
        });

        Route::group(['prefix' => 'priority-setup', 'as' => 'priority-setup.'], function () {
            Route::controller(BlogPrioritySetupController::class)->group(function () {
                Route::get('', 'index')->name('index');
                Route::post('', 'update');
            });
        });
    });
});
