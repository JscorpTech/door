<?php

namespace App\Traits;

use App\Models\CategoryShippingCost;
use App\Models\RestockProduct;
use App\Models\RestockProductCustomer;
use App\Models\ShippingMethod;
use App\Models\ShippingType;
use App\Services\ProductService;
use App\Utils\Helpers;

trait ProductTrait
{
    public function __construct(
        private readonly ProductService $productService,
    )
    {
    }

    public function isAddedByInHouse(string|null $addedBy): bool
    {
        return isset($addedBy) && $addedBy == 'in_house';
    }

    public static function getProductDeliveryCharge(object|array $product, string|int $quantity): array
    {
        $deliveryCost = 0;
        $shippingModel = getWebConfig(name: 'shipping_method');
        $shippingType = "";
        $maxOrderWiseShippingCost = 0;
        $minOrderWiseShippingCost = 0;

        if ($shippingModel == "inhouse_shipping") {
            $shippingType = ShippingType::where(['seller_id' => 0])->first();
            if ($shippingType->shipping_type == "category_wise") {
                $catId = $product->category_id;
                $CategoryShippingCost = CategoryShippingCost::where(['seller_id' => 0, 'category_id' => $catId])->first();
                $deliveryCost = $CategoryShippingCost ?
                    ($CategoryShippingCost->multiply_qty != 0 ? ($CategoryShippingCost->cost * $quantity) : $CategoryShippingCost->cost)
                    : 0;
            } elseif ($shippingType->shipping_type == "product_wise") {
                $deliveryCost = $product->multiply_qty != 0 ? ($product->shipping_cost * $quantity) : $product->shipping_cost;
            } elseif ($shippingType->shipping_type == 'order_wise') {
                $maxOrderWiseShippingCost = ShippingMethod::where(['creator_type' => 'admin', 'status' => 1])->max('cost');
                $minOrderWiseShippingCost = ShippingMethod::where(['creator_type' => 'admin', 'status' => 1])->min('cost');
            }
        } elseif ($shippingModel == "sellerwise_shipping") {

            if ($product->added_by == "admin") {
                $shippingType = ShippingType::where('seller_id', '=', 0)->first();
            } else {
                $shippingType = ShippingType::where('seller_id', '!=', 0)->where(['seller_id' => $product->user_id])->first();
            }

            if ($shippingType) {
                $shippingType = $shippingType ?? ShippingType::where('seller_id', '=', 0)->first();
                if ($shippingType->shipping_type == "category_wise") {
                    $catId = $product->category_id;
                    if ($product->added_by == "admin") {
                        $CategoryShippingCost = CategoryShippingCost::where(['seller_id' => 0, 'category_id' => $catId])->first();
                    } else {
                        $CategoryShippingCost = CategoryShippingCost::where(['seller_id' => $product->user_id, 'category_id' => $catId])->first();
                    }

                    $deliveryCost = $CategoryShippingCost ?
                        ($CategoryShippingCost->multiply_qty != 0 ? ($CategoryShippingCost->cost * $quantity) : $CategoryShippingCost->cost)
                        : 0;
                } elseif ($shippingType->shipping_type == "product_wise") {
                    $deliveryCost = $product->multiply_qty != 0 ? ($product->shipping_cost * $quantity) : $product->shipping_cost;
                } elseif ($shippingType->shipping_type == 'order_wise') {
                    if ($product->added_by == 'admin') {
                        $maxOrderWiseShippingCost = ShippingMethod::where(['creator_type' => 'admin', 'status' => 1])->max('cost');
                        $minOrderWiseShippingCost = ShippingMethod::where(['creator_type' => 'admin', 'status' => 1])->min('cost');
                    } else {
                        $maxOrderWiseShippingCost = ShippingMethod::where(['creator_id' => $product->user_id, 'creator_type' => $product->added_by, 'status' => 1])->max('cost');
                        $minOrderWiseShippingCost = ShippingMethod::where(['creator_id' => $product->user_id, 'creator_type' => $product->added_by, 'status' => 1])->min('cost');
                    }
                }
            }
        }
        return [
            'delivery_cost' => $deliveryCost,
            'delivery_cost_max' => $maxOrderWiseShippingCost,
            'delivery_cost_min' => $minOrderWiseShippingCost,
            'shipping_type' => $shippingType->shipping_type ?? '',
        ];
    }

    public function updateRestockRequestListAndNotify(object|array $product, object|array $updatedProduct): void
    {
        $productVariation = json_decode($product['variation'], true) ?? [];
        $productUpdateVariation = json_decode($updatedProduct['variation'], true) ?? [];

        if (count($productUpdateVariation) > 0) {
            foreach ($productUpdateVariation as $productUpdateVariant) {
                $restockRequest = RestockProduct::with(['product'])->where(['product_id' => $product['id'], 'variant' => $productUpdateVariant['type']])->first();
                foreach ($productVariation as $variation) {
                    if ($restockRequest && $variation['qty'] == 0 && $productUpdateVariant['qty'] > 0) {
                        RestockProduct::where(['id' => $restockRequest['id']])->delete();
                        RestockProductCustomer::where(['restock_product_id' => $restockRequest['id']])->delete();
                        $this->productService->sendRestockProductNotification(restockRequest: $restockRequest, type: 'restocked');
                    }
                }
            }
        }

        $isVariationChange = false;
        if (count($productUpdateVariation) > 0 || count($productVariation) > 0) {
            $originalTypes = array_column($productVariation, 'type') ?? [];
            $updatedTypes = array_column($productUpdateVariation, 'type') ?? [];

            ksort($originalTypes);
            ksort($updatedTypes);

            if (empty($originalTypes) || empty($updatedTypes)) {
                $isVariationChange = true;
            } else {
                $isVariationChange = array_diff($originalTypes, $updatedTypes) || array_diff($updatedTypes, $originalTypes);
            }
        }

        if (($product['current_stock'] <= 0 && $updatedProduct['current_stock'] > 0 && count($productUpdateVariation) <= 0) || $isVariationChange) {
            $restockRequestList = RestockProduct::with(['product'])->where(['product_id' => $product['id']])->get();
            foreach ($restockRequestList as $restockRequest) {
                RestockProduct::where(['id' => $restockRequest['id']])->delete();
                RestockProductCustomer::where(['restock_product_id' => $restockRequest['id']])->delete();
                $this->productService->sendRestockProductNotification(restockRequest: $restockRequest, type: 'update');
            }
        }
    }
}
