<?php

namespace App\Http\Controllers\RestAPI\v1;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Traits\CacheManagerTrait;
use App\Utils\Helpers;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BannerController extends Controller
{
    use CacheManagerTrait;

    public function getBannerList(Request $request): JsonResponse
    {
        $banners = $this->cacheBannerTable();
        $productIds = [];
        $bannerData = [];
        foreach ($banners as $banner) {
            if ($banner['resource_type'] == 'product' && !in_array($banner['resource_id'], $productIds)) {
                $productIds[] = $banner['resource_id'];
                $product = Product::find($banner['resource_id']);
                $banner['product'] = Helpers::product_data_formatting($product);
            }
            $bannerData[] = $banner;
        }

        return response()->json($bannerData, 200);

    }
}
