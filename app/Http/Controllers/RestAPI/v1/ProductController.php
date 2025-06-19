<?php

namespace App\Http\Controllers\RestAPI\v1;

use App\Contracts\Repositories\AuthorRepositoryInterface;
use App\Contracts\Repositories\CategoryRepositoryInterface;
use App\Contracts\Repositories\PublishingHouseRepositoryInterface;
use App\Contracts\Repositories\RestockProductCustomerRepositoryInterface;
use App\Contracts\Repositories\RestockProductRepositoryInterface;
use App\Http\Controllers\Controller;
use App\Models\Author;
use App\Models\Category;
use App\Models\DigitalProductAuthor;
use App\Models\DigitalProductPublishingHouse;
use App\Models\MostDemanded;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Product;
use App\Models\PublishingHouse;
use App\Models\Review;
use App\Models\ShippingMethod;
use App\Models\Shop;
use App\Models\StockClearanceProduct;
use App\Models\Wishlist;
use App\Services\ProductService;
use App\Traits\CacheManagerTrait;
use App\Traits\FileManagerTrait;
use App\Utils\CategoryManager;
use App\Utils\Helpers;
use App\Utils\ImageManager;
use App\Utils\ProductManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    use FileManagerTrait, CacheManagerTrait;

    public function __construct(
        private Product                                            $product,
        private Order                                              $order,
        private MostDemanded                                       $most_demanded,
        private readonly AuthorRepositoryInterface                 $authorRepo,
        private readonly PublishingHouseRepositoryInterface        $publishingHouseRepo,
        private readonly ProductService                            $productService,
        private readonly RestockProductCustomerRepositoryInterface $restockProductCustomerRepo,
        private readonly RestockProductRepositoryInterface         $restockProductRepo,
        private readonly CategoryRepositoryInterface         $categoryRepo,
    )
    {
    }

    public function get_latest_products(Request $request): JsonResponse
    {
        $products = ProductManager::get_latest_products($request, $request['limit'], $request['offset']);
        $products['products'] = Helpers::product_data_formatting($products['products'], true);
        return response()->json($products, 200);
    }

    public function getNewArrivalProducts(Request $request): JsonResponse
    {
        $products = ProductManager::getNewArrivalProducts($request, $request['limit'], $request['offset']);
        $productsList = $products->total() > 0 ? Helpers::product_data_formatting($products->items(), true) : [];
        return response()->json([
            'total_size' => $products->total(),
            'limit' => (int)$request['limit'],
            'offset' => (int)$request['offset'],
            'products' => $productsList
        ]);
    }

    public function getFeaturedProductsList(Request $request): JsonResponse
    {
        $products = ProductManager::getFeaturedProductsList($request, $request['limit'], $request['offset']);
        $products['products'] = Helpers::product_data_formatting($products['products'], true);
        return response()->json($products, 200);
    }

    public function getTopRatedProducts(Request $request): JsonResponse
    {
        $products = ProductManager::getTopRatedProducts($request, $request['limit'], $request['offset']);
        $productsList = count($products->items()) > 0 ? Helpers::product_data_formatting($products->items(), true) : [];
        return response()->json([
            'total_size' => $products->total(),
            'limit' => (int)$request['limit'],
            'offset' => (int)$request['offset'],
            'products' => $productsList
        ]);
    }

    public function get_searched_products(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::validationErrorProcessor($validator)], 403);
        }

        $products = ProductManager::search_products($request, $request['name'], 'all', $request['limit'], $request['offset']);

        if ($products['products'] == null) {
            $products = ProductManager::translated_product_search(base64_encode($request['name']), 'all', $request['limit'], $request['offset']);
        }
        $products['products'] = Helpers::product_data_formatting($products['products'], true);
        return response()->json($products, 200);
    }

    public function getProductsFilter(Request $request): JsonResponse
    {
        $search = [base64_decode($request->search)];
        $categories = json_decode($request->category);
        $brand = json_decode($request->brand);
        $publishingHouses = $request->has('publishing_houses') ? json_decode($request['publishing_houses']) : [];
        $productAuthors = $request->has('product_authors') ? json_decode($request['product_authors']) : [];

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

        $productIdsForUnknownPublisher = Product::active()->with(['clearanceSale' => function ($query) {
            return $query->active();
        }])->where(['product_type' => 'digital'])->whereNotIn('id', $productIdsForPublisher)->pluck('id')->toArray();

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
        $productIdsForUnknownAuthor = Product::active()->with(['clearanceSale' => function ($query) {
            return $query->active();
        }])->where(['product_type' => 'digital'])->whereNotIn('id', $productIdsForAuthor)->pluck('id')->toArray();

        $productsIDArray = [];
        if ($request->has('search') && !empty($request['search'])) {
            $searchProducts = ProductManager::search_products($request, base64_decode($request->search), 'all', $request['limit'], $request['offset']);
            if ($searchProducts['products'] == null) {
                $searchProducts = ProductManager::translated_product_search($request->search, 'all', $request['limit'], $request['offset']);
            }
            if ($searchProducts['products']) {
                foreach ($searchProducts['products'] as $product) {
                    $productsIDArray[] = $product->id;
                }
            }
        }

        $categoryList = Category::where(['position' => 0])->whereIn('id', $categories)->pluck('id')->toArray();
        $subCategoryIds = Category::where(['position' => 1])->whereIn('id', $categories)->pluck('id')->toArray();
        $subSubCategoryIds = Category::where(['position' => 2])->whereIn('id', $categories)->pluck('id')->toArray();

        // Products search
        $products = Product::active()->with(['rating', 'tags', 'clearanceSale' => function ($query) {
                return $query->active();
            }])
            ->when(!empty($productsIDArray), function ($query) use ($productsIDArray) {
                return $query->whereIn('id', $productsIDArray);
            })
            ->withCount(['reviews' => function ($query) {
                $query->active()->whereNull('delivery_man_id');
            }])
            ->when(in_array($request['product_type'], ['physical', 'digital']), function ($query) use ($request) {
                return $query->where(['product_type' => $request['product_type']]);
            })
            ->when($request->has('brand') && count($brand) > 0, function ($query) use ($request, $brand) {
                return $query->whereIn('brand_id', $brand);
            })
            ->when($request->has('category') && count($categoryList) > 0, function ($query) use ($categoryList, $subCategoryIds, $subSubCategoryIds) {
                return $query->whereIn('category_id', $categoryList)
                    ->when(count($subCategoryIds) > 0, function ($query) use ($subCategoryIds) {
                        return $query->whereIn('sub_category_id', $subCategoryIds);
                    })->when(count($subSubCategoryIds) > 0, function ($query) use ($subSubCategoryIds) {
                        return $query->whereIn('sub_sub_category_id', $subSubCategoryIds);
                    });
            })
            ->when($request->has('publishing_houses') && $publishingHouses, function ($query) use ($request, $publishingHouses, $productIdsForUnknownPublisher) {
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
                    $publishingHouseProductIds = array_merge($publishingHouseProductIds, $productIdsForUnknownPublisher);
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
            ->when($request->has('sort_by') && !empty($request->sort_by), function ($query) use ($request) {
                $query->when($request['sort_by'] == 'low-high', function ($query) {
                    return $query->orderBy('unit_price', 'ASC');
                })
                    ->when($request['sort_by'] == 'high-low', function ($query) {
                        return $query->orderBy('unit_price', 'DESC');
                    })
                    ->when($request['sort_by'] == 'a-z', function ($query) {
                        return $query->orderBy('name', 'ASC');
                    })
                    ->when($request['sort_by'] == 'z-a', function ($query) {
                        return $query->orderBy('name', 'DESC');
                    })
                    ->when($request['sort_by'] == 'latest', function ($query) {
                        return $query->latest();
                    });
            })
            ->when($request['offer_type'] == 'clearance_sale', function ($query) {
                $stockClearanceProductIds = StockClearanceProduct::active()->pluck('product_id')->toArray();
                return $query->whereIn('id', $stockClearanceProductIds);
            })
            ->when(!empty($request['price_min']) || !empty($request['price_max']), function ($query) use ($request) {
                return $query->whereBetween('unit_price', [$request['price_min'], $request['price_max']]);
            });

        if (request('offer_type') == 'clearance_sale') {
            $products = ProductManager::getPriorityWiseClearanceSaleProductsQuery(query: $products, dataLimit: $request['limit'], offset: $request['offset']);
        } else {
            $products = ProductManager::getPriorityWiseSearchedProductQuery(query: $products, keyword: implode(' ', $search), dataLimit: $request['limit'], offset: $request['offset'], type: 'searched');
        }

        return response()->json([
            'total_size' => $products->total(),
            'limit' => $request['limit'],
            'offset' => $request['offset'],
            'min_price' => $products->min('unit_price'),
            'max_price' => $products->max('unit_price'),
            'products' => count($products) > 0 ? Helpers::product_data_formatting($products->items(), true) : [],
        ]);
    }

    public function get_suggestion_product(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::validationErrorProcessor($validator)], 403);
        }

        $products = ProductManager::search_products($request, $request['name'], 'all', $request['limit'], $request['offset']);
        if ($products['products'] == null) {
            $products = ProductManager::translated_product_search(base64_encode($request['name']), 'all', $request['limit'], $request['offset']);
        }

        $products_array = [];
        if ($products['products']) {
            foreach ($products['products'] as $product) {
                $products_array[] = [
                    'id' => $product->id,
                    'name' => $product->name,
                ];
            }
        }

        return response()->json(['products' => $products_array], 200);
    }

    public function getProductDetails(Request $request, $slug): JsonResponse
    {
        $user = Helpers::getCustomerInformation($request);

        $product = Product::with(['reviews.customer', 'seller.shop', 'tags', 'digitalVariation', 'clearanceSale' => function ($query) {
                return $query->active();
            }])
            ->withCount(['wishList' => function ($query) use ($user) {
                $query->where('customer_id', $user != 'offline' ? $user->id : '0');
            }])
            ->where(['slug' => $slug])->first();

        if (isset($product)) {
            $restockRequestedIds = $this->restockProductRepo->getListWhere(filters: ['product_id' => $product['id']], dataLimit: 'all')?->pluck('id')->toArray() ?? [];

            $product = Helpers::product_data_formatting($product, false);
            if (isset($product->reviews) && !empty($product->reviews)) {
                $overallRating = getOverallRating($product->reviews);
                $product['average_review'] = $overallRating[0];
            } else {
                $product['average_review'] = 0;
            }

            $temporary_close = getWebConfig(name: 'temporary_close');
            $inhouse_vacation = getWebConfig(name: 'vacation_add');
            $inhouse_vacation_start_date = $product['added_by'] == 'admin' ? $inhouse_vacation['vacation_start_date'] : null;
            $inhouse_vacation_end_date = $product['added_by'] == 'admin' ? $inhouse_vacation['vacation_end_date'] : null;
            $inhouse_temporary_close = $product['added_by'] == 'admin' ? $temporary_close['status'] : false;
            $product['inhouse_vacation_start_date'] = $inhouse_vacation_start_date;
            $product['inhouse_vacation_end_date'] = $inhouse_vacation_end_date;
            $product['inhouse_temporary_close'] = $inhouse_temporary_close;
            $product['reviews_count'] = $product->reviews->count();
            $product['digital_product_authors_names'] = $this->productService->getProductAuthorsInfo(product: $product)['names'];
            $product['digital_product_publishing_house_names'] = $this->productService->getProductPublishingHouseInfo(product: $product)['names'];

            if ($user != 'offline' && count($restockRequestedIds) > 0) {

                $restockCustomerRequestedList = $this->restockProductCustomerRepo->getListWhere(
                    filters: ['customer_id' => $user->id, 'restock_product_ids' => $restockRequestedIds]
                )->pluck('variant')->toArray();

                $product['restock_requested_list'] = $restockCustomerRequestedList;
                $product['is_restock_requested'] = count($restockCustomerRequestedList) > 0 ? 1 : 0;
            } else {
                $product['restock_requested_list'] = [];
                $product['is_restock_requested'] = 0;
            }
        }
        return response()->json($product, 200);
    }

    public function getBestSellingProducts(Request $request): JsonResponse
    {
        $products = ProductManager::getBestSellingProductsList($request, $request['limit'], $request['offset']);
        $productsList = $products->total() > 0 ? Helpers::product_data_formatting($products->items(), true) : [];
        return response()->json([
            'total_size' => $products->total(),
            'limit' => (int)$request['limit'],
            'offset' => (int)$request['offset'],
            'products' => $productsList
        ]);
    }

    public function get_home_categories(Request $request)
    {
        $cacheKey = 'cache_home_categories_api_list' . (str_replace(' ', '_', strtolower(app()->getLocale())));
        $cacheKeys = Cache::get(CACHE_HOME_CATEGORIES_API_LIST, []);
        if (!in_array($cacheKey, $cacheKeys)) {
            $cacheKeys[] = $cacheKey;
            Cache::put(CACHE_HOME_CATEGORIES_API_LIST, $cacheKeys, CACHE_FOR_3_HOURS);
        }

        $categories = Cache::remember($cacheKey, CACHE_FOR_3_HOURS, function () use ($request) {
            $getCategories = Category::whereHas('product', function ($query) {
                return $query->active()->with(['clearanceSale' => function ($query) {
                    return $query->active();
                }]);
            })->where('home_status', true)->get();

            $getCategories->map(function ($data) use ($request) {
                $data['products'] = Helpers::product_data_formatting(CategoryManager::products($data['id'], $request, 8), true);
                return $data;
            });
            return $getCategories;
        });
        return response()->json($categories, 200);
    }

    public function get_related_products(Request $request, $id)
    {
        if (Product::find($id)) {
            $products = ProductManager::get_related_products($id, $request);
            $products = Helpers::product_data_formatting($products, true);
            return response()->json($products, 200);
        }
        return response()->json([
            'errors' => ['code' => 'product-001', 'message' => translate('product_not_found')]
        ], 404);
    }

    public function get_product_reviews($id)
    {
        $reviews = Review::with(['customer', 'reply'])->where(['product_id' => $id])->get();
        foreach ($reviews as $item) {
            $item['attachment_full_url'] = $item->attachment_full_url;
        }
        return response()->json($reviews, 200);
    }

    public function getProductReviewByOrder(Request $request, $productId, $orderId): JsonResponse
    {
        $user = $request->user();
        $reviews = Review::with('reply')->where(['product_id' => $productId, 'customer_id' => $user->id])->whereNull('delivery_man_id')->get();
        $reviewData = null;
        foreach ($reviews as $review) {
            if ($review->order_id == $orderId) {
                $reviewData = $review;
            }
        }
        if (isset($reviews[0]) && !$reviewData) {
            $reviewData = ($reviews[0]['order_id'] == null) ? $reviews[0] : null;
        }
        if ($reviewData) {
            $reviewData['attachment_full_url'] = $reviewData->attachment_full_url;
        }

        return response()->json($reviewData ?? [], 200);
    }

    public function deleteReviewImage(Request $request): JsonResponse
    {
        $review = Review::find($request['id']);

        $array = [];
        foreach ($review->attachment as $image) {
            $imageName = $image['file_name'] ?? $image;
            if ($imageName != $request['name']) {
                $array[] = $image;
            } else {
                $this->delete(filePath: 'review/' . $request['name']);
            }
        }

        $review->attachment = $array;
        $review->save();
        return response()->json(translate('review_image_removed_successfully'), 200);
    }

    public function get_product_rating($id)
    {
        try {
            $product = Product::find($id);
            $overallRating = getOverallRating($product->reviews);
            return response()->json(floatval($overallRating[0]), 200);
        } catch (\Exception $e) {
            return response()->json(['errors' => $e], 403);
        }
    }

    public function counter($product_id)
    {
        try {
            $countOrder = OrderDetail::where('product_id', $product_id)->count();
            $countWishlist = Wishlist::where('product_id', $product_id)->count();
            return response()->json(['order_count' => $countOrder, 'wishlist_count' => $countWishlist], 200);
        } catch (\Exception $e) {
            return response()->json(['errors' => $e], 403);
        }
    }

    public function socialShareLink($product_id): JsonResponse
    {
        $product = Product::where('slug', $product_id)->first();
        try {
            $link = route('product', $product->slug);
            return response()->json($link, 200);
        } catch (\Exception $e) {
            return response()->json(['errors' => $e], 403);
        }
    }

    public function submit_product_review(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'product_id' => 'required',
            'order_id' => 'required',
            'comment' => 'required',
            'rating' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::validationErrorProcessor($validator)], 403);
        }
        $image_array = [];
        if (!empty($request->file('fileUpload'))) {
            foreach ($request->file('fileUpload') as $image) {
                if ($image != null) {
                    $image_array[] = [
                        'file_name' => $this->upload('review/', 'webp', $image),
                        'storage' => getWebConfig(name: 'storage_connection_type') ?? 'public',
                    ];
                }
            }
        }


        $reviewData = Review::where([
            'delivery_man_id' => null,
            'customer_id' => $request->user()->id,
            'product_id' => $request['product_id'],
            'order_id' => $request['order_id'],
        ])->first();
        if ($reviewData) {
            $reviewData->update([
                'customer_id' => $request->user()->id,
                'product_id' => $request['product_id'],
                'comment' => $request['comment'],
                'rating' => $request['rating'],
                'attachment' => $image_array,
            ]);
        } else {
            $reviewArray = [
                'customer_id' => $request->user()->id,
                'order_id' => $request['order_id'],
                'product_id' => $request['product_id'],
                'comment' => $request['comment'],
                'rating' => $request['rating'],
                'attachment' => $image_array,
            ];


            $oldReview = Review::where(['order_id' => $request['order_id']])->get();
            if (count($oldReview) > 0) {
                $review_id = $oldReview[0]['order_id'] . (count($oldReview) + 1);
            } else {
                $review_id = $request['order_id'] . '1';
            }
            $reviewArray['id'] = $review_id;
            Review::create($reviewArray);
        }

        return response()->json(['message' => translate('successfully_review_submitted')], 200);
    }

    public function updateProductReview(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required',
            'order_id' => 'required',
            'comment' => 'required',
            'rating' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::validationErrorProcessor($validator)], 403);
        }

        $review = Review::find($request['id']);
        $image_array = [];
        if ($review && $review->attachment && $request->has('fileUpload')) {
            foreach ($review->attachment as $image) {
                $image_array[] = $image;
            }
        }
        if (!empty($request->file('fileUpload'))) {
            foreach ($request->file('fileUpload') as $image) {
                if ($image != null) {
                    $image_array[] = [
                        'file_name' => $this->upload('review/', 'webp', $image),
                        'storage' => getWebConfig(name: 'storage_connection_type') ?? 'public',
                    ];
                }
            }
        }

        $review->order_id = $request->order_id;
        $review->comment = $request->comment;
        $review->rating = $request->rating;
        $review->attachment = $image_array;
        $review->save();

        return response()->json(['message' => translate('successfully_review_updated')], 200);
    }

    public function submit_deliveryman_review(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'order_id' => 'required',
            'comment' => 'required',
            'rating' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::validationErrorProcessor($validator)], 403);
        }

        $order = Order::where([
            'id' => $request->order_id,
            'customer_id' => $request->user()->id,
            'payment_status' => 'paid'])->first();

        if (!isset($order->delivery_man_id)) {
            return response()->json(['message' => translate('invalid_review')], 403);
        }

        Review::updateOrCreate(
            [
                'delivery_man_id' => $order->delivery_man_id,
                'customer_id' => $request->user()->id,
                'order_id' => $order->id
            ],
            [
                'customer_id' => $request->user()->id,
                'order_id' => $order->id,
                'delivery_man_id' => $order->delivery_man_id,
                'comment' => $request->comment,
                'rating' => $request->rating,
            ]
        );

    }

    public function get_shipping_methods(Request $request)
    {
        $methods = ShippingMethod::where(['status' => 1])->get();
        return response()->json($methods, 200);
    }

    public function get_discounted_product(Request $request)
    {
        $products = ProductManager::get_discounted_product($request, $request['limit'], $request['offset']);
        $products['products'] = Helpers::product_data_formatting($products['products'], true);
        return response()->json($products, 200);
    }

    public function get_most_demanded_product(Request $request)
    {
        $user = Helpers::getCustomerInformation($request);
        $products = MostDemanded::where('status', 1)->with(['product' => function ($query) use ($user) {
            $query->withCount(['orderDetails', 'orderDelivered', 'reviews', 'wishList' => function ($query) use ($user) {
                $query->where('customer_id', $user != 'offline' ? $user->id : '0');
            }]);
        }])->whereHas('product', function ($query) {
            return $query->active();
        })->first();

        if ($products) {
            $products['banner'] = $products->banner ?? '';
            $products['product_id'] = $products->product['id'] ?? 0;
            $products['slug'] = $products->product['slug'] ?? '';
            $products['review_count'] = $products->product['reviews_count'] ?? 0;
            $products['order_count'] = $products->product['order_details_count'] ?? 0;
            $products['delivery_count'] = $products->product['order_delivered_count'] ?? 0;
            $products['wishlist_count'] = $products->product['wish_list_count'] ?? 0;

            unset($products->product['category_ids']);
            unset($products->product['images']);
            unset($products->product['details']);
            unset($products->product);
        } else {
            $products = [];
        }

        return response()->json($products, 200);
    }

    public function getShopAgainProduct(Request $request): JsonResponse
    {
        $user = Helpers::getCustomerInformation($request);
        if ($user != 'offline') {
            $products = Product::active()->with(['seller.shop', 'reviews', 'clearanceSale' => function ($query) {
                    return $query->active();
                }])
                ->withCount(['wishList' => function ($query) use ($user) {
                    $query->where('customer_id', $user != 'offline' ? $user->id : '0');
                }])
                ->whereHas('orderDetails.order', function ($query) use ($request) {
                    $query->where(['customer_id' => $request->user()->id]);
                })
                ->select('id', 'name', 'slug', 'thumbnail', 'unit_price', 'purchase_price', 'added_by', 'user_id')
                ->inRandomOrder()->take(12)->get();

            $products?->map(function ($product) {
                $product['reviews_count'] = $product->reviews->count();
                unset($product->reviews);
                return $product;
            });
        } else {
            $products = [];
        }


        return response()->json($products, 200);
    }

    public function just_for_you(Request $request)
    {
        $user = Helpers::getCustomerInformation($request);
        if ($user != 'offline') {
            $orders = $this->order->where(['customer_id' => $user->id])->with(['details'])->get();

            if ($orders) {
                $orders = $orders?->map(function ($order) {
                    $order_details = $order->details->map(function ($detail) {
                        $product = json_decode($detail->product_details);
                        $category = json_decode($product->category_ids)[0]->id;
                        $detail['category_id'] = $category;
                        return $detail;
                    });
                    $order['id'] = $order_details[0]->id;
                    $order['category_id'] = $order_details[0]->category_id;

                    return $order;
                });

                $categories = [];
                foreach ($orders as $order) {
                    $categories[] = ($order['category_id']);;
                }
                $ids = array_unique($categories);

                $justForYou = $this->product->with([
                        'compareList' => function ($query) use ($user) {
                            return $query->where('user_id', $user != 'offline' ? $user->id : 0);
                        },
                        'clearanceSale' => function ($query) {
                            return $query->active();
                        }
                    ])
                    ->withCount(['wishList' => function ($query) use ($user) {
                        $query->where('customer_id', $user != 'offline' ? $user->id : '0');
                    }])
                    ->active()
                    ->where(function ($query) use ($ids) {
                        foreach ($ids as $id) {
                            $query->orWhere('category_ids', 'like', "%{$id}%");
                        }
                    })
                    ->inRandomOrder()
                    ->take(8)
                    ->get();
            } else {
                $justForYou = $this->product->with([
                        'compareList' => function ($query) use ($user) {
                            return $query->where('user_id', $user != 'offline' ? $user->id : 0);
                        },
                        'clearanceSale' => function ($query) {
                            return $query->active();
                        }
                    ])
                    ->withCount(['wishList' => function ($query) use ($user) {
                        $query->where('customer_id', $user != 'offline' ? $user->id : '0');
                    }])
                    ->active()
                    ->inRandomOrder()
                    ->take(8)
                    ->get();
            }
        } else {
            $justForYou = $this->product->with([
                    'compareList' => function ($query) use ($user) {
                        return $query->where('user_id', $user != 'offline' ? $user->id : 0);
                    },
                    'clearanceSale' => function ($query) {
                        return $query->active();
                    }
                ])
                ->withCount(['wishList' => function ($query) use ($user) {
                    $query->where('customer_id', $user != 'offline' ? $user->id : '0');
                }])
                ->active()
                ->inRandomOrder()
                ->take(8)
                ->get();
        }

        $products = Helpers::product_data_formatting($justForYou, true);
        return response()->json($products, 200);
    }

    public function getMostSearchingProductsList(Request $request): JsonResponse
    {
        $products = ProductManager::getBestSellingProductsList($request, $request['limit'], $request['offset']);
        $productsList = $products->total() > 0 ? Helpers::product_data_formatting($products->items(), true) : [];
        return response()->json([
            'total_size' => $products->total(),
            'limit' => (int)$request['limit'],
            'offset' => (int)$request['offset'],
            'products' => $productsList
        ]);
    }

    public function getDigitalProductsAuthorList(Request $request): JsonResponse
    {
        $productIds = Product::active()
            ->when($request['seller_id'] == 0, function ($query) {
                return $query->where(['added_by' => 'admin']);
            })
            ->when($request['seller_id'] != 0, function ($query) use ($request) {
                return $query->where(['added_by' => 'seller', 'user_id' => $request['seller_id']]);
            })->pluck('id')->toArray();
        $authors = ProductManager::getProductAuthorList(productIds: $productIds);
        return response()->json($authors->values());
    }

    public function getDigitalPublishingHouseList(Request $request): JsonResponse
    {
        $productIds = Product::active()
            ->when($request['seller_id'] == 0, function ($query) {
                return $query->where(['added_by' => 'admin']);
            })
            ->when($request['seller_id'] != 0, function ($query) use ($request) {
                return $query->where(['added_by' => 'seller', 'user_id' => $request['seller_id']]);
            })->pluck('id')->toArray();
        $publishingHouseList = ProductManager::getPublishingHouseList(productIds: $productIds);
        return response()->json($publishingHouseList->values());
    }

    public function getClearanceSale(Request $request): JsonResponse
    {
        $productIds = StockClearanceProduct::active()->whereHas('setup', function ($query) {
            $addedBy = getWebConfig(name: 'stock_clearance_vendor_offer_in_homepage') ? ['admin', 'vendor'] : ['admin'];
            return $query->where('show_in_homepage', 1)->whereIn('setup_by', $addedBy);
        })->whereHas('product', function ($query) {
            return $query->active()->with(['reviews', 'rating', 'clearanceSale' => function ($query) {
                return $query->active();
            }])->withCount('reviews');
        })->pluck('product_id')->toArray();

        $basedQuery = Product::active()->whereIn('id', $productIds)->with(['reviews', 'rating', 'clearanceSale' => function ($query) {
            return $query->active();
        }])->withCount('reviews');

        $products = ProductManager::getPriorityWiseClearanceSaleProductsQuery(query: $basedQuery, dataLimit: (int)($request['limit'] ?? 10));

        return response()->json([
            'total_size' => $products->total(),
            'limit' => (int)($request['limit'] ?? 10),
            'offset' => (int)($request['offset'] ?? 1),
            'products' => Helpers::product_data_formatting($products->items(), true)
        ]);
    }
}
