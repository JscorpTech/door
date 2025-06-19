<?php

namespace App\Services;

class StockClearanceProductService
{
    public function checkConditions(object|array $request, object|array|null $product, object|array $config): array
    {
        $status = true;
        $message = 'update_successfully';

        if($product && $config->discount_type != 'flat') {
            if((!isset($request['discount_amount']) || $request['discount_amount'] == null)) {
                $status = false;
                $message = 'Discount_amount_can_not_be_empty';
            } else if (isset($request->discount_amount) && $request->discount_amount < 1) {
                $status = false;
                $message = 'Discount_amount_can_not_be_less_than_zero';
            } else if (isset($request->discount_type) && $request->discount_type === 'percentage') {
                if (isset($request->discount_amount) && ($request->discount_amount < 1 || $request->discount_amount > 100)) {
                    $status = false;
                    $message = 'Discount_amount_for_percentage_type_must_be_between_0_and_100';
                }
            } else {
                if (empty($product->variation) || $product->variation === '[]') {
                    if (isset($request->discount_type) && $request->discount_type === 'flat' && currencyConverter(amount: $request->discount_amount) > $product->unit_price) {
                        $status = false;
                        $message = 'Discount_amount_can_not_be_greater_than_unit_price';
                    }
                } else {
                    $productVariation = json_decode($product->variation, true);
                    $prices = array_column($productVariation, 'price');
                    $lowestPrice = min($prices);
                    if ($request->discount_amount > $lowestPrice ) {
                        $status = false;
                        $message = 'Discount_amount_can_not_be_greater_than_any_variation_product_unit_price';
                    }
                }
            }
        }
        return [
            'status' => $status,
            'message' => translate($message),
        ];
    }

    public function checkAddConditions(object|array $request, object|array $product, object|array $config): array
    {
        $status = true;
        $message = 'product_added_successfully';

        if($config->discount_type != 'flat') {
            if(!isset($request['discount_amount'][$product->id]) || $request['discount_amount'][$product->id] == null){
                $status = false;
                $message = 'Discount_amount_can_not_be_empty';
            } else if (isset($request->discount_amount[$product->id]) && $request->discount_amount[$product->id] < 1) {
                $status = false;
                $message = 'Discount_amount_can_not_be_less_than_zero';
            } else if (isset($request['discount_type'][$product->id]) && $request['discount_type'][$product->id] === 'percentage') {
                if (isset($request->discount_amount[$product->id]) && ($request->discount_amount[$product->id] < 1 || $request->discount_amount[$product->id] > 100)) {
                    $status = false;
                    $message = 'Discount_amount_for_percentage_type_must_be_between_0_and_100';
                }
            } else {
                if (empty($product->variation) || $product->variation === '[]') {
                    if (isset($request['discount_type'][$product->id]) && $request['discount_type'][$product->id] === 'flat' && currencyConverter(amount: $request['discount_amount'][$product->id]) > $product->unit_price) {
                        $status = false;
                        $message = 'Discount_amount_can_not_be_greater_than_unit_price';
                    }
                } else {
                    $productVariation = json_decode($product->variation, true);
                    $prices = array_column($productVariation, 'price');
                    $lowestPrice = min($prices);
                    if (isset($request->discount_amount[$product->id]) && $request->discount_amount[$product->id] > $lowestPrice ) {
                        $status = false;
                        $message = 'Discount_amount_can_not_be_greater_than_any_variation_product_unit_price';
                    }
                }
            }
        }

        return [
            'status' => $status,
            'message' => translate($message),
        ];
    }
}
