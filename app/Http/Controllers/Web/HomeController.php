<?php

namespace App\Http\Controllers\Web;

use App\Traits\CacheManagerTrait;
use App\Traits\EmailTemplateTrait;
use App\Traits\InHouseTrait;
use App\Utils\BrandManager;
use App\Utils\CategoryManager;
use App\Utils\Helpers;
use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\DealOfTheDay;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Product;
use App\Models\Seller;
use App\Models\Review;
use App\Utils\ProductManager;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    use InHouseTrait, EmailTemplateTrait;
    use CacheManagerTrait;

    public function __construct(
        private readonly Product      $product,
        private readonly Order        $order,
        private readonly OrderDetail  $orderDetails,
        private readonly Category     $category,
        private readonly Seller       $seller,
        private readonly Review       $review,
        private readonly DealOfTheDay $dealOfTheDay,
        private readonly Banner       $banner,
    )
    {
    }


    public function index(): View
    {
        $themeName = theme_root_path();
        return match ($themeName) {
            'default' => self::default_theme(),
            'theme_aster' => self::theme_aster(),
            'theme_fashion' => self::theme_fashion(),
        };
    }

    public function default_theme(): View
    {
        $brands = $this->cachePriorityWiseBrandList();
        $homeCategories = $this->cacheHomeCategoriesList();
        $topRatedProducts = $this->cacheTopRatedProductList();
        $latestProductsList = $this->cacheHomePageLatestProductList();
        $bestSellProduct = $this->cacheBestSellProductList();
        $recommendedProduct = $this->cacheHomePageRandomSingleProductItem();
        $bannerTypeMainBanner = $this->cacheBannerForTypeMainBanner();
        $bannerTypeMainSectionBanner = $this->cacheBannerTable(bannerType: 'Main Section Banner');
        $topVendorsList = ProductManager::getPriorityWiseTopVendorQuery($this->cacheHomePageTopVendorsList());
        $bannerTypeFooterBanner = $this->cacheBannerTable(bannerType: 'Footer Banner', dataLimit: 10);
        $clearanceSaleProducts = $this->cacheHomePageClearanceSaleProducts();

        $categories = CategoryManager::getCategoriesWithCountingAndPriorityWiseSorting();
        $userId = Auth::guard('customer')->user() ? Auth::guard('customer')->id() : 0;
        $flashDeal = ProductManager::getPriorityWiseFlashDealsProductsQuery(userId: $userId);
        $current_date = date('Y-m-d H:i:s');

        $bestSellProduct = $bestSellProduct->count() == 0 ? $latestProductsList : $bestSellProduct;
        $topRatedProducts = $topRatedProducts->count() == 0 ? $bestSellProduct : $topRatedProducts;

        $featuredProductsList = ProductManager::getPriorityWiseFeaturedProductsQuery(query: $this->product->active()->with(['clearanceSale' => function($query) {
            return $query->active();
        }]), dataLimit: 12);
        $newArrivalProducts = ProductManager::getPriorityWiseNewArrivalProductsQuery(query: $this->product->active()->with(['clearanceSale' => function($query) {
            return $query->active();
        }]), dataLimit: 8);

        $dealOfTheDay = DealOfTheDay::with(['product' => function($query) {
                            return $query->active()->with(['clearanceSale' => function($query) {
                                return $query->active();
                            }]);
                        }])
                        ->join('products', 'products.id', '=', 'deal_of_the_days.product_id')
                        ->select('deal_of_the_days.*', 'products.unit_price')
                        ->where('products.status', 1)
                        ->where('deal_of_the_days.status', 1)
                        ->first();
        return view(VIEW_FILE_NAMES['home'],
            compact(
                'flashDeal', 'featuredProductsList', 'topRatedProducts', 'bestSellProduct', 'latestProductsList', 'categories', 'brands',
                'dealOfTheDay', 'topVendorsList', 'homeCategories', 'bannerTypeMainBanner', 'bannerTypeMainSectionBanner',
                'current_date', 'recommendedProduct', 'bannerTypeFooterBanner', 'newArrivalProducts', 'clearanceSaleProducts'
            )
        );
    }

    public function theme_aster(): View
    {
        $moreVendors = $this->cacheHomePageMoreVendorsList();
        $homeCategories = $this->cacheHomeCategoriesList();
        $latestProductsList = $this->cacheHomePageLatestProductList();
        $randomSingleProduct = $this->cacheHomePageRandomSingleProductItem();
        $topVendorsList = ProductManager::getPriorityWiseTopVendorQuery(query: $this->cacheHomePageTopVendorsList());
        $clearanceSaleProducts = $this->cacheHomePageClearanceSaleProducts();

        $categories = CategoryManager::getCategoriesWithCountingAndPriorityWiseSorting(dataLimit: 11);
        $userId = Auth::guard('customer')->user() ? Auth::guard('customer')->id() : 0;
        $flashDeal = ProductManager::getPriorityWiseFlashDealsProductsQuery(userId: $userId);
        $current_date = date('Y-m-d H:i:s');

        $findWhatYouNeedCategoriesData = Cache::remember(FIND_WHAT_YOU_NEED_CATEGORIES_LIST, CACHE_FOR_3_HOURS, function () {
            return $this->category->where('parent_id', 0)
                ->with(['childes' => function ($query) {
                    $query->with(['subCategoryProduct' => function ($query) {
                        return $query->active();
                    }]);
                }])
                ->with(['product' => function ($query) {
                    return $query->active()->withCount(['orderDetails']);
                }])
                ->get();
        });

        $findWhatYouNeedCategoriesData = CategoryManager::getPriorityWiseCategorySortQuery(query: $findWhatYouNeedCategoriesData);

        $findWhatYouNeedCategoriesData->map(function ($category) {
            $category->product_count = $category->product->count();
            unset($category->product);
            $category->childes?->map(function ($sub_category) {
                $sub_category->subCategoryProduct_count = $sub_category->subCategoryProduct->count();
                unset($sub_category->subCategoryProduct);
            });
            return $category;
        });
        $findWhatYouNeedCategories = $findWhatYouNeedCategoriesData->toArray();

        $getCategories = [];
        foreach ($findWhatYouNeedCategories as $category) {
            $slice = array_slice($category['childes'], 0, 4);
            $category['childes'] = $slice;
            $getCategories[] = $category;
        }

        $final_category = [];
        foreach ($getCategories as $category) {
            if (count($category['childes']) > 0) {
                $final_category[] = $category;
            }
        }
        $category_slider = array_chunk($final_category, 4);

        $featuredProductsList = $this->product->active()->with(['seller.shop', 'flashDealProducts.flashDeal', 'clearanceSale' => function ($query) {
                    return $query->active();
                }])
            ->where('featured', 1)
            ->withCount(['orderDetails']);
        $featuredProductsList = ProductManager::getPriorityWiseFeaturedProductsQuery(query: $featuredProductsList, dataLimit: 10);

        $featuredProductsList?->map(function ($product) use ($current_date) {
            $flashDealStatus = 0;
            $flash_deal_end_date = 0;
            if (count($product->flashDealProducts) > 0) {
                $flash_deal = $product->flashDealProducts[0]->flashDeal;
                if ($flash_deal) {
                    $start_date = date('Y-m-d H:i:s', strtotime($flash_deal->start_date));
                    $end_date = date('Y-m-d H:i:s', strtotime($flash_deal->end_date));
                    $flashDealStatus = $flash_deal->status == 1 && (($current_date >= $start_date) && ($current_date <= $end_date)) ? 1 : 0;
                    $flash_deal_end_date = $flash_deal->end_date;
                }
            }
            $product['flash_deal_status'] = $flashDealStatus;
            $product['flash_deal_end_date'] = $flash_deal_end_date;
            return $product;
        });

        $bestSellProduct = Product::active()->with([
            'reviews', 'rating', 'seller.shop',
            'flashDealProducts.flashDeal',
        ])->withCount(['reviews']);

        $orderDetails = Cache::remember(CACHE_ORDER_DETAILS_TABLE, CACHE_FOR_3_HOURS, function () {
            return OrderDetail::with('product')
                ->select('product_id', DB::raw('COUNT(product_id) as count'))
                ->groupBy('product_id')
                ->get();
        });

        $getOrderedProductIds = [];
        foreach ($orderDetails as $detail) {
            $getOrderedProductIds[] = $detail['product_id'];
        }
        $bestSellProduct = ProductManager::getPriorityWiseBestSellingProductsQuery(query: $bestSellProduct->whereIn('id', $getOrderedProductIds), dataLimit: 10);

        $bestSellProduct?->map(function ($product) use ($current_date) {
            $flashDealStatus = 0;
            $flashDealEndDate = 0;
            if (isset($product->flashDealProducts) && count($product->flashDealProducts) > 0) {
                $flashDeal = $product->flashDealProducts[0]->flashDeal;
                if ($flashDeal) {
                    $startDate = date('Y-m-d H:i:s', strtotime($flashDeal->start_date));
                    $endDate = date('Y-m-d H:i:s', strtotime($flashDeal->end_date));
                    $flashDealStatus = $flashDeal->status == 1 && (($current_date >= $startDate) && ($current_date <= $endDate)) ? 1 : 0;
                    $flashDealEndDate = $flashDeal->end_date;
                }
            }
            $product['flash_deal_status'] = $flashDealStatus;
            $product['flash_deal_end_date'] = $flashDealEndDate;
            return $product;
        });

        $justForYouProducts = $this->cacheHomePageJustForYouProductList();

        if (auth('customer')->check()) {
            $orders = $this->order->where(['customer_id' => auth('customer')->id()])->with(['details'])->get();

            if ($orders) {
                $orders = $orders?->map(function ($order) {
                    $orderDetails = $order->details->map(function ($detail) {
                        $product = json_decode($detail->product_details);
                        $category = json_decode($product->category_ids)[0]->id;
                        $detail['category_id'] = $category;
                        return $detail;
                    });
                    try {
                        $order['id'] = $orderDetails[0]->id;
                        $order['category_id'] = $orderDetails[0]->category_id;
                    } catch (\Throwable $th) {

                    }

                    return $order;
                });

                $orderCategories = [];
                foreach ($orders as $order) {
                    $orderCategories[] = ($order['category_id']);
                }
                $ids = array_unique($orderCategories);


                $justForYouProducts = $this->product->active()
                    ->where(function ($query) use ($ids) {
                        foreach ($ids as $id) {
                            $query->orWhere('category_ids', 'like', "%{$id}%");
                        }
                    })->inRandomOrder()->take(8)->get();
            }
        }

        $topRatedProducts = $this->cacheTopRatedProductList();

        if ($bestSellProduct->count() == 0) {
            $bestSellProduct = $latestProductsList;
        }

        if ($topRatedProducts->count() == 0) {
            $topRatedProducts = $bestSellProduct;
        }
        $dealOfTheDay = $this->dealOfTheDay->with(['product' => function($query) {
            return $query->active()->with(['clearanceSale' => function($query) {
                return $query->active();
            }]);
        }])
        ->join('products', 'products.id', '=', 'deal_of_the_days.product_id')
        ->select('deal_of_the_days.*', 'products.unit_price')
        ->where('products.status', 1)
        ->where('deal_of_the_days.status', 1)
        ->first();
        $recommendedProduct = $this->cacheHomePageRandomSingleProductItem();

        $banners = $this->cacheBannerTable();
        $bannerTypeMainBanner = [];
        $bannerTypeFooterBanner = [];
        $bannerTypeSidebarBanner = [];
        $bannerTypeMainSectionBanner = [];
        $bannerTypeTopSideBanner = [];

        foreach ($banners as $banner) {
            $banner['photo_full_url'] = $banner->photo_full_url;
            if ($banner->banner_type == 'Main Banner') {
                $bannerTypeMainBanner[] = $banner;
            } elseif ($banner->banner_type == 'Footer Banner') {
                $bannerTypeFooterBanner[] = $banner->toArray();
            } elseif ($banner->banner_type == 'Sidebar Banner') {
                $bannerTypeSidebarBanner[] = $banner;
            } elseif ($banner->banner_type == 'Main Section Banner') {
                $bannerTypeMainSectionBanner[] = $banner;
            } elseif ($banner->banner_type == 'Top Side Banner') {
                $bannerTypeTopSideBanner[] = $banner;
            }
        }

        $bannerTypeSidebarBanner = $bannerTypeSidebarBanner ? $bannerTypeSidebarBanner[0] : [];
        $bannerTypeMainSectionBanner = $bannerTypeMainSectionBanner ? $bannerTypeMainSectionBanner[0] : [];
        $bannerTypeTopSideBanner = $bannerTypeTopSideBanner ? $bannerTypeTopSideBanner[0] : [];
        $bannerTypeFooterBanner = $bannerTypeFooterBanner ? array_slice($bannerTypeFooterBanner, 0, 2) : [];

        $decimal_point = getWebConfig(name: 'decimal_point_settings');
        $decimal_point_settings = !empty($decimal_point) ? $decimal_point : 0;
        $user = Helpers::getCustomerInformation();

        $order_again = $user != 'offline' ?
            $this->order->with('details.product')->where(['order_status' => 'delivered', 'customer_id' => $user->id])->latest()->take(8)->get()
            : [];

        $random_coupon = Coupon::with('seller')
            ->where(['status' => 1])
            ->whereDate('start_date', '<=', date('Y-m-d'))
            ->whereDate('expire_date', '>=', date('Y-m-d'))
            ->inRandomOrder()->take(3)->get();

        $topVendorsListSectionShowingStatus = false;
        foreach ($topVendorsList as $vendorList) {
            if ($vendorList->products_count > 0) {
                $topVendorsListSectionShowingStatus = true;
                break;
            }
        }

        return view(VIEW_FILE_NAMES['home'],
            compact(
                'flashDeal', 'topRatedProducts', 'bestSellProduct', 'latestProductsList', 'featuredProductsList', 'dealOfTheDay', 'topVendorsList',
                'homeCategories', 'bannerTypeMainBanner', 'bannerTypeFooterBanner', 'randomSingleProduct', 'decimal_point_settings', 'justForYouProducts', 'moreVendors',
                'final_category', 'category_slider', 'order_again', 'bannerTypeSidebarBanner', 'bannerTypeMainSectionBanner', 'random_coupon', 'bannerTypeTopSideBanner',
                'categories', 'topVendorsListSectionShowingStatus', 'clearanceSaleProducts', 'recommendedProduct'
            )
        );
    }

    public function theme_fashion(): View
    {
        $singlePageProductCount = 20;
        $currentDate = date('Y-m-d H:i:s');
        $user = Helpers::getCustomerInformation();
        $activeBrands = BrandManager::getActiveBrandWithCountingAndPriorityWiseSorting();
        $categories = CategoryManager::getCategoriesWithCountingAndPriorityWiseSorting();
        $userId = Auth::guard('customer')->user() ? Auth::guard('customer')->id() : 0;
        $flashDeal = ProductManager::getPriorityWiseFlashDealsProductsQuery(userId: $userId);
        $mostVisitedCategories = CategoryManager::getCategoriesWithCountingAndPriorityWiseSorting();
        $topVendorsList = ProductManager::getPriorityWiseTopVendorQuery(query: $this->cacheHomePageTopVendorsList());
        $mostDemandedProducts = $this->cacheMostDemandedProductItem();
        $bannerTypeMainBanner = $this->cacheBannerForTypeMainBanner();
        $bannerTypePromoBannerLeft = $this->cacheBannerTable(bannerType: 'Promo Banner Left');
        $bannerTypePromoBannerMiddleTop = $this->cacheBannerTable(bannerType: 'Promo Banner Middle Top');
        $bannerTypePromoBannerMiddleBottom = $this->cacheBannerTable(bannerType: 'Promo Banner Middle Bottom');
        $bannerTypePromoBannerRight = $this->cacheBannerTable(bannerType: 'Promo Banner Right');
        $bannerTypePromoBannerBottom = $this->cacheBannerTable(bannerType: 'Promo Banner Bottom');
        $bannerTypeSidebarBanner = $this->cacheBannerTable(bannerType: 'Sidebar Banner');
        $bannerTypeTopSideBanner = $this->cacheBannerTable(bannerType: 'Top Side Banner');
        $latestProductsList = $this->cacheHomePageLatestProductList();
        $randomSingleProduct = $this->cacheHomePageRandomSingleProductItem();
        $allProductsColorList = $this->cacheProductsColorsArray();
        $clearanceSaleProducts = $this->cacheHomePageClearanceSaleProducts();
        $recommendedProduct = $this->cacheHomePageRandomSingleProductItem();

        $featuredProductsList = Cache::remember(CACHE_FOR_FEATURED_PRODUCTS_LIST, CACHE_FOR_3_HOURS, function () {
            $featuredProductsList = $this->product->with(['clearanceSale' => function ($query) {
                $query->active();
            }])
            ->active()
            ->where('featured', 1)
            ->withCount(['reviews']);
            return ProductManager::getPriorityWiseFeaturedProductsQuery(query: $featuredProductsList, dataLimit: 15);
        });

        $mostSearchingProducts = Cache::remember(CACHE_FOR_MOST_SEARCHING_PRODUCTS_LIST, CACHE_FOR_3_HOURS, function () {
            return Product::active()->with(['category', 'clearanceSale' => function ($query) {
                return $query->active();
            }])
            ->withCount('reviews')
            ->withSum('tags', 'visit_count')->orderBy('tags_sum_visit_count', 'desc')->get()->take(10);
        });

        $dealOfTheDay = $this->dealOfTheDay->with(['product' => function ($query) {
            $query->active()->with(['clearanceSale' => function ($query) {
                $query->active();
            }]);
        }, 'product.clearanceSale' => function ($query) {
            $query->active();
        }])
        ->where('status', 1)
        ->first();

        $vendorList = $this->cacheShopTable();
        $newSellers = $vendorList->sortByDesc('id')->take(12);
        $topRatedShops = $vendorList->where('review_count', '!=', 0)->sortByDesc('average_rating')->take(12);

        $baseProductQuery = $this->product->with(['category', 'compareList', 'reviews', 'flashDealProducts.flashDeal', 'clearanceSale' => function ($query) {
                    $query->active();
            }])
            ->withSum('orderDetails', 'qty')
            ->active();

        $allProductsList = $baseProductQuery->orderBy('order_details_sum_qty', 'DESC')->paginate(20);
        $allProductsList?->map(function ($product) use ($currentDate) {
            $flashDealStatus = 0;
            $flashDealEndDate = 0;
            if (count($product->flashDealProducts) > 0) {
                $flash_deal = $product->flashDealProducts[0]->flashDeal;
                if ($flash_deal) {
                    $start_date = date('Y-m-d H:i:s', strtotime($flash_deal->start_date));
                    $end_date = date('Y-m-d H:i:s', strtotime($flash_deal->end_date));
                    $flashDealStatus = $flash_deal->status == 1 && (($currentDate >= $start_date) && ($currentDate <= $end_date)) ? 1 : 0;
                    $flashDealEndDate = $flash_deal->end_date;
                }
            }
            $product['flash_deal_status'] = $flashDealStatus;
            $product['flash_deal_end_date'] = $flashDealEndDate;
            return $product;
        });

        $recentOrderShopList = [];
        if ($user != 'offline') {
            $recentOrderShopList = $this->product->with('seller.shop')
                ->whereHas('seller.orders', function ($query) {
                    $query->where(['customer_id' => auth('customer')->id(), 'seller_is' => 'seller']);
                })
                ->active()
                ->inRandomOrder()->take(12)->get();
        }

        $allProductSectionOrders = $this->order->where(['order_type' => 'default_type'])->whereHas('orderDetails', function ($query) {
            return $query->whereHas('product', function ($query) {
                return $query->active();
            });
        });

        $allProductsGroupInfo = [
            'total_products' => $this->product->active()->count(),
            'total_orders' => $allProductSectionOrders->count(),
            'total_delivery' => $allProductSectionOrders->where(['payment_status' => 'paid', 'order_status' => 'delivered'])->count(),
            'total_reviews' => $this->review->active()->where('product_id', '!=', 0)->whereHas('product', function ($query) {
                return $query->active();
            })->whereNull('delivery_man_id')->count(),
        ];

        $data = [];
        return view(VIEW_FILE_NAMES['home'],
            compact(
                'activeBrands', 'latestProductsList', 'dealOfTheDay', 'topVendorsList', 'topRatedShops', 'bannerTypeMainBanner', 'mostVisitedCategories', 'randomSingleProduct', 'newSellers', 'bannerTypeSidebarBanner', 'bannerTypeTopSideBanner', 'recentOrderShopList',
                'categories', 'allProductsColorList', 'allProductsGroupInfo', 'mostSearchingProducts', 'mostDemandedProducts', 'featuredProductsList', 'bannerTypePromoBannerLeft', 'bannerTypePromoBannerMiddleTop', 'bannerTypePromoBannerMiddleBottom', 'bannerTypePromoBannerRight', 'bannerTypePromoBannerBottom', 'currentDate', 'allProductsList', 'flashDeal', 'data', 'clearanceSaleProducts', 'singlePageProductCount', 'recommendedProduct'
            )
        );
    }
}
