<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Contracts\Repositories\AnalyticScriptRepositoryInterface;
use App\Contracts\Repositories\DeliveryManRepositoryInterface;
use App\Contracts\Repositories\SocialMediaRepositoryInterface;
use App\Contracts\Repositories\VendorRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Traits\SettingsTrait;
use App\Traits\FileManagerTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Contracts\View\View;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\RedirectResponse;
use App\Http\Controllers\BaseController;
use App\Services\BusinessSettingService;
use App\Http\Requests\Admin\AnalyticsRequest;
use App\Enums\ViewPaths\Admin\BusinessSettings;
use App\Http\Requests\Admin\BusinessSettingRequest;
use App\Contracts\Repositories\CurrencyRepositoryInterface;
use App\Contracts\Repositories\BusinessSettingRepositoryInterface;
use Illuminate\Support\Facades\Cache;

class BusinessSettingsController extends BaseController
{

    use SettingsTrait;
    use FileManagerTrait {
        delete as deleteFile;
        update as updateFile;
    }

    public function __construct(
        private readonly BusinessSettingRepositoryInterface $businessSettingRepo,
        private readonly AnalyticScriptRepositoryInterface  $analyticScriptRepo,
        private readonly VendorRepositoryInterface          $vendorRepo,
        private readonly DeliveryManRepositoryInterface     $deliveryManRepo,
        private readonly CurrencyRepositoryInterface        $currencyRepo,
        private readonly SocialMediaRepositoryInterface     $socialMediaRepo,
        private readonly BusinessSettingService             $businessSettingService,
    )
    {
    }

    /**
     * @param Request|null $request
     * @param string|null $type
     * @return View Index function is the starting point of a controller
     * Index function is the starting point of a controller
     */
    public function index(Request|null $request, string $type = null): View
    {
        return $this->getView();
    }

    public function getView(): View
    {
        $web = $this->businessSettingRepo->getListWhere(dataLimit: 'all');
        $settings = $this->getSettings($web, 'colors');
        $data = json_decode($settings['value'], true);

        $businessSetting = [
            'primary_color' => $data['primary'] ?? '',
            'secondary_color' => $data['secondary'] ?? '',
            'primary_color_light' => $data['primary_light'] ?? '',
            'company_name' => $this->getSettings(object: $web, type: 'company_name')->value ?? '',
            'company_email' => $this->getSettings(object: $web, type: 'company_email')->value ?? '',
            'company_phone' => $this->getSettings(object: $web, type: 'company_phone')->value ?? '',
            'language' => $this->getSettings(object: $web, type: 'language')->value ?? '',
            'web_logo' => $this->getSettings(object: $web, type: 'company_web_logo')->value ?? '',
            'mob_logo' => $this->getSettings(object: $web, type: 'company_mobile_logo')->value ?? '',
            'fav_icon' => $this->getSettings(object: $web, type: 'company_fav_icon')->value ?? '',
            'footer_logo' => $this->getSettings(object: $web, type: 'company_footer_logo')->value ?? '',
            'shop_address' => $this->getSettings(object: $web, type: 'shop_address')->value ?? '',
            'company_copyright_text' => $this->getSettings(object: $web, type: 'company_copyright_text')->value ?? '',
            'system_default_currency' => $this->getSettings(object: $web, type: 'system_default_currency')->value ?? '',
            'currency_symbol_position' => $this->getSettings(object: $web, type: 'currency_symbol_position')->value ?? '',
            'business_mode' => $this->getSettings(object: $web, type: 'business_mode')->value ?? '',
            'email_verification' => $this->getSettings(object: $web, type: 'email_verification')->value ?? '',
            'otp_verification' => $this->getSettings(object: $web, type: 'otp_verification')->value ?? '',
            'guest_checkout' => $this->getSettings(object: $web, type: 'guest_checkout')->value ?? '',
            'pagination_limit' => $this->getSettings(object: $web, type: 'pagination_limit')->value ?? '',
            'copyright_text' => $this->getSettings(object: $web, type: 'company_copyright_text')->value ?? '',
            'decimal_point_settings' => $this->getSettings(object: $web, type: 'decimal_point_settings')->value ?? 0,
            'loader_gif' => $this->getSettings(object: $web, type: 'loader_gif')->value ?? '',
            'default_location' => $this->getSettings(object: $web, type: 'default_location')->value ?? '',
            'maintenance_mode' => $this->getSettings(object: $web, type: 'maintenance_mode')->value ?? 0,
        ];
        $maintenanceSystemSetup = json_decode($this->getSettings(object: $web, type: 'maintenance_system_setup')?->value, true) ?? [];
        $CurrencyList = $this->currencyRepo->getListWhere(dataLimit: 'all');

        $selectedMaintenanceDuration = getWebConfig(name: 'maintenance_duration_setup') ?? [];
        $maintenanceStartDate = isset($selectedMaintenanceDuration['start_date']) ? Carbon::parse($selectedMaintenanceDuration['start_date']) : null;
        $maintenanceEndDate = isset($selectedMaintenanceDuration['end_date']) ? Carbon::parse($selectedMaintenanceDuration['end_date']) : null;
        $selectedMaintenanceMessage = getWebConfig(name: 'maintenance_message_setup') ?? [];

        return view(BusinessSettings::INDEX[VIEW], [
            'CurrencyList' => $CurrencyList,
            'businessSetting' => $businessSetting,
            'maintenanceStartDate' => $maintenanceStartDate,
            'maintenanceEndDate' => $maintenanceEndDate,
            'selectedMaintenanceDuration' => $selectedMaintenanceDuration,
            'maintenanceSystemSetup' => $maintenanceSystemSetup,
            'selectedMaintenanceMessage' => $selectedMaintenanceMessage,
        ]);
    }

    public function updateSettings(BusinessSettingRequest $request, BusinessSettingService $businessSettingService): RedirectResponse
    {
        if ($request['email_verification'] == 1) {
            $request['phone_verification'] = 0;
        } elseif ($request['phone_verification'] == 1) {
            $request['email_verification'] = 0;
        }
        $this->businessSettingRepo->updateOrInsert(type: 'company_name', value: $request['company_name']);
        $this->businessSettingRepo->updateOrInsert(type: 'company_email', value: $request['company_email']);
        $this->businessSettingRepo->updateOrInsert(type: 'company_phone', value: $request['company_phone']);
        $this->businessSettingRepo->updateOrInsert(type: 'company_copyright_text', value: $request['company_copyright_text']);
        $this->businessSettingRepo->updateOrInsert(type: 'timezone', value: $request['timezone']);
        $this->businessSettingRepo->updateOrInsert(type: 'phone_verification', value: $request['phone_verification']);
        $this->businessSettingRepo->updateOrInsert(type: 'email_verification', value: $request['email_verification']);
        $this->businessSettingRepo->updateOrInsert(type: 'decimal_point_settings', value: $request['decimal_point_settings'] ?? 0);
        $this->businessSettingRepo->updateOrInsert(type: 'shop_address', value: $request['shop_address']);
        $this->businessSettingRepo->updateOrInsert(type: 'currency_symbol_position', value: $request['currency_symbol_position']);
        $this->businessSettingRepo->updateOrInsert(type: 'business_mode', value: $request['business_mode']);
        $this->businessSettingRepo->updateOrInsert(type: 'country_code', value: $request['country_code']);

        $this->businessSettingRepo->updateWhere(params: ['type' => 'system_default_currency'], data: ['value' => $request['currency_id']]);
        $currencyModel = getWebConfig(name: 'currency_model');
        if ($currencyModel == 'multi_currency') {
            $default = $this->currencyRepo->getFirstWhere(params: ['id' => $request['currency_id']]);
            $allCurrencies = $this->currencyRepo->getListWhere(dataLimit: 'all');
            foreach ($allCurrencies as $data) {
                $this->currencyRepo->update(id: $data['id'], data: ['exchange_rate' => ($data['exchange_rate'] / $default['exchange_rate'])]);
            }
        }

        $colors = json_encode(['primary' => $request['primary'], 'secondary' => $request['secondary'], 'primary_light' => $request['primary_light'] ?? '#CFDFFB']);
        $this->businessSettingRepo->updateOrInsert(type: 'colors', value: $colors);

        $defaultLocation = json_encode(['lat' => $request['latitude'], 'lng' => $request['longitude']]);
        $this->businessSettingRepo->updateOrInsert(type: 'default_location', value: $defaultLocation);

        $appAppleStore = json_encode(['status' => $request['app_store_download_status'] ?? 0, 'link' => $request['app_store_download_url']]);
        $this->businessSettingRepo->updateOrInsert(type: 'download_app_apple_stroe', value: $appAppleStore);

        $appGoogleStore = json_encode(['status' => $request['play_store_download_status'] ?? 0, 'link' => $request['play_store_download_url']]);
        $this->businessSettingRepo->updateOrInsert(type: 'download_app_google_stroe', value: $appGoogleStore);

        $webLogo = $this->businessSettingRepo->getFirstWhere(params: ['type' => 'company_web_logo']);
        if ($request->has('company_web_logo')) {
            $webLogoImage = [
                'image_name' => $this->updateFile(dir: 'company/', oldImage: (is_array($webLogo['value']) ? $webLogo['value']['image_name'] : $webLogo['value']), format: 'webp', image: $request->file('company_web_logo')),
                'storage' => config('filesystems.disks.default') ?? 'public'
            ];
            $this->businessSettingRepo->updateWhere(params: ['type' => 'company_web_logo'], data: ['value' => $webLogoImage]);
        }

        $mobileLogo = $this->businessSettingRepo->getFirstWhere(params: ['type' => 'company_mobile_logo']);
        if ($request->has('company_mobile_logo')) {
            $mobileLogoImage = [
                'image_name' => $this->updateFile(dir: 'company/', oldImage: (is_array($mobileLogo['value']) ? $mobileLogo['value']['image_name'] : $mobileLogo['value']), format: 'webp', image: $request->file('company_mobile_logo')),
                'storage' => config('filesystems.disks.default') ?? 'public'
            ];
            $this->businessSettingRepo->updateWhere(params: ['type' => 'company_mobile_logo'], data: ['value' => $mobileLogoImage]);
        }

        $webFooterLogo = $this->businessSettingRepo->getFirstWhere(params: ['type' => 'company_footer_logo']);
        if ($request->has('company_footer_logo')) {
            $webFooterLogoImage = [
                'image_name' => $this->updateFile(dir: 'company/', oldImage: (is_array($webFooterLogo['value']) ? $webFooterLogo['value']['image_name'] : $webFooterLogo['value']), format: 'webp', image: $request->file('company_footer_logo')),
                'storage' => config('filesystems.disks.default') ?? 'public'
            ];
            $this->businessSettingRepo->updateWhere(params: ['type' => 'company_footer_logo'], data: ['value' => $webFooterLogoImage]);
        }

        $favIcon = $this->businessSettingRepo->getFirstWhere(params: ['type' => 'company_fav_icon']);
        if ($request->has('company_fav_icon')) {
            $favIconImage = [
                'image_name' => $this->updateFile(dir: 'company/', oldImage: (is_array($favIcon['value']) ? $favIcon['value']['image_name'] : $favIcon['value']), format: 'webp', image: $request->file('company_fav_icon')),
                'storage' => config('filesystems.disks.default') ?? 'public'
            ];
            $this->businessSettingRepo->updateWhere(params: ['type' => 'company_fav_icon'], data: ['value' => $favIconImage]);
        }

        $loaderGif = $this->businessSettingRepo->getFirstWhere(params: ['type' => 'loader_gif']);
        if ($request->has('loader_gif')) {
            $loaderGifImage = $loaderGif ? $this->updateFile(dir: 'company/', oldImage: (is_array($loaderGif['value']) ? $loaderGif['value']['image_name'] : $loaderGif['value']), format: 'webp', image: $request->file('loader_gif'))
                : $this->upload(dir: 'company/', format: 'webp', image: $request->file('loader_gif'));

            $loaderGifImageArray = [
                'image_name' => $loaderGifImage,
                'storage' => config('filesystems.disks.default') ?? 'public'
            ];
            $this->businessSettingRepo->updateOrInsert(type: 'loader_gif', value: json_encode($loaderGifImageArray));
        }

        $language = $this->businessSettingRepo->getFirstWhere(params: ['type' => 'language']);
        $languageArray = $businessSettingService->getLanguageData(request: $request, language: $language);
        $this->businessSettingRepo->updateWhere(params: ['type' => 'language'], data: ['value' => $languageArray]);
        $this->businessSettingRepo->updateOrInsert(type: 'pagination_limit', value: $request['pagination_limit']);
        session()->forget('usd');
        session()->forget('default');
        session()->forget('system_default_currency_info');
        session()->forget('currency_code');
        session()->forget('currency_symbol');
        session()->forget('currency_exchange_rate');
        Toastr::success(translate('updated_successfully'));
        return back();
    }

    public function updateSystemMode(Request $request): RedirectResponse|JsonResponse
    {
        if (env('APP_MODE') == 'demo') {
            if ($request->ajax()) {
                return response()->json([
                    'message' => translate('you_can_not_update_this_on_demo_mode'), 401
                ]);
            } else {
                Toastr::error(translate('you_can_not_update_this_on_demo_mode'));
                return back();
            }
        }

        $selectedSystems = [];
        $totalSelectedSystems = 0;
        $systemPlatforms = ['user_app', 'user_website', 'vendor_app', 'deliveryman_app', 'vendor_panel'];
        foreach ($systemPlatforms as $system) {
            $selectedSystems[$system] = $request->get($system, 0);
            $totalSelectedSystems = $request->get($system, 0) + $totalSelectedSystems;
        }

        if ($totalSelectedSystems == 0 && $request->get('maintenance_mode', 0) == 1) {
            if ($request->ajax()) {
                return response()->json([
                    'message' => translate('Please_select_minimum_one_system'),
                    'status' => 'error'
                ]);
            } else {
                Toastr::error(translate('Please_select_minimum_one_system'));
                return back();
            }
        }

        $maintenanceDuration = [
            'maintenance_duration' => $request['maintenance_duration'],
            'start_date' => $request['start_date'] ?? null,
            'end_date' => $request['end_date'] ?? null,
        ];
        $maintenanceMessageSetup = [
            'business_number' => $request->has('business_number') ? 1 : 0,
            'business_email' => $request->has('business_email') ? 1 : 0,
            'maintenance_message' => $request['maintenance_message'],
            'message_body' => $request['message_body']
        ];

        $maintenanceModeStatus = $request->get('maintenance_mode', 0);
        $this->businessSettingRepo->updateOrInsert(type: 'maintenance_system_setup', value: json_encode($selectedSystems));
        $this->businessSettingRepo->updateOrInsert(type: 'maintenance_mode', value: $maintenanceModeStatus);
        $this->businessSettingRepo->updateOrInsert(type: 'maintenance_duration_setup', value: json_encode($maintenanceDuration));
        $this->businessSettingRepo->updateOrInsert(type: 'maintenance_message_setup', value: json_encode($maintenanceMessageSetup));
        clearWebConfigCacheKeys();

        $businessMode = getWebConfig(name: 'business_mode');
        if ($maintenanceModeStatus == 1) {
            $maintenanceArray = [
                'status' => $maintenanceModeStatus,
                'start_date' => $request->input('start_date', null),
                'end_date' => $request->input('end_date', null),
                'selectedSystems' => $selectedSystems,
                'maintenance_duration' => $maintenanceDuration,
                'maintenance_message' => $maintenanceMessageSetup,
            ];

            Cache::put('system_maintenance_mode', $maintenanceArray, now()->addYears(1));
        } else {
            Cache::forget('system_maintenance_mode');
        }

        if ($selectedSystems['user_app']) {
            $this->businessSettingService->sendMaintenanceModeNotification(status: $maintenanceModeStatus ? 'on' : 'off', topic: 'maintenance_mode_start_user_app');
        }

        if ($businessMode == 'multi' && $selectedSystems['vendor_app']) {
            $this->businessSettingService->sendMaintenanceModeNotification(status: $maintenanceModeStatus ? 'on' : 'off', topic: 'maintenance_mode_start_vendor');
        }

        if ($selectedSystems['deliveryman_app']) {
            $this->businessSettingService->sendMaintenanceModeNotification(status: $maintenanceModeStatus ? 'on' : 'off', topic: 'maintenance_mode_start_deliveryman');
        }

        if ($request->ajax()) {
            return response()->json([
                'message' => $maintenanceModeStatus ? translate('Maintenance_is_on') : translate('Maintenance_is_off'),
                'status' => 'success'
            ]);
        } else {
            Toastr::success($maintenanceModeStatus ? translate('Maintenance_is_on') : translate('Maintenance_is_off'));
            return back();
        }
    }

    public function getAppSettingsView(): View
    {
        $userApp = $this->businessSettingRepo->getFirstWhere(params: ['type' => 'user_app_version_control']);
        $userAppVersionControl = $userApp ? json_decode($userApp['value'], true) : [];
        $sellerApp = $this->businessSettingRepo->getFirstWhere(params: ['type' => 'seller_app_version_control']);
        $sellerAppVersionControl = $sellerApp ? json_decode($sellerApp['value'], true) : [];
        $deliverymanApp = $this->businessSettingRepo->getFirstWhere(params: ['type' => 'delivery_man_app_version_control']);
        $deliverymanAppVersionControl = $deliverymanApp ? json_decode($deliverymanApp['value'], true) : [];

        return view(BusinessSettings::APP_SETTINGS[VIEW], compact('userAppVersionControl', 'sellerAppVersionControl', 'deliverymanAppVersionControl'));
    }

    public function updateAppSettings(Request $request): RedirectResponse
    {
        if (in_array($request['type'], ['user_app_version_control', 'seller_app_version_control', 'delivery_man_app_version_control'])) {
            $this->businessSettingRepo->updateOrInsert(type: $request['type'], value: json_encode([
                'for_android' => $request['for_android'],
                'for_ios' => $request['for_ios'],
            ]));
        }
        Toastr::success(translate('updated_successfully'));
        return back();
    }

    public function getCookieSettingsView(Request $request): View
    {
        return view(BusinessSettings::COOKIE_SETTINGS[VIEW], [
            'cookieSetting' => getWebConfig(name: 'cookie_setting'),
        ]);
    }

    public function updateCookieSetting(Request $request): RedirectResponse
    {
        $this->businessSettingRepo->updateOrInsert(type: 'cookie_setting', value: json_encode([
            'status' => $request->get('status', 0),
            'cookie_text' => $request['cookie_text'],
        ]));
        Toastr::success(translate('cookie_settings_updated_successfully'));
        return redirect()->back();
    }

    public function getAnalyticsView(): View
    {
        $analytics = $this->analyticScriptRepo->getListWhere(dataLimit: 'all');
        $analyticsData = [];
        foreach ($analytics as $analytic) {
            $analyticsData[$analytic['type']] = $analytic;
        }
        return view(BusinessSettings::ANALYTICS_INDEX[VIEW], compact('analyticsData'));
    }

    public function updateAnalytics(Request $request): RedirectResponse
    {
        $analyticScriptsTypes = ['meta_pixel', 'linkedin_insight', 'tiktok_tag', 'snapchat_tag', 'twitter_tag', 'pinterest_tag', 'google_tag_manager', 'google_analytics'];
        if (!in_array($request['type'], $analyticScriptsTypes)) {
            Toastr::error(translate('Update_failed'));
            return back();
        }

        if (empty($request['script_id']) && $request['is_active'] == 1) {
            $type = str_replace(' ', '_', ucwords(str_replace('_', ' ', $request['type'])));
            Toastr::error(translate('Please_ensure_you_have_filled_in_the_'.$type.'_script_ID.'));
            return back();
        }

        $this->analyticScriptRepo->updateOrInsert(params: ['type' => $request['type']], data: [
            'name' => ucwords(str_replace('_', ' ', $request['type'])),
            'type' => $request['type'],
            'script_id' => $request['script_id'],
            'is_active' => $request['is_active'] ?? 0,
        ]);

        Toastr::success(translate('Update_successfully'));
        return back();
    }

    public function getProductSettingsView(): View
    {
        $digitalProduct = $this->businessSettingRepo->getFirstWhere(params: ['type' => 'digital_product']);
        $brand = $this->businessSettingRepo->getFirstWhere(params: ['type' => 'product_brand']);
        $stockLimit = $this->businessSettingRepo->getFirstWhere(params: ['type' => 'stock_limit']);
        return view(BusinessSettings::PRODUCT_SETTINGS[VIEW], compact('digitalProduct', 'brand', 'stockLimit'));
    }

    public function updateProductSettings(Request $request): RedirectResponse
    {
        $this->businessSettingRepo->updateOrInsert(type: 'stock_limit', value: $request->get('stock_limit', 0));
        $this->businessSettingRepo->updateOrInsert(type: 'product_brand', value: $request->get('product_brand', 0));
        $this->businessSettingRepo->updateOrInsert(type: 'digital_product', value: $request->get('digital_product', 0));
        clearWebConfigCacheKeys();
        Toastr::success(translate('updated_successfully'));
        return back();
    }

    public function getAnnouncementView(): View
    {
        $announcement = getWebConfig(name: 'announcement');
        return view(BusinessSettings::ANNOUNCEMENT[VIEW], compact('announcement'));
    }

    public function updateAnnouncement(Request $request): RedirectResponse
    {
        $value = json_encode(['status' => $request['announcement_status'], 'color' => $request['announcement_color'],
            'text_color' => $request['text_color'], 'announcement' => $request['announcement'],]);
        $this->businessSettingRepo->updateOrInsert(type: 'announcement', value: $value);
        Toastr::success(translate('announcement_updated_successfully'));
        return back();
    }

}
