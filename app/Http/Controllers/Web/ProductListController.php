<?php

namespace App\Http\Controllers\Web;

use App\Models\Author;
use App\Models\BusinessSetting;
use App\Models\PublishingHouse;
use App\Utils\BrandManager;
use App\Utils\CategoryManager;
use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
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

class ProductListController extends Controller
{


    public function products(Request $request)
    {;
        $themeName = theme_root_path();

        return match ($themeName) {
            'default' => self::default_theme($request),
            'theme_aster' => self::theme_aster($request),
            'theme_fashion' => self::theme_fashion($request),
        };
    }

    public function default_theme($request): View|JsonResponse|Redirector|RedirectResponse
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

        $categories = CategoryManager::getCategoriesWithCountingAndPriorityWiseSorting();
        $activeBrands = BrandManager::getActiveBrandWithCountingAndPriorityWiseSorting();

        $data = self::getProductListRequestData(request: $request);
        if ($request['data_from'] == 'category' && $request['category_id']) {
            $data['brand_name'] = Category::find((int)$request['category_id'])->name;
        }
        if ($request['data_from'] == 'brand') {
            $brand_data = Brand::active()->find((int)$request['brand_id']);
            if ($brand_data) {
                $data['brand_name'] = $brand_data->name;
            } else {
                Toastr::warning(translate('not_found'));
                return redirect('/');
            }
        }
        $productListData = ProductManager::getProductListData(request: $request);
        $products = $productListData->paginate(20)->appends($data);
        if ($request->ajax()) {
            return response()->json([
                'total_product' => $products->total(),
                'html_products' => view('web-views.products._ajax-products', compact('products'))->render()
            ], 200);
        }

        return view(VIEW_FILE_NAMES['products_view_page'], [
            'pageTitleContent' => translate($data['offer_type'] ?? $data['data_from']).' '.translate('products'),
            'products' => $products,
            'data' => $data,
            'activeBrands' => $activeBrands,
            'categories' => $categories,
        ]);
    }

    public function theme_aster($request): View|JsonResponse|Redirector|RedirectResponse
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
        $categories = CategoryManager::getCategoriesWithCountingAndPriorityWiseSorting();
        $activeBrands = BrandManager::getActiveBrandWithCountingAndPriorityWiseSorting();
        $singlePageProductCount = 20;

        $data = self::getProductListRequestData(request: $request);

        if ($request['data_from'] == 'category' && $request['category_id']) {
            $data['brand_name'] = Category::find((int)$request['category_id'])->name;
        }

        $productListData = ProductManager::getProductListData(request: $request);
        $ratings = self::getProductsRatingOneToFiveAsArray(productQuery: $productListData);
        $products = $productListData->paginate(20)->appends($data);
        $getProductIds = $products->pluck('id')->toArray();

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

        $selectedRatings = $request['rating'] ?? [];
        if ($request->ajax()) {
            return response()->json([
                'total_product' => $products->total(),
                'html_products' => view(VIEW_FILE_NAMES['products__ajax_partials'], [
                    'products' => $products,
                    'product_ids' => $getProductIds,
                    'singlePageProductCount' => $singlePageProductCount,
                    'page' => $request['page'] ?? 1,
                ])->render(),
                'html_tags' => view('theme-views.product._selected_filter_tags', [
                    'tags_category' => $category,
                    'tags_brands' => $brands,
                    'selectedRatings' => $selectedRatings,
                    'publishingHouse' => $publishingHouse,
                    'productAuthors' => $productAuthors,
                    'sort_by' => $request['sort_by'],
                ])->render(),
            ], 200);
        }

        return view(VIEW_FILE_NAMES['products_view_page'], [
            'pageTitleContent' => translate($data['offer_type'] ?? $data['data_from']).' '.translate('products'),
            'products' => $products,
            'data' => $data,
            'ratings' => $ratings,
            'selectedRatings' => $selectedRatings,
            'product_ids' => $getProductIds,
            'activeBrands' => $activeBrands,
            'categories' => $categories,
            'singlePageProductCount' => $singlePageProductCount,
            'page' => $request['page'] ?? 1,
            'tags_category' => $category,
            'tags_brands' => $brands,
            'publishingHouse' => $publishingHouse,
            'productAuthors' => $productAuthors,
            'sort_by' => $request['sort_by'],
        ]);
    }

    public function theme_fashion(Request $request): View|JsonResponse|Redirector|RedirectResponse
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

        $categories = CategoryManager::getCategoriesWithCountingAndPriorityWiseSorting();
        $activeBrands = BrandManager::getActiveBrandWithCountingAndPriorityWiseSorting();
        $banner = BusinessSetting::where(['type' => 'banner_product_list_page'])->whereJsonContains('value', ['status' => '1'])->first();
        $singlePageProductCount = 25;

        $data = self::getProductListRequestData(request: $request);
        if ($request['data_from'] == 'brand') {
            $brand_data = Brand::active()->find((int)$request['brand_id']);
            if (!$brand_data) {
                Toastr::warning(translate('not_found'));
                return redirect('/');
            }
        }

        $tagCategory = $this->getPageSelectedDataByType(request: $request, type: 'tag');
        $tagPublishingHouse = $this->getPageSelectedDataByType(request: $request, type: 'publishing_house');
        $tagProductAuthors = $this->getPageSelectedDataByType(request: $request, type: 'author');
        $tagBrand = $this->getPageSelectedDataByType(request: $request, type: 'brand');

        $productListData = ProductManager::getProductListData(request: $request);
        $products = $productListData->paginate($singlePageProductCount)->appends($data);
        $paginate_count = ceil(($products->total() / $singlePageProductCount));
        $getProductIds = $products->pluck('id')->toArray();

        if ($request['ratings'] != null) {
            $products = $products->map(function ($product) use ($request) {
                $product->rating = $product->rating->pluck('average')[0];
                return $product;
            });
            $products = $products->where('rating', '>=', $request['ratings'])
                ->where('rating', '<', $request['ratings'] + 1)
                ->paginate($singlePageProductCount)->appends($data);
        }

        $allProductsColorList = ProductManager::getProductsColorsArray();

        if ($request->ajax()) {
            return response()->json([
                'total_product' => $products->total(),
                'html_products' => view(VIEW_FILE_NAMES['products__ajax_partials'], [
                    'products' => $products,
                    'product_ids' => $getProductIds,
                    'paginate_count' => $paginate_count,
                    'singlePageProductCount' => $singlePageProductCount,
                ])->render(),
            ], 200);
        }

        return view(VIEW_FILE_NAMES['products_view_page'], [
            'pageTitleContent' => translate($data['offer_type'] ?? $data['data_from']).' '.translate('products'),
            'products' => $products,
            'tag_category' => $tagCategory,
            'tagPublishingHouse' => $tagPublishingHouse,
            'tagProductAuthors' => $tagProductAuthors,
            'tag_brand' => $tagBrand,
            'activeBrands' => $activeBrands,
            'categories' => $categories,
            'allProductsColorList' => $allProductsColorList,
            'banner' => $banner,
            'product_ids' => $getProductIds,
            'paginate_count' => $paginate_count,
            'singlePageProductCount' => $singlePageProductCount,
            'data' => $data
        ]);
    }


    public function getPageSelectedDataByType(Request $request, string $type)
    {
        $resultArray = [];
        if ($type == 'tag' && $request->has('category_ids') && !empty($request['category_ids'])) {
            $resultArray = Category::whereIn('id', $request['category_ids'])->select('id', 'name')->get();
        }

        if ($type == 'publishing_house' && $request->has('publishing_house_id') && !empty($request['publishing_house_id'])) {
            $resultArray = PublishingHouse::where('id', $request['publishing_house_id'])->select('id', 'name')->get();
        }

        if ($type == 'author' && $request->has('author_id') && !empty($request['author_id'])) {
            $resultArray = Author::where('id', $request['author_id'])->select('id', 'name')->get();
        }

        if ($type == 'brand' && $request['data_from'] == 'brand') {
            $resultArray = Brand::where('id', $request['brand_id'])->select('id', 'name')->get();
        }

        return $resultArray;
    }

    function getProductsRatingOneToFiveAsArray($productQuery): array
    {
        $rating_1 = 0;
        $rating_2 = 0;
        $rating_3 = 0;
        $rating_4 = 0;
        $rating_5 = 0;

        foreach ($productQuery as $rating) {
            if (isset($rating->rating[0]['average']) && ($rating->rating[0]['average'] > 0 && $rating->rating[0]['average'] < 2)) {
                $rating_1 += 1;
            } elseif (isset($rating->rating[0]['average']) && ($rating->rating[0]['average'] >= 2 && $rating->rating[0]['average'] < 3)) {
                $rating_2 += 1;
            } elseif (isset($rating->rating[0]['average']) && ($rating->rating[0]['average'] >= 3 && $rating->rating[0]['average'] < 4)) {
                $rating_3 += 1;
            } elseif (isset($rating->rating[0]['average']) && ($rating->rating[0]['average'] >= 4 && $rating->rating[0]['average'] < 5)) {
                $rating_4 += 1;
            } elseif (isset($rating->rating[0]['average']) && ($rating->rating[0]['average'] == 5)) {
                $rating_5 += 1;
            }
        }

        return [
            'rating_1' => $rating_1,
            'rating_2' => $rating_2,
            'rating_3' => $rating_3,
            'rating_4' => $rating_4,
            'rating_5' => $rating_5,
        ];
    }

    public static function getProductListRequestData($request): array
    {
        if ($request->has('product_view') && in_array($request['product_view'], ['grid-view', 'list-view'])) {
            session()->put('product_view_style', $request['product_view']);
        }

        return [
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
    }

    public function getFlashDealsView(Request $request, $id): View|RedirectResponse|JsonResponse
    {
        $request->merge(['offer_type' => 'flash-deals']);
        $request->merge(['flash_deals_id' => $id]);

        if ($request->has('product_name') && $request['product_name'] != '') {
            $request->merge(['data_from' => 'search']);
            $request->merge(['search' => $request['product_name']]);
        }

        $singlePageProductCount = 20;
        $userId = Auth::guard('customer')->user() ? Auth::guard('customer')->id() : 0;
        $flashDeal = ProductManager::getPriorityWiseFlashDealsProductsQuery(id: $id, userId: $userId);

        if (!isset($flashDeal['flashDeal']) || $flashDeal['flashDeal'] == null) {
            Toastr::warning(translate('not_found'));
            return back();
        }

        $data = self::getProductListRequestData(request: $request);
        $categories = CategoryManager::getCategoriesWithCountingAndPriorityWiseSorting(dataForm: 'flash-deals');
        $activeBrands = BrandManager::getActiveBrandWithCountingAndPriorityWiseSorting();

        $productListData = ProductManager::getProductListData(request: $request, type: 'flash-deals');
        $ratings = self::getProductsRatingOneToFiveAsArray(productQuery: $productListData);
        $products = $productListData->paginate(20)->appends($data);
        $getProductIds = $products->pluck('id')->toArray();

        if ($request['ratings'] != null) {
            $products = $products->map(function ($product) use ($request) {
                $product->rating = $product->rating->pluck('average')[0];
                return $product;
            });
            $products = $products->where('rating', '>=', $request['ratings'])
                ->where('rating', '<', $request['ratings'] + 1)
                ->paginate(20)->appends($data);
        }

        $allProductsColorList = ProductManager::getProductsColorsArray();
        $tagCategory = $this->getPageSelectedDataByType(request: $request, type: 'tag');
        $tagPublishingHouse = $this->getPageSelectedDataByType(request: $request, type: 'publishing_house');
        $tagProductAuthors = $this->getPageSelectedDataByType(request: $request, type: 'author');
        $tagBrand = $this->getPageSelectedDataByType(request: $request, type: 'brand');
        $paginateCount = ceil($products->count() / $singlePageProductCount);

        if ($request->ajax()) {
            return response()->json([
                'total_product' => $products->total(),
                'html_products' => view(VIEW_FILE_NAMES['products__ajax_partials'], ['products' => $products, 'product_ids' => $getProductIds])->render(),
            ], 200);
        }

        $selectedRatings = $request['rating'] ?? [];
        return view(VIEW_FILE_NAMES['flash_deals'], [
            'pageTitleContent' => translate('Flash_Deal_Products'),
            'products' => $products,
            'paginate_count' => $paginateCount,
            'data' => $data,
            'ratings' => $ratings,
            'selectedRatings' => $selectedRatings,
            'product_ids' => $getProductIds,
            'activeBrands' => $activeBrands,
            'productCategories' => $categories,
            'allProductsColorList' => $allProductsColorList,
            'deal' => $flashDeal['flashDeal'],
            'tag_category' => $tagCategory,
            'tagPublishingHouse' => $tagPublishingHouse,
            'tagProductAuthors' => $tagProductAuthors,
            'tag_brand' => $tagBrand,
            'singlePageProductCount' => $singlePageProductCount
        ]);
    }

    public function getFlashDealsProducts(Request $request): JsonResponse
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

        if ($request->has('product_name') && $request['product_name'] != '') {
            $request->merge(['data_from' => 'search']);
            $request->merge(['search' => $request['product_name']]);
        }

        $singlePageProductCount = 20;
        $productListData = ProductManager::getProductListData($request);

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

        $data = [
            'id' => $request['id'],
            'name' => $request['name'],
            'data_from' => $request['data_from'],
            'sort_by' => $request['sort_by'],
            'page_no' => $request['page'],
            'min_price' => $request['min_price'],
            'max_price' => $request['max_price'],
            'product_type' => $request['product_type'],
            'search_category_value' => $request['search_category_value'],
        ];
        if ($request->has('shop_id')) {
            $data['shop_id'] = $request['shop_id'];
        }

        return response()->json([
            'html_products' => view('theme-views.product._ajax-products', [
                'products' => $products,
                'paginate_count' => $paginateCount,
                'page' => $request['page'] ?? 1,
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
}
