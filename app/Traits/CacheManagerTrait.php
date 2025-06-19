<?php

namespace App\Traits;

use App\Models\AnalyticScript;
use App\Models\Banner;
use App\Models\BusinessSetting;
use App\Models\Category;
use App\Models\Color;
use App\Models\Coupon;
use App\Models\Currency;
use App\Models\HelpTopic;
use App\Models\MostDemanded;
use App\Models\Order;
use App\Models\Product;
use App\Models\Review;
use App\Models\RobotsMetaContent;
use App\Models\Seller;
use App\Models\ShippingType;
use App\Models\Shop;
use App\Models\StockClearanceProduct;
use App\Models\StockClearanceSetup;
use App\Models\Tag;
use App\Utils\BrandManager;
use App\Utils\ProductManager;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

trait CacheManagerTrait
{
    public function cacheBusinessSettingsTable()
    {
        return Cache::remember(CACHE_BUSINESS_SETTINGS_TABLE, CACHE_FOR_3_HOURS, function () {
            return BusinessSetting::all();
        });
    }

    public function cacheBannerTable($bannerType = null, $dataLimit = null)
    {
        $banners = Cache::remember(CACHE_BANNER_TABLE, CACHE_FOR_3_HOURS, function () {
            return Banner::with(['storage'])->where(['published' => 1, 'theme' => theme_root_path()])->orderBy('id', 'desc')->latest('created_at')->get();
        });
        if ($bannerType && $dataLimit) {
            return $banners?->where('banner_type', $bannerType)->take($dataLimit);
        } else if ($bannerType) {
            return $banners?->firstWhere('banner_type', $bannerType);
        }
        return $banners;
    }

    public function cacheCurrencyTable()
    {
        return Cache::remember(CACHE_FOR_CURRENCY_TABLE, CACHE_FOR_3_HOURS, function () {
            return Currency::all();
        });
    }

    public function cacheColorsList()
    {
        return Cache::remember(CACHE_FOR_ALL_COLOR_LIST, CACHE_FOR_3_HOURS, function () {
            return Color::all();
        });
    }

    public function cacheProductsColorsArray()
    {
        return Cache::remember(CACHE_FOR_ALL_PRODUCTS_COLOR_LIST, CACHE_FOR_3_HOURS, function () {
            return ProductManager::getProductsColorsArray();
        });
    }

    public function cacheProductsReviews()
    {
        return Cache::remember(CACHE_FOR_ALL_PRODUCTS_REVIEW_LIST, CACHE_FOR_3_HOURS, function () {
            return Review::active()->whereNull('delivery_man_id')->get();
        });
    }

    public function cacheShopTable()
    {
        $productReviews = $this->cacheProductsReviews();
        return Cache::remember(CACHE_SHOP_TABLE, CACHE_FOR_3_HOURS, function () use ($productReviews) {
            return Shop::active()
                ->withCount(['products' => function ($query) {
                    $query->active();
                }])
                ->with('seller', function ($query) {
                    $query->with('product', function ($query) {
                        $query->active()->withoutGlobalScopes();
                    })->withCount(['orders']);
                })
                ->get()
                ->each(function ($shop) use ($productReviews) {
                    $shop->orders_count = $shop->seller->orders_count;
                    $productIds = $shop->seller->product->pluck('id')->toArray();
                    $productReviews = $productReviews->whereIn('product_id', $productIds);
                    $productReviews = $productReviews->where('status', 1);
                    $shop->average_rating = $productReviews->avg('rating');
                    $shop->review_count = $productReviews->count();
                    $shop->total_rating = $productReviews->sum('rating');

                    $positiveReviewsCount = $productReviews->where('rating', '>=', 4)->count();
                    $shop->positive_review = ($shop->review_count !== 0) ? ($positiveReviewsCount * 100) / $shop->review_count : 0;
                    $currentDate = date('Y-m-d');
                    $shop->is_vacation_mode_now = $shop['vacation_status'] && ($currentDate >= $shop['vacation_start_date']) && ($currentDate <= $shop['vacation_end_date']) ? 1 : 0;
                    return $shop;
                });
        });
    }

    public function cacheHelpTopicTable()
    {
        return Cache::remember(CACHE_HELP_TOPICS_TABLE, CACHE_FOR_3_HOURS, function () {
            return HelpTopic::where(['type' => 'default', 'status' => 1])->get();
        });
    }

    public function cacheTagTable()
    {
        return Cache::remember(CACHE_TAGS_TABLE, CACHE_FOR_3_HOURS, function () {
            return Tag::orderBy('visit_count', 'desc')->take(15)->get();
        });
    }

    public function cacheInHouseShippingType()
    {
        return Cache::remember(CACHE_FOR_IN_HOUSE_SHIPPING_TYPE, CACHE_FOR_3_HOURS, function () {
            $adminShipping = ShippingType::where(['seller_id' => 0])->first();
            return isset($adminShipping) ? $adminShipping->shipping_type : 'order_wise';
        });
    }

    public function cacheMainCategoriesList()
    {
        return Cache::remember(CACHE_MAIN_CATEGORIES_LIST, CACHE_FOR_3_HOURS, function () {
            return Category::with(['product' => function ($query) {
                return $query->active()->withCount(['orderDetails']);
            }])->withCount(['product' => function ($query) {
                $query->active();
            }])->with(['childes' => function ($query) {
                $query->with(['childes' => function ($query) {
                    $query->withCount(['subSubCategoryProduct' => function ($query) {
                        $query->active();
                    }])->where('position', 2);
                }])->withCount(['subCategoryProduct' => function ($query) {
                    $query->active();
                }])->where('position', 1);
            }, 'childes.childes'])->where('position', 0)->get();
        });
    }

    public function cacheHomeCategoriesList()
    {
        return Cache::remember(CACHE_HOME_CATEGORIES_LIST, CACHE_FOR_3_HOURS, function () {
            $homeCategories = Category::whereHas('product', function ($query) {
                return $query->active();
            })->where('home_status', true)->get();

            $homeCategories->map(function ($data) {
                $current_date = date('Y-m-d H:i:s');
                $homeCategoriesProducts = Product::active()
                    ->with([
                        'flashDealProducts' => function ($query) {
                            return $query->with(['flashDeal']);
                        },
                        'clearanceSale' => function ($query) {
                            return $query->active();
                        }
                    ])
                    ->withCount('reviews')
                    ->where('category_id', $data['id']);

                $data['products'] = ProductManager::getPriorityWiseCategoryWiseProductsQuery(query: $homeCategoriesProducts, dataLimit: 12);

                $data['products']?->map(function ($product) use ($current_date) {
                    $flash_deal_status = 0;
                    if (count($product->flashDealProducts) > 0) {
                        $flash_deal = $product->flashDealProducts[0]->flashDeal;
                        if ($flash_deal) {
                            $start_date = date('Y-m-d H:i:s', strtotime($flash_deal->start_date));
                            $end_date = date('Y-m-d H:i:s', strtotime($flash_deal->end_date));
                            $flash_deal_status = $flash_deal->status == 1 && (($current_date >= $start_date) && ($current_date <= $end_date)) ? 1 : 0;
                        }
                    }
                    $product['flash_deal_status'] = $flash_deal_status;
                    return $product;
                });
            });
            return $homeCategories;
        });
    }

    public function cachePriorityWiseBrandList()
    {
        return Cache::remember(CACHE_PRIORITY_WISE_BRANDS_LIST, CACHE_FOR_3_HOURS, function () {
            return BrandManager::getActiveBrandWithCountingAndPriorityWiseSorting();
        });
    }

    public function cacheInHouseAllProducts()
    {
        return Cache::remember(CACHE_FOR_IN_HOUSE_ALL_PRODUCTS, CACHE_FOR_3_HOURS, function () {
            return Product::active()->with(['reviews', 'rating'])->withCount('reviews')->where(['added_by' => 'admin'])->get();
        });
    }

    public function cacheClearanceSaleSetupTable($shopId)
    {
        $config = null;
        $settings = Cache::remember(CACHE_CLEARANCE_SALE_SETUP_TABLE, CACHE_FOR_3_HOURS, function () {
            return StockClearanceSetup::all();
        });
        $data = $settings?->firstWhere('shop_id', $shopId);
        return $data ?? $config;
    }

    public function cacheClearanceSaleProductsCount()
    {
        return Cache::remember(CACHE_FOR_CLEARANCE_SALE_PRODUCTS_COUNT, CACHE_FOR_3_HOURS, function () {
            return StockClearanceProduct::active()->whereHas('product', function ($query) {
                return $query->active();
            })->count();
        });
    }

    public function cacheHomePageClearanceSaleProducts($dataLimit = 10)
    {
        $productIds = StockClearanceProduct::active()->whereHas('setup', function ($query) {
            $addedBy = getWebConfig(name: 'stock_clearance_vendor_offer_in_homepage') ? ['admin', 'vendor'] : ['admin'];
            return $query->where('show_in_homepage', 1)->whereIn('setup_by', $addedBy);
        })->whereHas('product', function ($query) {
            return $query->active()->with(['reviews', 'rating'])->withCount('reviews');
        })->with('product', function ($query) {
            return $query->active();
        })->pluck('product_id')->toArray();

        $products = Product::active()->with(['reviews', 'rating', 'clearanceSale' => function ($query) {
            return $query->active();
        }])->withCount('reviews')->whereIn('id', $productIds);
        return ProductManager::getPriorityWiseClearanceSaleProductsQuery(query: $products, dataLimit: $dataLimit);
    }

    public function cacheRobotsMetaContent(string $page)
    {
        $config = null;
        $settings = Cache::remember(CACHE_ROBOTS_META_CONTENT_TABLE, CACHE_FOR_3_HOURS, function () {
            return RobotsMetaContent::all();
        });
        $data = $settings?->firstWhere('page_name', $page);
        if (!$data) {
            $data = $settings?->firstWhere('page_name', 'default');
        }
        return $data ?? $config;
    }

    public function cacheHomePageTopVendorsList()
    {
        $inHouseProducts = $this->cacheInHouseAllProducts();
        $productReviews = $this->cacheProductsReviews();
        return Cache::remember(CACHE_FOR_HOME_PAGE_TOP_VENDORS_LIST, CACHE_FOR_3_HOURS, function () use ($inHouseProducts, $productReviews) {
            $topVendorsList = Shop::active()
                ->withCount(['products' => function ($query) {
                    $query->active();
                }])
                ->with(['products' => function ($query) {
                    $query->active();
                }])
                ->with('seller', function ($query) {
                    $query->with('product', function ($query) {
                        $query->active()->with('reviews', function ($query) {
                            $query->active();
                        });
                    })->with('coupon')->withCount(['orders']);
                })
                ->get()
                ->each(function ($shop) use ($productReviews) {
                    $productIds = $shop?->products?->pluck('id')->toArray() ?? [];
                    $productReviews = $productReviews->whereIn('product_id', $productIds);
                    $productReviews = $productReviews->where('status', 1);
                    $shop->average_rating = $productReviews->avg('rating');
                    $shop->review_count = $productReviews->count();
                    $shop->total_rating = $productReviews->sum('rating');

                    $shop->products = Arr::random($shop->products->toArray(), count($shop->products) < 3 ? count($shop->products) : 3);
                    $shop->orders_count = $shop->seller->orders_count;
                    $shop->coupon_list = $shop?->seller?->coupon ?? null;

                    $positiveReviewsCount = $productReviews->where('rating', '>=', 4)->count();
                    $shop->positive_review = ($shop->review_count !== 0) ? ($positiveReviewsCount * 100) / $shop->review_count : 0;

                    $currentDate = date('Y-m-d');
                    $shop->is_vacation_mode_now = $shop['vacation_status'] && ($currentDate >= $shop['vacation_start_date']) && ($currentDate <= $shop['vacation_end_date']) ? 1 : 0;
                });

            $inHouseCoupon = Coupon::where(['added_by' => 'admin', 'coupon_bearer' => 'inhouse', 'status' => 1])
                ->whereDate('start_date', '<=', date('Y-m-d'))
                ->whereDate('expire_date', '>=', date('Y-m-d'))->get();

            $inHouseProductCount = $inHouseProducts->count();

            $inHouseReviewData = Review::active()->whereIn('product_id', $inHouseProducts->pluck('id'));
            $inHouseReviewDataCount = $inHouseReviewData->count();
            $inHouseRattingStatusPositive = 0;
            foreach ($inHouseReviewData->pluck('rating') as $singleRating) {
                ($singleRating >= 4 ? ($inHouseRattingStatusPositive++) : '');
            }

            $inHouseShop = $this->getInHouseShopObject();
            $inHouseShop->id = 0;
            $inHouseShop->products_count = $inHouseProductCount;
            $inHouseShop->coupon_list = $inHouseCoupon;
            $inHouseShop->total_rating = $inHouseReviewDataCount;
            $inHouseShop->review_count = $inHouseReviewDataCount;
            $inHouseShop->average_rating = $inHouseReviewData->avg('rating');
            $inHouseShop->positive_review = $inHouseReviewDataCount != 0 ? ($inHouseRattingStatusPositive * 100) / $inHouseReviewDataCount : 0;
            $inHouseShop->orders_count = Order::where(['seller_is' => 'admin'])->count();
            $inHouseShop->products = Arr::random($inHouseProducts->toArray(), $inHouseProductCount < 3 ? $inHouseProductCount : 3);;
            return $topVendorsList->prepend($inHouseShop);
        });
    }

    public function cacheHomePageMoreVendorsList()
    {
        return Cache::remember(CACHE_FOR_HOME_PAGE_MORE_VENDORS_LIST, CACHE_FOR_3_HOURS, function () {
            return Seller::approved()->with(['shop', 'product.reviews'])
                ->withCount(['product' => function ($query) {
                    $query->active();
                }])->inRandomOrder()->take(7)->get();
        });
    }

    public function cacheHomePageJustForYouProductList()
    {
        return Cache::remember(CACHE_FOR_HOME_PAGE_JUST_FOR_YOU_PRODUCT_LIST, CACHE_FOR_3_HOURS, function () {
            return Product::active()->with(['clearanceSale' => function($query) {
                return $query->active();
            }])->inRandomOrder()->take(8)->get();
        });
    }

    public function cacheHomePageRandomSingleProductItem()
    {
        return Cache::remember(CACHE_FOR_RANDOM_SINGLE_PRODUCT, now()->addMinutes(10), function () {
            return $this->product->active()->with(['clearanceSale' =>function ($query) {
                return $query->active();
            }])->where('discount', '>', 0)->inRandomOrder()->first();
        });
    }

    public function cacheMostDemandedProductItem()
    {
        return Cache::remember(CACHE_FOR_MOST_DEMANDED_PRODUCT_ITEM, CACHE_FOR_3_HOURS, function () {
            return MostDemanded::where('status', 1)->with(['product' => function ($query) {
                $query->withCount('wishList', 'orderDetails', 'orderDelivered', 'reviews');
            }])->whereHas('product', function ($query) {
                return $query->active();
            })->first();
        });
    }

    public function cacheTopRatedProductList()
    {
        return Cache::remember(CACHE_FOR_HOME_PAGE_TOP_RATED_PRODUCT_LIST, CACHE_FOR_3_HOURS, function () {
            return Product::active()->with(['seller.shop', 'clearanceSale' =>function ($query) {
                return $query->active();
            }])
                ->whereHas('reviews', function ($query) {
                    return $query->select('product_id', DB::raw('AVG(rating) as average_rating'))->groupBy('product_id');
                })
                ->orderByDesc(DB::raw('(SELECT AVG(rating) FROM reviews WHERE reviews.product_id = products.id)'))
                ->paginate(10);
        });
    }

    public function cacheBestSellProductList()
    {
        return Cache::remember(CACHE_FOR_HOME_PAGE_BEST_SELL_PRODUCT_LIST, CACHE_FOR_3_HOURS, function () {
            return Product::active()
                ->with(['reviews', 'seller.shop', 'clearanceSale' => function ($query) {
                    return $query->active();
                }])
                ->whereHas('orderDetails', function ($query) {
                    $query->select('product_id', DB::raw('COUNT(product_id) as count'))
                        ->groupBy('product_id');
                })
                ->orderByDesc(DB::raw('(SELECT COUNT(product_id) FROM order_details WHERE order_details.product_id = products.id)'))
                ->paginate(10);
        });
    }

    public function cacheHomePageLatestProductList()
    {
        return Cache::remember(CACHE_FOR_HOME_PAGE_LATEST_PRODUCT_LIST, CACHE_FOR_3_HOURS, function () {
            $latestProductsList = Product::active()->with(['seller.shop', 'flashDealProducts.flashDeal', 'clearanceSale' => function ($query) {
                return $query->active();
            }])->orderBy('id', 'desc')->take(10)->get();
            return $this->getUpdateLatestProductWithFlashDeal(latestProducts: $latestProductsList);
        });
    }

    public function cacheBannerAllTypeKeys($cacheKey): void
    {
        $cacheKeys = Cache::get(CACHE_BANNER_ALL_CACHE_KEYS, []);
        if (!in_array($cacheKey, $cacheKeys)) {
            $cacheKeys[] = $cacheKey;
            Cache::put(CACHE_BANNER_ALL_CACHE_KEYS, $cacheKeys, CACHE_FOR_3_HOURS);
        }
    }

    public function cacheBannerForTypeMainBanner()
    {
        $themeName = theme_root_path() ?? 'default';
        $cacheKey = 'cache_banner_type_main_banner_' . ($themeName);
        $this->cacheBannerAllTypeKeys(cacheKey: $cacheKey);

        return Cache::remember($cacheKey, CACHE_FOR_3_HOURS, function () use ($themeName) {
            return Banner::where(['banner_type' => 'Main Banner', 'published' => 1, 'theme' => $themeName])->latest()->get();
        });
    }

    public function cacheBannerForTypeSidebarBanner()
    {
        $themeName = theme_root_path() ?? 'default';
        $cacheKey = 'cache_banner_type_sidebar_banner_' . ($themeName);
        $this->cacheBannerAllTypeKeys(cacheKey: $cacheKey);

        return Cache::remember($cacheKey, CACHE_FOR_3_HOURS, function () use ($themeName) {
            return Banner::where(['banner_type' => 'Sidebar Banner', 'published' => 1, 'theme' => $themeName])->latest()->first();
        });
    }

    public function cacheBannerForTypeTopSideBanner()
    {
        $themeName = theme_root_path() ?? 'default';
        $cacheKey = 'cache_banner_type_top_side_banner_' . ($themeName);
        $this->cacheBannerAllTypeKeys(cacheKey: $cacheKey);

        return Cache::remember($cacheKey, CACHE_FOR_3_HOURS, function () use ($themeName) {
            return Banner::where(['banner_type' => 'Top Side Banner', 'published' => 1, 'theme' => $themeName])->orderBy('id', 'desc')->latest()->first();
        });
    }

    public function cacheBannerForTypePromoBannerLeft()
    {
        $themeName = theme_root_path() ?? 'default';
        $cacheKey = 'cache_banner_type_top_side_banner_' . ($themeName);
        $this->cacheBannerAllTypeKeys(cacheKey: $cacheKey);

        return Cache::remember($cacheKey, CACHE_FOR_3_HOURS, function () use ($themeName) {
            return Banner::where(['banner_type' => 'Promo Banner Left', 'published' => 1, 'theme' => $themeName])->first();
        });
    }

    public function cacheBannerForTypePromoBanner($bannerType)
    {
        $themeName = theme_root_path() ?? 'default';
        $cacheKey = 'cache_banner_type_' . strtolower(str_replace(' ', '_', $bannerType)) . '_' . ($themeName);
        $this->cacheBannerAllTypeKeys(cacheKey: $cacheKey);

        return Cache::remember($cacheKey, CACHE_FOR_3_HOURS, function () use ($themeName, $bannerType) {
            return Banner::where(['banner_type' => $bannerType, 'published' => 1, 'theme' => $themeName])->first();
        });
    }

    public function cacheInHouseShopInTemporaryStatus(): void
    {
        Cache::forget(IN_HOUSE_SHOP_TEMPORARY_CLOSE_STATUS);
        $inHouseShopInTemporaryClose = Cache::get(IN_HOUSE_SHOP_TEMPORARY_CLOSE_STATUS);
        if ($inHouseShopInTemporaryClose === null) {
            $inHouseShopInTemporaryClose = getWebConfig(name: 'temporary_close');
            $inHouseShopInTemporaryClose = $inHouseShopInTemporaryClose['status'] ?? 0;
            Cache::put(IN_HOUSE_SHOP_TEMPORARY_CLOSE_STATUS, $inHouseShopInTemporaryClose, (60 * 24));
        }
    }

    public function cacheActiveAnalyticScript()
    {
        return Cache::remember(CACHE_FOR_ANALYTIC_SCRIPT_ACTIVE_LIST, CACHE_FOR_3_HOURS, function () {
            return AnalyticScript::where(['is_active' => 1])->get();
        });
    }

    private function getUpdateLatestProductWithFlashDeal($latestProducts)
    {
        $currentDate = date('Y-m-d H:i:s');
        return $latestProducts?->map(function ($product) use ($currentDate) {
            $flashDealStatus = 0;
            $flashDealEndDate = 0;
            if (count($product->flashDealProducts) > 0) {
                $flashDeal = $product->flashDealProducts[0]->flashDeal;
                if ($flashDeal) {
                    $startDate = date('Y-m-d H:i:s', strtotime($flashDeal->start_date));
                    $endDate = date('Y-m-d H:i:s', strtotime($flashDeal->end_date));
                    $flashDealStatus = $flashDeal->status == 1 && (($currentDate >= $startDate) && ($currentDate <= $endDate)) ? 1 : 0;
                    $flashDealEndDate = $flashDeal->end_date;
                }
            }
            $product['flash_deal_status'] = $flashDealStatus;
            $product['flash_deal_end_date'] = $flashDealEndDate;
            return $product;
        });
    }
}
