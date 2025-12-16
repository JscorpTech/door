<?php

namespace App\Utils;

use App\Http\Requests\Request;
use App\Models\Cart;
use Illuminate\Support\Facades\Request as FacadesRequest;
use App\Models\Author;
use App\Models\Category;
use App\Models\DigitalProductAuthor;
use App\Models\DigitalProductPublishingHouse;
use App\Models\FlashDeal;
use App\Models\CategoryShippingCost;
use App\Models\FlashDealProduct;
use App\Models\OrderDetail;
use App\Models\Product;
use App\Models\PublishingHouse;
use App\Models\Review;
use App\Models\ShippingMethod;
use App\Models\ShippingType;
use App\Models\StockClearanceProduct;
use App\Models\StockClearanceSetup;
use App\Models\Translation;
use App\Models\Wishlist;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use function JmesPath\search;

class ProductManager
{
    public static function get_product($id)
    {
        return Product::active()
            ->with(['rating', 'seller.shop', 'tags', 'seoInfo', 'digitalVariation', 'digitalProductAuthors.author', 'digitalProductPublishingHouse.publishingHouse', 'clearanceSale' => function ($query) {
                return $query->active();
            }])
            ->where('id', $id)->first();
    }

    public static function get_latest_products($request, $limit = 10, $offset = 1)
    {
        $user = Helpers::getCustomerInformation($request);
        $paginator = Product::active()
            ->with(['rating', 'tags', 'seller.shop', 'flashDealProducts.flashDeal', 'clearanceSale' => function ($query) {
                return $query->active();
            }])
            ->withCount(['reviews', 'wishList' => function ($query) use ($user) {
                $query->where('customer_id', $user != 'offline' ? $user->id : '0');
            }])
            ->orderBy('id', 'desc')
            ->paginate($limit, ['*'], 'page', $offset);

        $currentDate = date('Y-m-d H:i:s');
        $paginator?->map(function ($product) use ($currentDate) {
            $flashDealStatus = 0;
            $flashDealEndDate = 0;
            if (count($product->flashDealProducts) > 0) {
                $flashDeal = null;
                foreach ($product->flashDealProducts as $flashDealData) {
                    if ($flashDealData->flashDeal) {
                        $flashDeal = $flashDealData->flashDeal;
                    }
                }
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

        return [
            'total_size' => $paginator->total(),
            'limit' => (int)$limit,
            'offset' => (int)$offset,
            'products' => $paginator->items()
        ];
    }

    public static function getNewArrivalProducts($request, $limit = 10, $offset = 1)
    {
        $user = Helpers::getCustomerInformation($request);
        $products = Product::active()
            ->with(['rating', 'tags', 'seller.shop', 'flashDealProducts.flashDeal', 'clearanceSale' => function ($query) {
                return $query->active();
            }])
            ->withCount(['reviews', 'wishList' => function ($query) use ($user) {
                $query->where('customer_id', $user != 'offline' ? $user->id : '0');
            }]);

        $products = ProductManager::getPriorityWiseNewArrivalProductsQuery(query: $products, dataLimit: $limit, offset: $offset);

        $currentDate = date('Y-m-d H:i:s');
        $products?->map(function ($product) use ($currentDate) {
            $flashDealStatus = 0;
            $flashDealEndDate = 0;
            if (count($product->flashDealProducts) > 0) {
                $flashDeal = null;
                foreach ($product->flashDealProducts as $flashDealData) {
                    if ($flashDealData->flashDeal) {
                        $flashDeal = $flashDealData->flashDeal;
                    }
                }
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

        return $products;
    }

    public static function getFeaturedProductsList($request, $limit = 10, $offset = 1): array
    {
        $user = Helpers::getCustomerInformation($request);
        $currentDate = date('Y-m-d H:i:s');
        // Change review to ratting
        $products = Product::with(['seller.shop', 'rating', 'tags', 'flashDealProducts.flashDeal', 'clearanceSale' => function ($query) {
            return $query->active();
        }])->active()
            ->withCount(['reviews', 'wishList' => function ($query) use ($user) {
                $query->where('customer_id', $user != 'offline' ? $user->id : '0');
            }])
            ->where('featured', 1)
            ->withCount(['orderDetails', 'reviews']);

        $products = self::getPriorityWiseFeaturedProductsQuery(query: $products, dataLimit: $limit, offset: $request->get('page', $offset), appends: $request->all());

        $products?->map(function ($product) use ($currentDate) {
            $flashDealStatus = 0;
            $flashDealEndDate = 0;
            if (count($product->flashDealProducts) > 0) {
                $flashDeal = null;
                foreach ($product->flashDealProducts as $flashDealData) {
                    if ($flashDealData->flashDeal) {
                        $flashDeal = $flashDealData->flashDeal;
                    }
                }
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

        return [
            'total_size' => $products->total(),
            'limit' => (int)$limit,
            'offset' => (int)$offset,
            'products' => $products->items()
        ];
    }

    public static function getTopRatedProducts($request, $limit = 10, $offset = 1)
    {
        $user = Helpers::getCustomerInformation($request);
        $currentDate = date('Y-m-d H:i:s');

        $reviews = Review::select('product_id', DB::raw('AVG(rating) as count'))
            ->groupBy('product_id')->get();
        $getReviewProductIds = [];
        foreach ($reviews as $review) {
            $getReviewProductIds[] = $review['product_id'];
        }

        $productListData = Product::active()->withSum('orderDetails', 'qty', function ($query) {
            $query->where('delivery_status', 'delivered');
        })
            ->with(['seller.shop', 'category', 'reviews', 'rating', 'flashDealProducts.flashDeal',
                'wishList' => function ($query) use ($user) {
                    return $query->where('customer_id', $user != 'offline' ? $user->id : '0');
                },
                'compareList' => function ($query) use ($user) {
                    return $query->where('user_id', $user != 'offline' ? $user->id : '0');
                }, 'clearanceSale' => function ($query) {
                    return $query->active();
                }])
            ->withCount(['reviews', 'wishList' => function ($query) use ($user) {
                $query->where('customer_id', $user != 'offline' ? $user->id : '0');
            }]);

        $productListData = ProductManager::getPriorityWiseTopRatedProductsQuery(query: $productListData->whereIn('id', $getReviewProductIds), dataLimit: $limit, offset: $offset);

        $productListData?->map(function ($product) use ($currentDate) {
            $flashDealStatus = 0;
            $flashDealEndDate = 0;
            if (count($product->flashDealProducts) > 0) {
                $flashDeal = null;
                foreach ($product->flashDealProducts as $flashDealData) {
                    if ($flashDealData->flashDeal) {
                        $flashDeal = $flashDealData->flashDeal;
                    }
                }
                if ($flashDeal) {
                    $startDate = date('Y-m-d H:i:s', strtotime($flashDeal->start_date));
                    $endDate = date('Y-m-d H:i:s', strtotime($flashDeal->end_date));
                    $flashDealStatus = $flashDeal->status == 1 && (($currentDate >= $startDate) && ($currentDate <= $endDate)) ? 1 : 0;
                    $flashDealEndDate = $flashDeal->end_date;
                }
            }
            $product['flash_deal_status'] = $flashDealStatus;
            $product['flash_deal_end_date'] = $flashDealEndDate;
            $product['reviews_count'] = $product->reviews->count();
            unset($product->reviews);
            return $product;
        });

        return $productListData;
    }

    public static function getBestSellingProductsList($request, $limit = 10, $offset = 1)
    {
        $user = Helpers::getCustomerInformation($request);
        $currentDate = date('Y-m-d H:i:s');

        $orderDetails = OrderDetail::with('product')
            ->select('product_id', DB::raw('COUNT(product_id) as count'))
            ->groupBy('product_id')
            ->get();

        $getOrderedProductIds = [];
        foreach ($orderDetails as $detail) {
            $getOrderedProductIds[] = $detail['product_id'];
        }

        $productListData = Product::active()->withSum('orderDetails', 'qty', function ($query) {
            $query->where('delivery_status', 'delivered');
        })
            ->with(['seller.shop', 'category', 'reviews', 'rating', 'flashDealProducts.flashDeal',
                'wishList' => function ($query) use ($user) {
                    return $query->where('customer_id', $user != 'offline' ? $user->id : '0');
                },
                'compareList' => function ($query) use ($user) {
                    return $query->where('user_id', $user != 'offline' ? $user->id : '0');
                }, 'clearanceSale' => function ($query) {
                    return $query->active();
                }])
            ->withCount(['reviews', 'wishList' => function ($query) use ($user) {
                $query->where('customer_id', $user != 'offline' ? $user->id : '0');
            }]);

        $productListData = ProductManager::getPriorityWiseBestSellingProductsQuery(query: $productListData->whereIn('id', $getOrderedProductIds), dataLimit: $limit, offset: $offset);

        $productListData?->map(function ($product) use ($currentDate) {
            $flashDealStatus = 0;
            $flashDealEndDate = 0;
            if (count($product->flashDealProducts) > 0) {
                $flashDeal = null;
                foreach ($product->flashDealProducts as $flashDealData) {
                    if ($flashDealData->flashDeal) {
                        $flashDeal = $flashDealData->flashDeal;
                    }
                }
                if ($flashDeal) {
                    $startDate = date('Y-m-d H:i:s', strtotime($flashDeal->start_date));
                    $endDate = date('Y-m-d H:i:s', strtotime($flashDeal->end_date));
                    $flashDealStatus = $flashDeal->status == 1 && (($currentDate >= $startDate) && ($currentDate <= $endDate)) ? 1 : 0;
                    $flashDealEndDate = $flashDeal->end_date;
                }
            }
            $product['flash_deal_status'] = $flashDealStatus;
            $product['flash_deal_end_date'] = $flashDealEndDate;
            $product['reviews_count'] = $product->reviews->count();
            unset($product->reviews);
            return $product;
        });

        return $productListData;
    }

    public static function get_seller_best_selling_products($request, $seller_id, $limit = 10, $offset = 1)
    {
        $user = Helpers::getCustomerInformation($request);

        $paginator = OrderDetail::with(['product.rating', 'product' => function ($query) use ($user) {
            $query->withCount(['wishList' => function ($query) use ($user) {
                $query->where('customer_id', $user != 'offline' ? $user->id : '0');
            }]);
        }])
            ->whereHas('product', function ($query) use ($seller_id) {
                $query->when($seller_id == '0', function ($query) use ($seller_id) {
                    return $query->where(['added_by' => 'admin'])->active();
                })
                    ->when($seller_id != '0', function ($query) use ($seller_id) {
                        return $query->where(['added_by' => 'seller', 'user_id' => $seller_id])->active();
                    });
            })
            ->select('product_id', DB::raw('COUNT(product_id) as count'))
            ->groupBy('product_id')
            ->orderBy("count", 'desc')
            ->paginate($limit, ['*'], 'page', $offset);

        $data = [];
        foreach ($paginator as $order) {
            array_push($data, $order->product);
        }

        return [
            'total_size' => $paginator->total(),
            'limit' => (int)$limit,
            'offset' => (int)$offset,
            'products' => $data
        ];
    }

    public static function get_related_products($product_id, $request = null)
    {
        $user = Helpers::getCustomerInformation($request);
        $product = Product::find($product_id);
        $products = Product::active()->with(['rating', 'flashDealProducts.flashDeal', 'tags', 'seller.shop', 'clearanceSale' => function ($query) {
            return $query->active();
        }])
            ->withCount(['reviews', 'wishList' => function ($query) use ($user) {
                $query->where('customer_id', $user != 'offline' ? $user->id : '0');
            }])
            ->where('category_ids', $product->category_ids)
            ->where('id', '!=', $product->id)
            ->limit(10)
            ->get();

        $currentDate = date('Y-m-d H:i:s');
        $products?->map(function ($product) use ($currentDate) {
            $flashDealStatus = 0;
            $flashDealEndDate = 0;
            if (count($product->flashDealProducts) > 0) {
                $flashDeal = null;
                foreach ($product->flashDealProducts as $flashDealData) {
                    if ($flashDealData->flashDeal) {
                        $flashDeal = $flashDealData->flashDeal;
                    }
                }
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

        return $products;
    }

    public static function search_products($request, $name, $category = 'all', $limit = 10, $offset = 1): array
    {
        $key = explode(' ', $name);;
        $user = Helpers::getCustomerInformation($request);

        $authorIds = Author::where('name', 'like', "%{$name}%")->pluck('id')->toArray();
        $authorProductIds = DigitalProductAuthor::whereIn('author_id', $authorIds)->pluck('product_id')->toArray();

        $publishingHouseIds = PublishingHouse::where('name', 'like', "%{$name}%")->pluck('id')->toArray();
        $publishingHouseProductIds = DigitalProductPublishingHouse::whereIn('publishing_house_id', $publishingHouseIds)->pluck('product_id')->toArray();

        $productListData = Product::active()->with(['rating', 'tags', 'clearanceSale' => function ($query) {
            return $query->active();
        }])
            ->where(function ($q) use ($key) {
                foreach ($key as $value) {
                    $q->orWhere('name', 'like', "%{$value}%")
                        ->orWhereHas('tags', function ($query) use ($key) {
                            $query->where(function ($q) use ($key) {
                                foreach ($key as $value) {
                                    $q->where('tag', 'like', "%{$value}%");
                                }
                            });
                        });
                }
            })
            ->withCount(['wishList' => function ($query) use ($user) {
                $query->where('customer_id', $user != 'offline' ? $user->id : '0');
            }])->when(!empty($authorProductIds), function ($query) use ($authorProductIds) {
                $query->whereIn('id', $authorProductIds);
            })->when(!empty($publishingHouseProductIds), function ($query) use ($publishingHouseProductIds) {
                $query->whereIn('id', $publishingHouseProductIds);
            });

        if (isset($category) && $category != 'all') {
            $categoryWiseProduct = $productListData->where(['category_id' => $category])
                ->orWhere(['sub_category_id' => $category])
                ->orWhere(['sub_sub_category_id' => $category]);
            $productListData = ProductManager::getPriorityWiseCategoryWiseProductsQuery(query: $categoryWiseProduct, dataLimit: $limit, offset: $offset);
        } else {
            $productListData = ProductManager::getPriorityWiseSearchedProductQuery(query: $productListData, keyword: $name, dataLimit: $limit, offset: $offset, type: 'searched');
        }

        return [
            'total_size' => $productListData->total(),
            'limit' => (int)$limit,
            'offset' => (int)$offset,
            'products' => $productListData->items()
        ];
    }

    public static function suggestion_products($name, $limit = 10, $offset = 1)
    {
        $key = [base64_decode($name)];

        $product = Product::select('name')
            ->active()
            ->with(['rating', 'tags'])->where(function ($q) use ($key) {
                foreach ($key as $value) {
                    $q->orWhere('name', 'like', "%{$value}%")
                        ->orWhereHas('tags', function ($query) use ($key) {
                            $query->where(function ($q) use ($key) {
                                foreach ($key as $value) {
                                    $q->where('tag', 'like', "%{$value}%");
                                }
                            });
                        });
                }
            })->paginate($limit, ['*'], 'page', $offset);


        return [
            'products' => $product->items()
        ];
    }

    public static function getSearchProductsForWeb($name, $category = 'all', $limit = 10, $offset = 1): array
    {
        $authorIds = Author::where('name', 'like', "%{$name}%")->pluck('id')->toArray();
        $authorProductIds = DigitalProductAuthor::whereIn('author_id', $authorIds)->pluck('product_id')->toArray();

        $publishingHouseIds = PublishingHouse::where('name', 'like', "%{$name}%")->pluck('id')->toArray();
        $publishingHouseProductIds = DigitalProductPublishingHouse::whereIn('publishing_house_id', $publishingHouseIds)->pluck('product_id')->toArray();

        $productListData = Product::active()->with(['rating', 'tags'])->where(function ($q) use ($name) {
            $q->orWhere('name', 'like', "%{$name}%")
                ->orWhereHas('tags', function ($query) use ($name) {
                    $query->where('tag', 'like', "%{$name}%");
                });
        })->when(!empty($authorProductIds), function ($query) use ($authorProductIds) {
            $query->whereIn('id', $authorProductIds);
        })->when(!empty($publishingHouseProductIds), function ($query) use ($publishingHouseProductIds) {
            $query->whereIn('id', $publishingHouseProductIds);
        });

        if (isset($category) && $category != 'all') {
            $categoryWiseProduct = $productListData->where(['category_id' => $category])
                ->orWhere(['sub_category_id' => $category])
                ->orWhere(['sub_sub_category_id' => $category]);
            $productListData = ProductManager::getPriorityWiseCategoryWiseProductsQuery(query: $categoryWiseProduct, dataLimit: $limit, offset: $offset);
        } else {
            $productListData = ProductManager::getPriorityWiseSearchedProductQuery(query: $productListData, keyword: $name, dataLimit: $limit, offset: $offset, type: 'searched');
        }

        return [
            'total_size' => $productListData->total(),
            'limit' => (int)$limit,
            'offset' => (int)$offset,
            'products' => $productListData->items()
        ];
    }

    public static function translated_product_search($name, $category = 'all', $limit = 10, $offset = 1): array
    {
        $name = base64_decode($name);
        $product_ids = Translation::where('translationable_type', 'App\Models\Product')
            ->where('key', 'name')
            ->where('value', 'like', "%{$name}%")
            ->pluck('translationable_id');

        $productListData = Product::with(['tags', 'clearanceSale' => function ($query) {
            return $query->active();
        }])->whereIn('id', $product_ids);
        if ($category != 'all') {
            $categoryWiseProduct = $productListData->where(['category_id' => $category])
                ->orWhere(['sub_category_id' => $category])
                ->orWhere(['sub_sub_category_id' => $category]);
            $productListData = ProductManager::getPriorityWiseCategoryWiseProductsQuery(query: $categoryWiseProduct, dataLimit: $limit, offset: $offset);
        } else {
            $productListData = ProductManager::getPriorityWiseSearchedProductQuery(query: $productListData, keyword: $name, dataLimit: $limit, offset: $offset, type: 'translated');
        }

        return [
            'total_size' => $productListData->total(),
            'limit' => (int)$limit,
            'offset' => (int)$offset,
            'products' => $productListData->items()
        ];
    }

    public static function getTranslatedProductSearchForWeb($name, $category = 'all', $limit = 10, $offset = 1): array
    {
        $translationIds = Translation::where('translationable_type', 'App\Models\Product')
            ->where('key', 'name')
            ->where('value', 'like', "%{$name}%")
            ->pluck('translationable_id');

        $productListData = Product::with(['tags', 'translations'])
            ->whereIn('id', $translationIds);

        if ($category !== 'all') {
            $productListData->whereJsonContains('category_ids', [['id' => $category]]);
        }

        $productListData = ProductManager::getPriorityWiseSearchedProductQuery(query: $productListData, keyword: $name, dataLimit: $limit, offset: $offset, type: 'translated');

        return [
            'total_size' => $productListData->total(),
            'limit' => (int)$limit,
            'offset' => (int)$offset,
            'products' => $productListData->items(),
        ];
    }

    public static function product_image_path($image_type)
    {
        $path = '';
        if ($image_type == 'thumbnail') {
            $path = asset('storage/app/public/product/thumbnail');
        } elseif ($image_type == 'product') {
            $path = asset('storage/app/public/product');
        }
        return $path;
    }

    public static function get_product_review($id)
    {
        return Review::where(['product_id' => $id])->where('status', 1)->get();
    }

    public static function get_rating($reviews)
    {
        $rating5 = 0;
        $rating4 = 0;
        $rating3 = 0;
        $rating2 = 0;
        $rating1 = 0;
        foreach ($reviews as $key => $review) {
            if ($review->rating == 5) {
                $rating5 += 1;
            }
            if ($review->rating == 4) {
                $rating4 += 1;
            }
            if ($review->rating == 3) {
                $rating3 += 1;
            }
            if ($review->rating == 2) {
                $rating2 += 1;
            }
            if ($review->rating == 1) {
                $rating1 += 1;
            }
        }
        return [$rating5, $rating4, $rating3, $rating2, $rating1];
    }

    public static function get_overall_rating($reviews)
    {
        $totalRating = count($reviews);
        $rating = 0;
        foreach ($reviews as $key => $review) {
            $rating += $review->rating;
        }
        if ($totalRating == 0) {
            $overallRating = 0;
        } else {
            $overallRating = number_format($rating / $totalRating, 2);
        }

        return [$overallRating, $totalRating];
    }

    public static function get_shipping_methods($product)
    {
        if ($product['added_by'] == 'seller') {
            $methods = ShippingMethod::where(['creator_id' => $product['user_id']])->where(['status' => 1])->get();
            if ($methods->count() == 0) {
                $methods = ShippingMethod::where(['creator_type' => 'admin'])->where(['status' => 1])->get();
            }
        } else {
            $methods = ShippingMethod::where(['creator_type' => 'admin'])->where(['status' => 1])->get();
        }

        return $methods;
    }

    public static function getProductAuthorsInfo(object|array $product): array
    {
        $productAuthorIds = [];
        $productAuthorNames = [];
        $productAuthors = [];
        if ($product?->digitalProductAuthors && count($product?->digitalProductAuthors) > 0) {
            foreach ($product?->digitalProductAuthors as $author) {
                $productAuthorIds[] = $author['author_id'];
                $productAuthors[] = $author?->author;
                if ($author?->author?->name) {
                    $productAuthorNames[] = $author?->author?->name;
                }
            }
        }
        return [
            'ids' => $productAuthorIds,
            'names' => $productAuthorNames,
            'data' => $productAuthors,
        ];
    }

    public static function getProductPublishingHouseInfo(object|array $product): array
    {
        $productPublishingHouseIds = [];
        $productPublishingHouseNames = [];
        $productPublishingHouses = [];
        if ($product?->digitalProductPublishingHouse && count($product?->digitalProductPublishingHouse) > 0) {
            foreach ($product?->digitalProductPublishingHouse as $publishingHouse) {
                $productPublishingHouseIds[] = $publishingHouse['publishing_house_id'];
                $productPublishingHouses[] = $publishingHouse?->publishingHouse;
                if ($publishingHouse?->publishingHouse?->name) {
                    $productPublishingHouseNames[] = $publishingHouse?->publishingHouse?->name;
                }
            }
        }
        return [
            'ids' => $productPublishingHouseIds,
            'names' => $productPublishingHouseNames,
            'data' => $productPublishingHouses,
        ];
    }

    public static function get_seller_products($seller_id, $request)
    {
        $user = Helpers::getCustomerInformation($request);
        $categories = $request->has('category') ? json_decode($request->category) : [];
        $publishingHouses = $request->has('publishing_houses') ? json_decode($request->publishing_houses) : [];
        $productAuthors = $request->has('product_authors') ? json_decode($request->product_authors) : [];

        $publishingHouseList = PublishingHouse::with(['publishingHouseProducts'])
            ->whereHas('publishingHouseProducts.product', function ($query) {
                return $query->active();
            })
            ->withCount(['publishingHouseProducts' => function ($query) {
                return $query->whereHas('product', function ($query) {
                    return $query->active();
                });
            }])->get();

        $productIdsForPublisher = [];
        $publishingHouseList->each(function ($publishingHouseGroup) use (&$productIdsForPublisher) {
            $publishingHouseGroup?->publishingHouseProducts?->each(function ($publishingHouse) use (&$productIdsForPublisher) {
                $productIdsForPublisher[] = $publishingHouse->product_id;
            });
        });

        $productIdsForUnknownPublisher = Product::active()->where(['product_type' => 'digital'])->whereNotIn('id', $productIdsForPublisher)->pluck('id')->toArray();

        $authorList = Author::withCount(['digitalProductAuthor' => function ($query) {
            return $query->whereHas('product', function ($query) {
                return $query->active();
            });
        }])->get();

        $productIdsForAuthor = [];
        $authorList->each(function ($authorGroup) use (&$productIdsForAuthor) {
            $authorGroup?->digitalProductAuthor?->each(function ($authorItem) use (&$productIdsForAuthor) {
                $productIdsForAuthor[] = $authorItem->product_id;
            });
        });
        $productIdsForUnknownAuthor = Product::active()->where(['product_type' => 'digital'])->whereNotIn('id', $productIdsForAuthor)->pluck('id')->toArray();

        $limit = $request['limit'];
        $offset = $request['offset'];
        $products = Product::active()
            ->with(['rating', 'flashDealProducts.flashDeal', 'tags', 'digitalProductAuthors.author', 'digitalProductPublishingHouse.publishingHouse', 'clearanceSale' => function ($query) {
                return $query->active();
            }])
            ->withCount(['reviews', 'wishList' => function ($query) use ($user) {
                $query->where('customer_id', $user != 'offline' ? $user->id : '0');
            }])
            ->when(in_array($request['product_type'], ['physical', 'digital']), function ($query) use ($request) {
                return $query->where(['product_type' => $request['product_type']]);
            })
            ->when($seller_id == 0, function ($query) {
                return $query->where(['added_by' => 'admin']);
            })
            ->when($seller_id != 0, function ($query) use ($seller_id) {
                return $query->where(['added_by' => 'seller'])
                    ->where('user_id', $seller_id);
            })
            ->when($request->search, function ($query) use ($request) {
                $key = explode(' ', $request->search);
                foreach ($key as $value) {
                    $query->where('name', 'like', "%{$value}%");
                }
            })
            ->when($request->has('brand_ids') && json_decode($request->brand_ids), function ($query) use ($request) {
                $query->whereIn('brand_id', json_decode($request->brand_ids));
            })
            ->when($request->has('category') && $categories, function ($query) use ($categories) {
                $query->where(function ($query) use ($categories) {
                    return $query->whereIn('category_id', $categories)
                        ->orWhereIn('sub_category_id', $categories)
                        ->orWhereIn('sub_sub_category_id', $categories);
                });
            })
            ->when($request->has('publishing_houses') && $publishingHouses, function ($query) use ($request, $publishingHouses, $productIdsForPublisher) {
                $publishingHouseList = PublishingHouse::whereIn('id', $publishingHouses)->with(['publishingHouseProducts'])->withCount(['publishingHouseProducts' => function ($query) {
                    return $query->whereHas('product', function ($query) {
                        return $query->active();
                    });
                }])->get();

                $publishingHouseProductIds = [];
                $publishingHouseList->each(function ($publishingHouseGroup) use (&$publishingHouseProductIds) {
                    $publishingHouseGroup?->publishingHouseProducts?->each(function ($publishingHouse) use (&$publishingHouseProductIds) {
                        $publishingHouseProductIds[] = $publishingHouse->product_id;
                    });
                });

                if (in_array(0, $publishingHouses)) {
                    $publishingHouseProductIds = array_merge($publishingHouseProductIds, $productIdsForPublisher);
                }

                return $query->where(['product_type' => 'digital'])->whereIn('id', $publishingHouseProductIds);
            })
            ->when($request->has('product_authors') && $productAuthors, function ($query) use ($request, $productAuthors, $productIdsForUnknownAuthor) {
                $authorList = Author::whereIn('id', $productAuthors)->withCount(['digitalProductAuthor' => function ($query) {
                    return $query->whereHas('product', function ($query) {
                        return $query->active();
                    });
                }])->get();

                $authorProductIds = [];
                $authorList->each(function ($authorGroup) use (&$authorProductIds) {
                    $authorGroup?->digitalProductAuthor?->each(function ($authorItem) use (&$authorProductIds) {
                        $authorProductIds[] = $authorItem->product_id;
                    });
                });
                if (in_array(0, $productAuthors)) {
                    $authorProductIds = array_merge($authorProductIds, $productIdsForUnknownAuthor);
                }
                return $query->where(['product_type' => 'digital'])->whereIn('id', $authorProductIds);
            })
            ->when($request['offer_type'] == 'clearance_sale', function ($query) {
                $stockClearanceProductIds = StockClearanceProduct::active()->pluck('product_id')->toArray();
                return $query->whereIn('id', $stockClearanceProductIds);
            })
            ->when($request->has('product_id') && $request['product_id'], function ($query) use ($request) {
                return $query->whereNotIn('id', [$request['product_id']]);
            });

        if (request('offer_type') == 'clearance_sale') {
            $products = ProductManager::getPriorityWiseClearanceSaleProductsQuery(query: $products, dataLimit: $request['limit'], offset: $request['offset']);
        } else {
            $products = ProductManager::getPriorityWiseVendorProductListQuery(query: $products);
        }

        $currentDate = date('Y-m-d H:i:s');
        $products?->map(function ($product) use ($currentDate) {
            $product->digital_product_authors_names = self::getProductAuthorsInfo(product: $product)['names'];
            $product->digital_product_publishing_house_names = self::getProductPublishingHouseInfo(product: $product)['names'];

            $flashDealStatus = 0;
            $flashDealEndDate = 0;
            if (count($product->flashDealProducts) > 0) {
                $flashDeal = null;
                foreach ($product->flashDealProducts as $flashDealData) {
                    if ($flashDealData->flashDeal) {
                        $flashDeal = $flashDealData->flashDeal;
                    }
                }
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

        $currentPage = $offset ?? Paginator::resolveCurrentPage('page');
        $totalSize = $products->count();
        $results = $products->forPage($currentPage, $limit);
        return new LengthAwarePaginator(items: $results, total: $totalSize, perPage: $limit, currentPage: $currentPage, options: [
            'path' => Paginator::resolveCurrentPath(),
            'appends' => $request->all(),
        ]);
    }

    public static function get_seller_all_products($seller_id, $limit = 10, $offset = 1)
    {
        $paginator = Product::with(['rating', 'tags'])
            ->where(['user_id' => $seller_id, 'added_by' => 'seller'])
            ->orderBy('id', 'desc')
            ->paginate($limit, ['*'], 'page', $offset);

        return [
            'total_size' => $paginator->total(),
            'limit' => (int)$limit,
            'offset' => (int)$offset,
            'products' => $paginator->items()
        ];
    }

    public static function get_discounted_product($request, $limit = 10, $offset = 1)
    {
        $user = Helpers::getCustomerInformation($request);

        $stockClearanceProductIds = StockClearanceProduct::active()->pluck('product_id')->toArray();

        //change review to ratting
        $paginator = Product::with(['rating', 'reviews', 'tags', 'clearanceSale' => function ($query) {
                return $query->active();
            }])->active()
            ->withCount(['reviews', 'wishList' => function ($query) use ($user) {
                $query->where('customer_id', $user != 'offline' ? $user->id : '0');
            }])
            ->where(function ($subQuery) use ($stockClearanceProductIds) {
                return $subQuery->where(function ($query) {
                    return $query->where('discount', '!=', 0);
                })->orWhere(function ($query) use ($stockClearanceProductIds) {
                    return $query->whereIn('id', $stockClearanceProductIds);
                });
            })
            ->orderBy('id', 'desc')
            ->paginate($limit, ['*'], 'page', $offset);

        return [
            'total_size' => $paginator->total(),
            'limit' => (int)$limit,
            'offset' => (int)$offset,
            'products' => $paginator->items()
        ];
    }

    public static function export_product_reviews($data)
    {
        $storage = [];
        foreach ($data as $item) {
            $storage[] = [
                'product' => $item->product['name'] ?? '',
                'customer' => isset($item->customer) ? $item->customer->f_name . ' ' . $item->customer->l_name : '',
                'comment' => $item->comment,
                'rating' => $item->rating
            ];
        }
        return $storage;
    }

    public static function get_user_total_product($added_by, $user_id)
    {
        $total_product = Product::active()->where(['added_by' => $added_by, 'user_id' => $user_id])->count();
        return $total_product;
    }

    public static function get_products_rating_quantity($products)
    {
        $rating5 = 0;
        $rating4 = 0;
        $rating3 = 0;
        $rating2 = 0;
        $rating1 = 0;

        foreach ($products as $product) {
            $review = Review::where(['product_id' => $product])->avg('rating');
            if ($review == 5) {
                $rating5 += 1;
            } else if ($review >= 4 && $review < 5) {
                $rating4 += 1;
            } else if ($review >= 3 && $review < 4) {
                $rating3 += 1;
            } else if ($review >= 2 && $review < 3) {
                $rating2 += 1;
            } else if ($review >= 1 && $review < 2) {
                $rating1 += 1;
            }
        }

        return [$rating5, $rating4, $rating3, $rating2, $rating1];
    }

    public static function get_products_delivery_charge($product, $quantity)
    {
        $delivery_cost = 0;
        $shipping_model = getWebConfig(name: 'shipping_method');
        $shipping_type = "";

        if ($shipping_model == "inhouse_shipping") {
            $shipping_type = ShippingType::where(['seller_id' => 0])->first();
            if ($shipping_type->shipping_type == "category_wise") {
                $cat_id = $product->category_id;
                $CategoryShippingCost = CategoryShippingCost::where(['seller_id' => 0, 'category_id' => $cat_id])->first();
                $delivery_cost = $CategoryShippingCost ?
                    ($CategoryShippingCost->multiply_qty != 0 ? ($CategoryShippingCost->cost * $quantity) : $CategoryShippingCost->cost)
                    : 0;

            } elseif ($shipping_type->shipping_type == "product_wise") {
                $delivery_cost = $product->multiply_qty != 0 ? ($product->shipping_cost * $quantity) : $product->shipping_cost;
            } elseif ($shipping_type->shipping_type == 'order_wise') {
                $max_order_wise_shipping_cost = ShippingMethod::where(['creator_id' => 1, 'creator_type' => 'admin', 'status' => 1])->max('cost');
                $min_order_wise_shipping_cost = ShippingMethod::where(['creator_id' => 1, 'creator_type' => 'admin', 'status' => 1])->min('cost');
            }
        } elseif ($shipping_model == "sellerwise_shipping") {

            if ($product->added_by == "admin") {
                $shipping_type = ShippingType::where('seller_id', '=', 0)->first();
            } else {
                $shipping_type = ShippingType::where('seller_id', '!=', 0)->where(['seller_id' => $product->user_id])->first();
            }

            if ($shipping_type) {
                $shipping_type = $shipping_type ?? ShippingType::where('seller_id', '=', 0)->first();
                if ($shipping_type->shipping_type == "category_wise") {
                    $cat_id = $product->category_id;
                    if ($product->added_by == "admin") {
                        $CategoryShippingCost = CategoryShippingCost::where(['seller_id' => 0, 'category_id' => $cat_id])->first();
                    } else {
                        $CategoryShippingCost = CategoryShippingCost::where(['seller_id' => $product->user_id, 'category_id' => $cat_id])->first();
                    }

                    $delivery_cost = $CategoryShippingCost ?
                        ($CategoryShippingCost->multiply_qty != 0 ? ($CategoryShippingCost->cost * $quantity) : $CategoryShippingCost->cost)
                        : 0;
                } elseif ($shipping_type->shipping_type == "product_wise") {
                    $delivery_cost = $product->multiply_qty != 0 ? ($product->shipping_cost * $quantity) : $product->shipping_cost;
                } elseif ($shipping_type->shipping_type == 'order_wise') {
                    $max_order_wise_shipping_cost = ShippingMethod::where(['creator_id' => $product->user_id, 'creator_type' => $product->added_by, 'status' => 1])->max('cost');
                    $min_order_wise_shipping_cost = ShippingMethod::where(['creator_id' => $product->user_id, 'creator_type' => $product->added_by, 'status' => 1])->min('cost');
                }
            }
        }
        $data = [
            'delivery_cost' => $delivery_cost,
            'delivery_cost_max' => isset($max_order_wise_shipping_cost) ? $max_order_wise_shipping_cost : 0,
            'delivery_cost_min' => isset($min_order_wise_shipping_cost) ? $min_order_wise_shipping_cost : 0,
            'shipping_type' => $shipping_type->shipping_type ?? '',
        ];
        return $data;
    }

    public static function getProductsColorsArray($productIds = []): array
    {
        $colorsMerge = [];
        $colorsCollection = Product::active()->where('colors', '!=', '[]')
            ->when(!empty($productIds), function ($query) use ($productIds) {
                return $query->whereIn('id', $productIds);
            })
            ->pluck('colors')->unique()->toArray();
        foreach ($colorsCollection as $colorJson) {
            $colorArray = json_decode($colorJson, true);
            if ($colorArray) {
                $colorsMerge = array_merge($colorsMerge, $colorArray);
            }
        }
        return array_unique($colorsMerge);
    }

    public static function getPriorityWiseFeaturedProductsQuery($query, $dataLimit = 'all', $offset = 1, $appends = null)
    {
        $featuredProductSortBy = getWebConfig(name: 'featured_product_priority');

        $query = $query->withCount(['orderDetails', 'reviews', 'wishList'])->withAvg('reviews', 'rating');

        if ($featuredProductSortBy && ($featuredProductSortBy['custom_sorting_status'] == 1)) {

            $query = self::getSortingProductByTemporaryClose(query: $query, temporaryCloseStatus: $featuredProductSortBy['temporary_close_sorting']);

            if ($featuredProductSortBy['out_of_stock_product'] == 'hide') {
                $query = $query->where(function ($query) {
                    $query->where('product_type', 'digital')->orWhere(function ($query) {
                        $query->where('product_type', 'physical')->where('current_stock', '>', 0);
                    });
                });
            }

            if ($featuredProductSortBy['sort_by'] == 'latest_created') {
                $query = $query->orderBy('id', 'desc');
            } elseif ($featuredProductSortBy['sort_by'] == 'first_created') {
                $query = $query->orderBy('id', 'asc');
            } elseif ($featuredProductSortBy['sort_by'] == 'most_order') {
                $query = $query->orderBy('order_details_count', 'desc');
            } elseif ($featuredProductSortBy['sort_by'] == 'reviews_count') {
                $query = $query->orderBy('reviews_count', 'desc');
            } elseif ($featuredProductSortBy['sort_by'] == 'rating') {
                $query = $query->orderBy('reviews_avg_rating', 'desc')->orderBy('reviews_avg_rating', 'desc');
            } elseif ($featuredProductSortBy['sort_by'] == 'a_to_z') {
                $query = $query->orderBy('name', 'asc');
            } elseif ($featuredProductSortBy['sort_by'] == 'z_to_a') {
                $query = $query->orderBy('name', 'desc');
            }

            $query = $query->where(['featured' => 1])->get();

            if ($featuredProductSortBy['out_of_stock_product'] == 'desc') {
                $query = self::mergeStockAndOutOfStockProduct(query: $query);
            }

            if ($featuredProductSortBy['temporary_close_sorting'] == 'desc') {
                $query = $query->sortBy('is_shop_temporary_close');
            }

            if ($dataLimit != 'all') {
                $currentPage = $offset ?? Paginator::resolveCurrentPage('page');
                $totalSize = $query->count();
                $results = $query->forPage($currentPage, $dataLimit);
                return new LengthAwarePaginator($results, $totalSize, $dataLimit, $currentPage, [
                    'path' => Paginator::resolveCurrentPath(),
                    'appends' => $appends,
                ]);
            }

            return $query;
        }

        $query = $query->where(['featured' => 1])->orderBy('id', 'desc');

        if ($dataLimit != 'all') {
            return $query->paginate($dataLimit, ['*'], 'page', request()->get('page', $offset));
        }

        return $query->orderBy('id', 'desc')->get();
    }


    public static function getPriorityWiseTopRatedProductsQuery($query, $dataLimit = 'all', $offset = 1, $appends = null)
    {
        $topRatedProductSortBy = getWebConfig(name: 'top_rated_product_list_priority');

        $query = $query->withCount(['orderDetails', 'reviews', 'wishList'])->withAvg('reviews', 'rating');

        if ($topRatedProductSortBy && ($topRatedProductSortBy['custom_sorting_status'] == 1)) {

            $query = self::getSortingProductByTemporaryClose(query: $query, temporaryCloseStatus: $topRatedProductSortBy['temporary_close_sorting']);

            if ($topRatedProductSortBy['out_of_stock_product'] == 'hide') {
                $query = $query->where(function ($query) {
                    $query->where('product_type', 'digital')->orWhere(function ($query) {
                        $query->where('product_type', 'physical')->where('current_stock', '>', 0);
                    });
                });
            }

            if ($topRatedProductSortBy['sort_by'] == 'latest_created') {
                $query = $query->orderBy('id', 'desc');
            } elseif ($topRatedProductSortBy['sort_by'] == 'first_created') {
                $query = $query->orderBy('id', 'asc');
            } elseif ($topRatedProductSortBy['sort_by'] == 'most_order') {
                $query = $query->orderBy('order_details_count', 'desc');
            } elseif ($topRatedProductSortBy['sort_by'] == 'reviews_count') {
                $query = $query->orderBy('reviews_count', 'desc');
            } elseif ($topRatedProductSortBy['sort_by'] == 'rating') {
                $query = $query->orderBy('reviews_avg_rating', 'desc')->orderBy('reviews_avg_rating', 'desc');
            } elseif ($topRatedProductSortBy['sort_by'] == 'a_to_z') {
                $query = $query->orderBy('name', 'asc');
            } elseif ($topRatedProductSortBy['sort_by'] == 'z_to_a') {
                $query = $query->orderBy('name', 'desc');
            }

            $query = $query->get();

            if ($topRatedProductSortBy['out_of_stock_product'] == 'desc') {
                $query = self::mergeStockAndOutOfStockProduct(query: $query);
            }

            if ($topRatedProductSortBy['minimum_rating_point'] == '4') {
                $query = $query->filter(function ($shop) {
                    return $shop->reviews_avg_rating >= 4;
                });
            } else if ($topRatedProductSortBy['minimum_rating_point'] == '3.5') {
                $query = $query->filter(function ($shop) {
                    return $shop->reviews_avg_rating >= 3.5;
                });
            } else if ($topRatedProductSortBy['minimum_rating_point'] == '3') {
                $query = $query->filter(function ($shop) {
                    return $shop->reviews_avg_rating >= 3;
                });
            } else if ($topRatedProductSortBy['minimum_rating_point'] == '2') {
                $query = $query->filter(function ($shop) {
                    return $shop->reviews_avg_rating >= 2;
                });
            }

            if ($topRatedProductSortBy['temporary_close_sorting'] == 'desc') {
                $query = $query->sortBy('is_shop_temporary_close');
            }

            if ($dataLimit != 'all') {
                $currentPage = $offset ?? Paginator::resolveCurrentPage('page');
                $totalSize = $query->count();
                $results = $query->forPage($currentPage, $dataLimit);
                return new LengthAwarePaginator($results, $totalSize, $dataLimit, $currentPage, [
                    'path' => Paginator::resolveCurrentPath(),
                    'appends' => $appends,
                ]);
            }

            return $query;
        }

        $query = $query->orderBy('reviews_count', 'desc');

        if ($dataLimit != 'all') {
            return $query->paginate($dataLimit, ['*'], 'page', request()->get('page', $offset));
        }

        return $query->get();
    }

    public static function getPriorityWiseBestSellingProductsQuery($query, $dataLimit = 'all', $offset = 1, $appends = null)
    {
        $bestSellingProductSortBy = getWebConfig(name: 'best_selling_product_list_priority');

        $query = $query->withCount(['orderDetails', 'reviews', 'wishList'])->withAvg('reviews', 'rating');

        if ($bestSellingProductSortBy && ($bestSellingProductSortBy['custom_sorting_status'] == 1)) {

            $query = self::getSortingProductByTemporaryClose(query: $query, temporaryCloseStatus: $bestSellingProductSortBy['temporary_close_sorting']);

            if ($bestSellingProductSortBy['out_of_stock_product'] == 'hide') {
                $query = $query->where(function ($query) {
                    $query->where('product_type', 'digital')->orWhere(function ($query) {
                        $query->where('product_type', 'physical')->where('current_stock', '>', 0);
                    });
                });
            }

            if ($bestSellingProductSortBy['sort_by'] == 'latest_created') {
                $query = $query->orderBy('id', 'desc');
            } elseif ($bestSellingProductSortBy['sort_by'] == 'first_created') {
                $query = $query->orderBy('id', 'asc');
            } elseif ($bestSellingProductSortBy['sort_by'] == 'most_order') {
                $query = $query->orderBy('order_details_count', 'desc');
            } elseif ($bestSellingProductSortBy['sort_by'] == 'reviews_count') {
                $query = $query->orderBy('reviews_count', 'desc');
            } elseif ($bestSellingProductSortBy['sort_by'] == 'rating') {
                $query = $query->orderBy('reviews_avg_rating', 'desc')->orderBy('reviews_avg_rating', 'desc');
            } elseif ($bestSellingProductSortBy['sort_by'] == 'a_to_z') {
                $query = $query->orderBy('name', 'asc');
            } elseif ($bestSellingProductSortBy['sort_by'] == 'z_to_a') {
                $query = $query->orderBy('name', 'desc');
            }

            $query = $query->get();

            if ($bestSellingProductSortBy['out_of_stock_product'] == 'desc') {
                $query = self::mergeStockAndOutOfStockProduct(query: $query);
            }

            if ($bestSellingProductSortBy['temporary_close_sorting'] == 'desc') {
                $query = $query->sortBy('is_shop_temporary_close');
            }

            if ($dataLimit != 'all') {
                $currentPage = $offset ?? Paginator::resolveCurrentPage('page');
                $totalSize = $query->count();
                $results = $query->forPage($currentPage, $dataLimit);
                return new LengthAwarePaginator(items: $results, total: $totalSize, perPage: $dataLimit, currentPage: $currentPage, options: [
                    'path' => Paginator::resolveCurrentPath(),
                    'appends' => $appends,
                ]);
            }

            return $query;
        }

        $query = $query->orderBy('order_details_count', 'desc');

        if ($dataLimit != 'all') {
            return $query->paginate($dataLimit, ['*'], 'page', request()->get('page', $offset));
        }

        return $query->get();
    }

    public static function getPriorityWiseNewArrivalProductsQuery($query, $dataLimit = 'all', $offset = 1, $appends = null)
    {
        $newArrivalProductSortBy = getWebConfig(name: 'new_arrival_product_list_priority');

        $query = $query->withCount(['orderDetails', 'reviews', 'wishList'])->withAvg('reviews', 'rating');

        if ($newArrivalProductSortBy && ($newArrivalProductSortBy['custom_sorting_status'] == 1)) {

            $query = self::getSortingProductByTemporaryClose(query: $query, temporaryCloseStatus: $newArrivalProductSortBy['temporary_close_sorting']);

            if ($newArrivalProductSortBy['out_of_stock_product'] == 'hide') {
                $query = $query->where(function ($query) {
                    $query->where('product_type', 'digital')->orWhere(function ($query) {
                        $query->where('product_type', 'physical')->where('current_stock', '>', 0);
                    });
                });
            }

            if ($newArrivalProductSortBy['duration'] && $newArrivalProductSortBy['duration'] != 0) {
                $currentDate = Carbon::now();
                $query = $query->when($newArrivalProductSortBy['duration_type'] == 'days', function ($query) use ($currentDate, $newArrivalProductSortBy) {
                    $getDate = $currentDate->subDays($newArrivalProductSortBy['duration'] ?? 60);
                    return $query->whereDate('created_at', '>=', $getDate);
                })->when($newArrivalProductSortBy['duration_type'] == 'month', function ($query) use ($currentDate, $newArrivalProductSortBy) {
                    $getMonth = $currentDate->subMonths($newArrivalProductSortBy['duration'] ?? 1);
                    return $query->whereDate('created_at', '>=', $getMonth);
                });
            }

            if ($newArrivalProductSortBy['sort_by'] == 'latest_created') {
                $query = $query->orderBy('id', 'desc');
            } elseif ($newArrivalProductSortBy['sort_by'] == 'first_created') {
                $query = $query->orderBy('id', 'asc');
            } elseif ($newArrivalProductSortBy['sort_by'] == 'most_order') {
                $query = $query->orderBy('order_details_count', 'desc');
            } elseif ($newArrivalProductSortBy['sort_by'] == 'reviews_count') {
                $query = $query->orderBy('reviews_count', 'desc');
            } elseif ($newArrivalProductSortBy['sort_by'] == 'rating') {
                $query = $query->orderBy('reviews_avg_rating', 'desc')->orderBy('reviews_avg_rating', 'desc');
            } elseif ($newArrivalProductSortBy['sort_by'] == 'a_to_z') {
                $query = $query->orderBy('name', 'asc');
            } elseif ($newArrivalProductSortBy['sort_by'] == 'z_to_a') {
                $query = $query->orderBy('name', 'desc');
            }

            $query = $query->get();

            if ($newArrivalProductSortBy['out_of_stock_product'] == 'desc') {
                $query = self::mergeStockAndOutOfStockProduct(query: $query);
            }

            if ($newArrivalProductSortBy['temporary_close_sorting'] == 'desc') {
                $query = $query->sortBy('is_shop_temporary_close');
            }

            if ($dataLimit != 'all') {
                $currentPage = $offset ?? Paginator::resolveCurrentPage('page');
                $totalSize = $query->count();
                $results = $query->forPage($currentPage, $dataLimit);
                return new LengthAwarePaginator(items: $results, total: $totalSize, perPage: $dataLimit, currentPage: $currentPage, options: [
                    'path' => Paginator::resolveCurrentPath(),
                    'appends' => $appends,
                ]);
            }

            return $query;
        }

        $query = $query->orderBy('order_details_count', 'desc');

        if ($dataLimit != 'all') {
            return $query->paginate($dataLimit, ['*'], 'page', request()->get('page', $offset));
        }

        return $query->get();
    }

    public static function getPriorityWiseCategoryWiseProductsQuery($query, $dataLimit = 'all', $offset = 1, $appends = null)
    {
        $categoryWiseProductSortBy = getWebConfig(name: 'category_wise_product_list_priority');

        $query = $query->withCount(['orderDetails', 'reviews', 'wishList'])->withAvg('reviews', 'rating');

        if ($categoryWiseProductSortBy && ($categoryWiseProductSortBy['custom_sorting_status'] == 1)) {

            $query = self::getSortingProductByTemporaryClose(query: $query, temporaryCloseStatus: $categoryWiseProductSortBy['temporary_close_sorting']);

            if ($categoryWiseProductSortBy['out_of_stock_product'] == 'hide') {
                $query = $query->where(function ($query) {
                    $query->where('product_type', 'digital')->orWhere(function ($query) {
                        $query->where('product_type', 'physical')->where('current_stock', '>', 0);
                    });
                });
            }

            if ($categoryWiseProductSortBy['sort_by'] == 'latest_created') {
                $query = $query->orderBy('id', 'desc');
            } elseif ($categoryWiseProductSortBy['sort_by'] == 'first_created') {
                $query = $query->orderBy('id', 'asc');
            } elseif ($categoryWiseProductSortBy['sort_by'] == 'most_order') {
                $query = $query->orderBy('order_details_count', 'desc');
            } elseif ($categoryWiseProductSortBy['sort_by'] == 'reviews_count') {
                $query = $query->orderBy('reviews_count', 'desc');
            } elseif ($categoryWiseProductSortBy['sort_by'] == 'rating') {
                $query = $query->orderBy('reviews_avg_rating', 'desc')->orderBy('reviews_avg_rating', 'desc');
            } elseif ($categoryWiseProductSortBy['sort_by'] == 'a_to_z') {
                $query = $query->orderBy('name', 'asc');
            } elseif ($categoryWiseProductSortBy['sort_by'] == 'z_to_a') {
                $query = $query->orderBy('name', 'desc');
            }

            $query = $query->get();

            if ($categoryWiseProductSortBy['temporary_close_sorting'] == 'hide') {
                $inHouseShopInTemporaryClose = Cache::get(IN_HOUSE_SHOP_TEMPORARY_CLOSE_STATUS) ?? 0;
                if ($inHouseShopInTemporaryClose) {
                    $query = $query->filter(function ($product) {
                        return $product->added_by != 'admin';
                    });
                }
            }

            if ($categoryWiseProductSortBy['out_of_stock_product'] == 'hide') {
                $query = $query->filter(function ($product) {
                    return $product->product_type != 'digital' && $product->current_stock > 0;
                });
            }

            if ($categoryWiseProductSortBy['out_of_stock_product'] == 'desc') {
                $query = self::mergeStockAndOutOfStockProduct(query: $query);
            }

            if ($categoryWiseProductSortBy['temporary_close_sorting'] == 'desc') {
                $query = $query->sortBy('is_shop_temporary_close');
            }

            if ($dataLimit != 'all') {
                $currentPage = $offset ?? Paginator::resolveCurrentPage('page');
                $totalSize = $query->count();
                $results = $query->forPage($currentPage, $dataLimit);
                return new LengthAwarePaginator(items: $results, total: $totalSize, perPage: $dataLimit, currentPage: $currentPage, options: [
                    'path' => Paginator::resolveCurrentPath(),
                    'appends' => $appends,
                ]);
            }

            return $query;
        }

        $query = $query->orderBy('order_details_count', 'desc');

        if ($dataLimit != 'all') {
            return $query->paginate($dataLimit, ['*'], 'page', request()->get('page', $offset));
        }

        return $query->get();
    }

    public static function getPriorityWiseFeatureDealQuery($query, $dataLimit = 'all', $offset = 1, $appends = null)
    {
        $featureDealSortBy = getWebConfig(name: 'feature_deal_priority');

        $query = $query->with([
            'seller.shop',
            'flashDealProducts.featureDeal',
            'flashDealProducts.featureDeal' => function ($query) {
                return $query->whereDate('start_date', '<=', date('Y-m-d'))
                    ->whereDate('end_date', '>=', date('Y-m-d'));
            }
        ])
            ->whereHas('flashDealProducts.featureDeal', function ($query) {
                $query->whereDate('start_date', '<=', date('Y-m-d'))
                    ->whereDate('end_date', '>=', date('Y-m-d'));
            })->withCount(['orderDetails', 'reviews', 'wishList'])
            ->withAvg('reviews', 'rating');

        if ($featureDealSortBy && ($featureDealSortBy['custom_sorting_status'] == 1)) {

            $query = $query->when(isset($featureDealSortBy['temporary_close_sorting']) && $featureDealSortBy['temporary_close_sorting'] == 'hide', function ($query) use ($featureDealSortBy) {
                return $query->where(function ($query) {
                    return $query->where(['added_by' => 'seller'])->whereHas('seller.shop', function ($query) {
                        return $query->where(['temporary_close' => 0]);
                    });
                })->orWhere(function ($query) use ($featureDealSortBy) {
                    $inHouseShopInTemporaryClose = Cache::get(IN_HOUSE_SHOP_TEMPORARY_CLOSE_STATUS);
                    if (!$inHouseShopInTemporaryClose && $featureDealSortBy['temporary_close_sorting'] == 'hide') {
                        return $query->where(['added_by' => 'admin']);
                    } else {
                        return $query;
                    }
                });
            });

            if ($featureDealSortBy['out_of_stock_product'] == 'hide') {
                $query = $query->where(function ($query) {
                    $query->where('product_type', 'digital')->orWhere(function ($query) {
                        $query->where('product_type', 'physical')->where('current_stock', '>', 0);
                    });
                });
            }

            $query = $query->get();

            if ($featureDealSortBy['sort_by'] == 'latest_created') {
                $query = $query->sortByDesc('id');
            } elseif ($featureDealSortBy['sort_by'] == 'first_created') {
                $query = $query->sortBy('id');
            } elseif ($featureDealSortBy['sort_by'] == 'most_order') {
                $query = $query->sortByDesc('order_details_count');
            } elseif ($featureDealSortBy['sort_by'] == 'reviews_count') {
                $query = $query->sortByDesc('reviews_count');
            } elseif ($featureDealSortBy['sort_by'] == 'rating') {
                $query = $query->sortByDesc('reviews_avg_rating')->sortByDesc('reviews_avg_rating');
            } elseif ($featureDealSortBy['sort_by'] == 'a_to_z') {
                $query = $query->sortBy('name', SORT_NATURAL | SORT_FLAG_CASE);
            } elseif ($featureDealSortBy['sort_by'] == 'z_to_a') {
                $query = $query->sortByDesc('name', SORT_NATURAL | SORT_FLAG_CASE);
            }

            if ($featureDealSortBy['out_of_stock_product'] == 'desc') {
                $stockProduct = $query->filter(function ($product) {
                    return $product->product_type == 'digital' || $product->current_stock != 0;
                });
                $outOfStock = $query->filter(function ($product) {
                    return $product->current_stock <= 0 && $product->product_type != 'digital';
                });
                $query = $stockProduct->merge($outOfStock);
            }

            if (isset($featureDealSortBy['temporary_close_sorting']) && $featureDealSortBy['temporary_close_sorting'] == 'desc') {
                $query = $query->sortBy('is_shop_temporary_close');
            }

            if ($dataLimit != 'all') {
                $currentPage = $offset ?? Paginator::resolveCurrentPage('page');
                $totalSize = $query->count();
                $results = $query->forPage($currentPage, $dataLimit);
                return new LengthAwarePaginator($results, $totalSize, $dataLimit, $currentPage, [
                    'path' => Paginator::resolveCurrentPath(),
                    'appends' => $appends,
                ]);
            }

            if ($query) {
                foreach ($query as $product) {
                    $flashDealStatus = 0;
                    $flashDealEndDate = 0;
                    foreach ($product->flashDealProducts as $deal) {
                        $flashDealStatus = $deal->flashDeal ? 1 : $flashDealStatus;
                        $flashDealEndDate = isset($deal->flashDeal->end_date) ? date('Y-m-d H:i:s', strtotime($deal->flashDeal->end_date)) : $flashDealEndDate;
                    }
                    $product['flash_deal_status'] = $flashDealStatus;
                    $product['flash_deal_end_date'] = $flashDealEndDate;
                }
            }

            return $query;
        }

        if ($dataLimit != 'all') {
            return $query->paginate($dataLimit, ['*'], 'page', request()->get('page', $offset));
        }

        return $query->orderBy('id', 'desc')->get();
    }

    public static function getPriorityWiseSearchedProductQuery($query, $keyword, $dataLimit = 'all', $offset = 1, $appends = null, $type = null)
    {
        $searchedProductListSortBy = getWebConfig(name: 'searched_product_list_priority');
        $query = $query->withCount(['orderDetails', 'reviews', 'wishList'])->withAvg('reviews', 'rating');

        if ($searchedProductListSortBy && ($searchedProductListSortBy['custom_sorting_status'] == 1)) {
            $query = self::getSortingProductByTemporaryClose(query: $query, temporaryCloseStatus: $searchedProductListSortBy['temporary_close_sorting']);
            if ($searchedProductListSortBy['out_of_stock_product'] == 'hide') {
                $query = $query->where(function ($query) {
                    $query->where('product_type', 'digital')->orWhere(function ($query) {
                        $query->where('product_type', 'physical')->where('current_stock', '>', 0);
                    });
                });
            }
        }
        $searchKeyword = str_ireplace(['\'', '"', ',', ';', '<', '>', '?'], ' ', preg_replace('/\s\s+/', ' ', $keyword));
        $query = $query->orderByRaw("CASE WHEN name LIKE '%$searchKeyword%' THEN 1 ELSE 2 END, LOCATE('$searchKeyword', name), name")->get();

        if ($searchedProductListSortBy && ($searchedProductListSortBy['custom_sorting_status'] == 1 && $searchedProductListSortBy['out_of_stock_product'] == 'desc')) {
            $query = self::mergeStockAndOutOfStockProduct(query: $query);
        }
        if ($type == 'translated') {
            $query = $query->sortBy(function ($product) use ($keyword) {
                $translationValue = $product->translations->first()?->value ?? $product->name;
                return strpos($translationValue, $keyword);
            });
        }

        if ($searchedProductListSortBy && ($searchedProductListSortBy['custom_sorting_status'] == 1) && $searchedProductListSortBy['temporary_close_sorting'] == 'desc') {
            $query = $query->sortBy('is_shop_temporary_close');
        }

        if ($dataLimit != 'all') {
            $currentPage = $offset ?? Paginator::resolveCurrentPage('page');
            $totalSize = $query->count();
            $results = $query->forPage($currentPage, $dataLimit);
            return new LengthAwarePaginator(items: $results, total: $totalSize, perPage: $dataLimit, currentPage: $currentPage, options: [
                'path' => Paginator::resolveCurrentPath(),
                'appends' => $appends,
            ]);
        }

        return $query;
    }

    public static function getPriorityWiseFlashDealsProductsQuery($id = null, $userId = null): array
    {
        $cacheKey = 'cache_flash_deal_' . ($id ?? 'default');
        $cacheKeys = Cache::get(CACHE_FLASH_DEAL_KEYS, []);

        if (!in_array($cacheKey, $cacheKeys)) {
            $cacheKeys[] = $cacheKey;
            Cache::put(CACHE_FLASH_DEAL_KEYS, $cacheKeys, CACHE_FOR_3_HOURS);
        }

        $flashDeal = Cache::remember($cacheKey, CACHE_FOR_3_HOURS, function () use ($id) {
            return FlashDeal::where(['deal_type' => 'flash_deal', 'status' => 1])
                ->when($id, function ($query) use ($id) {
                    return $query->where(['id' => $id]);
                })
                ->whereDate('start_date', '<=', date('Y-m-d'))
                ->whereDate('end_date', '>=', date('Y-m-d'))
                ->withCount('products')
                ->first();
        });

        if ($flashDeal) {
            $flashDealProducts = ProductManager::getPriorityWiseFlashDealsProductsQuerySorting(
                query: Product::active()->with(['compareList', 'clearanceSale' => function ($query) {
                    return $query->active();
                }]),
                flashDeal: $flashDeal,
                userId: $userId,
            );
        }

        return [
            'flashDeal' => $flashDeal ?? null,
            'flashDealProducts' => $flashDealProducts ?? null,
        ];
    }

    public static function getPriorityWiseFlashDealsProductsQuerySorting($query, $flashDeal, $userId = null)
    {
        $flashDealSortBy = getWebConfig(name: 'flash_deal_priority');

        $query = $query->flashDeal($flashDeal['id'])
            ->with(['seller.shop', 'rating', 'reviews'])
            ->withCount(['orderDetails', 'reviews'])
            ->withAvg('reviews', 'rating');

        if ($flashDealSortBy && ($flashDealSortBy['custom_sorting_status'] == 1)) {

            $query = self::getSortingProductByTemporaryClose(query: $query, temporaryCloseStatus: $flashDealSortBy['temporary_close_sorting'] ?? 'desc');

            if ($flashDealSortBy['out_of_stock_product'] == 'hide') {
                $query = $query->where(function ($query) {
                    $query->where('product_type', 'digital')->orWhere(function ($query) {
                        $query->where('product_type', 'physical')->where('current_stock', '>', 0);
                    });
                });
            }

            $query = $query->get();

            if ($flashDealSortBy['sort_by'] == 'latest_created') {
                $query = $query->sortByDesc('id');
            } elseif ($flashDealSortBy['sort_by'] == 'first_created') {
                $query = $query->sortBy('id');
            } elseif ($flashDealSortBy['sort_by'] == 'most_order') {
                $query = $query->sortByDesc('order_details_count');
            } elseif ($flashDealSortBy['sort_by'] == 'reviews_count') {
                $query = $query->sortByDesc('reviews_count');
            } elseif ($flashDealSortBy['sort_by'] == 'rating') {
                $query = $query->sortByDesc('reviews_avg_rating');
            } elseif ($flashDealSortBy['sort_by'] == 'a_to_z') {
                $query = $query->sortBy('name', SORT_NATURAL | SORT_FLAG_CASE);
            } elseif ($flashDealSortBy['sort_by'] == 'z_to_a') {
                $query = $query->sortByDesc('name', SORT_NATURAL | SORT_FLAG_CASE);
            }

            if ($flashDealSortBy['out_of_stock_product'] == 'desc') {
                $query = self::mergeStockAndOutOfStockProduct(query: $query);
            }

            if (isset($flashDealSortBy['temporary_close_sorting']) && $flashDealSortBy['temporary_close_sorting'] == 'desc') {
                $query = $query->sortBy('is_shop_temporary_close');
            }
            return $query;
        }

        return $query->get();
    }

    public static function getPriorityWiseTopVendorQuery($query)
    {
        $request = Request::capture();
        $topVendorsSortBy = getWebConfig(name: 'top_vendor_list_priority');

        if ($topVendorsSortBy && ($topVendorsSortBy['custom_sorting_status'] == 1)) {
            if ($topVendorsSortBy['minimum_rating_point'] == '4') {
                $query = $query->filter(function ($shop) {
                    return $shop->average_rating >= 4;
                });
            } else if ($topVendorsSortBy['minimum_rating_point'] == '3.5') {
                $query = $query->filter(function ($shop) {
                    return $shop->average_rating >= 3.5;
                });
            } else if ($topVendorsSortBy['minimum_rating_point'] == '3') {
                $query = $query->filter(function ($shop) {
                    return $shop->average_rating >= 3;
                });
            } else if ($topVendorsSortBy['minimum_rating_point'] == '2') {
                $query = $query->filter(function ($shop) {
                    return $shop->average_rating >= 2;
                });
            }

            if ($topVendorsSortBy['vacation_mode_sorting'] == 'hide') {
                $query = $query->filter(function ($shop) {
                    return $shop->is_vacation_mode_now != 1;
                });
            }

            if ($topVendorsSortBy['temporary_close_sorting'] == 'hide') {
                $query = $query->filter(function ($shop) {
                    return $shop->temporary_close != 1;
                });
            }

            if ($topVendorsSortBy['sort_by'] == 'order') {
                $query = $query->sortByDesc('orders_count');
            } elseif ($topVendorsSortBy['sort_by'] == 'reviews_count') {
                $query = $query->sortByDesc('review_count');
            } elseif ($topVendorsSortBy['sort_by'] == 'rating') {
                $query = $query->sortByDesc('average_rating');
            } elseif ($topVendorsSortBy['sort_by'] == 'rating_and_review') {
                $query = $query->sortByDesc('review_count')->sortByDesc('average_rating');
            }

            if ($topVendorsSortBy['vacation_mode_sorting'] == 'desc') {
                $query = $query->sortBy('is_vacation_mode_now');
            }

            if ($topVendorsSortBy['temporary_close_sorting'] == 'desc') {
                $query = $query->sortBy('temporary_close');
            }
            return $query;
        }
        return $query;
    }

    public static function getPriorityWiseVendorQuery($query)
    {
        $vendorsSortBy = getWebConfig(name: 'vendor_list_priority');

        if ($vendorsSortBy && ($vendorsSortBy['custom_sorting_status'] == 1)) {

            if ($vendorsSortBy['sort_by'] == 'most_order') {
                $query = $query->sortByDesc('orders_count');
            } elseif ($vendorsSortBy['sort_by'] == 'reviews_count') {
                $query = $query->sortByDesc('review_count');
            } elseif ($vendorsSortBy['sort_by'] == 'rating') {
                $query = $query->sortByDesc('average_rating');
            } elseif ($vendorsSortBy['sort_by'] == 'latest_created') {
                $query = $query->sortByDesc('id');
            } elseif ($vendorsSortBy['sort_by'] == 'first_created') {
                $query = $query->sortBy('id');
            } elseif ($vendorsSortBy['sort_by'] == 'a_to_z') {
                $query = $query->sortBy('name', SORT_NATURAL | SORT_FLAG_CASE);
            } elseif ($vendorsSortBy['sort_by'] == 'z_to_a') {
                $query = $query->sortByDesc('name', SORT_NATURAL | SORT_FLAG_CASE);
            }

            if ($vendorsSortBy['vacation_mode_sorting'] == 'hide') {
                $query = $query->filter(function ($shop) {
                    if ($shop->seller_id == 0) {
                        return $shop->vacation_status != 1;
                    } else {
                        return $shop->is_vacation_mode_now != 1;
                    }
                });
            } elseif ($vendorsSortBy['vacation_mode_sorting'] == 'desc') {
                $query = $query->sortBy('is_vacation_mode_now');
            }

            if ($vendorsSortBy['temporary_close_sorting'] == 'hide') {
                $query = $query->filter(function ($shop) {
                    return $shop->temporary_close != 1;
                });
            } elseif ($vendorsSortBy['temporary_close_sorting'] == 'desc') {
                $query = $query->sortBy('temporary_close');
            }

            return $query;
        }
        return $query;
    }

    public static function getPriorityWiseVendorProductListQuery($query)
    {
        $vendorProductListSortBy = getWebConfig(name: 'vendor_product_list_priority');
        $query = $query->withCount(['orderDetails', 'reviews', 'wishList'])->withAvg('reviews', 'rating');

        if ($vendorProductListSortBy && ($vendorProductListSortBy['custom_sorting_status'] == 1)) {
            if ($vendorProductListSortBy['out_of_stock_product'] == 'hide') {
                $query = $query->where(function ($query) {
                    $query->where('product_type', 'digital')->orWhere(function ($query) {
                        $query->where('product_type', 'physical')->where('current_stock', '>', 0);
                    });
                });
            }

            $query = $query->get();

            if ($vendorProductListSortBy['sort_by'] == 'latest_created') {
                $query = $query->sortByDesc('id');
            } elseif ($vendorProductListSortBy['sort_by'] == 'first_created') {
                $query = $query->sortBy('id');
            } elseif ($vendorProductListSortBy['sort_by'] == 'most_order') {
                $query = $query->sortByDesc('order_details_count');
            } elseif ($vendorProductListSortBy['sort_by'] == 'reviews_count') {
                $query = $query->sortByDesc('reviews_count');
            } elseif ($vendorProductListSortBy['sort_by'] == 'rating') {
                $query = $query->sortByDesc('reviews_avg_rating');
            } elseif ($vendorProductListSortBy['sort_by'] == 'a_to_z') {
                $query = $query->sortBy('name', SORT_NATURAL | SORT_FLAG_CASE);
            } elseif ($vendorProductListSortBy['sort_by'] == 'z_to_a') {
                $query = $query->sortByDesc('name', SORT_NATURAL | SORT_FLAG_CASE);
            }

            if ($vendorProductListSortBy['out_of_stock_product'] == 'desc') {
                $query = self::mergeStockAndOutOfStockProduct(query: $query);
            }
            return $query;
        }

        return $query->orderBy('id', 'desc')->get();
    }

    public static function getSortingProductByTemporaryClose($query, $temporaryCloseStatus)
    {
        return $query->when($temporaryCloseStatus == 'hide', function ($query) use ($temporaryCloseStatus) {
            return $query->where(function ($query) use ($temporaryCloseStatus) {
                return $query->where(['added_by' => 'seller'])->whereHas('seller.shop', function ($query) {
                    return $query->where(['temporary_close' => 0]);
                })->orWhere(function ($query) use ($temporaryCloseStatus) {
                    $inHouseShopInTemporaryClose = Cache::get(IN_HOUSE_SHOP_TEMPORARY_CLOSE_STATUS);
                    if (!$inHouseShopInTemporaryClose && $temporaryCloseStatus == 'hide') {
                        return $query->where(['added_by' => 'admin']);
                    } else {
                        return $query;
                    }
                });
            });
        });
    }

    public static function mergeStockAndOutOfStockProduct($query): mixed
    {
        $stockProduct = $query->filter(function ($product) {
            return $product->product_type == 'digital' || $product->current_stock > 0;
        });
        $outOfStock = $query->filter(function ($product) {
            return $product->current_stock <= 0 && $product->product_type != 'digital';
        });
        return $stockProduct->merge($outOfStock);
    }

    public static function getPublishingHouseList($productIds = [], $vendorId = null): mixed
    {
        $publishingHouseList = PublishingHouse::with(['publishingHouseProducts.product'])
            ->withCount(['publishingHouseProducts' => function ($query) use ($productIds, $vendorId) {
                return $query->whereHas('product', function ($query) use ($productIds, $vendorId) {
                    return $query->active()->where('product_type', 'digital')->when(!empty($productIds), function ($query) use ($productIds) {
                        return $query->whereIn('id', $productIds);
                    })->when($vendorId && $vendorId == 0, function ($query) use ($vendorId) {
                        return $query->where(['added_by' => 'admin']);
                    })->when($vendorId && $vendorId != 0, function ($query) use ($vendorId) {
                        return $query->where(['user_id' => $vendorId, 'added_by' => 'seller']);
                    })->when(request('offer_type') == 'clearance_sale', function ($query) {
                        $stockClearanceProductIds = StockClearanceProduct::active()->pluck('product_id')->toArray();
                        return $query->whereIn('id', $stockClearanceProductIds);
                    })->when(request('offer_type') == 'flash-deals' || FacadesRequest::is('flash-deals/*'), function ($query) {
                        return $query->whereHas('flashDealProducts.flashDeal');
                    });
                });
            }])->when(!empty($productIds), function ($query) use ($productIds, $vendorId) {
                return $query->whereHas('publishingHouseProducts.product', function ($query) use ($productIds, $vendorId) {
                    return $query->active()->where('product_type', 'digital')->when(!empty($productIds), function ($query) use ($productIds, $vendorId) {
                        return $query->whereIn('id', $productIds);
                    })->when($vendorId && $vendorId == 0, function ($query) use ($vendorId) {
                        return $query->where(['added_by' => 'admin']);
                    })->when($vendorId && $vendorId != 0, function ($query) use ($vendorId) {
                        return $query->where(['user_id' => $vendorId, 'added_by' => 'seller']);
                    });
                });
            })
            ->get()->sortByDesc('publishing_house_products_count');

        $productIdsArray = [];
        $publishingHouseList->each(function ($publishingHouseGroup) use (&$productIdsArray) {
            $publishingHouseGroup?->publishingHouseProducts?->each(function ($publishingHouse) use (&$productIdsArray) {
                $productIdsArray[] = $publishingHouse->product_id;
            });
        });

        if (request()->is('flash-deals*')) {
            $productIdsArray = self::getFlashDealProductsArray();
        }

        $productCount = Product::active()
            ->where(['product_type' => 'digital'])
            ->whereNotIn('id', $productIdsArray)
            ->when(!empty($productIds), function ($query) use ($productIds) {
                return $query->whereIn('id', $productIds);
            })
            ->when($vendorId && $vendorId == 0, function ($query) use ($vendorId) {
                return $query->where(['added_by' => 'admin']);
            })->when($vendorId && $vendorId != 0, function ($query) use ($vendorId) {
                return $query->where(['user_id' => $vendorId, 'added_by' => 'seller']);
            })
            ->when(request('offer_type') == 'clearance_sale', function ($query) use ($vendorId) {
                $stockClearanceProductIds = StockClearanceProduct::active()->pluck('product_id')->toArray();
                return $query->whereIn('id', $stockClearanceProductIds);
            })
            ->when(request('offer_type') == 'flash-deals' || FacadesRequest::is('flash-deals/*'), function ($query) {
                return $query->whereHas('flashDealProducts.flashDeal');
            })
            ->count();
        if ($productCount > 0) {
            $unknownItem = new PublishingHouse([
                "name" => "Unknown",
                "created_at" => now(),
                "updated_at" => now(),
            ]);
            $unknownItem['id'] = 0;
            $unknownItem['publishing_house_products_count'] = $productCount;
            return $publishingHouseList->push($unknownItem);
        }
        return $publishingHouseList;
    }

    public static function getProductAuthorList($productIds = [], $vendorId = null): mixed
    {
        $authorList = Author::withCount(['digitalProductAuthor' => function ($query) use ($productIds, $vendorId) {
            return $query->whereHas('product', function ($query) use ($productIds, $vendorId) {
                    return $query->active()->where('product_type', 'digital')->when(!empty($productIds), function ($query) use ($productIds, $vendorId) {
                        return $query->whereIn('id', $productIds);
                    })
                    ->when($vendorId && $vendorId == 0, function ($query) use ($vendorId) {
                        return $query->where(['added_by' => 'admin']);
                    })->when($vendorId && $vendorId != 0, function ($query) use ($vendorId) {
                        return $query->where(['user_id' => $vendorId, 'added_by' => 'seller']);
                    })
                    ->when(request('offer_type') == 'clearance_sale', function ($query) {
                        $stockClearanceProductIds = StockClearanceProduct::active()->pluck('product_id')->toArray();
                        return $query->whereIn('id', $stockClearanceProductIds);
                    })
                    ->when(request('offer_type') == 'flash-deals' || FacadesRequest::is('flash-deals/*'), function ($query) {
                        return $query->whereHas('flashDealProducts.flashDeal');
                    });
            });
        }])->when(!empty($productIds), function ($query) use ($productIds, $vendorId) {
            return $query->whereHas('digitalProductAuthor.product', function ($query) use ($productIds, $vendorId) {
                return $query->active()->where('product_type', 'digital')->when(!empty($productIds), function ($query) use ($productIds) {
                    return $query->whereIn('id', $productIds);
                })
                    ->when($vendorId && $vendorId == 0, function ($query) use ($vendorId) {
                        return $query->where(['added_by' => 'admin']);
                    })->when($vendorId && $vendorId != 0, function ($query) use ($vendorId) {
                        return $query->where(['user_id' => $vendorId, 'added_by' => 'seller']);
                    });
            });
        })->orderBy('name', 'asc')->get()->sortByDesc('digital_product_author_count');

        $productIdsArray = [];
        $authorList->each(function ($authorGroup) use (&$productIdsArray) {
            $authorGroup?->digitalProductAuthor?->each(function ($authorItem) use (&$productIdsArray) {
                $productIdsArray[] = $authorItem->product_id;
            });
        });

        if (request()->is('flash-deals*')) {
            $productIdsArray = self::getFlashDealProductsArray();
        }

        $productCount = Product::active()
            ->where(['product_type' => 'digital'])
            ->whereNotIn('id', $productIdsArray)
            ->when(!empty($productIds), function ($query) use ($productIds) {
                return $query->whereIn('id', $productIds);
            })
            ->when($vendorId && $vendorId == 0, function ($query) use ($vendorId) {
                return $query->where(['added_by' => 'admin']);
            })->when($vendorId && $vendorId != 0, function ($query) use ($vendorId) {
                return $query->where(['user_id' => $vendorId, 'added_by' => 'seller']);
            })->when(request('offer_type') == 'clearance_sale', function ($query) use ($vendorId) {
                $stockClearanceProductIds = StockClearanceProduct::active()->pluck('product_id')->toArray();
                return $query->whereIn('id', $stockClearanceProductIds);
            })
            ->when(request('offer_type') == 'flash-deals' || FacadesRequest::is('flash-deals/*'), function ($query) {
                return $query->whereHas('flashDealProducts.flashDeal');
            })
            ->count();
        if ($productCount > 0) {
            $unknownItem = new Author([
                "name" => "Unknown",
                "created_at" => now(),
                "updated_at" => now(),
            ]);
            $unknownItem['id'] = 0;
            $unknownItem['digital_product_author_count'] = $productCount;
            return $authorList->push($unknownItem);
        }
        return $authorList;
    }

    public static function getFlashDealProductsArray()
    {
        $flashDealQuery = FlashDeal::where(['deal_type' => 'flash_deal', 'status' => 1])
            ->whereDate('start_date', '<=', date('Y-m-d'))
            ->whereDate('end_date', '>=', date('Y-m-d'))
            ->first();

        $productIdsArray = [];
        if ($flashDealQuery) {
            $flashDealProducts = FlashDealProduct::where(['flash_deal_id' => $flashDealQuery->id])
                ->with('product')
                ->whereHas('product', function ($query) {
                    return $query->active();
                })
                ->get()
                ->pluck('product');

            $productIdsArray = $flashDealProducts?->whereNotNull('id')?->pluck('id')->toArray() ?? [];
        }
        return $productIdsArray;
    }

    public static function getProductListData($request, $productUserID = null, $productAddedBy = null, $type = null): mixed
    {
        if ($request->has('category_ids')) {
            $filteredData = array_filter($request['category_ids'], function ($value) {
                return !is_null($value) && $value != 'all';
            });

            $request->merge(['category_ids' => $filteredData]);
        }

        $publishingHouseList = PublishingHouse::with(['publishingHouseProducts'])
            ->whereHas('publishingHouseProducts.product', function ($query) {
                return $query->active();
            })
            ->withCount(['publishingHouseProducts' => function ($query) {
                return $query->whereHas('product', function ($query) {
                    return $query->active();
                });
            }])->get();

        $productIdsForPublisher = [];
        $publishingHouseList->each(function ($publishingHouseGroup) use (&$productIdsForPublisher) {
            $publishingHouseGroup?->publishingHouseProducts?->each(function ($publishingHouse) use (&$productIdsForPublisher) {
                $productIdsForPublisher[] = $publishingHouse->product_id;
            });
        });

        $productIdsForUnknownPublisher = Product::active()->where(['product_type' => 'digital'])->whereNotIn('id', $productIdsForPublisher)->pluck('id')->toArray();

        $authorList = Author::withCount(['digitalProductAuthor' => function ($query) {
            return $query->whereHas('product', function ($query) {
                return $query->active();
            });
        }])->get();

        $productIdsForAuthor = [];
        $authorList->each(function ($authorGroup) use (&$productIdsForAuthor) {
            $authorGroup?->digitalProductAuthor?->each(function ($authorItem) use (&$productIdsForAuthor) {
                $productIdsForAuthor[] = $authorItem->product_id;
            });
        });
        $productIdsForUnknownAuthor = Product::active()->where(['product_type' => 'digital'])->whereNotIn('id', $productIdsForAuthor)->pluck('id')->toArray();

        $productSortBy = $request->get('sort_by');

        if (FacadesRequest::is('flash-deals/*') && $request->has('flash_deals_id') && $request['flash_deals_id']) {
            $type = 'flash-deals';
        }

        if ($request->has('search_category_value') && !$request->has('category_ids')) {
            unset($request['search_category_value']);
        }

        $productListData = Product::active()
            ->with(['category', 'reviews', 'rating', 'seller.shop', 'clearanceSale' => function ($query) {
                return $query->active()->with(['setup']);
            }])
            ->withAvg('reviews', 'rating')
            ->when($productAddedBy == 'admin', function ($query) use ($productAddedBy) {
                return $query->where(['added_by' => $productAddedBy]);
            })
            ->when($productUserID && $productAddedBy == 'seller', function ($query) use ($productUserID, $productAddedBy) {
                return $query->where(['added_by' => $productAddedBy, 'user_id' => $productUserID]);
            })
            ->when(in_array($request['product_type'], ['physical', 'digital']), function ($query) use ($request) {
                return $query->where(['product_type' => $request['product_type']]);
            })
            ->withCount(['reviews'])
            ->withSum('orderDetails', 'qty', function ($query) {
                $query->where('delivery_status', 'delivered');
            })
            ->when($type == 'flash-deals' && $request->has('flash_deals_id') && !empty($request['flash_deals_id']), function ($query) use ($request) {
                $flashDealProducts = FlashDealProduct::where(['flash_deal_id' => $request['flash_deals_id']])?->pluck('product_id')?->toArray() ?? [];
                return $query->whereIn('id', $flashDealProducts);
            })
            ->when($request['data_from'] == 'brand' && $request['brand_id'], function ($query) use ($request) {
                return $query->where('brand_id', $request['brand_id']);
            })
            ->when($request->has('brand_ids') && !empty($request['brand_ids']) && is_array($request['brand_ids']), function ($query) use ($request) {
                return $query->whereIn('brand_id', $request['brand_ids']);
            })
            ->when($request->has('search_category_value') && !empty($request['search_category_value']) && $request['search_category_value'] != 'all', function ($query) use ($request) {
                return $query->where(['category_id' => $request['search_category_value']])
                    ->orWhere(function ($query) use ($request) {
                        $query->where('sub_category_id', $request['search_category_value']);
                    })
                    ->orWhere(function ($query) use ($request) {
                        $query->where('sub_sub_category_id', $request['search_category_value']);
                    });
            })
            ->when($request['offer_type'] == 'clearance_sale', function ($query) {
                $stockClearanceProductIds = StockClearanceProduct::active()->pluck('product_id')->toArray();
                return $query->whereIn('id', $stockClearanceProductIds);
            })
            ->when($request['offer_type'] == 'clearance_sale' && $productUserID && $productAddedBy == 'seller', function ($query) use ($productUserID, $productAddedBy) {
                $stockClearanceProductIds = StockClearanceProduct::active()->pluck('product_id')->toArray();
                return $query->where(['added_by' => $productAddedBy, 'user_id' => $productUserID])->whereIn('id', $stockClearanceProductIds);
            })
            ->when(
                ($request['data_from'] == 'category' && ($request['category_id'] || $request['sub_category_id'] || $request['sub_sub_category_id'])) ||
                ($request->has('category_ids') && !empty($request['category_ids']) && is_array($request['category_ids'])),
                function ($query) use ($request, $productAddedBy, $productUserID) {
                    return self::addAdditionalQueryForCategoryWithSubCategories(
                        request: $request,
                        query: $query,
                        productAddedBy: $productAddedBy,
                        productUserID: $productUserID,
                    );
                })
            ->when($request->has('publishing_house_id') && $request['publishing_house_id'] != '' && $request['publishing_house_id'] != 0, function ($query) use ($request) {
                $digitalPublishingHouseIds = DigitalProductPublishingHouse::whereHas('product', function ($query) {
                    return $query->active();
                })->where(['publishing_house_id' => $request['publishing_house_id']])->pluck('product_id')->toArray();
                return $query->whereIn('id', $digitalPublishingHouseIds ?? []);
            })
            ->when($request->has('publishing_house_id') && $request['publishing_house_id'] != '' && $request['publishing_house_id'] == 0, function ($query) use ($request) {
                $publishingHouseList = PublishingHouse::with(['publishingHouseProducts'])
                    ->whereHas('publishingHouseProducts.product', function ($query) {
                        return $query->active();
                    })
                    ->withCount(['publishingHouseProducts' => function ($query) {
                        return $query->whereHas('product', function ($query) {
                            return $query->active();
                        });
                    }])->get();

                $productIds = [];
                $publishingHouseList->each(function ($publishingHouseGroup) use (&$productIds) {
                    $publishingHouseGroup?->publishingHouseProducts?->each(function ($publishingHouse) use (&$productIds) {
                        $productIds[] = $publishingHouse->product_id;
                    });
                });
                return $query->where(['product_type' => 'digital'])->whereNotIn('id', $productIds);
            })
            ->when($request->has('publishing_house_ids') && !empty($request['publishing_house_ids']), function ($query) use ($request, $productIdsForUnknownPublisher) {
                $publishingHouseList = PublishingHouse::whereIn('id', $request['publishing_house_ids'])->with(['publishingHouseProducts'])->withCount(['publishingHouseProducts' => function ($query) {
                    return $query->whereHas('product', function ($query) {
                        return $query->active();
                    });
                }])->get();

                $publishingHouseProductIds = [];
                $publishingHouseList->each(function ($publishingHouseGroup) use (&$publishingHouseProductIds) {
                    $publishingHouseGroup?->publishingHouseProducts?->each(function ($publishingHouse) use (&$publishingHouseProductIds) {
                        $publishingHouseProductIds[] = $publishingHouse->product_id;
                    });
                });

                if (in_array(0, $request['publishing_house_ids'])) {
                    $publishingHouseProductIds = array_merge($publishingHouseProductIds, $productIdsForUnknownPublisher);
                }

                return $query->where(['product_type' => 'digital'])->whereIn('id', $publishingHouseProductIds);
            })
            ->when($request->has('author_id') && $request['author_id'] != '' && $request['author_id'] != 0, function ($query) use ($request) {
                $digitalAuthorIds = DigitalProductAuthor::where(['author_id' => $request['author_id']])->pluck('product_id')->toArray();
                return $query->whereIn('id', $digitalAuthorIds);
            })
            ->when($request->has('author_id') && $request['author_id'] != '' && $request['author_id'] == 0, function ($query) use ($request) {
                $authorList = Author::withCount(['digitalProductAuthor' => function ($query) {
                    return $query->whereHas('product', function ($query) {
                        return $query->active();
                    });
                }])->get();

                $productIds = [];
                $authorList->each(function ($authorGroup) use (&$productIds) {
                    $authorGroup?->digitalProductAuthor?->each(function ($authorItem) use (&$productIds) {
                        $productIds[] = $authorItem->product_id;
                    });
                });

                return $query->where(['product_type' => 'digital'])->whereNotIn('id', $productIds);
            })
            ->when($request->has('author_ids') && !empty($request['author_ids']) && is_array($request['author_ids']), function ($query) use ($request, $productIdsForUnknownAuthor) {

                $authorList = Author::whereIn('id', $request['author_ids'])->withCount(['digitalProductAuthor' => function ($query) {
                    return $query->whereHas('product', function ($query) {
                        return $query->active();
                    });
                }])->get();

                $authorProductIds = [];
                $authorList->each(function ($authorGroup) use (&$authorProductIds) {
                    $authorGroup?->digitalProductAuthor?->each(function ($authorItem) use (&$authorProductIds) {
                        $authorProductIds[] = $authorItem->product_id;
                    });
                });
                if (in_array(0, $request['author_ids'])) {
                    $authorProductIds = array_merge($authorProductIds, $productIdsForUnknownAuthor);
                }
                return $query->where(['product_type' => 'digital'])->whereIn('id', $authorProductIds);
            })
            ->when($request['offer_type'] == 'discounted', function ($query) {
                $stockClearanceProductIds = StockClearanceProduct::active()->pluck('product_id')->toArray();
                return $query->where(function ($subQuery) use ($stockClearanceProductIds) {
                    return $subQuery->where(function ($query) {
                        return $query->where('discount', '!=', 0);
                    })->orWhere(function ($query) use ($stockClearanceProductIds) {
                        return $query->whereIn('id', $stockClearanceProductIds);
                    });
                });
            })
            ->when($request['data_from'] == 'most-favorite', function ($query) {
                $wishListItems = Wishlist::with('product')
                    ->select('product_id', DB::raw('COUNT(product_id) as count'))
                    ->groupBy('product_id')
                    ->orderBy("count", 'desc')
                    ->get();
                $getWishListedProductIds = [];
                foreach ($wishListItems as $detail) {
                    $getWishListedProductIds[] = $detail['product_id'];
                }
                return $query->whereIn('id', $getWishListedProductIds);
            })
            ->when(($request['data_from'] == 'latest' || $request['data_from'] == ''), function ($query) {
                return $query->orderBy('id', 'desc');
            })
            ->when($request->has('color_ids') && !empty($request['color_ids']) && is_array($request['color_ids']), function ($query) use ($request) {
                return $query->where(function ($query) use ($request) {
                    foreach ($request['color_ids'] as $color) {
                        $query->orWhere('colors', 'like', '%' . $color . '%');
                    }
                });
            })
            ->when($request['data_from'] == 'top-rated', function ($query) use ($request) {
                $reviews = Review::select('product_id', DB::raw('AVG(rating) as count'))->groupBy('product_id')->get();
                $getReviewProductIds = [];
                foreach ($reviews as $review) {
                    $getReviewProductIds[] = $review['product_id'];
                }
                return $query->whereIn('id', $getReviewProductIds);
            })
            ->when($request['data_from'] == 'best-selling', function ($query) use ($request) {
                $orderDetails = OrderDetail::with('product')
                    ->select('product_id', DB::raw('COUNT(product_id) as count'))
                    ->groupBy('product_id')
                    ->get();
                $getOrderedProductIds = [];
                foreach ($orderDetails as $detail) {
                    $getOrderedProductIds[] = $detail['product_id'];
                }
                return $query->whereIn('id', $getOrderedProductIds);
            })
            ->when($request['offer_type'] == 'featured_deal', function ($query) use ($request) {
                $featuredDealID = FlashDeal::where(['deal_type' => 'feature_deal', 'status' => 1])->whereDate('start_date', '<=', date('Y-m-d'))
                    ->whereDate('end_date', '>=', date('Y-m-d'))->pluck('id')->first();
                $featuredDealProductIDs = $featuredDealID ? FlashDealProduct::where('flash_deal_id', $featuredDealID)->pluck('product_id')->toArray() : [];
                return $query->whereIn('id', $featuredDealProductIDs);
            })
            ->when($request->has('name') && !empty($request['name']), function ($query) use ($request) {
                $searchName = str_ireplace(['\'', '"', ',', ';', '<', '>', '?'], ' ', preg_replace('/\s\s+/', ' ', $request['name']));
                return $query->orderByRaw("CASE WHEN name LIKE '%{$searchName}%' THEN 1 ELSE 2 END, LOCATE('{$searchName}', name), name");
            })
            ->when(($request['data_from'] == 'search' && !empty($request['search'])) || !empty($request['name']) || !empty($request['product_name']), function ($query) use ($request) {
                $searchKey = $request->search ? $request->search : ($request['product_name'] ?? $request['name']);
                $productsIDArray = [];
                $searchProducts = ProductManager::search_products($request, $searchKey);
                if ($searchProducts['products'] == null || getDefaultLanguage() != 'en') {
                    $searchProducts = ProductManager::translated_product_search(base64_encode($searchKey));
                }
                if ($searchProducts['products']) {
                    foreach ($searchProducts['products'] as $product) {
                        $productsIDArray[] = $product->id;
                    }
                }

                $searchName = str_ireplace(['\'', '"', ',', ';', '<', '>', '?'], ' ', preg_replace('/\s\s+/', ' ', $searchKey));
                return $query->when(!empty($productsIDArray), function ($query) use ($productsIDArray) {
                    return $query->whereIn('id', $productsIDArray);
                })->when(empty($productsIDArray), function ($query) use ($productsIDArray) {
                    return $query->whereIn('id', [0]);
                })->orderByRaw("CASE WHEN name LIKE '%{$searchName}%' THEN 1 ELSE 2 END, LOCATE('{$searchName}', name), name");
            })
            ->when(($request['min_price'] != null && $request['min_price'] > 0), function ($query) use ($request) {
                $minPrice = Convert::usdPaymentModule($request['min_price'] ?? 0, session('currency_code'));
                return $query->where('unit_price', '>=', $minPrice);
            })
            ->when(($request['max_price'] != null), function ($query) use ($request) {
                $maxPrice = Convert::usdPaymentModule($request['max_price'] ?? 0, session('currency_code'));
                return $query->where('unit_price', '<=', $maxPrice);
            })
            ->when($request['ratings'] != null, function ($query) use ($request) {
                return $query->whereHas('rating', function ($query) use ($request) {
                    return $query;
                });
            });
        if ($request['data_from'] == 'category') {
            $productListData = ProductManager::getPriorityWiseCategoryWiseProductsQuery(query: $productListData, dataLimit: 'all');
        } elseif ($request['data_from'] == 'top-rated') {
            $productListData = ProductManager::getPriorityWiseTopRatedProductsQuery(query: $productListData);
        } elseif ($request['data_from'] == 'best-selling') {
            $productListData = ProductManager::getPriorityWiseBestSellingProductsQuery(query: $productListData, dataLimit: 'all');
        } elseif ($request['data_from'] == 'featured') {
            $productListData = ProductManager::getPriorityWiseFeaturedProductsQuery(query: $productListData);
        } elseif ($request['offer_type'] == 'featured_deal') {
            $productListData = ProductManager::getPriorityWiseFeatureDealQuery(query: $productListData, dataLimit: 'all');
        } elseif ($request['data_from'] == 'search') {
            $productListData = ProductManager::getPriorityWiseSearchedProductQuery(query: $productListData, keyword: $request['name'], dataLimit: 'all', type: 'searched');
        } elseif ($request['offer_type'] == 'clearance_sale') {
            $productListData = ProductManager::getPriorityWiseClearanceSaleProductsQuery(query: $productListData, dataLimit: 'all');
        } elseif ($productUserID && $productAddedBy) {
            $productListData = ProductManager::getPriorityWiseVendorProductListQuery(query: $productListData);
        } else {
            $productListData = $productListData->get();
        }
        if ($productSortBy) {
            if ($request['offer_type'] == 'clearance_sale' && $productSortBy == 'latest') {
                $productListData = $productListData->map(function ($product) {
                    return $product->clearanceSale;
                });
            }
            if ($productSortBy == 'latest') {
                $productListData = $productListData->sortByDesc('id');
            } elseif ($productSortBy == 'low-high') {
                $productListData = $productListData->sortBy('unit_price');
            } elseif ($productSortBy == 'high-low') {
                $productListData = $productListData->sortByDesc('unit_price');
            } elseif ($productSortBy == 'a-z') {
                $productListData = $productListData->sortBy('name', SORT_NATURAL | SORT_FLAG_CASE);
            } elseif ($productSortBy == 'z-a') {
                $productListData = $productListData->sortByDesc('name', SORT_NATURAL | SORT_FLAG_CASE);
            } elseif ($productSortBy == 'rating-low-high') {
                $productListData = $productListData->sortBy('reviews_count')->sortBy('reviews_avg_rating');
            } elseif ($productSortBy == 'rating-high-low') {
                $productListData = $productListData->sortByDesc('reviews_count')->sortByDesc('reviews_avg_rating');
            }
        }

        if ($request['rating'] != null) {
            $productListData = $productListData->map(function ($product) use ($request) {
                $product->rating = $product?->rating?->pluck('average')[0] ?? 0;
                return $product;
            });

            $productListData = $productListData->filter(function ($product) use ($request) {
                return in_array($product->rating, $request['rating']);
            });
        }

        return $productListData;
    }

    public static function getAllProductsData($request, $productUserID = null, $productAddedBy = null): mixed
    {
        return Product::active()->with('rating')->withCount('reviews')
            ->when($productAddedBy == 'admin', function ($query) use ($productAddedBy) {
                return $query->where(['added_by' => $productAddedBy]);
            })
            ->when($request['offer_type'] == 'clearance_sale', function ($query) use ($productAddedBy) {
                $stockClearanceProductIds = StockClearanceProduct::active()->where('added_by', 'admin')->pluck('product_id')->toArray();
                return $query->whereIn('id', $stockClearanceProductIds);
            })
            ->when($request['offer_type'] == 'clearance_sale' && $productUserID && $productAddedBy == 'seller', function ($query) use ($productUserID, $productAddedBy) {
                $stockClearanceProductIds = StockClearanceProduct::active()->pluck('product_id')->toArray();
                return $query->where(['added_by' => $productAddedBy, 'user_id' => $productUserID])->whereIn('id', $stockClearanceProductIds);
            })
            ->when($productUserID && $productAddedBy == 'seller', function ($query) use ($productUserID, $productAddedBy) {
                return $query->where(['added_by' => $productAddedBy, 'user_id' => $productUserID]);
            })->get();
    }

    public static function getPriorityWiseClearanceSaleProductsQuery($query, $dataLimit = 'all', $offset = 1, $appends = null)
    {
        $stockClearanceProductSortBy = getWebConfig(name: 'stock_clearance_product_list_priority');
        $stockClearanceVendors = getWebConfig(name: 'stock_clearance_vendor_priority');

        $query = $query->withCount(['orderDetails', 'reviews', 'wishList'])->withAvg('reviews', 'rating');
        if ($stockClearanceProductSortBy && ($stockClearanceProductSortBy['custom_sorting_status'] == 1)) {

            $query = self::getSortingProductByTemporaryClose(query: $query, temporaryCloseStatus: $stockClearanceProductSortBy['temporary_close_sorting']);

            if ($stockClearanceProductSortBy['out_of_stock_product'] == 'hide') {
                $query = $query->where(function ($query) {
                    $query->where('product_type', 'digital')->orWhere(function ($query) {
                        $query->where('product_type', 'physical')->where('current_stock', '>', 0);
                    });
                });
            }

            if ($stockClearanceProductSortBy['sort_by'] == 'clearance_expiration_date') {
                $query = $query->whereHas('clearanceSale.setup', function ($query) {
                    $query->where('duration_end_date', '>=', now());
                });
            }

            $query = $query->get();

            if ($stockClearanceProductSortBy['sort_by'] == 'latest_created') {
                $query = $query->sortByDesc('id');
            } elseif ($stockClearanceProductSortBy['sort_by'] == 'most_order') {
                $query = $query->sortByDesc('order_details_count');
            } elseif ($stockClearanceProductSortBy['sort_by'] == 'reviews_count') {
                $query = $query->sortByDesc('reviews_count');
            } elseif ($stockClearanceProductSortBy['sort_by'] == 'rating') {
                $query = $query->sortByDesc('reviews_avg_rating');
            } elseif ($stockClearanceProductSortBy['sort_by'] == 'a_to_z') {
                $query = $query->sortBy('name', SORT_NATURAL | SORT_FLAG_CASE);
            } elseif ($stockClearanceProductSortBy['sort_by'] == 'z_to_a') {
                $query = $query->sortByDesc('name', SORT_NATURAL | SORT_FLAG_CASE);
            }

            if ($stockClearanceProductSortBy['sort_by'] == 'clearance_expiration_date') {
                $query = $query->sortBy(function ($query) {
                    return $query?->clearanceSale?->setup?->duration_end_date ?? $query?->clearance_sale?->setup?->duration_end_date;
                });
            }

            if (!empty($stockClearanceVendors)) {
                $query = $query->sortBy(function ($query) use ($stockClearanceVendors) {
                    return array_search($query?->clearanceSale?->shop_id, $stockClearanceVendors);
                });
            }

            if ($stockClearanceProductSortBy['out_of_stock_product'] == 'desc') {
                $query = self::mergeStockAndOutOfStockProduct(query: $query);
            }

            if ($stockClearanceProductSortBy['temporary_close_sorting'] == 'desc') {
                $query = $query->sortBy('is_shop_temporary_close');
            }

            if ($dataLimit != 'all') {
                $currentPage = $offset ?? Paginator::resolveCurrentPage('page');
                $totalSize = $query->count();
                $results = $query->forPage($currentPage, $dataLimit);
                return new LengthAwarePaginator(items: $results, total: $totalSize, perPage: $dataLimit, currentPage: $currentPage, options: [
                    'path' => Paginator::resolveCurrentPath(),
                    'appends' => $appends,
                ]);
            }

            return $query;
        }

        if ($dataLimit != 'all') {
            return $query->paginate($dataLimit, ['*'], 'page', request()->get('page', $offset));
        }

        return $query->get();
    }

    public static function updateProductPriceInCartList(object|array $request): void
    {
        $user = Helpers::getCustomerInformation($request);
        $cartItemsList = Cart::with(['product.digitalVariation'])->whereHas('product', function ($query) {
                return $query->active();
            })->when($user == 'offline', function ($query) use ($request) {
                return $query->where(['customer_id' => session('guest_id') ?? ($request->guest_id ?? 0), 'is_guest' => 1]);
            })->when($user != 'offline', function ($query) use ($user) {
                return $query->where(['customer_id' => $user->id, 'is_guest' => '0']);
            })->get();

        foreach ($cartItemsList as $cartItem) {
            $cartItemProduct = $cartItem?->product;
            if (empty($cartItem->variant) && $cartItem->price != $cartItemProduct->unit_price) {
                Cart::where(['id' => $cartItem['id']])->update(['price' => $cartItemProduct->unit_price]);
            } else if (!empty($cartItem->variant) && $cartItem->product_type == 'physical') {
                $productVariation = json_decode($cartItemProduct?->variation ?? '', true);
                if (!empty($productVariation)) {
                    foreach ($productVariation as $variation) {
                        if (isset($variation['type']) && isset($variation['price']) && $variation['type'] == $cartItem->variant && $variation['price'] != $cartItem['price']) {
                            Cart::where(['id' => $cartItem['id']])->update(['price' => $variation['price']]);
                        }
                    }
                }
            } else if ($cartItem->product_type == 'digital') {
                if (!empty($cartItem->variant) && $cartItemProduct?->digitalVariation && !empty($cartItemProduct?->digitalVariation)) {
                    foreach ($cartItemProduct->digitalVariation as $variation) {
                        if ($cartItem->variant == $variation['variant_key'] && $variation['price'] != $cartItem['price']) {
                            Cart::where(['id' => $cartItem['id']])->update(['price' => $variation['price']]);
                        }
                    }
                }
            }
        }
    }

    public static function addAdditionalQueryForCategoryWithSubCategories(
        object|array $request,
        object|array $query, mixed $productAddedBy, mixed $productUserID
    ) {
        if ($request->has('category_ids') && !empty($request['category_ids']) && is_array($request['category_ids'])) {
            $getCategories = Category::whereIn('id', ($request['category_ids'] ?? [0]))->get();
            $getSubCategories = Category::whereIn('id', ($request['sub_category_ids'] ?? [0]))->get();
            $getSubSubCategories = Category::whereIn('id', ($request['sub_sub_category_ids'] ?? [0]))->get();
        } else {
            $getCategories = Category::whereIn('id', [$request['category_id']])->get();
            $getSubCategories = Category::whereIn('id', [$request['sub_category_id']])->get();
            $getSubSubCategories = Category::whereIn('id', [$request['sub_sub_category_id']])->get();
        }

        $filteredSubCategories = $getSubCategories->filter(function ($subCategory) use ($getSubSubCategories) {
            return !$getSubSubCategories->pluck('parent_id')->contains($subCategory->id);
        });

        $filteredCategories = $getCategories->filter(function ($category) use ($getSubCategories) {
            return !$getSubCategories->pluck('parent_id')->contains($category->id);
        });

        $filteredCategoryIds = $filteredCategories->pluck('id')->toArray();
        $filteredSubCategoryIds = $filteredSubCategories->pluck('id')->toArray();
        $filteredSubSubCategoryIds = $getSubSubCategories->pluck('id')->toArray();

        $getCategoryWiseProductIds = [];

        if (!empty($filteredCategoryIds)) {
            $getCategoryWiseProductIds = array_merge($getCategoryWiseProductIds, self::addAdditionalQueryForCategoryProduct(
                request: $request,
                query: Product::whereIn('category_id', $filteredCategoryIds),
                productAddedBy: $productAddedBy,
                productUserID: $productUserID
            ));
        }

        if (!empty($filteredSubCategoryIds)) {
            $getCategoryWiseProductIds = array_merge($getCategoryWiseProductIds, self::addAdditionalQueryForCategoryProduct(
                request: $request,
                query: Product::whereIn('sub_category_id', $filteredSubCategoryIds),
                productAddedBy: $productAddedBy,
                productUserID: $productUserID
            ));
        }

        if (!empty($filteredSubSubCategoryIds)) {
            $getCategoryWiseProductIds = array_merge($getCategoryWiseProductIds, self::addAdditionalQueryForCategoryProduct(
                request: $request,
                query: Product::whereIn('sub_sub_category_id', $filteredSubSubCategoryIds),
                productAddedBy: $productAddedBy,
                productUserID: $productUserID
            ));
        }

        return $query->whereIn('id', $getCategoryWiseProductIds);
    }

    public static function addAdditionalQueryForCategoryProduct(object|array $request, object|array $query, mixed $productAddedBy, mixed $productUserID)
    {
        return $query->active()
            ->when($productAddedBy == 'admin', function ($query) use ($productAddedBy) {
                return $query->where(['added_by' => $productAddedBy]);
            })
            ->when($productUserID && $productAddedBy == 'seller', function ($query) use ($productUserID, $productAddedBy) {
                return $query->where(['added_by' => $productAddedBy, 'user_id' => $productUserID]);
            })
            ->when(in_array($request['product_type'], ['physical', 'digital']), function ($query) use ($request) {
                return $query->where(['product_type' => $request['product_type']]);
            })->pluck('id')->toArray();
    }
}
