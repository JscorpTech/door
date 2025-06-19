<?php

namespace App\Providers;

use App\Models\FlashDealProduct;
use App\Models\LoginSetup;
use App\Models\StockClearanceProduct;
use App\Traits\CacheManagerTrait;
use App\Traits\FileManagerTrait;
use App\Traits\UpdateClass;
use App\Utils\Helpers;
use App\Enums\GlobalConstant;
use App\Models\Currency;
use App\Models\Setting;
use App\Models\Shop;
use App\Models\SocialMedia;
use App\Models\FlashDeal;
use App\Models\Product;
use App\Traits\AddonHelper;
use App\Traits\ThemeHelper;
use App\Utils\ProductManager;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

ini_set('memory_limit', -1);
ini_set('upload_max_filesize', '180M');
ini_set('post_max_size', '200M');

class AppServiceProvider extends ServiceProvider
{

    use AddonHelper;
    use CacheManagerTrait;
    use FileManagerTrait;
    use ThemeHelper;
    use UpdateClass;

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register(): void
    {
        if ($this->app->isLocal()) {
            $this->app->register(\Amirami\Localizator\ServiceProvider::class);
        }
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */

    public function boot(): void
    {
        if (!in_array(request()->ip(), ['127.0.0.1', '::1']) && env('FORCE_HTTPS')) {
            \URL::forceScheme('https');
        }

        if (!App::runningInConsole()) {
            Paginator::useBootstrap();

            Config::set('addon_admin_routes', $this->getAddonAdminRoutes());
            Config::set('get_payment_publish_status', $this->getPaymentPublishStatus());
            Config::set('get_theme_routes', $this->getThemeRoutesArray());

            try {
                if (Schema::hasTable('business_settings')) {
                    $this->setStorageConnectionEnvironment();
                    $this->cacheInHouseShopInTemporaryStatus();

                    $web = $this->cacheBusinessSettingsTable();

                    $firebaseOTPVerification = getWebConfig(name: 'firebase_otp_verification');
                    $firebaseOTPVerificationStatus = (int)($firebaseOTPVerification && $firebaseOTPVerification['status'] && $firebaseOTPVerification['web_api_key']);

                    $systemColors = getWebConfig('colors');
                    $web_config = [
                        'primary_color' => $systemColors['primary'] ?? '',
                        'secondary_color' => $systemColors['secondary'] ?? '',
                        'primary_color_light' => $systemColors['primary_light'] ?? '',
                        'name' => Helpers::get_settings($web, 'company_name'),
                        'company_name' => getWebConfig(name: 'company_name'),
                        'phone' => getWebConfig(name: 'company_phone'),
                        'web_logo' => getWebConfig(name: 'company_web_logo'),
                        'mob_logo' => getWebConfig(name:'company_mobile_logo'),
                        'fav_icon' => getWebConfig(name: 'company_fav_icon'),
                        'email' => getWebConfig(name: 'company_email'),
                        'about' => Helpers::get_settings($web, 'about_us'),
                        'footer_logo' => getWebConfig(name: 'company_footer_logo'),
                        'copyright_text' => getWebConfig(name: 'company_copyright_text'),
                        'decimal_point_settings' => !empty(getWebConfig(name: 'decimal_point_settings')) ? getWebConfig(name: 'decimal_point_settings') : 0,
                        'seller_registration' => getWebConfig(name: 'seller_registration') ?? 0,
                        'wallet_status' => getWebConfig(name: 'wallet_status'),
                        'loyalty_point_status' => getWebConfig(name: 'loyalty_point_status'),
                        'guest_checkout_status' => getWebConfig(name: 'guest_checkout'),
                        'digital_product_setting' => getWebConfig(name:'digital_product'),
                        'language' => getWebConfig(name: 'language'),
                        'publishing_houses' => Schema::hasTable('publishing_houses') ? ProductManager::getPublishingHouseList() : null,
                        'digital_product_authors' => Schema::hasTable('authors') ? ProductManager::getProductAuthorList() : null,
                        'firebase_otp_verification' => $firebaseOTPVerification,
                        'firebase_otp_verification_status' => $firebaseOTPVerificationStatus,
                        'meta_description' => substr(strip_tags(str_replace('&nbsp;', ' ', (getWebConfig(name: 'about_us') ?? ''))),0,160),
                    ];

                    if ((!Request::is('admin') && !Request::is('admin/*') && !Request::is('seller/*') && !Request::is('vendor/*')) || Request::is('vendor/auth/registration/*')) {
                        $userId = Auth::guard('customer')->user() ? Auth::guard('customer')->id() : 0;
                        $flashDeal = ProductManager::getPriorityWiseFlashDealsProductsQuery(userId: $userId);

                        $featuredDealID = FlashDeal::where(['deal_type' => 'feature_deal', 'status' => 1])->whereDate('start_date', '<=', date('Y-m-d'))
                            ->whereDate('end_date', '>=', date('Y-m-d'))->pluck('id')->first();
                        $featuredDealProductIDs = $featuredDealID ? FlashDealProduct::where('flash_deal_id', $featuredDealID)->pluck('product_id')->toArray() : [];
                        $featuredDealList = ProductManager::getPriorityWiseFeatureDealQuery(
                            query: Product::active()->with(['category', 'clearanceSale' => function($query) {
                                return $query->active();
                            }])->whereIn('id', $featuredDealProductIDs),
                            dataLimit: 'all'
                        );

                        $shops = Shop::whereHas('seller', function ($query) {
                            return $query->approved();
                        })->take(9)->get();

                        $recaptcha = getWebConfig(name: 'recaptcha');
                        $paymentGatewayPublishedStatus = config('get_payment_publish_status') ?? 0;

                        $paymentGatewaysQuery = Setting::whereIn('settings_type', ['payment_config'])->where('is_active', 1);
                        if ($paymentGatewayPublishedStatus == 1) {
                            $paymentsGatewaysList = $paymentGatewaysQuery->select('key_name', 'additional_data')->get();
                        } else {
                            $paymentsGatewaysList = $paymentGatewaysQuery->whereIn('key_name', GlobalConstant::DEFAULT_PAYMENT_GATEWAYS)->select('key_name', 'additional_data')->get();
                        }

                        $customerLoginOptions = LoginSetup::where(['key' => 'login_options'])->first()?->value ?? '';
                        $customerSocialLoginOptions = LoginSetup::where(['key' => 'social_media_for_login'])->first()?->value ?? '';
                        $customerSocialLoginOptions = json_decode($customerSocialLoginOptions, true) ?? [];
                        $socialLoginTextShowStatus = false;
                        foreach ($customerSocialLoginOptions as $socialLoginService) {
                            if ($socialLoginService == 1) {
                                $socialLoginTextShowStatus = true;
                            }
                        }
                        $totalDiscountProducts = Product::active()
                            ->withCount('reviews')
                            ->where(function ($subQuery) {
                                return $subQuery->where(function ($query) {
                                    return $query->where('discount', '!=', 0);
                                })->orWhere(function ($query) {
                                    $stockClearanceProductIds = StockClearanceProduct::active()->pluck('product_id')->toArray();
                                    return $query->whereIn('id', $stockClearanceProductIds);
                                });
                            })
                            ->count();

                        $web_config += [
                            'cookie_setting' => Helpers::get_settings($web, 'cookie_setting'),
                            'announcement' => getWebConfig(name: 'announcement'),
                            'currency_model' => getWebConfig(name: 'currency_model'),
                            'currencies' => Currency::where(['status' => 1])->get(),
                            'main_categories' => $this->cacheMainCategoriesList(),
                            'priority_wise_brands' => $this->cachePriorityWiseBrandList(),
                            'business_mode' => getWebConfig(name: 'business_mode'),
                            'social_media' => SocialMedia::where('active_status', 1)->get(),
                            'ios' => getWebConfig(name: 'download_app_apple_stroe'),
                            'android' => getWebConfig(name: 'download_app_google_stroe'),
                            'refund_policy' => getWebConfig(name: 'refund-policy'),
                            'return_policy' => getWebConfig(name: 'return-policy'),
                            'cancellation_policy' => getWebConfig(name: 'cancellation-policy'),
                            'shipping_policy' => getWebConfig(name: 'shipping-policy'),
                            'flash_deals' => $flashDeal['flashDeal'],
                            'flash_deals_products' => $flashDeal['flashDealProducts'] ?? [],
                            'featured_deals' => $featuredDealList,
                            'shops' => $shops,
                            'brand_setting' => getWebConfig(name: 'product_brand'),
                            'discount_product' => $totalDiscountProducts,
                            'recaptcha' => $recaptcha,
                            'socials_login' => getWebConfig(name: 'social_login'),
                            'social_login_text' => $socialLoginTextShowStatus,
                            'popup_banner' => $this->cacheBannerTable(bannerType: 'Popup Banner'),
                            'header_banner' => $this->cacheBannerTable(bannerType: 'Header Banner'),
                            'payments_list' => $paymentsGatewaysList, // Fashion_theme
                            'ref_earning_status' => getWebConfig('ref_earning_status'),
                            'customer_login_options' => json_decode($customerLoginOptions, true),
                            'customer_social_login_options' => $customerSocialLoginOptions,
                            'customer_phone_verification' => getLoginConfig(key: 'phone_verification'),
                            'customer_email_verification' => getLoginConfig(key: 'email_verification'),
                            'default_meta_content' => $this->cacheRobotsMetaContent(page: 'default'),
                            'analytic_scripts' => $this->cacheActiveAnalyticScript(),
                            'clearance_sale_product_count' => $this->cacheClearanceSaleProductsCount(),
                        ];

                        if (theme_root_path() == "theme_fashion") {
                            $featuresSection = [
                                'features_section_top' => getWebConfig(name: 'features_section_top') ?? [],
                                'features_section_middle' => getWebConfig(name: 'features_section_middle') ?? [],
                                'features_section_bottom' => getWebConfig(name: 'features_section_bottom') ?? [],
                            ];

                            $tags = $this->cacheTagTable();

                            $web_config += [
                                'tags' => $tags,
                                'features_section' => $featuresSection,
                                'total_discount_products' => $totalDiscountProducts,
                                'products_stock_limit' => getWebConfig(name: 'stock_limit'),
                            ];
                        }
                    }

                    // Language
                    $language = getWebConfig(name: 'language') ?? [];

                    // Currency
                    \App\Utils\Helpers::currency_load();

                    View::share(['web_config' => $web_config, 'language' => $language]);

                    Schema::defaultStringLength(191);
                }
            } catch (\Exception $exception) {

            }
        }

        /**
         * Paginate a standard Laravel Collection.
         *
         * @param int $perPage
         * @param int $total
         * @param int $page
         * @param string $pageName
         * @return array
         */

        Collection::macro('paginate', function ($perPage, $total = null, $page = null, $pageName = 'page') {
            $page = $page ?: LengthAwarePaginator::resolveCurrentPage($pageName);

            return new LengthAwarePaginator(
                $this->forPage($page, $perPage),
                $total ?: $this->count(),
                $perPage,
                $page,
                [
                    'path' => LengthAwarePaginator::resolveCurrentPath(),
                    'pageName' => $pageName,
                ]
            );
        });

    }
}
