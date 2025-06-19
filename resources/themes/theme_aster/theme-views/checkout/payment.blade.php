@php use App\Utils\Helpers; @endphp
@extends('theme-views.layouts.app')

@section('title', translate('Payment_Details').' | '.$web_config['company_name'].' '.translate('ecommerce'))

@section('content')
    <main class="main-content d-flex flex-column gap-3 py-3 mb-5">
        <div class="container">
            <h4 class="text-center mb-3 text-capitalize">{{ translate('payment_details') }}</h4>
            <div class="row payment-method-list-page">
                <div class="col-lg-8 mb-3 mb-lg-0">
                    <div class="card h-100">
                        <div class="card-body  px-sm-4">
                            <div class="d-flex justify-content-center mb-30">
                                <ul class="cart-step-list">
                                    <li class="done cursor-pointer get-view-by-onclick"
                                        data-link="{{route('shop-cart')}}">
                                        <span><i class="bi bi-check2"></i></span> {{ translate('cart') }}
                                    </li>
                                    <li class="done cursor-pointer get-view-by-onclick text-capitalize"
                                        data-link="{{route('checkout-details')}}">
                                        <span><i class="bi bi-check2"></i></span> {{ translate('shipping_details') }}
                                    </li>
                                    <li class="current"><span><i
                                                class="bi bi-check2"></i></span> {{ translate('payment') }}</li>
                                </ul>
                            </div>

                            @if(!$activeMinimumMethods)
                                <div class="d-flex justify-content-center py-5 align-items-center">
                                    <div class="text-center">
                                        <img src="{{ theme_asset(path: 'assets/img/not_found.png') }}" alt="" class="mb-4" width="70">
                                        <h5 class="fs-14 text-muted">{{ translate('payment_methods_are_not_available_at_this_time.') }}</h5>
                                    </div>
                                </div>
                            @else
                                <h5 class="mb-4 text-capitalize">{{ translate('payment_information') }}</h5>

                                <div class="mb-30">
                                    <ul class="option-select-btn d-grid flex-wrap gap-3">
                                        @if($cashOnDeliveryBtnShow && $cash_on_delivery['status'])
                                            <li>
                                                <form action="{{ route('checkout-complete') }}" method="get" class="checkout-payment-form payment-method-form checkout-cash-on-payment">
                                                    <label class="w-100">
                                                        <input type="radio" hidden name="payment_method" value="cash_on_delivery" data-form=".checkout-cash-on-payment">
                                                        <button type="submit" class="payment-method payment-method_parent next-btn-enable d-flex align-items-center overflow-hidden flex-column p-0 w-100">
                                                           <div class="d-flex align-items-center gap-3 pt-1">
                                                                <img width="30" class="dark-support" alt=""
                                                                src="{{ theme_asset('assets/img/icons/cash-on.png') }}">
                                                                <span class="text-capitalize fs-16">{{ translate('cash_on_delivery') }}</span>
                                                           </div>

                                                            <div class="w-100">
                                                                <div class="collapse" id="bring_change_amount" data-more="{{ translate('See_More') }}" data-less="{{ translate('See_Less') }}">
                                                                    <div class="bg-primary-op-05 border border-white rounded text-start p-3 mx-3 my-2">
                                                                        <h6 class="fs-12 fw-semibold mb-1">
                                                                            {{ translate('Change_Amount') }} ({{ getCurrencySymbol(type: 'web') }})
                                                                        </h6>
                                                                        <p class="mb-0 fs-12 opacity-75 fw-normal text-transform-none">
                                                                            {{ translate('Insert_amount_if_you_need_deliveryman_to_bring') }}
                                                                        </p>
                                                                        <input type="text" class="form-control mt-2 only-integer-input-field" placeholder="{{ translate('Amount') }}" name="bring_change_amount">
                                                                    </div>
                                                                </div>
                                                                <div class="text-center">
                                                                    <a id="bring_change_amount_btn" class="btn primary-color border-0 fs-12 text-center text-capitalize shadow-none border-0 base-color p-0" data-bs-toggle="collapse" href="#bring_change_amount" role="button" aria-expanded="false" aria-controls="change_amount">
                                                                        {{ translate('see_more') }}
                                                                    </a>
                                                                </div>
                                                            </div>
                                                        </button>
                                                    </label>
                                                </form>
                                            </li>
                                        @endif

                                        @if(auth('customer')->check() && $wallet_status == 1)
                                            <li>
                                                <label class="w-100">
                                                    <button class="payment-method payment-method_parent d-flex align-items-center gap-3 overflow-hidden w-100"
                                                        type="submit" data-bs-toggle="modal"
                                                        data-bs-target="#wallet_submit_button">
                                                        <img width="30"
                                                             src="{{ theme_asset('assets/img/icons/wallet.png') }}"
                                                             class="dark-support" alt="">
                                                        <span class="fs-16">{{ translate('wallet') }}</span>
                                                    </button>
                                                </label>
                                            </li>
                                        @endif

                                        @if($digital_payment['status'] == 1)
                                            @if(isset($offline_payment) && $offline_payment['status'] && count($offline_payment_methods) > 0)
                                                <li>
                                                    <label class="w-100">
                                                        <span
                                                            class="payment-method payment-method_parent d-flex align-items-center gap-3 overflow-hidden"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#offline_payment_submit_button">
                                                                <img width="30"
                                                                     src="{{ theme_asset('assets/img/icons/cash-payment.png') }}"
                                                                     class="dark-support" alt="">
                                                                <span class="fs-16">{{ translate('offline_payment') }}</span>
                                                         </span>
                                                    </label>
                                                </li>
                                            @endif

                                            @if(count($payment_gateways_list) > 0 || (isset($offline_payment) && $offline_payment['status'] && count($offline_payment_methods) > 0))
                                                <li>
                                                    <label id="digital-payment-btn" class="w-100">
                                                        <span class="payment-method payment-method_parent d-flex align-items-center gap-3">
                                                            <img width="30"
                                                                 src="{{ theme_asset('assets/img/icons/degital-payment.png') }}"
                                                                 class="dark-support" alt="">
                                                            <span class="fs-16">{{ translate('Digital_Payment') }}</span>
                                                        </span>
                                                    </label>
                                                </li>

                                                @foreach ($payment_gateways_list as $payment_gateway)
                                                    <li>
                                                        <form method="post"
                                                              class="digital-payment d--none payment-method-form checkout-payment-{{ $payment_gateway->key_name }}"
                                                              action="{{ route('customer.web-payment-request') }}"
                                                        >
                                                            @csrf
                                                            <input type="text" hidden name="user_id" value="{{ auth('customer')->check() ? auth('customer')->id() : session('guest_id') }}">
                                                            <input type="text" hidden name="customer_id" value="{{ auth('customer')->check() ? auth('customer')->id() : session('guest_id') }}">
                                                            <input type="radio" hidden name="payment_method" value="{{ $payment_gateway->key_name }}" data-form=".checkout-payment-{{ $payment_gateway->key_name }}">
                                                            <input type="text" hidden name="payment_platform" value="web">
                                                            @if ($payment_gateway->mode == 'live' && isset($payment_gateway->live_values['callback_url']))
                                                                <input type="text" hidden name="callback" value="{{ $payment_gateway->live_values['callback_url'] }}">
                                                            @elseif ($payment_gateway->mode == 'test' && isset($payment_gateway->test_values['callback_url']))
                                                                <input type="text" hidden name="callback" value="{{ $payment_gateway->test_values['callback_url'] }}">
                                                            @else
                                                                <input type="text" hidden name="callback" value="">
                                                            @endif
                                                            <input type="text" hidden name="external_redirect_link" value="{{ route('web-payment-success') }}">

                                                            <label class="w-100">
                                                                @php($additional_data = $payment_gateway['additional_data'] != null ? json_decode($payment_gateway['additional_data']) : [])
                                                                <button class="payment-method next-btn-enable d-flex align-items-center gap-3 digital-payment-card overflow-hidden w-100" type="submit">
                                                                    @if($additional_data != null && isset($additional_data->gateway_image) && file_exists(base_path('storage/app/public/payment_modules/gateway_image/'.$additional_data->gateway_image)))
                                                                        <img width="100" class="dark-support" alt=""
                                                                             src="{{ getStorageImages(path: null, type: 'payment-banner', source: 'storage/app/public/payment_modules/gateway_image/'.($additional_data != null ? $additional_data->gateway_image : '')) }}">
                                                                    @else
                                                                        <h4>{{ ucwords(str_replace('_', ' ', $payment_gateway->key_name)) }}</h4>
                                                                    @endif
                                                                </button>
                                                            </label>
                                                        </form>
                                                    </li>
                                                @endforeach
                                            @endif
                                        @endif
                                    </ul>

                                    @if(auth('customer')->check() && $wallet_status==1)
                                        <div class="modal fade" id="wallet_submit_button">
                                            <div class="modal-dialog modal-dialog-centered">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title"
                                                            id="exampleModalLongTitle">{{ translate('wallet_payment') }}</h5>
                                                        <button type="reset" class="btn-close" data-bs-dismiss="modal"
                                                                aria-label="Close"></button>
                                                    </div>
                                                    @php($customer_balance = auth('customer')->user()->wallet_balance)
                                                    @php($remain_balance = $customer_balance - $amount)
                                                    <form action="{{route('checkout-complete-wallet')}}" method="get"
                                                          class="needs-validation checkout-wallet-payment-form">
                                                        @csrf
                                                        <div class="modal-body">
                                                            <div class="form-row mb-3">
                                                                <div class="form-group col-12">
                                                                    <label
                                                                        for="">{{ translate('your_current_balance') }}</label>
                                                                    <input class="form-control" type="text"
                                                                           value="{{webCurrencyConverter($customer_balance)}}"
                                                                           readonly>
                                                                </div>
                                                            </div>

                                                            <div class="form-row mb-3">
                                                                <div class="form-group col-12">
                                                                    <label
                                                                        for="">{{ translate('order_amount') }}</label>
                                                                    <input class="form-control" type="text"
                                                                           value="{{webCurrencyConverter($amount)}}"
                                                                           readonly>
                                                                </div>
                                                            </div>
                                                            <div class="form-row mb-2">
                                                                <div class="form-group col-12">
                                                                    <label for="">
                                                                        {{ translate('remaining_balance') }}
                                                                    </label>
                                                                    <input class="form-control" type="text"
                                                                           value="{{webCurrencyConverter($remain_balance)}}"
                                                                           readonly>
                                                                    @if ($remain_balance<0)
                                                                        <label
                                                                            class="__color-crimson mt-2">{{ translate('you_do_not_have_sufficient_balance_for_pay_this_order') }}
                                                                            !!</label>
                                                                    @endif
                                                                </div>
                                                            </div>

                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" data-bs-dismiss="modal"
                                                                    class="update_cart_button fs-16 btn btn-secondary"
                                                                    data-dismiss="modal">{{ translate('close') }}</button>
                                                            <button type="submit"
                                                                    class="update_cart_button update_wallet_cart_button fs-16 btn btn-primary" {{$remain_balance>0? '':'disabled'}}>{{ translate('submit') }}</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    @endif

                                    @if(isset($offline_payment) && $offline_payment['status'])
                                        <div class="modal fade" id="offline_payment_submit_button">
                                            <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title"
                                                            id="exampleModalLongTitle">{{ translate('offline_Payment') }}</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                                aria-label="Close"></button>
                                                    </div>
                                                    <form action="{{route('offline-payment-checkout-complete')}}" method="post" class="needs-validation form-loading-button-form">
                                                        @csrf
                                                        <div class="modal-body p-3 p-md-5">

                                                            <div class="text-center px-5">
                                                                <img
                                                                    src="{{ theme_asset('assets/img/offline-payments.png') }}"
                                                                    alt="">
                                                                <p class="py-2">
                                                                    {{ translate('pay_your_bill_using_any_of_the_payment_method_below_and_input_the_required_information_in_the_form') }}
                                                                </p>
                                                            </div>

                                                            <div class="">

                                                                <select class="form-select" id="pay-offline-method"
                                                                        name="payment_by" required>
                                                                    <option
                                                                        value="">{{ translate('select_Payment_Method') }}</option>
                                                                    @foreach ($offline_payment_methods as $method)
                                                                        <option value="{{ $method->id }}">{{ translate('payment_Method') .' : '. $method->method_name }}</option>
                                                                    @endforeach
                                                                </select>
                                                            </div>

                                                            <div class="" id="method-filed-div">
                                                                <div class="text-center py-5">
                                                                    <img class="pt-5"
                                                                        src="{{ theme_asset('assets/img/offline-payments-vectors.png') }}"
                                                                        alt="">
                                                                    <p class="py-2 pb-5 text-muted">{{ translate('select_a_payment_method first') }}</p>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            @endif

                        </div>
                    </div>
                </div>
                @include('theme-views.partials._order-summery')
            </div>
        </div>
    </main>
    <span class="get-payment-method-list" data-action="{{route('pay-offline-method-list')}}"></span>
@endsection

@push('script')
    <script src="{{ theme_asset('assets/js/payment-page.js') }}"></script>
@endpush
