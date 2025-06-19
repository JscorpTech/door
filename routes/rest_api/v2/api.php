<?php

use App\Http\Controllers\RestAPI\v2\delivery_man\auth\LoginController;
use App\Http\Controllers\RestAPI\v2\delivery_man\ChatController as DeliveryChatController;
use App\Http\Controllers\RestAPI\v2\Seller\ChatController as VendorChatController;
use App\Http\Controllers\RestAPI\v2\delivery_man\DeliveryManController;
use App\Http\Controllers\RestAPI\v2\delivery_man\WithdrawController;
use App\Http\Controllers\RestAPI\v2\seller\BrandController;
use App\Http\Controllers\RestAPI\v2\seller\OrderController;
use App\Http\Controllers\RestAPI\v2\seller\ProductController;
use App\Http\Controllers\RestAPI\v2\seller\SellerController;
use App\Http\Controllers\RestAPI\v2\seller\shippingController;
use App\Http\Controllers\RestAPI\v2\seller\ShippingMethodController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Old Seller Mobile APP API Routes
|--------------------------------------------------------------------------
*/

Route::group(['namespace' => 'RestAPI\v2', 'prefix' => 'v2', 'middleware' => ['api_lang']], function () {
    Route::group(['prefix' => 'seller', 'namespace' => 'seller'], function () {

        Route::controller(SellerController::class)->group(function () {
            Route::get('seller-info', 'seller_info');
            Route::get('account-delete', 'account_delete');
            Route::get('seller-delivery-man', 'seller_delivery_man');
            Route::get('shop-product-reviews', 'shop_product_reviews');
            Route::get('shop-product-reviews-status', 'shop_product_reviews_status');
            Route::put('seller-update', 'seller_info_update');
            Route::get('monthly-earning', 'monthly_earning');
            Route::get('monthly-commission-given', 'monthly_commission_given');
            Route::put('cm-firebase-token', 'update_cm_firebase_token');

            Route::get('shop-info', 'shop_info');
            Route::get('transactions', 'transaction');
            Route::put('shop-update', 'shop_info_update');

            Route::post('balance-withdraw', 'withdraw_request');
            Route::delete('close-withdraw-request', 'close_withdraw_request');
        });

        Route::group(['prefix' => 'brands'], function () {
            Route::controller(BrandController::class)->group(function () {
                Route::get('/', 'getBrands');
            });
        });

        Route::group(['prefix' => 'products'], function () {
            Route::controller(ProductController::class)->group(function () {
                Route::post('upload-images', 'upload_images');
                Route::post('upload-digital-product', 'upload_digital_product');
                Route::post('add', 'add_new');
                Route::get('list', 'list');
                Route::get('stock-out-list', 'stock_out_list');
                Route::get('status-update', 'status_update');
                Route::get('edit/{id}', 'edit');
                Route::put('update/{id}', 'update');
                Route::delete('delete/{id}', 'delete');
                Route::get('barcode/generate', 'barcode_generate');
            });
        });

        Route::group(['prefix' => 'orders'], function () {
            Route::controller(OrderController::class)->group(function () {
                Route::get('list', 'list');
                Route::get('/{id}', 'details');
                Route::put('order-detail-status/{id}', 'order_detail_status');
                Route::put('assign-delivery-man', 'assign_delivery_man');
                Route::put('order-wise-product-upload', 'digital_file_upload_after_sell');
                Route::put('delivery-charge-date-update', 'amount_date_update');
                Route::post('assign-third-party-delivery', 'assign_third_party_delivery');
                Route::post('update-payment-status', 'update_payment_status');
            });
        });

        Route::group(['prefix' => 'refund'], function () {
            Route::get('list', 'RefundController@list');
            Route::get('refund-details', 'RefundController@refund_details');
            Route::post('refund-status-update', 'RefundController@refund_status_update');
        });

        Route::group(['prefix' => 'shipping'], function () {
            Route::controller(shippingController::class)->group(function () {
                Route::get('get-shipping-method', 'get_shipping_type');
                Route::get('selected-shipping-method', 'selected_shipping_type');
                Route::get('all-category-cost', 'all_category_cost');
                Route::post('set-category-cost', 'set_category_cost');
            });
        });

        Route::group(['prefix' => 'shipping-method'], function () {
            Route::controller(ShippingMethodController::class)->group(function () {
                Route::get('list', 'list');
                Route::post('add', 'store');
                Route::get('edit/{id}', 'edit');
                Route::put('status', 'status_update');
                Route::put('update/{id}', 'update');
                Route::delete('delete/{id}', 'delete');
            });
        });

        Route::group(['prefix' => 'messages'], function () {
            Route::controller(VendorChatController::class)->group(function () {
                Route::get('list/{type}', 'list');
                Route::get('get-message/{type}/{id}', 'get_message');
                Route::post('send/{type}', 'send_message');
                Route::get('search/{type}', 'search');
            });
        });

        Route::group(['prefix' => 'auth', 'namespace' => 'auth'], function () {
            Route::post('login', 'LoginController@login');

            Route::post('forgot-password', 'ForgotPasswordController@reset_password_request');
            Route::post('verify-otp', 'ForgotPasswordController@otp_verification_submit');
            Route::put('reset-password', 'ForgotPasswordController@reset_password_submit');
        });

        Route::group(['prefix' => 'registration', 'namespace' => 'auth'], function () {
            Route::post('/', 'RegisterController@store');
        });
    });

    Route::post('ls-lib-update', 'LsLibController@lib_update');

    Route::group(['prefix' => 'delivery-man', 'namespace' => 'delivery_man'], function () {

        Route::group(['prefix' => 'auth', 'namespace' => 'auth'], function () {
            Route::controller(LoginController::class)->group(function () {
                Route::post('login', 'login');
                Route::post('forgot-password', 'reset_password_request');
                Route::post('verify-otp', 'otp_verification_submit');
                Route::post('reset-password', 'reset_password_submit');
            });
        });

        Route::group(['middleware' => ['delivery_man_auth']], function () {
            Route::controller(DeliveryManController::class)->group(function () {
                Route::put('language-change', 'language_change');
                Route::put('is-online', 'is_online');
                Route::get('info', 'info');
                Route::post('distance-api', 'distance_api');
                Route::get('current-orders', 'get_current_orders');
                Route::get('all-orders', 'get_all_orders');
                Route::post('record-location-data', 'record_location_data');
                Route::get('order-delivery-history', 'get_order_history');
                Route::put('update-order-status', 'update_order_status');
                Route::put('update-expected-delivery', 'update_expected_delivery');
                Route::put('update-payment-status', 'order_payment_status_update');
                Route::put('order-update-is-pause', 'order_update_is_pause');
                Route::get('order-item', 'getOrderItem');
                Route::get('order-details', 'get_order_details');
                Route::get('last-location', 'get_last_location');
                Route::put('update-fcm-token', 'update_fcm_token');

                Route::get('delivery-wise-earned', 'delivery_wise_earned');
                Route::get('order-list-by-date', 'order_list_date_filter');
                Route::get('search', 'search');
                Route::get('profile-dashboard-counts', 'profile_dashboard_counts');
                Route::post('change-status', 'change_status');
                Route::put('update-info', 'update_info');
                Route::put('bank-info', 'bank_info');
                Route::get('review-list', 'review_list');
                Route::put('save-review', 'is_saved');
                Route::get('collected_cash_history', 'collected_cash_history');
                Route::get('emergency-contact-list', 'emergency_contact_list');
                Route::get('notifications', 'get_all_notification');
                Route::post('verify-order-delivery-otp', 'verify_order_delivery_otp');
                Route::post('resend-verification-code', 'resend_verification_code');
                Route::post('order-delivery-verification', 'order_delivery_verification');
            });

            Route::controller(WithdrawController::class)->group(function () {
                Route::post('withdraw-request', 'sendWithdrawRequest');
                Route::get('withdraw-list-by-approved', 'getWithdrawListByApproved');
            });

            Route::group(['prefix' => 'messages'], function () {
                Route::controller(DeliveryChatController::class)->group(function () {
                    Route::get('list/{type}', 'list');
                    Route::get('get-message/{type}/{id}', 'get_message');
                    Route::post('send-message/{type}', 'send_message');
                    Route::get('search/{type}', 'search');
                });
            });
        });

    });
});

