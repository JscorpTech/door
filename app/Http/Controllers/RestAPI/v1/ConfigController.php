<?php

namespace App\Http\Controllers\RestAPI\v1;

use App\Http\Controllers\Controller;
use App\Traits\CacheManagerTrait;
use App\Traits\MaintenanceModeTrait;
use App\Traits\SettingsTrait;
use App\Utils\Helpers;
use App\Utils\ProductManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Route;
use function App\Utils\payment_gateways;

class ConfigController extends Controller
{
    use SettingsTrait, MaintenanceModeTrait, CacheManagerTrait;

    public function configuration(): JsonResponse
    {
        $socialLoginConfig = [];
        foreach (getWebConfig(name: 'social_login') as $social) {
            $config = [
                'login_medium' => $social['login_medium'],
                'status' => (boolean)$social['status']
            ];
            $socialLoginConfig[] = $config;
        }

        foreach (getWebConfig(name: 'apple_login') as $social) {
            $config = [
                'login_medium' => $social['login_medium'],
                'status' => (boolean)$social['status']
            ];
            $socialLoginConfig[] = $config;
        }

        $languageArray = [];
        foreach (getWebConfig(name: 'pnc_language') as $language) {
            $languageArray[] = [
                'code' => $language,
                'name' => Helpers::get_language_name($language)
            ];
        }

        $offlinePayment = null;
        $offlinePaymentStatus = getWebConfig(name: 'offline_payment')['status'] == 1 ?? 0;
        if ($offlinePaymentStatus) {
            $offlinePayment = [
                'name' => 'offline_payment',
                'image' => dynamicAsset(path: 'public/assets/back-end/img/pay-offline.png'),
            ];
        }

        $paymentMethods = payment_gateways();
        $paymentMethods->map(function ($payment) {
            $payment->additional_datas = json_decode($payment->additional_data);

            unset(
                $payment->additional_data,
                $payment->live_values,
                $payment->test_values,
                $payment->id,
                $payment->settings_type,
                $payment->mode,
                $payment->is_active,
                $payment->created_at,
                $payment->updated_at
            );
        });

        $shippingType = $this->cacheInHouseShippingType();
        $companyLogo = getWebConfig(name: 'company_web_logo');
        $companyFavIcon = getWebConfig(name: 'company_fav_icon');
        $companyShopBanner = getWebConfig(name: 'shop_banner');

        $loginOptions = getLoginConfig(key: 'login_options');
        $socialMediaLoginOptions = getLoginConfig(key: 'social_media_for_login');

        foreach ($socialMediaLoginOptions as $socialMediaLoginKey => $socialMediaLogin) {
            $socialMediaLoginOptions[$socialMediaLoginKey] = (int)$socialMediaLogin;
        }

        $customerLogin = [
            'login_option' => $loginOptions,
            'social_media_login_options' => $socialMediaLoginOptions
        ];

        $emailVerification = getLoginConfig(key: 'email_verification') ?? 0;
        $phoneVerification = getLoginConfig(key: 'phone_verification') ?? 0;

        $firebaseOTPVerification = getWebConfig(name: 'firebase_otp_verification');
        $customerVerification = [
            'status' => (int)($emailVerification == 1 || $phoneVerification == 1) ? 1 : 0,
            'phone' => (int)$phoneVerification,
            'email' => (int)$emailVerification,
            'firebase' => (int)($firebaseOTPVerification && $firebaseOTPVerification['status'] && $firebaseOTPVerification['web_api_key']),
        ];

        $maintenanceMode = [
            'maintenance_status' => (int)$this->checkMaintenanceMode(),
            'selected_maintenance_system' => getWebConfig(name: 'maintenance_system_setup') ?? [],
            'maintenance_messages' => getWebConfig(name: 'maintenance_message_setup') ?? [],
            'maintenance_type_and_duration' => getWebConfig(name: 'maintenance_duration_setup') ?? [],
        ];

        $themeComfortablePanelVersion = '';
        if (is_file(base_path('resources/themes/' . theme_root_path() . '/public/addon/theme_routes.php'))) {
            $themeRoutes = include(base_path('resources/themes/' . theme_root_path() . '/public/addon/theme_routes.php'));
            $themeComfortablePanelVersion = $themeRoutes['comfortable_panel_version'] ?? '';
        }

        $systemColors = getWebConfig('colors');
        return response()->json([
            'primary_color' => $systemColors['primary'],
            'secondary_color' => $systemColors['secondary'],
            'primary_color_light' => $systemColors['primary_light'] ?? '',
            'brand_setting' => (string)getWebConfig(name: 'product_brand'),
            'digital_product_setting' => (string)getWebConfig(name: 'digital_product'),
            'system_default_currency' => (int)getWebConfig(name: 'system_default_currency'),
            'digital_payment' => (boolean)getWebConfig(name: 'digital_payment')['status'] ?? 0,
            'cash_on_delivery' => (boolean)getWebConfig(name: 'cash_on_delivery')['status'] ?? 0,
            'seller_registration' => (string)getWebConfig(name: 'seller_registration') ?? 0,
            'pos_active' => (string)getWebConfig(name: 'seller_pos') ?? 0,
            'company_name' => getWebConfig(name: 'company_name') ?? '',
            'company_phone' => getWebConfig(name: 'company_phone') ?? '',
            'company_email' => getWebConfig(name: 'company_email') ?? '',
            'company_logo' => $companyLogo,
            'company_cover_image' => $companyShopBanner,
            'company_fav_icon' => $companyFavIcon,
            'delivery_country_restriction' => (int)getWebConfig(name: 'delivery_country_restriction'),
            'delivery_zip_code_area_restriction' => (int)getWebConfig(name: 'delivery_zip_code_area_restriction'),
            'base_urls' => [
                'product_image_url' => ProductManager::product_image_path('product'),
                'product_thumbnail_url' => ProductManager::product_image_path('thumbnail'),
                'digital_product_url' => dynamicStorage(path: 'storage/app/public/product/digital-product'),
                'brand_image_url' => dynamicStorage(path: 'storage/app/public/brand'),
                'customer_image_url' => dynamicStorage(path: 'storage/app/public/profile'),
                'banner_image_url' => dynamicStorage(path: 'storage/app/public/banner'),
                'category_image_url' => dynamicStorage(path: 'storage/app/public/category'),
                'review_image_url' => dynamicStorage(path: 'storage/app/public'),
                'seller_image_url' => dynamicStorage(path: 'storage/app/public/seller'),
                'shop_image_url' => dynamicStorage(path: 'storage/app/public/shop'),
                'notification_image_url' => dynamicStorage(path: 'storage/app/public/notification'),
                'delivery_man_image_url' => dynamicStorage(path: 'storage/app/public/delivery-man'),
                'support_ticket_image_url' => dynamicStorage(path: 'storage/app/public/support-ticket'),
                'chatting_image_url' => dynamicStorage(path: 'storage/app/public/chatting'),
            ],
            'static_urls' => [
                'contact_us' => route('contacts'),
                'brands' => route('brands'),
                'categories' => route('categories'),
                'customer_account' => route('user-account'),
            ],
            'about_us' => getWebConfig(name: 'about_us'),
            'privacy_policy' => getWebConfig(name: 'privacy_policy'),
            'faq' => $this->cacheHelpTopicTable(),
            'terms_&_conditions' => getWebConfig(name: 'terms_condition'),
            'refund_policy' => getWebConfig(name: 'refund-policy'),
            'return_policy' => getWebConfig(name: 'return-policy'),
            'cancellation_policy' => getWebConfig(name: 'cancellation-policy'),
            'shipping_policy' => getWebConfig(name: 'shipping-policy'),
            'currency_list' => $this->cacheCurrencyTable(),
            'currency_symbol_position' => getWebConfig(name: 'currency_symbol_position') ?? 'right',
            'business_mode' => getWebConfig(name: 'business_mode'),
            'language' => $languageArray,
            'colors' => $this->cacheColorsList(),
            'unit' => Helpers::units(),
            'shipping_method' => getWebConfig(name: 'shipping_method'),
            'email_verification' => (boolean)getLoginConfig(key: 'email_verification'),
            'phone_verification' => (boolean)getLoginConfig(key: 'phone_verification'),
            'country_code' => getWebConfig(name: 'country_code'),
            'social_login' => $socialLoginConfig,
            'currency_model' => getWebConfig(name: 'currency_model'),
            'forgot_password_verification' => getWebConfig(name: 'forgot_password_verification'),
            'announcement' => getWebConfig(name: 'announcement'),
            'pixel_analytics' => getWebConfig(name: 'pixel_analytics'),
            'software_version' => SOFTWARE_VERSION,
            'decimal_point_settings' => (int)getWebConfig(name: 'decimal_point_settings'),
            'inhouse_selected_shipping_type' => $shippingType,
            'billing_input_by_customer' => (int)getWebConfig(name: 'billing_input_by_customer'),
            'minimum_order_limit' => (int)getWebConfig(name: 'minimum_order_limit'),
            'wallet_status' => (int)getWebConfig(name: 'wallet_status'),
            'loyalty_point_status' => (int)getWebConfig(name: 'loyalty_point_status'),
            'loyalty_point_exchange_rate' => (int)getWebConfig(name: 'loyalty_point_exchange_rate'),
            'loyalty_point_minimum_point' => (int)getWebConfig(name: 'loyalty_point_minimum_point'),
            'payment_methods' => $paymentMethods,
            'offline_payment' => $offlinePayment,
            'payment_method_image_path' => dynamicStorage(path: 'storage/app/public/payment_modules/gateway_image'),
            'ref_earning_status' => getWebConfig(name: 'ref_earning_status') ?? 0,
            'active_theme' => theme_root_path(),
            'theme_comfortable_panel_version' => $themeComfortablePanelVersion,
            'popular_tags' => $this->cacheTagTable(),
            'guest_checkout' => (int)getWebConfig(name: 'guest_checkout'),
            'upload_picture_on_delivery' => getWebConfig(name: 'upload_picture_on_delivery'),
            'user_app_version_control' => getWebConfig(name: 'user_app_version_control'),
            'seller_app_version_control' => getWebConfig(name: 'seller_app_version_control'),
            'delivery_man_app_version_control' => getWebConfig(name: 'delivery_man_app_version_control'),
            'add_funds_to_wallet' => (int)getWebConfig(name: 'add_funds_to_wallet'),
            'minimum_add_fund_amount' => getWebConfig(name: 'minimum_add_fund_amount'),
            'maximum_add_fund_amount' => getWebConfig(name: 'maximum_add_fund_amount'),
            'inhouse_temporary_close' => getWebConfig(name: 'temporary_close'),
            'inhouse_vacation_add' => getWebConfig(name: 'vacation_add'),
            'free_delivery_status' => (int)getWebConfig(name: 'free_delivery_status'),
            'free_delivery_over_amount' => getWebConfig(name: 'free_delivery_over_amount'),
            'free_delivery_responsibility' => getWebConfig(name: 'free_delivery_responsibility'),
            'free_delivery_over_amount_seller' => getWebConfig(name: 'free_delivery_over_amount_seller'),
            'minimum_order_amount_status' => (int)getWebConfig(name: 'minimum_order_amount_status'),
            'minimum_order_amount' => getWebConfig(name: 'minimum_order_amount'),
            'minimum_order_amount_by_seller' => (int)getWebConfig(name: 'minimum_order_amount_by_seller'),
            'order_verification' => (int)getWebConfig(name: 'order_verification'),
            'referral_customer_signup_url' => route('home') . '?referral_code=',
            'system_timezone' => getWebConfig(name: 'timezone'),
            'refund_day_limit' => getWebConfig(name: 'refund_day_limit') ?? 0,
            'map_api_status' => (int)getWebConfig(name: 'map_api_status'),
            'default_location' => getWebConfig(name: 'default_location'),
            'vendor_review_reply_status' => (int)getWebConfig(name: 'vendor_review_reply_status') ?? 0,
            'maintenance_mode' => $maintenanceMode,
            'customer_login' => $customerLogin,
            'customer_verification' => $customerVerification,
            'otp_resend_time' => getWebConfig(name: 'otp_resend_time') ?? 60,
            'vendor_forgot_password_method' => getWebConfig(name: 'vendor_forgot_password_method') ?? 'phone',
            'deliveryman_forgot_password_method' => getWebConfig(name: 'deliveryman_forgot_password_method') ?? 'phone',
            'blog_page' => Route::has('app.blog.index') && getWebConfig(name: 'blog_feature_active_status') ? route('app.blog.index') : '',
            'server_upload_max_filesize' => ini_get('upload_max_filesize'),
            'product_max_unit_price_range' => getProductMaxUnitPriceRange(),
            'product_min_unit_price_range' => getProductMinUnitPriceRange(),
        ]);
    }
}
