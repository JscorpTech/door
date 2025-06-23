<?php

namespace App\Http\Controllers;

use App\Events\WholesaleEvent;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;

class WholesaleController extends Controller
{
    public function post(Request $request)
    {
        $validated_data = $request->validate([
            "product_id" => ["required", "exists:products,id"],
        ]);
        dd($validated_data);
        $product = Product::find($request->input("product_id"));
        $customer = User::find($product->user_id);
        $data = [
            'userType' => 'customer',
            'templateName' => 'forgot-password',
            'userName' => $customer['f_name'],
            'subject' => translate('password_reset'),
            'title' => translate('password_reset'),
            'passwordResetURL' => "none",
        ];
        event(new WholesaleEvent(email: $customer['email'], data: $data));
        return response()->json(["detail" => "ok"]);
    }
}
