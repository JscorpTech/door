<?php

namespace App\Http\Controllers\RestAPI\v1;

use App\Events\DigitalProductOtpVerificationEvent;
use App\Events\RefundEvent;
use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Currency;
use App\Models\DigitalProductOtpVerification;
use App\Models\OfflinePaymentMethod;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\OrderDetailsRewards;
use App\Models\RefundRequest;
use App\Models\Setting;
use App\Models\ShippingAddress;
use App\Traits\CommonTrait;
use App\Traits\FileManagerTrait;
use App\Traits\SmsGateway;
use App\Models\User;
use App\Utils\CartManager;
use App\Utils\Convert;
use App\Utils\CustomerManager;
use App\Utils\Helpers;
use App\Utils\ImageManager;
use App\Utils\OrderManager;
use App\Utils\SMSModule;
use Carbon\Carbon;
use Carbon\CarbonInterval;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;



class OrderController extends Controller
{
    use CommonTrait, FileManagerTrait;

    public function track_by_order_id(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'order_id' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::validationErrorProcessor($validator)], 403);
        }

        $data = Order::with(['deliveryMan', 'orderStatusHistory' => function ($query) {
            return $query->latest();
        }])->where(['id' => $request['order_id']])->first();

        $data = json_decode(json_encode($data), true);
        return response()->json($data, 200);
    }

    public function order_cancel(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'order_id' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::validationErrorProcessor($validator)], 403);
        }
        $order = Order::where(['id' => $request->order_id])->first();

        if ($order['payment_method'] == 'cash_on_delivery' && $order['order_status'] == 'pending') {
            OrderManager::getStockUpdateOnOrderStatusChange($order, 'canceled');
            Order::where(['id' => $request->order_id])->update([
                'order_status' => 'canceled'
            ]);

            return response()->json(translate('order_canceled_successfully'), 200);
        }

        return response()->json(['message' => translate('status_not_changeable_now')], 403);
    }

    public function place_order(Request $request): JsonResponse
    {
        $user = Helpers::getCustomerInformation($request);
        $newCustomerRegister = null;

        // Faqat checked bo'lgan savatlarni olish
        $cartGroupIds = CartManager::get_cart_group_ids(request: $request, type: 'checked');
        $carts = Cart::whereHas('product', function ($query) {
            return $query->active();
        })->with('product')->whereIn('cart_group_id', $cartGroupIds)->where(['is_checked' => 1])->get();

        if ($carts->isEmpty()) {
            return response()->json(['message' => translate('Your cart is empty')], 403);
        }

        // Mahsulot zaxirasi tekshiruvi
        $productStockCheck = CartManager::product_stock_check($carts);
        if (!$productStockCheck) {
            return response()->json(['message' => translate('The_following_items_in_your_cart_are_currently_out_of_stock')], 403);
        }

        // Minimal buyurtma summasi
        $verifyStatus = OrderManager::verifyCartListMinimumOrderAmount($request);
        if ($verifyStatus['status'] == 0) {
            return response()->json(['message' => translate('Check_minimum_order_amount_requirement')], 403);
        }

        // Yangi mijozni ro'yxatdan o'tkazish
        if ($user == 'offline' && $request->has('address_id') && $request['address_id']) {
            $shippingAddress = ShippingAddress::where([
                'customer_id' => $request['guest_id'],
                'is_guest' => 1,
                'id' => $request->input('address_id')
            ])->first();

            if ($request['is_check_create_account'] && $shippingAddress) {
                if (User::where(['email' => $shippingAddress['email']])->orWhere(['phone' => $shippingAddress['phone']])->first()) {
                    return response()->json(['message' => translate('Already registered ')], 403);
                } else {
                    $newCustomerRegister = self::addNewCustomer(request: $request, address: $shippingAddress);
                }
            }
        }

        \Log::info('Placing order with cart_group_id: '.$cart_group_id, [
            'cart_group_ids' => $cartGroupIds,
            'user_id' => $user == 'offline' ? $request['guest_id'] : $user['id']
        ]);


        // **Bitta umumiy cart_group_id ishlatish**
        $cart_group_id = $cartGroupIds[0];

        // Order yaratish
        $order_id = OrderManager::generate_order([
            'is_guest' => $user == 'offline' ? 1 : 0,
            'guest_id' => $request['guest_id'],
            'customer_id' => $user == 'offline' ? $request['guest_id'] : $user['id'],
            'order_status' => 'pending',
            'payment_method' => 'cash_on_delivery',
            'payment_status' => 'unpaid',
            'transaction_ref' => '',
            'address_id' => $request['address_id'],
            'billing_address_id' => $request['billing_address_id'],
            'coupon_code' => $request['coupon_code'],
            'newCustomerRegister' => $newCustomerRegister,
            'bring_change_amount' => $request['bring_change_amount'] ?? 0,
            'bring_change_amount_currency' => getWebConfig(name: 'currency_model') == 'multi_currency' ? ($request->current_currency_code ?? Currency::find(getWebConfig(name: 'system_default_currency'))->code) : Currency::find(getWebConfig(name: 'system_default_currency'))->code,
            'requestObj' => $request,
            'cart_group_id' => $cart_group_id
        ]);

        return response()->json([
            'order_id' => $order_id,
            'message' => "Your payment has been successfully processed and your order - {$order_id} Был размещен.",
            'new_user' => (bool)$newCustomerRegister
        ], 200);
    }



    function addNewCustomer($request, $address)
    {
        return User::create([
            'name' => $address['contact_person_name'],
            'f_name' => $address['contact_person_name'],
            'l_name' => '',
            'email' => $address['email'],
            'phone' => $address['phone'],
            'is_active' => 1,
            'password' => bcrypt($request['password']),
            'referral_code' => Helpers::generate_referer_code(),
        ]);
    }

    public function placeOrderByOfflinePayment(Request $request): JsonResponse
    {
        $user = Helpers::getCustomerInformation($request);
        $newCustomerRegister = null;
        $cartGroupIds = CartManager::get_cart_group_ids(request: $request, type: 'checked');
        $carts = Cart::whereHas('product', function ($query) {
            return $query->active();
        })->with('product')->whereIn('cart_group_id', $cartGroupIds)->where(['is_checked' => 1])->get();

        $productStockCheck = CartManager::product_stock_check($carts);
        if (!$productStockCheck) {
            return response()->json(['message' => 'The following items in your cart are currently out of stock'], 403);
        }

        $verifyStatus = OrderManager::verifyCartListMinimumOrderAmount($request);
        if ($verifyStatus['status'] == 0) {
            return response()->json(['message' => 'Check minimum order amount requirement'], 403);
        }

        if ($user == 'offline' && $request->has('address_id') && $request['address_id']) {
            $shippingAddress = ShippingAddress::where(['customer_id' => $request['guest_id'], 'is_guest' => 1, 'id' => $request->input('address_id')])->first();
            if ($request['is_check_create_account'] && $shippingAddress) {
                if (User::where(['email' => $shippingAddress['email']])->orWhere(['phone' => $shippingAddress['phone']])->first()) {
                    return response()->json(['message' => translate('Already_registered ')], 403);
                } else {
                    $newCustomerRegister = self::addNewCustomer(request: $request, address: $shippingAddress);
                }
            }
        }

        $physicalProductExist = false;
        foreach ($carts as $cart) {
            if ($cart->product_type == 'physical') {
                $physicalProductExist = true;
            }
        }

        if ($physicalProductExist) {
            $zipRestrictStatus = getWebConfig(name: 'delivery_zip_code_area_restriction');
            $countryRestrictStatus = getWebConfig(name: 'delivery_country_restriction');

            if ($request->has('billing_address_id') && $request['billing_address_id']) {
                if ($user == 'offline') {
                    $billingAddress = ShippingAddress::where(['customer_id' => $request['guest_id'], 'is_guest' => 1, 'id' => $request->input('billing_address_id')])->first();
                    if ($request['is_check_create_account'] && $billingAddress && $request['address_id'] == null) {
                        if (User::where(['email' => $billingAddress['email']])->orWhere(['phone' => $billingAddress['phone']])->first()) {
                            return response()->json(['message' => translate('Already_registered ')], 403);
                        } else {
                            $newCustomerRegister = self::addNewCustomer(request: $request, address: $billingAddress);
                        }
                    }
                } else {
                    $billingAddress = ShippingAddress::where(['customer_id' => $user->id, 'is_guest' => '0', 'id' => $request->input('billing_address_id')])->first();
                }

                if (!$billingAddress) {
                    return response()->json(['message' => translate('address_not_found')], 200);
                } elseif ($countryRestrictStatus && !self::delivery_country_exist_check($billingAddress->country)) {
                    return response()->json(['message' => translate('Delivery_unavailable_for_this_country')], 403);

                } elseif ($zipRestrictStatus && !self::delivery_zipcode_exist_check($billingAddress->zip)) {
                    return response()->json(['message' => translate('Delivery_unavailable_for_this_zip_code_area')], 403);
                }
            }
        }

        $offlinePaymentInfo = [];
        $method = OfflinePaymentMethod::where(['id' => $request['method_id'], 'status' => 1])->first();

        if (isset($method)) {
            $fields = array_column($method->method_informations, 'customer_input');
            $values = (array)json_decode(base64_decode($request['method_informations']));
            $offlinePaymentInfo['method_id'] = $request['method_id'];
            $offlinePaymentInfo['method_name'] = $method->method_name;
            foreach ($fields as $field) {
                if (key_exists($field, $values)) {
                    $offlinePaymentInfo[$field] = $values[$field];
                }
            }
        }

        OrderManager::generateOrder(data: [
            'is_guest' => $user == 'offline' ? 1 : 0,
            'guest_id' => $request['guest_id'],
            'customer_id' => $user == 'offline' ? $request['guest_id'] : $user['id'],
            'order_status' => 'pending',
            'payment_method' => 'offline_payment',
            'payment_status' => 'unpaid',
            'transaction_ref' => '',
            'address_id' => $request['address_id'],
            'billing_address_id' => $request['billing_address_id'],
            'newCustomerRegister' => $newCustomerRegister,
            'offline_payment_info' => $offlinePaymentInfo,
            'payment_note' => $request['payment_note'],
            'order_note' => $request['order_note'],
            'coupon_code' => $request['coupon_code'],
            'requestObj' => $request,
        ]);

        return response()->json([
            'messages' => translate('order_placed_successfully'),
            'new_user' => (bool)$newCustomerRegister,
        ], 200);
    }

    public function placeOrderByWallet(Request $request): JsonResponse
    {
        $cartGroupIds = CartManager::get_cart_group_ids(request: $request, type: 'checked');
        $carts = Cart::whereHas('product', function ($query) {
            return $query->active();
        })->with('product')->whereIn('cart_group_id', $cartGroupIds)->where(['is_checked' => 1])->get();

        $product_stock = CartManager::product_stock_check($carts);
        if (!$product_stock) {
            return response()->json(['message' => 'The following items in your cart are currently out of stock'], 403);
        }

        $verifyStatus = OrderManager::verifyCartListMinimumOrderAmount($request);
        if ($verifyStatus['status'] == 0) {
            return response()->json(['message' => 'Check minimum order amount requirement'], 403);
        }

        $vendorWiseCartList = OrderManager::processOrderGenerateData(data: [
            'coupon_code' => $request['coupon_code'] ?? '',
            'requestObj' => $request,
        ]);
        $paymentAmount = collect($vendorWiseCartList)->sum('order_amount_with_tax');

        $user = Helpers::getCustomerInformation($request);
        if ($paymentAmount > $user->wallet_balance) {
            return response()->json(['message' => 'inefficient balance in your wallet to pay for this order'], 403);
        } else {
            $physical_product = false;
            foreach ($carts as $cart) {
                if ($cart->product_type == 'physical') {
                    $physical_product = true;
                }
            }

            if ($physical_product) {
                $zip_restrict_status = getWebConfig(name: 'delivery_zip_code_area_restriction');
                $country_restrict_status = getWebConfig(name: 'delivery_country_restriction');

                if ($request->has('billing_address_id') && $request['billing_address_id']) {
                    $shipping_address = ShippingAddress::where(['customer_id' => $request->user()->id, 'id' => $request->input('billing_address_id')])->first();

                    if (!$shipping_address) {
                        return response()->json(['message' => translate('address_not_found')], 403);
                    } elseif ($country_restrict_status && !self::delivery_country_exist_check($shipping_address->country)) {
                        return response()->json(['message' => translate('Delivery_unavailable_for_this_country')], 403);

                    } elseif ($zip_restrict_status && !self::delivery_zipcode_exist_check($shipping_address->zip)) {
                        return response()->json(['message' => translate('Delivery_unavailable_for_this_zip_code_area')], 403);
                    }
                }
            }

            OrderManager::generateOrder(data: [
                'is_guest' => 0,
                'guest_id' => 0,
                'customer_id' => $user['id'],
                'order_status' => 'confirmed',
                'payment_method' => 'pay_by_wallet',
                'payment_status' => 'paid',
                'transaction_ref' => '',
                'address_id' => $request['address_id'],
                'billing_address_id' => $request['billing_address_id'],
                'payment_note' => $request['payment_note'],
                'order_note' => $request['order_note'],
                'coupon_code' => $request['coupon_code'],
                'requestObj' => $request,
            ]);

            CustomerManager::create_wallet_transaction($user->id, Convert::default($paymentAmount), 'order_place', 'order payment');
            return response()->json(translate('order_placed_successfully'), 200);
        }
    }

    public function refund_request(Request $request): JsonResponse
    {
        $order_details = OrderDetail::find($request->order_details_id);

        $user = $request->user();
        $loyaltyPointStatus = getWebConfig(name: 'loyalty_point_status');
        if ($loyaltyPointStatus == 1) {
            $loyaltyPoint = CustomerManager::count_loyalty_point_for_amount($request->order_details_id);
            if (($user->loyalty_point ?? 0) < $loyaltyPoint) {
                return response()->json(['message' => translate('you_have_not_sufficient_loyalty_point_to_refund_this_order')], 202);
            }
        }

        if ($order_details->delivery_status == 'delivered') {
            $order = Order::find($order_details->order_id);
            $total_product_price = 0;
            $data = [];
            foreach ($order->details as $key => $or_d) {
                $total_product_price += ($or_d->qty * $or_d->price) + $or_d->tax - $or_d->discount;
            }

            $subtotal = ($order_details->price * $order_details->qty) - $order_details->discount + $order_details->tax;
            $coupon_discount = ($order->discount_amount * $subtotal) / $total_product_price;

            $refundInfo = OrderManager::getRefundDetailsForSingleOrderDetails(orderDetailsId: $request['order_details_id']);

            $data['product_price'] = $order_details->price;
            $data['quntity'] = $order_details->qty;
            $data['product_total_discount'] = $order_details->discount;
            $data['product_total_tax'] = $order_details->tax;
            $data['subtotal'] = $subtotal;
            $data['coupon_discount'] = $coupon_discount;
            $data['refund_amount'] = $refundInfo['total_refundable_amount'];
            $data['referral_discount'] = $refundInfo['referral_discount'];

            $expired = false;
            $already_requested = false;
            if ($order_details->refund_request != 0) {
                $already_requested = true;
            }
            if (!is_null($order_details?->refund_started_at) && $order_details?->refund_started_at?->diffInDays(Carbon::now()) > getWebConfig(name: 'refund_day_limit')) {
                $expired = true;
            }
            return response()->json(['already_requested' => $already_requested, 'expired' => $expired, 'refund' => $data], 200);
        } else {
            return response()->json(['message' => translate('You_can_request_for_refund_after_order_delivered')], 200);
        }
    }

    public function store_refund(Request $request): JsonResponse
    {
        $orderDetails = OrderDetail::find($request['order_details_id']);
        $user = $request->user();
        $loyalty_point_status = getWebConfig(name: 'loyalty_point_status');
        $orderDetailsReward = OrderDetailsRewards::where('order_details_id', $request['order_details_id'])->where('reward_type', '!=', 'loyalty_point')->first();
        if($orderDetailsReward && $user->loyalty_point < $orderDetailsReward['reward_amount']) {
            return response()->json(translate('you have not sufficient loyalty point to refund this order!!'), 200);
        }
        if ($orderDetails->refund_request == 0) {
            $validator = Validator::make($request->all(), [
                'order_details_id' => 'required',
                'amount' => 'required',
                'refund_reason' => 'required'

            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => Helpers::validationErrorProcessor($validator)], 403);
            }
            $refund_request = new RefundRequest;
            $refund_request->order_details_id = $request['order_details_id'];
            $refund_request->customer_id = $request->user()->id;
            $refund_request->status = 'pending';
            $refund_request->amount = OrderManager::getRefundDetailsForSingleOrderDetails(orderDetailsId: $orderDetails['id'])['total_refundable_amount'];
            $refund_request->product_id = $orderDetails->product_id;
            $refund_request->order_id = $orderDetails->order_id;
            $refund_request->refund_reason = $request->refund_reason;

            if ($request->file('images')) {
                foreach ($request->file('images') as $img) {
                    $images[] = [
                        'image_name' => $this->upload('refund/', 'webp', $img),
                        'storage' => getWebConfig(name: 'storage_connection_type') ?? 'public',
                    ];

                }
                $refund_request->images = $images;
            }
            $refund_request->save();

            $orderDetails->refund_request = 1;
            $orderDetails->save();

            $order = Order::find($orderDetails->order_id);
            event(new RefundEvent(status: 'refund_request', order: $order, refund: $refund_request, orderDetails: $orderDetails));

            return response()->json(translate('refunded_request_updated_successfully!!'), 200);
        } else {
            return response()->json(translate('already_applied_for_refund_request!!'), 302);
        }
    }

    public function refund_details(Request $request): JsonResponse
    {
        $orderDetails = OrderDetail::find($request->id);
        $refund = RefundRequest::where('customer_id', $request->user()->id)
            ->with(['refundStatus'])
            ->where('order_details_id', $orderDetails->id)->get();

        $order = Order::find($orderDetails->order_id);

        $total_product_price = 0;
        $data = [];
        foreach ($order->details as $key => $or_d) {
            $total_product_price += ($or_d->qty * $or_d->price) + $or_d->tax - $or_d->discount;
        }

        $subtotal = ($orderDetails->price * $orderDetails->qty) - $orderDetails->discount + $orderDetails->tax;
        $coupon_discount = ($order->discount_amount * $subtotal) / $total_product_price;

        $data['product_price'] = $orderDetails->price;
        $data['quntity'] = $orderDetails->qty;
        $data['product_total_discount'] = $orderDetails->discount;
        $data['product_total_tax'] = $orderDetails->tax;
        $data['subtotal'] = $subtotal;
        $data['coupon_discount'] = $coupon_discount;
        $data['refund_amount'] = OrderManager::getRefundDetailsForSingleOrderDetails(orderDetailsId: $orderDetails['id'])['total_refundable_amount'];
        $data['refund_request'] = $refund;
        $data['order_place_date'] = $order->created_at;
        $data['referral_discount'] = $order?->refer_and_earn_discount ?? 0;

        return response()->json($data, 200);
    }

    public function digital_product_download($id, Request $request)
    {
        $user = Helpers::getCustomerInformation($request);
        $order_details_data = OrderDetail::with('order.customer')->find($id);

        if ($order_details_data) {
            if ($order_details_data->order->payment_status !== "paid") {
                return response()->json([
                    'status' => 0,
                    'message' => translate('Payment_must_be_confirmed_first') . ' !!',
                ]);
            };

            if ($order_details_data->order->is_guest) {
                $customer_email = $order_details_data->order->shipping_address_data ? $order_details_data->order->shipping_address_data->email : ($order_details_data->order->billing_address_data ? $order_details_data->order->billing_address_data->email : '');

                $customer_phone = $order_details_data->order->shipping_address_data ? $order_details_data->order->shipping_address_data->phone : ($order_details_data->order->billing_address_data ? $order_details_data->order->billing_address_data->phone : '');

                $customer_data = ['email' => $customer_email, 'phone' => $customer_phone];
                return self::digital_product_download_process($order_details_data, $customer_data);
            } else {
                if ($user != 'offline' && $user->id == $order_details_data->order->customer->id) {
                    $file_name = '';
                    if ($order_details_data->product->digital_product_type == 'ready_product' && $order_details_data->product->digital_file_ready) {
                        $file_path = asset('storage/app/public/product/digital-product/' . $order_details_data->product->digital_file_ready);
                        $file_name = $order_details_data->product->digital_file_ready;
                    } else {
                        $file_path = asset('storage/app/public/product/digital-product/' . $order_details_data->digital_file_after_sell);
                        $file_name = $order_details_data->digital_file_after_sell;
                    }

                    if (File::exists(base_path('storage/app/public/product/digital-product/' . $file_name))) {
                        return \response()->download($file_path);
                    } else {
                        return response()->json([
                            'status' => 0,
                            'message' => translate('file_not_found'),
                        ]);
                    }
                } else {
                    $customer_data = ['email' => $order_details_data->order->customer->email ?? '', 'phone' => $order_details_data->order->customer->phone ?? ''];
                    return self::digital_product_download_process($order_details_data, $customer_data);
                }
            }
        } else {
            return response()->json(['message' => translate('order_Not_Found')], 403);
        }
    }

    public function digital_product_download_process($order_details_data, $customer)
    {
        $status = 2;
        $emailServices_smtp = getWebConfig(name: 'mail_config');
        if ($emailServices_smtp['status'] == 0) {
            $emailServices_smtp = getWebConfig(name: 'mail_config_sendgrid');
        }

        $paymentPublishedStatus = config('get_payment_publish_status') ?? 0;

        if ($paymentPublishedStatus == 1) {
            $smsConfigStatus = Setting::where(['settings_type' => 'sms_config', 'is_active' => 1])->count() > 0 ? 1 : 0;
        } else {
            $smsConfigStatus = Setting::where(['settings_type' => 'sms_config', 'is_active' => 1])->whereIn('key_name', Helpers::getDefaultSMSGateways())->count() > 0 ? 1 : 0;
        }

        if ($emailServices_smtp['status'] || $smsConfigStatus) {
            $token = rand(1000, 9999);
            if ($customer['email'] == '' && $customer['phone'] == '') {
                return response()->json([
                    'status' => $status,
                    'file_path' => '',
                    'view' => view(VIEW_FILE_NAMES['digital_product_order_otp_verify_failed'])->render(),
                ]);
            }

            $verification_data = DigitalProductOtpVerification::where('identity', $customer['email'])->orWhere('identity', $customer['phone'])->where('order_details_id', $order_details_data->id)->latest()->first();
            $otp_interval_time = getWebConfig(name: 'otp_resend_time') ?? 1; //second

            if (isset($verification_data) && Carbon::parse($verification_data->created_at)->diffInSeconds() < $otp_interval_time) {
                $time_count_in_second = $otp_interval_time - Carbon::parse($verification_data->created_at)->diffInSeconds();
                return response()->json([
                    'status' => 0,
                    'email_config_status' => $emailServices_smtp['status'],
                    'sms_config_status' => $smsConfigStatus,
                    'time_count_in_second' => $time_count_in_second,
                ]);
            } else {
                $verify_data = [
                    'order_details_id' => $order_details_data->id,
                    'token' => $token,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                DigitalProductOtpVerification::updateOrInsert(['identity' => $customer['email'], 'order_details_id' => $order_details_data->id], $verify_data);
                DigitalProductOtpVerification::updateOrInsert(['identity' => $customer['phone'], 'order_details_id' => $order_details_data->id], $verify_data);

                $reset_data = DigitalProductOtpVerification::where('identity', $customer['email'])->orWhere('identity', $customer['phone'])->where('order_details_id', $order_details_data->id)->latest()->first();
                $otp_resend_time = getWebConfig(name: 'otp_resend_time') > 0 ? getWebConfig(name: 'otp_resend_time') : 0;
                $token_time = Carbon::parse($reset_data->created_at);
                $convert_time = $token_time->addSeconds($otp_resend_time);
                $time_count_in_second = $convert_time > Carbon::now() ? Carbon::now()->diffInSeconds($convert_time) : 0;
                $mail_status = 0;

                if ($emailServices_smtp['status'] == 1) {
                    try {
                        $data = [
                            'userName' => $customer['f_name'],
                            'userType' => 'customer',
                            'templateName' => 'digital-product-otp',
                            'subject' => translate('verification_Code'),
                            'title' => translate('verification_Code') . '!',
                            'verificationCode' => $token,
                        ];
                        event(new DigitalProductOtpVerificationEvent(email: $customer['email'], data: $data));
                        $mail_status = 1;
                    } catch (\Exception $exception) {
                    }
                }

                $response = SMSModule::sendCentralizedSMS($customer['phone'], $token);

                $sms_status = ($response == "not_found" || $smsConfigStatus == 0) ? 0 : 1;
                if ($mail_status || $sms_status) {
                    return response()->json([
                        'status' => 1,
                        'email_config_status' => $emailServices_smtp['status'],
                        'sms_config_status' => $smsConfigStatus,
                        'email_sent_status' => $mail_status,
                        'sms_sent_status' => $sms_status,
                        'time_count_in_second' => $time_count_in_second,
                    ], 200);
                } else {
                    return response()->json([
                        'status' => 0,
                        'email_config_status' => $emailServices_smtp['status'],
                        'sms_config_status' => $smsConfigStatus,
                        'email_sent_status' => $mail_status,
                        'sms_sent_status' => $sms_status,
                        'time_count_in_second' => $time_count_in_second,
                    ], 403);
                }
            }
        } else {
            return response()->json([
                'status' => 0,
                'email_config_status' => $emailServices_smtp['status'],
                'sms_config_status' => $smsConfigStatus,
                'email_config_status' => $emailServices_smtp['status'],
                'sms_config_status' => $smsConfigStatus,
            ], 403);
        }

    }

    public function digital_product_download_otp_verify(Request $request)
    {
        $verification = DigitalProductOtpVerification::where(['token' => $request->otp, 'order_details_id' => $request->order_details_id])->first();
        $order_details_data = OrderDetail::with('order.customer')->find($request->order_details_id);

        if ($verification) {
            if ($order_details_data) {
                if ($order_details_data->product->digital_product_type == 'ready_product' && $order_details_data->product->digital_file_ready) {
                    $file_path = storage_path('app/public/product/digital-product/' . $order_details_data->product->digital_file_ready);
                    $file_name = $order_details_data->product->digital_file_ready;
                } else if ($order_details_data->digital_file_after_sell) {
                    $file_path = storage_path('app/public/product/digital-product/' . $order_details_data->digital_file_after_sell);
                    $file_name = $order_details_data->digital_file_after_sell;
                }
            }

            if ($request->has('action') && $request->action == "download") {
                DigitalProductOtpVerification::where(['token' => $request->otp, 'order_details_id' => $request->order_details_id])->delete();
            }

            if (isset($file_name) && File::exists(base_path('storage/app/public/product/digital-product/' . $file_name))) {
                return \response()->download($file_path);
            } else {
                return response()->json([
                    'status' => 0,
                    'message' => translate('file_not_found'),
                ]);
            }

        } else {
            return response()->json([
                'message' => translate('The_OTP_is_incorrect'),
            ], 403);
        }
    }

    public function digital_product_download_otp_resend(Request $request)
    {
        $token_info = DigitalProductOtpVerification::where(['order_details_id' => $request->order_details_id])->first();
        $otp_interval_time = getWebConfig(name: 'otp_resend_time') ?? 1; //minute
        if (isset($token_info) && Carbon::parse($token_info->created_at)->diffInSeconds() < $otp_interval_time) {
            $time_count_in_second = $otp_interval_time - Carbon::parse($token_info->created_at)->diffInSeconds();

            return response()->json([
                'status' => 0,
                'time_count_in_second' => $time_count_in_second,
                'message' => 'Please try again after ' . CarbonInterval::seconds($time_count_in_second)->cascade()->forHumans()
            ]);
        } else {
            $guest_email = '';
            $guest_phone = '';
            $token = rand(1000, 9999);

            $order_details_data = OrderDetail::with('order.customer')->find($request->order_details_id);

            try {
                if ($order_details_data->order->shipping_address_data) {
                    $guest_name = $order_details_data->order->shipping_address_data ? $order_details_data->order->shipping_address_data->contact_person_name : null;
                    $guest_email = $order_details_data->order->shipping_address_data ? $order_details_data->order->shipping_address_data->email : null;
                    $guest_phone = $order_details_data->order->shipping_address_data ? $order_details_data->order->shipping_address_data->phone : null;
                } else {
                    $guest_name = $order_details_data->order->billing_address_data ? $order_details_data->order->billing_address_data->contact_person_name : null;
                    $guest_email = $order_details_data->order->billing_address_data ? $order_details_data->order->billing_address_data->email : null;
                    $guest_phone = $order_details_data->order->billing_address_data ? $order_details_data->order->billing_address_data->phone : null;
                }
            } catch (\Throwable $th) {

            }

            $emailServices_smtp = getWebConfig(name: 'mail_config');
            if ($emailServices_smtp['status'] == 0) {
                $emailServices_smtp = getWebConfig(name: 'mail_config_sendgrid');
            }
            if ($emailServices_smtp['status'] == 1) {
                try {
                    $data = [
                        'userName' => $guest_name,
                        'userType' => 'customer',
                        'templateName' => 'digital-product-otp',
                        'subject' => translate('verification_Code'),
                        'title' => translate('verification_Code') . '!',
                        'verificationCode' => $token,
                    ];
                    event(new DigitalProductOtpVerificationEvent(email: $guest_email, data: $data));
                    $mail_status = 1;
                } catch (\Exception $exception) {
                    $mail_status = 0;
                }
            } else {
                $mail_status = 0;
            }

            $response = SMSModule::sendCentralizedSMS($guest_phone, $token);

            $sms_status = $response == "not_found" ? 0 : 1;
            if ($mail_status || $sms_status) {
                $verify_data = [
                    'order_details_id' => $order_details_data->id,
                    'token' => $token,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                DigitalProductOtpVerification::updateOrInsert(['identity' => $guest_email, 'order_details_id' => $order_details_data->id], $verify_data);
                DigitalProductOtpVerification::updateOrInsert(['identity' => $guest_phone, 'order_details_id' => $order_details_data->id], $verify_data);
            }

            return response()->json([
                'mail_status' => $mail_status,
                'sms_status' => $sms_status,
                'status' => ($mail_status || $sms_status) ? 1 : 0,
                'new_time' => $otp_interval_time,
                'message' => 'OTP sent successfully',
            ]);

        }
    }

    public function order_again(Request $request): JsonResponse
    {
        $orderData = OrderManager::generateOrderAgain($request);
        $addToCartCount = $orderData['add_to_cart_count'];

        if ($orderData['order_product_count'] == $addToCartCount) {
            return response()->json(['message' => 'Added to cart successfully'], 200);
        } elseif ($addToCartCount > 0) {
            return response()->json(['message' => $addToCartCount . ' item added to cart successfully!'], 200);
        }

        return response()->json(['message' => 'All items were not added to cart as they are currently unavailable for purchase'], 403);
    }

    public function offline_payment_method_list(Request $request): JsonResponse
    {
        $data = OfflinePaymentMethod::where('status', 1)->get();
        return response()->json(['offline_methods' => $data], 200);
    }

    public function track_order(Request $request): JsonResponse
    {
        $user = Helpers::getCustomerInformation($request);

        if ($user != 'offline') {
            $order = Order::where(['id' => $request['order_id'], 'order_type' => 'default_type'])->first();
            if ($order && $order->is_guest) {
                $orderDetails = Order::where(['id' => $request['order_id'], 'order_type' => 'default_type'])->whereHas('shippingAddress', function ($query) use ($request) {
                    $query->where('phone', $request['phone_number']);
                })->first();

                if (!$orderDetails) {
                    $orderDetails = Order::where(['id' => $request['order_id'], 'order_type' => 'default_type'])->whereHas('billingAddress', function ($query) use ($request) {
                        $query->where('phone', $request['phone_number']);
                    })->first();
                }
            } elseif ($user->phone == $request['phone_number']) {
                $orderDetails = Order::where(['id' => $request['order_id'], 'order_type' => 'default_type', 'customer_id' => auth('customer')->id()])
                    ->whereHas('details', function ($query) {
                        return $query;
                    })->first();
            }

            if ($request['from_order_details'] == 1) {
                $orderDetails = Order::where(['id' => $request['order_id'], 'order_type' => 'default_type'])->whereHas('details', function ($query) {
                    $query->where('customer_id', auth('customer')->id());
                })->first();
            }

        } else {
            $user_id = User::where('phone', $request['phone_number'])->first();
            $order = Order::where('id', $request['order_id'])->first();

            if ($order && $order->is_guest) {
                $orderDetails = Order::where(['id' => $request['order_id'], 'order_type' => 'default_type'])->whereHas('shippingAddress', function ($query) use ($request) {
                    $query->where('phone', $request['phone_number']);
                })->first();

                if (!$orderDetails) {
                    $orderDetails = Order::where(['id' => $request['order_id'], 'order_type' => 'default_type'])->whereHas('billingAddress', function ($query) use ($request) {
                        $query->where('phone', $request['phone_number']);
                    })->first();
                }
            } elseif ($user_id) {
                $orderDetails = Order::where(['customer_id' => $user_id->id, 'id' => $request['order_id'], 'order_type' => 'default_type'])->whereHas('details', function ($query) {
                    return $query;
                })->first();
            } else {
                return response()->json(['message' => 'Invalid Phone Number'], 403);
            }
        }

        if (isset($orderDetails)) {
            $details = OrderDetail::with(['order.deliveryMan', 'verificationImages', 'seller.shop', 'product'])
                ->where(['order_id' => $orderDetails['id']])
                ->get();

            $details->map(function ($query) {
                $query['variation'] = json_decode($query['variation'], true);
                $product = json_decode($query['product_details'], true);
                if ($product['product_type'] == 'digital' && $product['digital_product_type'] == 'ready_product' && $product['digital_file_ready']) {
                    $checkFilePath = storageLink('product/digital-product', $product['digital_file_ready'], ($product['storage_path'] ?? 'public'));
                    $product['digital_file_ready_full_url'] = $checkFilePath;
                }

                if (!isset($product['thumbnail_full_url']) && isset($query?->product?->thumbnail_full_url)) {
                    $product['thumbnail_full_url'] = $query?->product?->thumbnail_full_url;
                }

                $query['product_details'] = Helpers::product_data_formatting($product);
                return $query;
            });

            return response()->json($details, 200);
        }

        return response()->json(['message' => 'Invalid Order Id or Phone Number'], 403);
    }
}
