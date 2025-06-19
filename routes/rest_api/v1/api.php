<?php

use App\Http\Controllers\RestAPI\v1\auth\CustomerAPIAuthController;
use App\Http\Controllers\RestAPI\v1\auth\EmailVerificationController;
use App\Http\Controllers\RestAPI\v1\auth\ForgotPasswordController;
use App\Http\Controllers\RestAPI\v1\auth\PassportAuthController;
use App\Http\Controllers\RestAPI\v1\auth\PhoneVerificationController;
use App\Http\Controllers\RestAPI\v1\auth\SocialAuthController;
use App\Http\Controllers\RestAPI\v1\BannerController;
use App\Http\Controllers\RestAPI\v1\BrandController;
use App\Http\Controllers\RestAPI\v1\CartController;
use App\Http\Controllers\RestAPI\v1\CategoryController;
use App\Http\Controllers\RestAPI\v1\ChatController;
use App\Http\Controllers\RestAPI\v1\ConfigController;
use App\Http\Controllers\RestAPI\v1\CouponController;
use App\Http\Controllers\RestAPI\v1\CustomerController;
use App\Http\Controllers\RestAPI\v1\CustomerRestockRequestController;
use App\Http\Controllers\RestAPI\v1\DealController;
use App\Http\Controllers\RestAPI\v1\DealOfTheDayController;
use App\Http\Controllers\RestAPI\v1\FlashDealController;
use App\Http\Controllers\RestAPI\v1\OrderController;
use App\Http\Controllers\RestAPI\v1\ProductController;
use App\Http\Controllers\RestAPI\v1\SellerController;
use App\Http\Controllers\RestAPI\v1\ShippingMethodController;
use App\Http\Controllers\RestAPI\v1\UserLoyaltyController;
use App\Http\Controllers\RestAPI\v1\UserWalletController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Customer\PaymentController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
 */

Route::group(['namespace' => 'RestAPI\v1', 'prefix' => 'v1', 'middleware' => ['api_lang']], function () {

    Route::group(['prefix' => 'auth', 'namespace' => 'auth'], function () {
        Route::controller(PassportAuthController::class)->group(function () {
            Route::get('logout', 'logout')->middleware('auth:api');
        });

        Route::controller(CustomerAPIAuthController::class)->group(function () {
            Route::post('register', 'register');
            Route::post('login', 'login');
            Route::post('check-email', 'checkEmail');
            Route::post('check-phone', 'checkPhone');
            Route::post('firebase-auth-verify', 'firebaseAuthVerify');
            Route::post('firebase-auth-token-store', 'firebaseAuthTokenStore');
            Route::post('verify-otp', 'verifyOTP');
            Route::post('verify-email', 'verifyEmail');
            Route::post('verify-phone', 'verifyPhone');
            Route::post('registration-with-otp', 'registrationWithOTP');
            Route::post('existing-account-check', 'existingAccountCheck');
            Route::post('registration-with-social-media', 'registrationWithSocialMedia');
            Route::post('forgot-password', 'passwordResetRequest');
        });

        Route::group(['middleware' => 'apiGuestCheck'], function () {
            Route::controller(CustomerAPIAuthController::class)->group(function () {
                Route::post('verify-profile-info', 'verifyProfileInfo');
            });
        });

        Route::controller(PhoneVerificationController::class)->group(function () {
            Route::post('resend-otp-check-phone', 'resend_otp_check_phone');
        });
        Route::controller(EmailVerificationController::class)->group(function () {
            Route::post('resend-otp-check-email', 'resend_otp_check_email');
        });
        Route::controller(ForgotPasswordController::class)->group(function () {
            Route::post('verify-token', 'tokenVerificationSubmit');
            Route::put('reset-password', 'reset_password_submit');
        });
        Route::controller(SocialAuthController::class)->group(function () {
            Route::post('social-login', 'social_login');
            Route::post('update-phone', 'update_phone');
            Route::post('social-customer-login', 'customerSocialLogin');
            Route::post('existing-account-check', 'existingAccountCheck');
            Route::post('registration-with-social-media', 'registrationWithSocialMedia');
        });
    });

    Route::controller(ConfigController::class)->group(function () {
        Route::get('config', 'configuration');
    });

    Route::group(['prefix' => 'shipping-method', 'middleware' => 'apiGuestCheck'], function () {
        Route::controller(ShippingMethodController::class)->group(function () {
            Route::get('detail/{id}', 'get_shipping_method_info');
            Route::get('by-seller/{id}/{seller_is}', 'shipping_methods_by_seller');
            Route::post('choose-for-order', 'choose_for_order');
            Route::get('chosen', 'chosen_shipping_methods');
            Route::get('check-shipping-type', 'check_shipping_type');
        });
    });

    Route::group(['prefix' => 'cart', 'middleware' => 'apiGuestCheck'], function () {
        Route::controller(CartController::class)->group(function () {
            Route::get('/', 'getCartList');
            Route::post('add', 'addToCart');
            Route::put('update', 'update_cart');
            Route::delete('remove', 'remove_from_cart');
            Route::delete('remove-all', 'remove_all_from_cart');
            Route::post('select-cart-items', 'updateCheckedCartItems');
            Route::post('product-restock-request', 'addProductRestockRequest');
        });
    });

    Route::group(['prefix' => 'customer/order', 'middleware' => 'apiGuestCheck'], function () {
        Route::get('get-order-by-id', 'CustomerController@get_order_by_id');
    });

    Route::get('faq', 'GeneralController@faq');

    Route::group(['prefix' => 'notifications'], function () {
        Route::get('/', 'NotificationController@list');
        Route::get('/seen', 'NotificationController@notification_seen')->middleware('auth:api');
    });

    Route::group(['prefix' => 'attributes'], function () {
        Route::get('/', 'AttributeController@get_attributes');
    });

    Route::group(['prefix' => 'flash-deals'], function () {
        Route::controller(FlashDealController::class)->group(function () {
            Route::get('/', 'getFlashDeal');
            Route::get('products/{deal_id}', 'getFlashDealProducts');
        });
    });

    Route::group(['prefix' => 'deals'], function () {
        Route::controller(DealController::class)->group(function () {
            Route::get('featured', 'getFeaturedDealProducts');
        });
    });

    Route::group(['prefix' => 'dealsoftheday'], function () {
        Route::controller(DealOfTheDayController::class)->group(function () {
            Route::get('deal-of-the-day', 'getDealOfTheDayProduct');
        });
    });

    Route::group(['prefix' => 'products'], function () {
        Route::controller(ProductController::class)->group(function () {
            Route::get('reviews/{product_id}', 'get_product_reviews');
            Route::get('rating/{product_id}', 'get_product_rating');
            Route::get('counter/{product_id}', 'counter');
            Route::get('shipping-methods', 'get_shipping_methods');
            Route::get('social-share-link/{product_id}', 'socialShareLink');
            Route::post('reviews/submit', 'submit_product_review')->middleware('auth:api');
            Route::put('review/update', 'updateProductReview')->middleware('auth:api');
            Route::get('review/{product_id}/{order_id}', 'getProductReviewByOrder')->middleware('auth:api');
            Route::delete('review/delete-image', 'deleteReviewImage')->middleware('auth:api');
        });
    });

    Route::group(['middleware' => 'apiGuestCheck'], function () {
        Route::group(['prefix' => 'products'], function () {
            Route::controller(ProductController::class)->group(function () {
                Route::get('latest', 'get_latest_products');
                Route::get('new-arrival', 'getNewArrivalProducts');
                Route::get('featured', 'getFeaturedProductsList');
                Route::get('top-rated', 'getTopRatedProducts');
                Route::any('search', 'get_searched_products');
                Route::post('filter', 'getProductsFilter');
                Route::any('suggestion-product', 'get_suggestion_product');
                Route::get('details/{slug}', 'getProductDetails');
                Route::get('related-products/{product_id}', 'get_related_products');
                Route::get('best-sellings', 'getBestSellingProducts');
                Route::get('home-categories', 'get_home_categories');
                Route::get('discounted-product', 'get_discounted_product');
                Route::get('most-demanded-product', 'get_most_demanded_product');
                Route::get('shop-again-product', 'getShopAgainProduct')->middleware('auth:api');
                Route::get('just-for-you', 'just_for_you');
                Route::get('most-searching', 'getMostSearchingProductsList');
                Route::get('digital-author-list', 'getDigitalProductsAuthorList');
                Route::get('digital-publishing-house-list', 'getDigitalPublishingHouseList');
                Route::get('clearance-sale', 'getClearanceSale');
            });
        });

        Route::group(['prefix' => 'seller'], function () {
            Route::controller(SellerController::class)->group(function () {
                Route::get('{seller_id}/products', 'getVendorProducts');
                Route::get('{seller_id}/seller-best-selling-products', 'get_seller_best_selling_products');
                Route::get('{seller_id}/seller-featured-product', 'get_sellers_featured_product');
                Route::get('{seller_id}/seller-recommended-products', 'get_sellers_recommended_products');
            });
        });

        Route::group(['prefix' => 'categories'], function () {
            Route::controller(CategoryController::class)->group(function () {
                Route::get('/', 'get_categories');
                Route::get('products/{category_id}', 'get_products');
                Route::get('/find-what-you-need', 'find_what_you_need');
            });
        });

        Route::group(['prefix' => 'brands'], function () {
            Route::controller(BrandController::class)->group(function () {
                Route::get('/', 'get_brands');
                Route::get('products/{brand_id}', 'get_products');
            });
        });

        Route::group(['prefix' => 'customer'], function () {
            Route::controller(CustomerController::class)->group(function () {
                Route::put('cm-firebase-token', 'update_cm_firebase_token');
                Route::get('get-restricted-country-list', 'get_restricted_country_list');
                Route::get('get-restricted-zip-list', 'get_restricted_zip_list');
            });

            Route::group(['prefix' => 'address'], function () {
                Route::controller(CustomerController::class)->group(function () {
                    Route::post('add', 'add_new_address');
                    Route::get('list', 'address_list');
                    Route::delete('/', 'delete_address');
                    Route::post('update', 'update_address');
                });
            });

            Route::group(['prefix' => 'order'], function () {
                Route::controller(OrderController::class)->group(function () {
                    Route::get('place', 'place_order');
                    Route::get('offline-payment-method-list', 'offline_payment_method_list');
                    Route::post('place-by-offline-payment', 'placeOrderByOfflinePayment');
                });
                Route::controller(CustomerController::class)->group(function () {
                    Route::get('details', 'get_order_details');
                    Route::get('generate-invoice', 'getOrderInvoice');
                });
            });
        });
    });

    Route::group(['prefix' => 'customer', 'middleware' => 'auth:api'], function () {
        Route::controller(CustomerController::class)->group(function () {
            Route::get('info', 'info');
            Route::put('update-profile', 'update_profile');
            Route::get('account-delete/{id}', 'account_delete');
        });

        Route::group(['prefix' => 'address'], function () {
            Route::controller(CustomerController::class)->group(function () {
                Route::get('get/{id}', 'get_address');
            });
        });

        Route::group(['prefix' => 'support-ticket'], function () {
            Route::controller(CustomerController::class)->group(function () {
                Route::post('create', 'create_support_ticket');
                Route::get('get', 'get_support_tickets');
                Route::get('conv/{ticket_id}', 'get_support_ticket_conv');
                Route::post('reply/{ticket_id}', 'reply_support_ticket');
                Route::get('close/{id}', 'support_ticket_close');
            });
        });

        Route::group(['prefix' => 'compare'], function () {
            Route::get('list', 'CompareController@list');
            Route::post('product-store', 'CompareController@compare_product_store');
            Route::delete('clear-all', 'CompareController@clear_all');
            Route::get('product-replace', 'CompareController@compare_product_replace');
        });

        Route::group(['prefix' => 'wish-list'], function () {
            Route::controller(CustomerController::class)->group(function () {
                Route::get('/', 'wish_list');
                Route::post('add', 'add_to_wishlist');
                Route::delete('remove', 'remove_from_wishlist');
            });
        });

        Route::group(['prefix' => 'restock-requests'], function () {
            Route::controller(CustomerRestockRequestController::class)->group(function () {
                Route::get('list', 'restockRequestsList');
                Route::post('delete', 'deleteRestockRequests');
            });
        });

        Route::group(['prefix' => 'order'], function () {
            Route::controller(OrderController::class)->group(function () {
                Route::get('place-by-wallet', 'placeOrderByWallet');
                Route::get('refund', 'refund_request');
                Route::post('refund-store', 'store_refund');
                Route::get('refund-details', 'refund_details');
                Route::post('again', 'order_again');
            });

            Route::controller(CustomerController::class)->group(function () {
                Route::get('list', 'get_order_list');
            });

            Route::controller(ProductController::class)->group(function () {
                Route::post('deliveryman-reviews/submit', 'submit_deliveryman_review')->middleware('auth:api');
            });
        });

        // Chatting
        Route::group(['prefix' => 'chat'], function () {
            Route::controller(ChatController::class)->group(function () {
                Route::get('list/{type}', 'list');
                Route::get('get-messages/{type}/{id}', 'get_message');
                Route::post('send-message/{type}', 'send_message');
                Route::post('seen-message/{type}', 'seen_message');
                Route::get('search/{type}', 'search');
            });
        });

        //wallet
        Route::group(['prefix' => 'wallet'], function () {
            Route::controller(UserWalletController::class)->group(function () {
                Route::get('list', 'list');
                Route::get('bonus-list', 'bonus_list');
            });
        });

        //loyalty
        Route::group(['prefix' => 'loyalty'], function () {
            Route::controller(UserLoyaltyController::class)->group(function () {
                Route::get('list', 'list');
                Route::post('loyalty-exchange-currency', 'loyalty_exchange_currency');
            });
        });
    });

    Route::group(['prefix' => 'customer', 'middleware' => 'apiGuestCheck'], function () {
        Route::group(['prefix' => 'order'], function () {
            Route::controller(OrderController::class)->group(function () {
                Route::get('digital-product-download/{id}', 'digital_product_download');
                Route::get('digital-product-download-otp-verify', 'digital_product_download_otp_verify');
                Route::post('digital-product-download-otp-resend', 'digital_product_download_otp_resend');
            });
        });
    });

    Route::group(['prefix' => 'digital-payment', 'middleware' => 'apiGuestCheck'], function () {
        Route::post('/', [PaymentController::class, 'payment']);
    });

    Route::group(['prefix' => 'add-to-fund', 'middleware' => 'auth:api'], function () {
        Route::post('/', [PaymentController::class, 'customer_add_to_fund_request']);
    });

    Route::group(['prefix' => 'order'], function () {
        Route::controller(OrderController::class)->group(function () {
            Route::get('track', 'track_by_order_id');
            Route::get('cancel-order', 'order_cancel');
            Route::post('track-order', 'track_order');
        });
    });

    Route::group(['prefix' => 'banners'], function () {
        Route::controller(BannerController::class)->group(function () {
            Route::get('/', 'getBannerList');
        });
    });

    Route::group(['prefix' => 'seller'], function () {
        Route::controller(SellerController::class)->group(function () {
            Route::get('/', 'get_seller_info');
            Route::get('list/{type}', 'getSellerList');
            Route::get('more', 'more_sellers');
        });
    });

    Route::group(['prefix' => 'coupon', 'middleware' => 'auth:api'], function () {
        Route::get('apply', 'CouponController@apply');
    });

    Route::controller(CouponController::class)->group(function () {
        Route::get('coupon/list', 'list')->middleware('auth:api');
        Route::get('coupon/applicable-list', 'applicable_list')->middleware('auth:api');
        Route::get('coupons/{seller_id}/seller-wise-coupons', 'getSellerWiseCoupon');
    });

    Route::get('get-guest-id', 'GeneralController@get_guest_id');

    //map api
    Route::group(['prefix' => 'mapapi'], function () {
        Route::get('place-api-autocomplete', 'MapApiController@place_api_autocomplete');
        Route::get('distance-api', 'MapApiController@distance_api');
        Route::get('place-api-details', 'MapApiController@place_api_details');
        Route::get('geocode-api', 'MapApiController@geocode_api');
    });

    Route::post('contact-us', 'GeneralController@contact_store');
    Route::put('customer/language-change', 'CustomerController@language_change')->middleware('auth:api');
});
