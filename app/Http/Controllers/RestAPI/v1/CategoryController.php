<?php

namespace App\Http\Controllers\RestAPI\v1;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Utils\CategoryManager;
use App\Utils\Helpers;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function get_categories(Request $request): JsonResponse
    {
        $categoriesID = [];
        if ($request->has('seller_id') && $request['seller_id'] != null) {
            $categoriesID = Product::active()
                ->when($request->has('seller_id') && $request['seller_id'] != null && $request['seller_id'] != 0, function ($query) use ($request) {
                    return $query->where(['added_by' => 'seller'])
                        ->where('user_id', $request['seller_id']);
                })->when($request->has('seller_id') && $request['seller_id'] != null && $request['seller_id'] == 0, function ($query) use ($request) {
                    return $query->where(['added_by' => 'admin',
                    ]);
                })->pluck('category_id');
        }

        $categories = Category::when($request->has('seller_id') && $request['seller_id'] != null, function ($query) use ($categoriesID) {
                $query->whereIn('id', $categoriesID);
            })
            ->with(['product' => function ($query) {
                return $query->active()->withCount(['orderDetails']);
            }])
            ->withCount(['product' => function ($query) use ($request) {
                $query->when($request->has('seller_id') && !empty($request['seller_id']), function ($query) use ($request) {
                    $query->where(['added_by' => 'seller', 'user_id' => $request['seller_id'], 'status' => '1']);
                });
            }])->with(['childes' => function ($query) {
                $query->with(['childes' => function ($query) {
                    $query->withCount(['subSubCategoryProduct'])->where('position', 2);
                }])->withCount(['subCategoryProduct'])->where('position', 1);
            }, 'childes.childes'])
            ->where(['position' => 0])->get();

        $categories = CategoryManager::getPriorityWiseCategorySortQuery(query: $categories);

        return response()->json($categories->values());
    }

    public function get_products(Request $request, $id): JsonResponse
    {
        $dataLimit = $request['limit'] ?? 'all';
        $products = CategoryManager::products($id, $request, $dataLimit);
        $productFinal = Helpers::product_data_formatting($products, true);

        if ($dataLimit == 'all') {
            return response()->json($productFinal, 200);
        }

        return response()->json([
            'total_size' => $products->total(),
            'limit' => (int)$request['limit'],
            'offset' => (int)$request['offset'],
            'products' => $productFinal,
        ], 200);
    }

    public function find_what_you_need()
    {
        $find_what_you_need_categories = Category::where('parent_id', 0)
            ->with(['childes' => function ($query) {
                $query->withCount(['subCategoryProduct' => function ($query) {
                    return $query->active();
                }]);
            }])
            ->withCount(['product' => function ($query) {
                return $query->active();
            }])
            ->get()->toArray();

        $get_categories = [];
        foreach($find_what_you_need_categories as $category){
            $slice = array_slice($category['childes'], 0, 4);
            $category['childes'] = $slice;
            $get_categories[] = $category;
        }

        $final_category = [];
        foreach ($get_categories as $category) {
            if (count($category['childes']) > 0) {
                $final_category[] = $category;
            }
        }

        return response()->json(['find_what_you_need'=>$final_category], 200);
    }

}
