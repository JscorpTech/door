<?php

namespace App\Http\Controllers\RestAPI\v1;

use App\Contracts\Repositories\ProductRepositoryInterface;
use App\Contracts\Repositories\RestockProductCustomerRepositoryInterface;
use App\Contracts\Repositories\RestockProductRepositoryInterface;
use App\Events\RequestProductRestockEvent;
use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Color;
use App\Models\Order;
use App\Models\Product;
use App\Services\RestockProductService;
use App\Utils\CartManager;
use App\Utils\Helpers;
use App\Utils\OrderManager;
use App\Utils\ProductManager;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CartController extends Controller
{
    public function __construct(
        private Order                                              $order,
        private readonly RestockProductService                     $restockProductService,
        private readonly ProductRepositoryInterface                $productRepo,
        private readonly RestockProductRepositoryInterface         $restockProductRepo,
        private readonly RestockProductCustomerRepositoryInterface $restockProductCustomerRepo,
    )
    {
    }

    public function getCartList(Request $request): JsonResponse
    {
        $user = Helpers::getCustomerInformation($request);

        ProductManager::updateProductPriceInCartList(request: $request);

        $cart = Cart::whereHas('product', function ($query) {
                return $query->active();
            })
            ->with(['shop', 'product' => function ($query) {
                return $query->with(['clearanceSale' => function ($query) {
                    return $query->active();
                }]);
            }])
            ->when($user == 'offline', function ($query) use ($request) {
                return $query->where(['customer_id' => $request->guest_id, 'is_guest' => 1]);
            })
            ->when($user != 'offline', function ($query) use ($user) {
                return $query->where(['customer_id' => $user->id, 'is_guest' => '0']);
            })->get();

        if ($cart) {
            foreach ($cart as $key => $value) {
                if (!isset($value['product'])) {
                    $cart_data = Cart::find($value['id']);
                    $cart_data->delete();

                    unset($cart[$key]);
                }
            }

            $cart->map(function ($data) use ($request) {
                $product = Product::active()->find($data->product_id);
                if ($product) {
                    $data['is_product_available'] = 1;
                } else {
                    $data['is_product_available'] = 0;
                }
                $data['choices'] = json_decode($data['choices']);
                $data['variations'] = json_decode($data['variations']);

                $data['minimum_order_amount_info'] = OrderManager::verifyCartListMinimumOrderAmount($request, $data['cart_group_id'])['minimum_order_amount'];

                $cart_group = Cart::where(['product_type' => 'physical'])->where('cart_group_id', $data['cart_group_id'])->get()->groupBy('cart_group_id');
                if (isset($cart_group[$data['cart_group_id']])) {
                    $data['free_delivery_order_amount'] = OrderManager::getFreeDeliveryOrderAmountArray($data['cart_group_id']);
                } else {
                    $data['free_delivery_order_amount'] = [
                        'status' => 0,
                        'amount' => 0,
                        'percentage' => 0,
                        'shipping_cost_saved' => 0,
                    ];
                }

                $data['product']['total_current_stock'] = isset($data['product']['current_stock']) ? $data['product']['current_stock'] : 0;
                if (isset($data['product']['variation']) && !empty($data['product']['variation'])) {
                    $variants = json_decode($data['product']['variation']);
                    foreach ($variants as $var) {
                        if ($data['variant'] == $var->type) {
                            $data['product']['total_current_stock'] = $var->qty;
                        }
                    }
                }

                $data['discount'] = getProductPriceByType(product: $data['product'], type: 'discounted_amount', result: 'value', price: $data['price']);
                unset($data['product']['variation']);
                return $data;
            });
        }

        return response()->json($cart, 200);
    }

    public function addToCart(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required',
            'quantity' => 'required',
        ], [
            'id.required' => translate('Product ID is required!')
        ]);

        if ($validator->errors()->count() > 0) {
            return response()->json(['errors' => Helpers::validationErrorProcessor($validator)]);
        }

        $cart = CartManager::add_to_cart($request);
        return response()->json($cart, 200);
    }

    public function update_cart(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'key' => 'required',
            'quantity' => 'required',
        ], [
            'key.required' => translate('Cart key or ID is required!')
        ]);

        if ($validator->errors()->count() > 0) {
            return response()->json(['errors' => Helpers::validationErrorProcessor($validator)]);
        }

        $response = CartManager::update_cart_qty($request);
        return response()->json($response);
    }

    public function remove_from_cart(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'key' => 'required'
        ], [
            'key.required' => translate('Cart key or ID is required!')
        ]);

        if ($validator->errors()->count() > 0) {
            return response()->json(['errors' => Helpers::validationErrorProcessor($validator)]);
        }

        $user = Helpers::getCustomerInformation($request);
        Cart::where([
            'id' => $request->key,
            'customer_id' => ($user == 'offline' ? (session('guest_id') ?? $request->guest_id) : $user->id),
            'is_guest' => ($user == 'offline' ? 1 : '0'),
        ])->delete();
        return response()->json(translate('successfully_removed'));
    }

    public function remove_all_from_cart(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'key' => 'required'
        ], [
            'key.required' => translate('Cart key or ID is required!')
        ]);

        if ($validator->errors()->count() > 0) {
            return response()->json(['errors' => Helpers::validationErrorProcessor($validator)]);
        }

        $user = Helpers::getCustomerInformation($request);
        Cart::where([
            'customer_id' => ($user == 'offline' ? $request->guest_id : $user->id),
            'is_guest' => ($user == 'offline' ? 1 : '0'),
        ])->delete();
        return response()->json(translate('successfully_removed'));
    }

    public function updateCheckedCartItems(Request $request): JsonResponse
    {
        if ($request['action'] == 'unchecked') {
            Cart::whereIn('id', $request['ids'])->update(['is_checked' => 0]);
        } elseif ($request['action'] == 'checked') {
            Cart::whereIn('id', $request['ids'])->update(['is_checked' => 1]);
        }
        return response()->json(translate('Successfully_Update'), 200);
    }

    public function addProductRestockRequest(Request $request): JsonResponse
    {
        $user = Helpers::getCustomerInformation($request);
        $product = $this->productRepo->getWebFirstWhereActive(params: ['id' => $request['id']]);

        if ($product && $user != 'offline') {
            $variationCode = '';
            if ($request->has('color')) {
                $variationCode .= Color::where(['code' => $request['color']])->first()->name;
            }

            foreach (json_decode($product['choice_options']) as $key => $choice) {
                if ($variationCode != null) {
                    $variationCode .= '-' . str_replace(' ', '', $request[$choice->name]);
                } else {
                    $variationCode .= str_replace(' ', '', $request[$choice->name]);
                }
            }

            $restockRequest = $this->restockProductRepo->updateOrCreate(params: ['product_id' => $request['id'], 'variant' => $variationCode], value: [
                'product_id' => $request['id'],
                'variant' => $variationCode,
            ]);
            $restockData = [
                'restock_product_id' => $restockRequest ? $restockRequest['id'] : 0,
                'customer_id' => $user->id,
                'variant' => $variationCode,
            ];
            $checkRequest = $this->restockProductCustomerRepo->getFirstWhere(params: $restockData);
            if ($checkRequest) {
                return response()->json([
                    'status' => 'warning',
                    'message' => translate('Already_Requested'),
                ], 200);
            }
            $this->restockProductCustomerRepo->updateOrCreate(params: $restockData, value: $restockData);
            $this->restockProductRepo->updateByParams(params: ['id' => $restockRequest['id']], data: ['updated_at' => Carbon::now()]);
            if ($product['added_by'] == 'seller' && $product?->seller?->cm_firebase_token) {
                $this->sendRestockProductNotificationToAuthor($restockRequest);
            }

            return response()->json([
                'message' => translate('Request_sent_successfully'),
                'topic' => getRestockProductFCMTopic(restockRequest: $restockRequest)
            ], 200);
        }

        return response()->json(['message' => translate('Invalid_product')], 403);
    }

    public function sendRestockProductNotificationToAuthor(mixed $product): void
    {
        $filters = [
            'added_by' => $product['added_by'] == 'seller' ? $product['added_by'] : 'in_house',
            'seller_id' => $product['user_id'],
        ];

        $restockProductList = $this->restockProductRepo->getListWhere(filters: $filters, dataLimit: 'all')->groupBy('product_id');
        $data = [];
        if (count($restockProductList) == 1) {
            $firstProduct = $this->restockProductRepo->getListWhere(orderBy: ['updated_at' => 'desc'], filters: $filters, relations: ['product'], dataLimit: 5)->first();
            $count = $firstProduct?->restock_product_customers_count ?? 0;
            $data = [
                'title' => $firstProduct?->product?->name ?? '',
                'body' => $count < 100 ? translate('This_product_has') . ' ' . $count . ' ' . translate('restock_request') : translate('This_product_has') . ' 99+ ' . translate('restock_request'),
                'image' => getStorageImages(path: $firstProduct?->product?->thumbnail_full_url ?? '', type: 'product'),
                'firebase_token' => $product?->seller?->cm_firebase_token
            ];
        } elseif (count($restockProductList) > 1) {
            $data = [
                'title' => translate('Restock_Request'),
                'body' => (count($restockProductList) < 100 ? count($restockProductList) : '99 +') . ' ' . translate('more_products_have_restock_request'),
                'image' => dynamicAsset(path: 'public/assets/back-end/img/icons/restock-request-icon.svg'),
                'firebase_token' => $product?->seller?->cm_firebase_token
            ];
        }

        event(new RequestProductRestockEvent(key: 'message_from_customer', data: $data));
    }
}
