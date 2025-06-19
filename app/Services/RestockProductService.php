<?php

namespace App\Services;

use App\Enums\UserRole;
use App\Enums\ViewPaths\Admin\Product;
use App\Models\Color;
use App\Traits\FileManagerTrait;
use Illuminate\Support\Str;
use phpDocumentor\Reflection\Types\Boolean;
use Rap2hpoutre\FastExcel\FastExcel;
use function React\Promise\all;

class RestockProductService
{

    public function getProductRestockRequestAddData(object|array $request, object|array $restockRequest): array
    {
        return [
            'restock_product_id' => $restockRequest ? $restockRequest['id'] : 0,
            'customer_id' => auth('customer')->id(),
            'variant' => $request['product_variation_code'],
        ];
    }

}
