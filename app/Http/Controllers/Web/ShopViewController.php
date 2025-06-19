<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Author;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\PublishingHouse;
use App\Models\Review;
use App\Models\Seller;
use App\Models\Shop;
use App\Models\StockClearanceProduct;
use App\Models\StockClearanceSetup;
use App\Utils\CartManager;
use App\Utils\CategoryManager;
use App\Utils\ProductManager;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Auth;

class ShopViewController extends Controller
{
    public function getShopInfoArray($shopId, $shopProducts, $sellerType, $sellerId): array
    {
        $totalOrder = Order::when($sellerType == 'admin', function ($query) {
            return $query->where(['seller_is' => 'admin']);
        })->when($sellerType == 'seller', function ($query) use ($sellerId) {
            return $query->where(['seller_is' => 'seller', 'seller_id' => $sellerId]);
        })->where('order_type', 'default_type')->count();

        $inhouseVacation = getWebConfig(name: 'vacation_add');
        $temporaryClose = getWebConfig(name: 'temporary_close');

        if ($shopId == 0) {
            $shop = ['id' => 0, 'name' => getWebConfig(name: 'company_name')];
        } else {
            $shop = Shop::where('id', $shopId)->first();
        }

        $getProductIDs = $shopProducts->pluck('id')->toArray();
        return [
            'id' => $shopId,
            'name' => $shopId == 0 ? getWebConfig(name: 'company_name') : Shop::where('id', $shopId)->first()->name,
            'seller_id' => $shopId == 0 ? 0 : $shop?->seller_id,
            'average_rating' => Review::active()->where('status', 1)->whereIn('product_id', $getProductIDs)->avg('rating'),
            'total_review' => Review::active()->where('status', 1)->whereIn('product_id', $getProductIDs)->count(),
            'total_order' => $totalOrder,
            'current_date' => date('Y-m-d'),
            'vacation_start_date' => $shopId == 0 ? $inhouseVacation['vacation_start_date'] : date('Y-m-d', strtotime($shop->vacation_start_date)),
            'vacation_end_date' => $shopId == 0 ? $inhouseVacation['vacation_end_date'] : date('Y-m-d', strtotime($shop->vacation_end_date)),
            'temporary_close' => $shopId == 0 ? $temporaryClose['status'] : $shop->temporary_close,
            'vacation_status' => $shopId == 0 ? $inhouseVacation['status'] : $shop->vacation_status,
            'banner_full_url' => $shopId == 0 ? $inhouseVacation['status'] : $shop->banner_full_url,
            'bottom_banner' => $shopId == 0 ? getWebConfig(name: 'bottom_banner') : $shop->bottom_banner,
            'bottom_banner_full_url' => $shopId == 0 ? getWebConfig(name: 'bottom_banner') : $shop->bottom_banner_full_url,
            'image_full_url' => $shopId == 0 ? $inhouseVacation['status'] : $shop->image_full_url,
            'minimum_order_amount' => $shopId == 0 ? getWebConfig(name: 'minimum_order_amount') : $shop->seller->minimum_order_amount,
        ];
    }

    public function seller_shop(Request $request, $id): View|JsonResponse|Redirector|RedirectResponse
    {
        $themeName = theme_root_path();

        return match ($themeName) {
            'default' => self::default_theme($request, $id),
            'theme_aster' => self::theme_aster($request, $id),
            'theme_fashion' => self::theme_fashion($request, $id),
        };
    }

    public function default_theme($request, $id): View|JsonResponse|Redirector|RedirectResponse
    {
        self::checkShopExistence($id);
        $productAddedBy = $id == 0 ? 'admin' : 'seller';
        $productUserID = $id == 0 ? $id : Shop::where('id', $id)->first()->seller_id;
        $shopAllProducts = ProductManager::getAllProductsData($request, $productUserID, $productAddedBy);
        $productListData = ProductManager::getProductListData($request, $productUserID, $productAddedBy);
        $categories = self::getShopCategoriesList(products: $shopAllProducts);
        $brands = self::getShopBrandsList(request: $request, products: $shopAllProducts, sellerType: $productAddedBy, sellerId: $productUserID);
        $shopPublishingHouses = ProductManager::getPublishingHouseList(productIds: $shopAllProducts->pluck('id')->toArray(), vendorId: $productUserID);
        $digitalProductAuthors = ProductManager::getProductAuthorList(productIds: $shopAllProducts->pluck('id')->toArray(), vendorId: $productUserID);
        $shopInfoArray = self::getShopInfoArray(shopId: $id, shopProducts: $shopAllProducts, sellerType: $productAddedBy, sellerId: $productUserID);
        $products = $productListData->paginate(20)->appends($request->all());
        $stockClearanceProducts = StockClearanceProduct::active()->where(['shop_id'=> $id])->count();
        $stockClearanceSetup = StockClearanceSetup::where(['shop_id'=> $id])->first()?->is_active ?? 0;

        if ($request->ajax()) {
            return response()->json([
                'html_products' => view(VIEW_FILE_NAMES['products__ajax_partials'], compact('products', 'categories'))->render()
            ], 200);
        }
        $data = self::getProductListRequestData(request: $request);

        return view(VIEW_FILE_NAMES['shop_view_page'], [
            'products' => $products,
            'categories' => $categories,
            'seller_id' => $id,
            'activeBrands'=> $brands,
            'shopInfoArray'=> $shopInfoArray,
            'shopPublishingHouses' => $shopPublishingHouses,
            'digitalProductAuthors' => $digitalProductAuthors,
            'stockClearanceProducts' => $stockClearanceProducts,
            'stockClearanceSetup' => $stockClearanceSetup,
            'data' => $data,
        ]);
    }

    public function theme_aster($request, $id): View|JsonResponse|Redirector|RedirectResponse
    {
        self::checkShopExistence($id);
        $productAddedBy = $id == 0 ? 'admin' : 'seller';
        $productUserID = $id == 0 ? $id : Shop::where('id', $id)->first()->seller_id;
        $shopAllProducts = ProductManager::getAllProductsData($request, $productUserID, $productAddedBy);
        $productListData = ProductManager::getProductListData($request, $productUserID, $productAddedBy);
        $categories = self::getShopCategoriesList(products: $shopAllProducts);
        $activeBrands = self::getShopBrandsList(request: $request,products: $shopAllProducts, sellerType: $productAddedBy, sellerId: $productUserID);
        $shopPublishingHouses = ProductManager::getPublishingHouseList(productIds: $shopAllProducts->pluck('id')->toArray(), vendorId: $productUserID);
        $digitalProductAuthors = ProductManager::getProductAuthorList(productIds: $shopAllProducts->pluck('id')->toArray(), vendorId: $productUserID);
        $shopInfoArray = self::getShopInfoArray(shopId: $id, shopProducts: $shopAllProducts, sellerType: $productAddedBy, sellerId: $productUserID);
        $singlePageProductCount = 20;
        $ratings = [
            'rating_1' => 0,
            'rating_2' => 0,
            'rating_3' => 0,
            'rating_4' => 0,
            'rating_5' => 0,
        ];

        foreach ($shopAllProducts as $product) {
            if (isset($product->rating[0]['average'])) {
                $average = $product->rating[0]['average'];
                if ($average > 0 && $average < 2) {
                    $ratings['rating_1']++;
                } elseif ($average >= 2 && $average < 3) {
                    $ratings['rating_2']++;
                } elseif ($average >= 3 && $average < 4) {
                    $ratings['rating_3']++;
                } elseif ($average >= 4 && $average < 5) {
                    $ratings['rating_4']++;
                } elseif ($average == 5) {
                    $ratings['rating_5']++;
                }
            }
        }

        $reviewData = Review::active()->whereIn('product_id', $shopAllProducts->pluck('id')->toArray());
        $averageRating = $reviewData->avg('rating');
        $totalReviews = $reviewData->count();

        $rattingStatusPositive = 0;
        $rattingStatusGood = 0;
        $rattingStatusNeutral = 0;
        $rattingStatusNegative = 0;
        foreach ($reviewData->pluck('rating') as $singleRating) {
            ($singleRating >= 4 ? ($rattingStatusPositive++) : '');
            ($singleRating == 3 ? ($rattingStatusGood++) : '');
            ($singleRating == 2 ? ($rattingStatusNeutral++) : '');
            ($singleRating == 1 ? ($rattingStatusNegative++) : '');
        }
        $rattingStatusArray = [
            'positive' => $totalReviews != 0 ? ($rattingStatusPositive * 100) / $totalReviews : 0,
            'good' => $totalReviews != 0 ? ($rattingStatusGood * 100) / $totalReviews : 0,
            'neutral' => $totalReviews != 0 ? ($rattingStatusNeutral * 100) / $totalReviews : 0,
            'negative' => $totalReviews != 0 ? ($rattingStatusNegative * 100) / $totalReviews : 0,
        ];

        $featuredProductQuery = Product::active()->with([
            'seller.shop',
            'wishList' => function ($query) {
                return $query->where('customer_id', Auth::guard('customer')->user()->id ?? 0);
            },
            'compareList' => function ($query) {
                return $query->where('user_id', Auth::guard('customer')->user()->id ?? 0);
            }
        ])->when($id == 0, function ($query) {
            return $query->where(['added_by' => 'admin']);
        })->when($id != 0, function ($query) use ($id) {
            $seller = Seller::find($id);
            return $query->where(['added_by' => 'seller', 'user_id' => $seller->id]);
        });

        if ($id == 0) {
            $totalOrder = Order::where('seller_is', 'admin')->where('order_type', 'default_type')->count();
            $products_for_review = Product::active()->where('added_by', 'admin')->withCount('reviews')->count();
        } else {
            $seller = Seller::find($id);
            $totalOrder = $seller->orders->where('seller_is', 'seller')->where('order_type', 'default_type')->count();
            $products_for_review = Product::active()->where('added_by', 'seller')->where('user_id', $seller->id)->withCount('reviews')->count();
        }

        $featuredProductsList = ProductManager::getPriorityWiseFeaturedProductsQuery(query: $featuredProductQuery, dataLimit: 'all');
        $products = $productListData->paginate(20)->appends($request->all());
        $getProductIds = $products->pluck('id')->toArray();
        $stockClearanceProducts = StockClearanceProduct::active()->where('shop_id', $id)->count();
        $stockClearanceSetup = StockClearanceSetup::where(['shop_id'=> $id])->first()?->is_active ?? 0;

        $data = self::getProductListRequestData(request: $request);

        if ($request->ajax()) {
            return response()->json([
                'total_product' => $products->total(),
                'html_products' => view(VIEW_FILE_NAMES['products__ajax_partials'], [
                    'products' => $products,
                    'product_ids' => $getProductIds,
                    'singlePageProductCount' => $singlePageProductCount,
                    'page' => $request['page'] ?? 1,
                ])->render(),
            ], 200);
        }

        return view(VIEW_FILE_NAMES['shop_view_page'], [
            'products' => $products,
            'categories' => $categories,
            'products_for_review' => $products_for_review,
            'featuredProductsList' => $featuredProductsList,
            'activeBrands' => $activeBrands,
            'selectedRatings' => $request['rating'] ?? [],
            'data' => $data,
            'ratings' => $ratings,
            'rattingStatusArray' => $rattingStatusArray,
            'stockClearanceProducts' => $stockClearanceProducts,
            'stockClearanceSetup' => $stockClearanceSetup,
            'seller_id' => $id,
            'total_review' => $totalReviews,
            'avg_rating' => $averageRating,
            'shopInfoArray' => $shopInfoArray,
            'shopPublishingHouses' => $shopPublishingHouses,
            'digitalProductAuthors' => $digitalProductAuthors,
            'singlePageProductCount' => $singlePageProductCount,
            'page' => $request['page'] ?? 1,
            'total_order' => $totalOrder
        ]);
    }

    public function theme_fashion($request, $id): View|JsonResponse|Redirector|RedirectResponse
    {
        $singlePageProductCount = $request['per_page_product'] ?? 25;
        self::checkShopExistence($id);
        $productAddedBy = $id == 0 ? 'admin' : 'seller';
        $productUserID = $id == 0 ? $id : Shop::where('id', $id)->first()->seller_id;
        $productListData = ProductManager::getProductListData($request, $productUserID, $productAddedBy);
        $categories = self::getShopCategoriesList(products: $productListData);
        $brands = self::getShopBrandsList(request: $request,products: $productListData, sellerType: $productAddedBy, sellerId: $productUserID);
        $shopPublishingHouses = ProductManager::getPublishingHouseList(productIds: $productListData->pluck('id')->toArray(), vendorId: $productUserID);
        $digitalProductAuthors = ProductManager::getProductAuthorList(productIds: $productListData->pluck('id')->toArray(), vendorId: $productUserID);
        $shopInfoArray = self::getShopInfoArray(shopId: $id, shopProducts: $productListData, sellerType: $productAddedBy, sellerId: $productUserID);

        $id = $id != 0 ? Shop::where('id', $id)->first()->seller_id : $id;

        $product_ids = Product::active()
            ->when($id == 0, function ($query) {
                return $query->where(['added_by' => 'admin']);
            })
            ->when($id != 0, function ($query) use ($id) {
                return $query->where(['added_by' => 'seller', 'user_id' => $id]);
            })
            ->pluck('id')->toArray();
        $reviewData = Review::active()->whereIn('product_id', $product_ids)->latest();
        $averageRating = $reviewData->avg('rating');
        $totalReviews = $reviewData->count();

        $rattingStatusPositive = 0;
        $rattingStatusGood = 0;
        $rattingStatusNeutral = 0;
        $rattingStatusNegative = 0;
        foreach ($reviewData->pluck('rating') as $singleRating) {
            ($singleRating >= 4 ? ($rattingStatusPositive++) : '');
            ($singleRating == 3 ? ($rattingStatusGood++) : '');
            ($singleRating == 2 ? ($rattingStatusNeutral++) : '');
            ($singleRating == 1 ? ($rattingStatusNegative++) : '');
        }
        $rattingStatusArray = [
            'positive' => $totalReviews != 0 ? ($rattingStatusPositive * 100) / $totalReviews : 0,
            'good' => $totalReviews != 0 ? ($rattingStatusGood * 100) / $totalReviews : 0,
            'neutral' => $totalReviews != 0 ? ($rattingStatusNeutral * 100) / $totalReviews : 0,
            'negative' => $totalReviews != 0 ? ($rattingStatusNegative * 100) / $totalReviews : 0,
        ];

        $reviews = $reviewData->take(4)->get();

        $allProductsColorList = ProductManager::getProductsColorsArray(productIds: $product_ids);

        $featuredProductQuery = Product::active()->with([
            'seller.shop',
            'wishList' => function ($query) {
                return $query->where('customer_id', Auth::guard('customer')->user()->id ?? 0);
            },
            'compareList' => function ($query) {
                return $query->where('user_id', Auth::guard('customer')->user()->id ?? 0);
            }
        ]);

        if ($id == 0) {
            $total_order = Order::where('seller_is', 'admin')->where('order_type', 'default_type')->count();
            $products_for_review = Product::active()->where('added_by', 'admin')->withCount('reviews')->count();
            $featuredProductsList = $featuredProductQuery->where(['added_by' => 'admin']);
        } else {
            $seller = Seller::find($id);
            $total_order = $seller->orders->where('seller_is', 'seller')->where('order_type', 'default_type')->count();
            $products_for_review = Product::active()->where('added_by', 'seller')->where('user_id', $seller->id)->withCount('reviews')->count();
            $featuredProductsList = $featuredProductQuery->where(['added_by' => 'seller', 'user_id' => $seller->id]);
        }

        $featuredProductsList = ProductManager::getPriorityWiseFeaturedProductsQuery(query: $featuredProductsList, dataLimit: 'all');

        $products = Product::active()
            ->when($id == 0, function ($query) {
                return $query->where(['added_by' => 'admin']);
            })
            ->when($request['offer_type'] == 'clearance_sale', function ($query) {
                $stockClearanceProductIds = StockClearanceProduct::active()->pluck('product_id')->toArray();
                return $query->whereIn('id', $stockClearanceProductIds);
            })
            ->when($request['offer_type'] == 'clearance_sale' && $productUserID && $productAddedBy == 'seller', function ($query) use ($productUserID, $productAddedBy) {
                $stockClearanceProductIds = StockClearanceProduct::active()->pluck('product_id')->toArray();
                return $query->where(['added_by' => $productAddedBy, 'user_id' => $productUserID])->whereIn('id', $stockClearanceProductIds);
            })
            ->when($id != 0, function ($query) use ($id) {
                return $query->where(['added_by' => 'seller'])
                    ->where('user_id', $id);
            })->with(['wishList' => function ($query) {
                return $query->where('customer_id', Auth::guard('customer')->user()->id ?? 0);
            }, 'compareList' => function ($query) {
                return $query->where('user_id', Auth::guard('customer')->user()->id ?? 0);
            }])->withSum('orderDetails', 'qty', function ($query) {
                $query->where('delivery_status', 'delivered');
            })
            ->withCount('reviews')
            ->get();

        $categoriesIdArray = [];
        foreach ($products as $product) {
            $categoriesIdArray[] = $product['category_id'];
        }

        $categories = Category::with(['product' => function ($query) {
                return $query->active()->withCount(['orderDetails']);
            }])
            ->withCount(['product' => function ($query) use ($id, $request, $productUserID, $productAddedBy) {
                return $query->when($id == 0, function ($query) {
                    return $query->where(['added_by' => 'admin', 'status' => '1']);
                })->when($id != 0, function ($query) use ($id) {
                    return $query->where(['added_by' => 'seller', 'user_id' => $id, 'status' => '1']);
                })->when(request('offer_type') == 'clearance_sale', function ($query) {
                    return $query->whereHas('clearanceSale', function ($query) {
                        return $query->active();
                    });
                });
            }])
            ->with(['childes' => function ($query) use ($id, $request, $productUserID, $productAddedBy) {
                $query->with(['childes' => function ($query) use ($id, $request, $productUserID, $productAddedBy) {
                    return $query->withCount(['subSubCategoryProduct' => function ($query) use ($id, $request, $productUserID, $productAddedBy) {
                        return $query->when($id == 0, function ($query) {
                            return $query->where(['added_by' => 'admin', 'status' => '1']);
                        })->when($id != 0, function ($query) use ($id) {
                            return $query->where(['added_by' => 'seller', 'user_id' => $id, 'status' => '1']);
                        })->when(request('offer_type') == 'clearance_sale', function ($query) {
                            return $query->whereHas('clearanceSale', function ($query) {
                                return $query->active();
                            });
                        });
                    }])->where('position', 2);
                }])
                    ->withCount(['subCategoryProduct' => function ($query) use ($id, $request, $productUserID, $productAddedBy) {
                        return $query->when($id == 0, function ($query) {
                            return $query->where(['added_by' => 'admin', 'status' => '1']);
                        })->when($id != 0, function ($query) use ($id) {
                            return $query->where(['added_by' => 'seller', 'user_id' => $id, 'status' => '1']);
                        })->when(request('offer_type') == 'clearance_sale', function ($query) {
                            return $query->whereHas('clearanceSale', function ($query) {
                                return $query->active();
                            });
                        });
                    }])->where('position', 1);
            }, 'childes.childes' => function ($query) {
                return $query->when(request('offer_type') == 'clearance_sale', function ($query) {
                    return $query->whereHas('clearanceSale', function ($query) {
                        return $query->active();
                    });
                });
            }])
            ->whereIn('id', $categoriesIdArray)
            ->where('position', 0)->get();

        $categories = CategoryManager::getPriorityWiseCategorySortQuery(query: $categories);

        if ($id == 0) {
            $shop = ['id' => 0, 'name' => getWebConfig(name: 'company_name')];
        } else {
            $shop = Shop::where('seller_id', $id)->first();
        }

        $products = $productListData->paginate(25)->appends($request->all());
        $paginate_count = ceil($products->total() / 25);

        $current_date = date('Y-m-d');
        $seller_vacation_start_date = $id != 0 ? date('Y-m-d', strtotime($shop->vacation_start_date)) : null;
        $seller_vacation_end_date = $id != 0 ? date('Y-m-d', strtotime($shop->vacation_end_date)) : null;
        $seller_temporary_close = $id != 0 ? $shop->temporary_close : false;
        $seller_vacation_status = $id != 0 ? $shop->vacation_status : false;

        $temporary_close = getWebConfig(name: 'temporary_close');
        $inhouse_vacation = getWebConfig(name: 'vacation_add');
        $inhouse_vacation_start_date = $id == 0 ? $inhouse_vacation['vacation_start_date'] : null;
        $inhouse_vacation_end_date = $id == 0 ? $inhouse_vacation['vacation_end_date'] : null;
        $inHouseVacationStatus = $id == 0 ? $inhouse_vacation['status'] : false;
        $inhouse_temporary_close = $id == 0 ? $temporary_close['status'] : false;
        $stockClearanceProducts = StockClearanceProduct::active()->where('shop_id', $id)->count();
        $stockClearanceSetup = StockClearanceSetup::where(['shop_id'=> $id])->first()?->is_active ?? 0;

        return view(VIEW_FILE_NAMES['shop_view_page'], compact('products', 'shop', 'categories', 'current_date', 'seller_vacation_start_date', 'seller_vacation_status', 'seller_vacation_end_date', 'seller_temporary_close', 'inhouse_vacation_start_date', 'inhouse_vacation_end_date', 'inHouseVacationStatus', 'inhouse_temporary_close', 'products_for_review', 'featuredProductsList', 'brands', 'rattingStatusArray', 'reviews', 'allProductsColorList', 'paginate_count', 'shopPublishingHouses', 'digitalProductAuthors', 'stockClearanceProducts', 'stockClearanceSetup', 'singlePageProductCount'))
            ->with('seller_id', $id)
            ->with('total_review', $totalReviews)
            ->with('avg_rating', $averageRating)
            ->with('total_order', $total_order);
    }

    public function checkShopExistence($id): bool|Redirector|RedirectResponse
    {
        $businessMode = getWebConfig(name: 'business_mode');

        if ($id != 0 && $businessMode == 'single') {
            Toastr::error(translate('access_denied!!'));
            return back();
        }

        if ($id != 0) {
            $shop = Shop::where('id', $id)->first();
            if (!$shop) {
                Toastr::error(translate('Shop_does_not_exist'));
                return back();
            } else {
                if (!Seller::approved()->find($shop['seller_id'])) {
                    Toastr::warning(translate('not_found'));
                    return redirect('/');
                }
            }
        }
        return true;
    }

    public function getShopCategoriesList($products)
    {
        $categoryInfoDecoded = [];
        foreach ($products->pluck('category_ids')->toArray() as $info) {
            $categoryInfoDecoded[] = json_decode($info, true);
        }

        $categoryIds = [];
        foreach ($categoryInfoDecoded as $decoded) {
            foreach ($decoded as $info) {
                $categoryIds[] = $info['id'];
            }
        }

        $categories = Category::with(['product' => function ($query) {
                return $query->active()->withCount(['orderDetails']);
            }])
            ->with(['childes.childes' => function ($query) {
                return $query->withCount(['product' => function ($query) {
                    return $query->active()->when(request('offer_type') == 'clearance_sale', function ($query) {
                        return $query->whereHas('clearanceSale', function ($query) {
                            return $query->active();
                        });
                    });
                }]);
            }])->where('position', 0)
            ->whereIn('id', $categoryIds)
            ->withCount(['product' => function ($query) {
                $query->active()->when(request('offer_type') == 'clearance_sale', function ($query) {
                    return $query->whereHas('clearanceSale', function ($query) {
                        return $query->active();
                    });
                });
            }])
            ->get();
        return CategoryManager::getPriorityWiseCategorySortQuery(query: $categories);
    }

    public function getShopBrandsList($request, $products, $sellerType, $sellerId)
    {
        $brandIds = $products->pluck('brand_id')->toArray();
        $brands = Brand::active()->whereIn('id', $brandIds)->with(['brandProducts' => function ($query) use ($sellerType, $sellerId,  $request) {
            return $query->active()->when($sellerType == 'admin', function ($query) use ($sellerType) {
                return $query->where(['added_by' => $sellerType]);
            })
                ->when($sellerId && $sellerType == 'seller', function ($query) use ($sellerId, $sellerType) {
                    return $query->where(['added_by' => $sellerType, 'user_id' => $sellerId]);
                })->withCount(['orderDetails']);
        }])
            ->withCount(['brandProducts' => function ($query) use ($sellerType, $sellerId, $request) {
                    return $query->active()->when($sellerType == 'admin', function ($query) use ($sellerType) {
                        return $query->where(['added_by' => $sellerType]);
                    })
                    ->when($sellerId && $sellerType == 'seller', function ($query) use ($sellerId, $sellerType, $request) {
                        return $query->where(['added_by' => $sellerType, 'user_id' => $sellerId]);
                    })
                    ->when($request['offer_type'] == 'clearance_sale', function ($query) use ($sellerId, $sellerType) {
                        $stockClearanceProductIds = StockClearanceProduct::active()
                            ->when($sellerId && $sellerType == 'admin', function ($query) use ($sellerId, $sellerType) {
                                return $query->where(['added_by' => 'admin']);
                            })
                            ->when($sellerId && $sellerType == 'seller', function ($query) use ($sellerId, $sellerType) {
                                return $query->where(['added_by' => 'vendor', 'user_id' => $sellerId]);
                            })
                            ->pluck('product_id')->toArray();
                        return $query->whereIn('id', $stockClearanceProductIds);
                    });
            }])->get();

        $brandProductSortBy = getWebConfig(name: 'brand_list_priority');
        if ($brandProductSortBy && ($brandProductSortBy['custom_sorting_status'] == 1)) {
            if ($brandProductSortBy['sort_by'] == 'most_order') {
                $brands = $brands->map(function ($brand) {
                    $brand['order_count'] = $brand->brandProducts->sum('order_details_count');
                    return $brand;
                })->sortByDesc('order_count');
            } elseif ($brandProductSortBy['sort_by'] == 'latest_created') {
                $brands = $brands->sortByDesc('id');
            } elseif ($brandProductSortBy['sort_by'] == 'first_created') {
                $brands = $brands->sortBy('id');
            } elseif ($brandProductSortBy['sort_by'] == 'a_to_z') {
                $brands = $brands->sortBy('name', SORT_NATURAL | SORT_FLAG_CASE);
            } elseif ($brandProductSortBy['sort_by'] == 'z_to_a') {
                $brands = $brands->sortByDesc('name', SORT_NATURAL | SORT_FLAG_CASE);
            }
        }
        return $brands;
    }

    public function filterProductsAjaxResponse(Request $request): JsonResponse
    {
        if ($request->has('min_price') && $request['min_price'] != '' && $request->has('max_price') && $request['max_price'] != '' && $request['min_price'] > $request['max_price']) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => 0,
                    'message' => translate('Minimum_price_should_be_less_than_or_equal_to_maximum_price.'),
                ]);
            }
            Toastr::error(translate('Minimum_price_should_be_less_than_or_equal_to_maximum_price.'));
            redirect()->back();
        }

        $singlePageProductCount = $request['per_page_product'] ?? 25;
        if ($request->has('shop_id')) {
            $shopID = $request['shop_id'];
            self::checkShopExistence($shopID);
            $productAddedBy = $shopID == 0 ? 'admin' : 'seller';
            $productUserID = $shopID == 0 ? $shopID : Shop::where('id', $shopID)->first()->seller_id;
            $productListData = ProductManager::getProductListData($request, $productUserID, $productAddedBy);
        } else {
            $productListData = ProductManager::getProductListData($request);
        }

        $category = [];
        if ($request['category_ids']) {
            $category = Category::whereIn('id', $request['category_ids'])->get();
        }

        $brands = [];
        if ($request['brand_ids']) {
            $brands = Brand::whereIn('id', $request['brand_ids'])->get();
        }

        $publishingHouse = [];
        if ($request['publishing_house_ids']) {
            $publishingHouse = PublishingHouse::whereIn('id', $request['publishing_house_ids'])->select('id', 'name')->get();
        }

        $productAuthors = [];
        if ($request['author_ids']) {
            $productAuthors = Author::whereIn('id', $request['author_ids'])->select('id', 'name')->get();
        }

        $rating = $request->rating ?? [];
        $productsCount = $productListData->count();
        $paginateCount = ceil($productsCount / $singlePageProductCount);
        $currentPage = $offset ?? Paginator::resolveCurrentPage('page');
        $results = $productListData->forPage($currentPage, $singlePageProductCount);
        $products = new LengthAwarePaginator(items: $results, total: $productsCount, perPage: $singlePageProductCount, currentPage: $currentPage, options: [
            'path' => Paginator::resolveCurrentPath(),
            'appends' => $request->all(),
        ]);

        $data = self::getProductListRequestData(request: $request);

        return response()->json([
            'html_products' => view('theme-views.product._ajax-products', [
                'products' => $products,
                'paginate_count' => $paginateCount,
                'page' => ($request->page ?? 1),
                'request_data' => $request->all(),
                'singlePageProductCount' => $singlePageProductCount,
                'data' => $data,
            ])->render(),
            'html_tags' => view('theme-views.product._selected_filter_tags', [
                'tags_category' => $category,
                'tags_brands' => $brands,
                'rating' => $rating,
                'publishingHouse' => $publishingHouse,
                'productAuthors' => $productAuthors,
                'sort_by' => $request['sort_by'],
            ])->render(),
            'products_count' => $productsCount,
            'products' => $products,
            'singlePageProductCount' => $singlePageProductCount,
        ]);
    }

    public function ajax_shop_vacation_check(Request $request): JsonResponse
    {
        $current_date = date('Y-m-d');
        $vacation_start_date = $current_date;
        $vacation_end_date = $current_date;
        $temporary_close = null;
        $vacation_status = null;

        if ($request['added_by'] == "seller") {
            $shop = Shop::where('seller_id', $request['user_id'])->first();
            $vacation_start_date = $shop->vacation_start_date ? date('Y-m-d', strtotime($shop->vacation_start_date)) : null;
            $vacation_end_date = $shop->vacation_end_date ? date('Y-m-d', strtotime($shop->vacation_end_date)) : null;
            $temporary_close = $shop->temporary_close;
            $vacation_status = $shop->vacation_status;
        } else {
            $temporary_close = getWebConfig(name: 'temporary_close');
            $inhouse_vacation = getWebConfig(name: 'vacation_add');
            $vacation_start_date = $inhouse_vacation['vacation_start_date'];
            $vacation_end_date = $inhouse_vacation['vacation_end_date'];
            $vacation_status = $inhouse_vacation['status'];
            $temporary_close = $temporary_close['status'];
        }

        if ($temporary_close || ($vacation_status && $current_date >= $vacation_start_date && $current_date <= $vacation_end_date)) {
            return response()->json(['status' => 'inactive']);
        } else {
            $product_data = Product::find($request['id']);

            unset($request['added_by']);
            $request['quantity'] = $product_data->minimum_order_qty;

            $cart = CartManager::add_to_cart($request);
            session()->forget('coupon_code');
            session()->forget('coupon_type');
            session()->forget('coupon_bearer');
            session()->forget('coupon_discount');
            session()->forget('coupon_seller_id');
            return response()->json($cart);
        }
    }

    public static function getProductListRequestData($request): array
    {
        if ($request->has('product_view') && in_array($request['product_view'], ['grid-view', 'list-view'])) {
            session()->put('product_view_style', $request['product_view']);
        }

        $data = [
            'id' => $request['id'],
            'name' => $request['name'],
            'brand_id' => $request['brand_id'],
            'category_id' => $request['category_id'],
            'data_from' => $request['data_from'],
            'offer_type' => $request['offer_type'],
            'sort_by' => $request['sort_by'],
            'page_no' => $request['page'],
            'min_price' => $request['min_price'],
            'max_price' => $request['max_price'],
            'product_type' => $request['product_type'],
            'shop_id' => $request['shop_id'],
            'author_id' => $request['author_id'],
            'publishing_house_id' => $request['publishing_house_id'],
            'search_category_value' => $request['search_category_value'],
            'product_name' => $request['product_name'],
            'page' => $request['page'] ?? 1,
        ];

        if ($request->has('shop_id')) {
            $data['shop_id'] = $request['shop_id'];
        }

        return $data;
    }
}
