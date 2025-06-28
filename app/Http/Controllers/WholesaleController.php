<?php

namespace App\Http\Controllers;

use App\Events\WholesaleEvent;
use App\Models\Product;
use App\Models\Seller;
use Illuminate\Http\Request;

class WholesaleController extends Controller
{
    public function post(Request $request)
    {
        $validated_data = $request->validate([
            "product_id" => ["required", "exists:products,id"],
        ]);
        $product = Product::find($validated_data["product_id"]);
        $seller = Seller::find($product->user_id);
        $data = [
            'subject' => translate('new_order_received'),
            'title' => translate('new_order_received'),
            'userType' => "vendor",
            "user"=>auth("api")->user(),
            "product"=>$product,
            'templateName' => 'wholesale',
            'vendorName' => $seller?->f_name,
            'adminName' => $seller?->name,
        ];
        event(new WholesaleEvent(email: $seller['email'], data: $data));
        return response()->json(["detail" => "Запрос отправлен..."]);
    }
}
