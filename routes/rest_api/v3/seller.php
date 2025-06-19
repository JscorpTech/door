<?php

use App\Http\Controllers\RestAPI\v3\seller\auth\ForgotPasswordController;
use App\Http\Controllers\RestAPI\v3\seller\auth\LoginController as VendorLoginController;
use App\Http\Controllers\RestAPI\v3\seller\BrandController;
use App\Http\Controllers\RestAPI\v3\seller\ChatController;
use App\Http\Controllers\RestAPI\v3\seller\ClearanceSaleController;
use App\Http\Controllers\RestAPI\v3\seller\CouponController;
use App\Http\Controllers\RestAPI\v3\seller\DeliveryManCashCollectController;
use App\Http\Controllers\RestAPI\v3\seller\DeliveryManController;
use App\Http\Controllers\RestAPI\v3\seller\DeliverymanWithdrawController;
use App\Http\Controllers\RestAPI\v3\seller\EmergencyContactController;
use App\Http\Controllers\RestAPI\v3\seller\OrderController;
use App\Http\Controllers\RestAPI\v3\seller\POSController;
use App\Http\Controllers\RestAPI\v3\seller\ProductController;
use App\Http\Controllers\RestAPI\v3\seller\RefundController;
use App\Http\Controllers\RestAPI\v3\seller\SellerController;
use App\Http\Controllers\RestAPI\v3\seller\shippingController;
use App\Http\Controllers\RestAPI\v3\seller\ShippingMethodController;
use App\Http\Controllers\RestAPI\v3\seller\ShopController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Seller Mobile APP API Routes
|--------------------------------------------------------------------------
|*/

Route::group(['namespace' => 'RestAPI\v3\seller', 'prefix' => 'v3/seller', 'middleware' => ['api_lang']], function () {
    Route::group(['prefix' => 'auth', 'namespace' => 'auth'], function () {

        Route::controller(VendorLoginController::class)->group(function () {
            Route::post('login', 'login');
        });
        Route::controller(ForgotPasswordController::class)->group(function () {
            Route::post('forgot-password', 'reset_password_request');
            Route::post('verify-otp', 'otp_verification_submit');
            Route::put('reset-password', 'reset_password_submit');
        });
    });

    Route::group(['prefix' => 'registration', 'namespace' => 'auth'], function () {
        Route::post('/', 'RegisterController@store');
    });

    Route::group(['middleware' => ['seller_api_auth']], function () {
        Route::controller(SellerController::class)->group(function () {
            Route::put('language-change', 'language_change');
            Route::get('seller-info', 'getSellerInfo');
            Route::get('get-earning-statitics', 'getEarningStatics');
            Route::get('order-statistics', 'order_statistics');
            Route::get('account-delete', 'account_delete');
            Route::get('seller-delivery-man', 'seller_delivery_man');
            Route::get('shop-product-reviews', 'shop_product_reviews');
            Route::post('shop-product-reviews-reply', 'shopProductReviewReply');
            Route::get('shop-product-reviews-status', 'shop_product_reviews_status');
            Route::put('seller-update', 'seller_info_update');
            Route::get('monthly-earning', 'monthly_earning');
            Route::get('monthly-commission-given', 'monthly_commission_given');
            Route::put('cm-firebase-token', 'update_cm_firebase_token');
            Route::get('shop-info', 'shop_info');
            Route::get('transactions', 'transaction');
            Route::put('shop-update', 'shop_info_update');
            Route::get('withdraw-method-list', 'withdraw_method_list');
            Route::post('balance-withdraw', 'withdraw_request');
            Route::delete('close-withdraw-request', 'close_withdraw_request');
        });

        Route::controller(ShopController::class)->group(function () {
            Route::put('vacation-add', 'vacation_add');
            Route::put('temporary-close', 'temporary_close');
        });

        Route::group(['prefix' => 'brands'], function () {
            Route::controller(BrandController::class)->group(function () {
                Route::get('/', 'getBrands');
            });
        });

        Route::controller(ProductController::class)->group(function () {
            Route::get('top-delivery-man', 'top_delivery_man');
            Route::get('categories', 'get_categories');

            Route::group(['prefix' => 'products'], function () {
                Route::get('list', 'getProductList');
                Route::post('upload-images', 'upload_images');
                Route::post('upload-digital-product', 'upload_digital_product');
                Route::post('delete-digital-product', 'deleteDigitalProduct');
                Route::post('add', 'add_new');
                Route::get('details/{id}', 'details');
                Route::get('stock-out-list', 'stock_out_list');
                Route::put('status-update', 'status_update');
                Route::get('edit/{id}', 'edit');
                Route::put('update/{id}', 'update');
                Route::get('review-list/{id}', 'review_list');
                Route::put('quantity-update', 'updateProductQuantity');
                Route::delete('delete/{id}', 'delete');
                Route::get('barcode/generate', 'barcode_generate');
                Route::get('top-selling-product', 'top_selling_products');
                Route::get('most-popular-product', 'most_popular_products');
                Route::get('delete-image', 'deleteImage');
                Route::get('get-product-images/{id}', 'getProductImages');
                Route::get('stock-limit-status', 'getStockLimitStatus');
                Route::get('delete-preview-file', 'deletePreviewFile');
                Route::get('digital-author-list', 'getDigitalProductsAuthorList');
                Route::get('digital-publishing-house-list', 'getDigitalPublishingHouseList');
                Route::post('restock-request-list', 'getRestockRequestList');
                Route::get('restock-request-delete', 'deleteRestockRequest');
                Route::post('restock-request-stock-update', 'updateRestockQuantity');
                Route::get('restock-request-brands-list', 'getRestockRequestBrands');
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
                Route::post('address-update', 'address_update');
            });
        });

        Route::group(['prefix' => 'clearance-sale'], function () {
            Route::controller(ClearanceSaleController::class)->group(function () {
                Route::get('product-list', 'list');
                Route::post('product-add', 'addClearanceProduct');
                Route::post('product-delete', 'deleteClearanceProduct');
                Route::post('all-product-delete', 'deleteAllClearanceProduct');
                Route::post('product-status-update', 'updateClearanceProductStatus');
                Route::post('product-discount-update', 'updateClearanceProductDiscount');
                Route::post('config-status-update', 'updateClearanceConfigStatus');
                Route::get('config-data', 'getConfigData');
                Route::post('config-data-update', 'updateConfigData');
            });
        });

        Route::group(['prefix' => 'refund'], function () {
            Route::controller(RefundController::class)->group(function () {
                Route::get('list', 'list');
                Route::get('single-item', 'getSingleItem');
                Route::get('refund-details', 'refund_details');
                Route::post('refund-status-update', 'refund_status_update');
            });
        });

        Route::group(['prefix' => 'coupon'], function () {
            Route::controller(CouponController::class)->group(function () {
                Route::get('list', 'list');
                Route::post('store', 'store');
                Route::put('update/{id}', 'update');
                Route::put('status-update/{id}', 'status_update');
                Route::delete('delete/{id}', 'delete');
                Route::post('check-coupon', 'check_coupon');
                Route::get('customers', 'customers');
            });
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
            Route::controller(ChatController::class)->group(function () {
                Route::get('list/{type}', 'list');
                Route::get('get-message/{type}/{id}', 'get_message');
                Route::post('send/{type}', 'send_message');
                Route::post('seen/{type}', 'seenMessage');
                Route::get('search/{type}', 'search');
            });
        });

        Route::group(['prefix' => 'pos'], function () {
            Route::controller(POSController::class)->group(function () {
                Route::get('get-categories', 'get_categories');
                Route::get('customers', 'customers');
                Route::post('customer-store', 'customer_store');
                Route::get('products', 'get_product_by_barcode');
                Route::get('product-list', 'product_list');
                Route::post('place-order', 'place_order');
                Route::get('get-invoice', 'get_invoice');
            });
        });

        Route::group(['prefix' => 'delivery-man'], function () {
            Route::controller(DeliveryManController::class)->group(function () {
                Route::get('list', 'list');
                Route::post('store', 'store');
                Route::put('update/{id}', 'update');
                Route::get('details/{id}', 'details');
                Route::post('status-update', 'status');
                Route::get('delete/{id}', 'delete');
                Route::get('reviews/{id}', 'reviews');
                Route::get('order-list/{id}', 'order_list');
                Route::get('order-status-history/{id}', 'order_status_history');
                Route::get('earning/{id}', 'earning');
            });

            Route::controller(DeliveryManCashCollectController::class)->group(function () {
                Route::post('cash-receive', 'cash_receive');
                Route::get('collect-cash-list/{id}', 'list');
            });

            Route::group(['prefix' => 'withdraw'], function () {
                Route::controller(DeliverymanWithdrawController::class)->group(function () {
                    Route::get('list', 'list');
                    Route::get('details/{id}', 'details');
                    Route::put('status-update', 'status_update');
                });
            });

            Route::group(['prefix' => 'emergency-contact'], function () {
                Route::controller(EmergencyContactController::class)->group(function () {
                    Route::get('list', 'list');
                    Route::post('store', 'store');
                    Route::put('update', 'update');
                    Route::put('status-update', 'status_update');
                    Route::delete('delete', 'destroy');
                });
            });
        });

        Route::group(['prefix' => 'notification'], function () {
            Route::controller(ShopController::class)->group(function () {
                Route::get('/', 'notification_index');
                Route::get('/view', 'seller_notification_view');
            });
        });

    });

    Route::controller(ProductController::class)->group(function () {
        Route::group(['prefix' => 'products'], function () {
            Route::get('{seller_id}/all-products', 'getVendorAllProducts');
        });
    });

    Route::post('ls-lib-update', 'LsLibController@lib_update');
});

