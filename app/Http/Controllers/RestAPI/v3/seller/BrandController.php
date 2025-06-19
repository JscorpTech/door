<?php

namespace App\Http\Controllers\RestAPI\v3\seller;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use Illuminate\Http\JsonResponse;

class BrandController extends Controller
{
    public function getBrands(): JsonResponse
    {
        $brands = Brand::all();
        return response()->json($brands,200);
    }
}
