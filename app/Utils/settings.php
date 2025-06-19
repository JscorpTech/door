<?php

use App\Models\BusinessSetting;
use App\Models\Color;
use App\Models\LoginSetup;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;

if (!function_exists('getWebConfig')) {
    function getWebConfig($name): string|object|array|null
    {
        $config = null;
        if (in_array($name, getWebConfigCacheKeys()) && Cache::has($name)) {
            $config = Cache::get($name);
        } else {
            $settings = Cache::remember(CACHE_BUSINESS_SETTINGS_TABLE, CACHE_FOR_3_HOURS, function () {
                return BusinessSetting::all();
            });
            $data = $settings?->firstWhere('type', $name);
            $config = isset($data) ? setWebConfigCache($name, $data) : $config;
        }
        return $config;
    }
}

if (!function_exists('clearWebConfigCacheKeys')) {
    function clearWebConfigCacheKeys(): bool
    {
        $cacheKeys = getWebConfigCacheKeys();
        $allConfig = BusinessSetting::whereIn('type', $cacheKeys)->get();

        foreach ($cacheKeys as $cacheKey) {
            Cache::forget($cacheKey);
        }
        Cache::forget(CACHE_BUSINESS_SETTINGS_TABLE);
        cacheRemoveByType(type: 'business_settings');
        foreach ($allConfig as $item) {
            setWebConfigCache($item['type'], $item);
        }
        return true;
    }

    function setWebConfigCache($name, $data)
    {
        $cacheKeys = getWebConfigCacheKeys();
        $arrayOfCompaniesValue = ['company_web_logo', 'company_mobile_logo', 'company_footer_logo', 'company_fav_icon', 'loader_gif', 'blog_feature_download_app_icon', 'blog_feature_download_app_background'];
        $arrayOfBanner = ['shop_banner', 'offer_banner', 'bottom_banner'];
        $mergeArray = array_merge($arrayOfCompaniesValue, $arrayOfBanner);

        $config = json_decode($data['value'], true);
        if (in_array($name, $mergeArray)) {
            $folderName = in_array($name, $arrayOfCompaniesValue) ? 'company' : 'shop';
            $value = isset($config['image_name']) ? $config : ['image_name' => $data['value'], 'storage' => 'public'];
            $config = storageLink($folderName, $value['image_name'], $value['storage']);
        }

        if (is_null($config)) {
            $config = $data['value'];
        }

        if (in_array($name, $cacheKeys)) {
            Cache::put($name, $config, now()->addMinutes(30));
        }
        return $config;
    }
}

if (!function_exists('getWebConfigCacheKeys')) {
    function getWebConfigCacheKeys(): string|object|array|null
    {
        return [
            'currency_model', 'currency_symbol_position', 'system_default_currency', 'language',
            'company_name', 'decimal_point_settings', 'product_brand', 'company_email',
            'business_mode', 'storage_connection_type', 'company_web_logo', 'digital_product', 'storage_connection_type', 'recaptcha',
            'language', 'pagination_limit', 'company_phone', 'stock_limit',
        ];
    }
}

if (!function_exists('storageDataProcessing')) {
    function storageDataProcessing($name, $value)
    {
        $arrayOfCompaniesValue = ['company_web_logo', 'company_mobile_logo', 'company_footer_logo', 'company_fav_icon', 'loader_gif'];
        if (in_array($name, $arrayOfCompaniesValue)) {
            if (!is_array($value)) {
                return storageLink('company', $value, 'public');
            } else {
                return storageLink('company', $value['image_name'], $value['storage']);
            }
        } else {
            return $value;
        }
    }
}

if (!function_exists('imagePathProcessing')) {
    function imagePathProcessing($imageData, $path): array|string|null
    {
        if ($imageData) {
            $imageData = is_string($imageData) ? $imageData : (array)$imageData;
            $imageArray = [
                'image_name' => is_array($imageData) ? $imageData['image_name'] : $imageData,
                'storage' => $imageData['storage'] ?? 'public',
            ];
            return storageLink($path, $imageArray['image_name'], $imageArray['storage']);
        }
        return null;
    }
}

if (!function_exists('storageLink')) {
    function storageLink($path, $data, $type): string|array
    {
        if ($type == 's3' && config('filesystems.disks.default') == 's3') {
            $fullPath = ltrim($path . '/' . $data, '/');
            if (fileCheck(disk: 's3', path: $fullPath) && !empty($data)) {
                return [
                    'key' => $data,
                    'path' => Storage::disk('s3')->url($fullPath),
                    'status' => 200,
                ];
            }
        } else {
            if (fileCheck(disk: 'public', path: $path . '/' . $data) && !empty($data)) {

                $resultPath = asset('storage/app/public/' . $path . '/' . $data);
                if (DOMAIN_POINTED_DIRECTORY == 'public') {
                    $resultPath = asset('storage/' . $path . '/' . $data);
                }

                return [
                    'key' => $data,
                    'path' => $resultPath,
                    'status' => 200,
                ];
            }
        }
        return [
            'key' => $data,
            'path' => null,
            'status' => 404,
        ];
    }
}


if (!function_exists('storageLinkForGallery')) {
    function storageLinkForGallery($path, $type): string|null
    {
        if ($type == 's3' && config('filesystems.disks.default') == 's3') {
            $fullPath = ltrim($path, '/');
            if (fileCheck(disk: 's3', path: $fullPath)) {
                return Storage::disk('s3')->url($fullPath);
            }
        } else {
            if (fileCheck(disk: 'public', path: $path)) {
                if (DOMAIN_POINTED_DIRECTORY == 'public') {
                    $result = str_replace('storage/app/public', 'storage', 'storage/app/public/' . $path);
                } else {
                    $result = 'storage/app/public/' . $path;
                }
                return asset($result);
            }
        }
        return null;
    }
}

if (!function_exists('fileCheck')) {
    function fileCheck($disk, $path): bool
    {
        return Storage::disk($disk)->exists($path);
    }
}


if (!function_exists('getLoginConfig')) {
    function getLoginConfig($key): string|object|array|null
    {
        $config = null;
        $settings = Cache::remember(CACHE_LOGIN_SETUP_TABLE, CACHE_FOR_3_HOURS, function () {
            return LoginSetup::all();
        });
        $data = $settings?->firstWhere('key', $key);
        return isset($data) ? json_decode($data['value'], true) : $config;
    }
}

if (!function_exists('getCustomerFromQuery')) {
    function getCustomerFromQuery()
    {
        return auth('customer')->check() ? User::where('id', auth('customer')->id())->first() : null;
    }
}

if (!function_exists('getFCMTopicListToSubscribe')) {
    function getFCMTopicListToSubscribe(): array
    {
        $topics = ['sixvalley', 'maintenance_mode_start_user_app'];
        return array_merge((session('customer_fcm_topic') ?? []), $topics);
    }
}

if (!function_exists('checkDateFormatInMDY')) {
    function checkDateFormatInMDY($date): bool
    {
        try {
            Carbon::createFromFormat('m/d/Y', trim($date))->startOfDay();
            return true;
        } catch (\Exception $e) {
        }
        return false;
    }
}

if (!function_exists('checkDateFormatInMDYAndTime')) {
    function checkDateFormatInMDYAndTime($dateTime): bool
    {
        try {
            Carbon::createFromFormat('m/d/Y h:i:s A', $dateTime);
            return true;
        } catch (\Exception $e) {
        }
        return false;
    }
}

if (!function_exists('checkTimeFormatInRequestTime')) {
    function checkTimeFormatInRequestTime($time): bool
    {
        try {
            Carbon::createFromFormat('h:i:s A', $time);
            return true;
        } catch (\Exception $e) {
        }
        return false;
    }
}


if (!function_exists('getColorNameByCode')) {
    function getColorNameByCode($code)
    {
        $settings = Cache::remember(CACHE_FOR_ALL_COLOR_LIST, CACHE_FOR_3_HOURS, function () {
            return Color::all();
        });
        $data = $settings?->firstWhere('code', $code);
        return isset($data) ? $data->name : $data;
    }
}


if (!function_exists('getStoreTempResponse')) {
    function getStoreTempResponse($response): void
    {
        $folderPath = base_path('storage/app/temp-note');
        if (!file_exists($folderPath)) {
            mkdir($folderPath, 0777, true);
        }
        $string = "<?php return " . var_export($response, true) . ";";
        file_put_contents(base_path('storage/app/temp-note/temp-note-'.rand(1111, 9999).'.php'), $string);
    }
}



if (!function_exists('cacheRemoveByType')) {
    function cacheRemoveByType(string $type): void
    {
        if ($type == 'business_settings') {
            Cache::forget(CACHE_BUSINESS_SETTINGS_TABLE);
            Cache::forget(CACHE_FOR_IN_HOUSE_SHIPPING_TYPE);
            Cache::forget(CACHE_FOR_ANALYTIC_SCRIPT_ACTIVE_LIST);
            cacheRemoveByType(type: 'products');
        } else if ($type == 'banners') {
            $cacheKeys = Cache::get(CACHE_BANNER_ALL_CACHE_KEYS, []);
            foreach ($cacheKeys as $key) {
                Cache::forget($key);
            }
            Cache::forget(CACHE_BANNER_ALL_CACHE_KEYS);
            Cache::forget(CACHE_BANNER_TABLE);
        } else if ($type == 'currencies') {
            Cache::forget(CACHE_FOR_CURRENCY_TABLE);
        } else if ($type == 'categories') {
            Cache::forget(CACHE_MAIN_CATEGORIES_LIST);
            Cache::forget(FIND_WHAT_YOU_NEED_CATEGORIES_LIST);
            Cache::forget(CACHE_HOME_CATEGORIES_LIST);
            Cache::forget(CACHE_HOME_CATEGORIES_API_LIST);

            foreach (Cache::get(CACHE_CONTAINER_FOR_LANGUAGE_WISE_CACHE_KEYS, []) as $key) {
                Cache::forget($key);
            }
        } else if ($type == 'flash_deals') {
            $cacheKeys = Cache::get(CACHE_FLASH_DEAL_KEYS, []);
            foreach ($cacheKeys as $key) {
                Cache::forget($key);
            }
            Cache::forget(CACHE_FLASH_DEAL_KEYS);
        } else if ($type == 'help_topics') {
            Cache::forget(CACHE_HELP_TOPICS_TABLE);
        } else if ($type == 'login_setups') {
            Cache::forget(CACHE_LOGIN_SETUP_TABLE);
        } else if ($type == 'tags') {
            Cache::forget(CACHE_TAGS_TABLE);
        } else if ($type == 'products' || $type == 'brands') {
            cacheRemoveByType(type: 'categories');
            cacheRemoveByType(type: 'flash_deals');
            cacheRemoveByType(type: 'shops');
            cacheRemoveByType(type: 'order_details');

            Cache::forget(CACHE_FOR_MOST_DEMANDED_PRODUCT_ITEM);
            Cache::forget(CACHE_FOR_BEST_SELLING_PRODUCT_ITEM);
            Cache::forget(CACHE_FOR_FEATURED_PRODUCTS_LIST);
            Cache::forget(CACHE_FOR_MOST_SEARCHING_PRODUCTS_LIST);
            Cache::forget(CACHE_FOR_ALL_PRODUCTS_COLOR_LIST);
            Cache::forget(CACHE_FOR_PRODUCTS_MAX_UNIT_PRICE);
            Cache::forget(CACHE_FOR_PRODUCTS_MIN_UNIT_PRICE);
            Cache::forget(CACHE_FOR_ALL_PRODUCTS_REVIEW_LIST);
            Cache::forget(CACHE_FOR_RANDOM_SINGLE_PRODUCT);
            Cache::forget(CACHE_FOR_HOME_PAGE_JUST_FOR_YOU_PRODUCT_LIST);
            Cache::forget(CACHE_FOR_HOME_PAGE_LATEST_PRODUCT_LIST);
            Cache::forget(CACHE_FOR_HOME_PAGE_TOP_RATED_PRODUCT_LIST);
            Cache::forget(CACHE_FOR_HOME_PAGE_BEST_SELL_PRODUCT_LIST);
            Cache::forget(CACHE_FOR_CLEARANCE_SALE_PRODUCTS_COUNT);

            // Brands
            Cache::forget(CACHE_PRIORITY_WISE_BRANDS_LIST);
            Cache::forget(CACHE_ACTIVE_BRANDS_WITH_COUNTING_AND_PRIORITY);
            Cache::forget(CACHE_CONTAINER_FOR_LANGUAGE_WISE_CACHE_KEYS);
        } else if ($type == 'shipping_types') {
             Cache::forget(CACHE_FOR_IN_HOUSE_SHIPPING_TYPE);
        } else if ($type == 'sellers' || $type == 'shops') {
            Cache::forget(CACHE_FOR_IN_HOUSE_ALL_PRODUCTS);
            Cache::forget(CACHE_FOR_HOME_PAGE_TOP_VENDORS_LIST);
            Cache::forget(CACHE_FOR_HOME_PAGE_MORE_VENDORS_LIST);
            Cache::forget(CACHE_SHOP_TABLE);
            cacheRemoveByType(type: 'flash_deals');
        } else if ($type == 'reviews') {
            cacheRemoveByType(type: 'products');
        } else if ($type == 'order_details') {
            Cache::forget(CACHE_ORDER_DETAILS_TABLE);
        } else if ($type == 'analytic_script') {
            Cache::forget(CACHE_FOR_ANALYTIC_SCRIPT_ACTIVE_LIST);
        } else if ($type == 'robots_meta_contents') {
            Cache::forget(CACHE_ROBOTS_META_CONTENT_TABLE);
        }
    }
}

