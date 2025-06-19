@php use Illuminate\Support\Facades\Session; @endphp
@extends('layouts.back-end.app')
@section('title', translate('payment_options'))
@section('content')
    @php($direction = Session::get('direction') === 'rtl' ? 'right' : 'left')
    <div class="content container-fluid">
        <div class="mb-4 pb-2">
            <h2 class="h1 mb-0 text-capitalize d-flex align-items-center gap-2">
                <img src="{{ dynamicAsset(path: 'public/assets/back-end/img/business-setup.png') }}" alt="">
                {{ translate('business_Setup') }}
            </h2>
        </div>
        @include('admin-views.business-settings.business-setup-inline-menu')
        <div class="card mb-4">
            <div class="card-body">
                <form action="{{ route('admin.business-settings.payment-method.payment-option') }}" method="post" id="payment-methods-settings-form">
                    @csrf
                    <h5 class="mb-4 text-uppercase d-flex text-capitalize">{{ translate('payment_methods') }}</h5>
                    <div class="row">
                        @isset($cashOnDelivery)
                            <div class="col-xl-4 col-sm-6">
                                <div class="form-group">
                                    <div class="d-flex justify-content-between align-items-center gap-10 form-control">
                                        <span class="title-color">
                                            {{ translate('cash_on_delivery') }}
                                            <span class="input-label-secondary cursor-pointer" data-toggle="tooltip"
                                                data-placement="top"
                                                title="{{ translate('if_enabled,_the_cash_on_delivery_option_will_be_available_on_the_system._Customers_can_use_COD_as_a_payment_option.') }}">
                                                <img width="16"
                                                    src="{{ dynamicAsset(path: 'public/assets/back-end/img/info-circle.svg') }}"
                                                    alt="">
                                            </span>
                                        </span>
                                        <label class="switcher" for="cash-on-delivery">
                                            <input type="checkbox" class="switcher_input toggle-switch-message"
                                                name="cash_on_delivery" id="cash-on-delivery" value="1"
                                                {{ $cashOnDelivery['status'] == 1 ? 'checked' : '' }}
                                                data-modal-id="toggle-modal" data-toggle-id="cash-on-delivery"
                                                data-on-image="cod-on.png" data-off-image="warning.png"
                                                data-on-title="{{ translate('want_to_Turn_ON_the_Cash_On_Delivery_option') }}"
                                                data-off-title="{{ translate('want_to_Turn_OFF_the_Cash_On_Delivery_option') }}"
                                                data-on-message="<p>{{ translate('if_enabled_customers_can_select_Cash_on_Delivery_as_a_payment_method_during_checkout') }}</p>"
                                                data-off-message="<p>{{ translate('if_disabled_the_Cash_on_Delivery_payment_method_will_be_hidden_from_the_checkout_page') }}</p>">
                                            <span class="switcher_control"></span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        @endisset
                        @isset($digitalPayment)
                            <div class="col-xl-4 col-sm-6">
                                <div class="form-group">
                                    <div class="d-flex justify-content-between align-items-center gap-10 form-control">
                                        <span class="title-color">
                                            {{ translate('digital_payment') }}
                                            <span class="input-label-secondary cursor-pointer" data-toggle="tooltip"
                                                data-placement="top"
                                                title="{{ translate('if_enabled,_customers_can_choose_digital_payment_options_during_the_checkout_process') }}">
                                                <img width="16"
                                                    src="{{ dynamicAsset(path: 'public/assets/back-end/img/info-circle.svg') }}"
                                                    alt="">
                                            </span>
                                        </span>

                                        <label class="switcher" for="digital-payment">
                                            <input type="checkbox" class="switcher_input toggle-switch-message"
                                                name="digital_payment" id="digital-payment" value="1"
                                                {{ $digitalPayment['status'] == 1 ? 'checked' : '' }}
                                                data-modal-id="toggle-modal" data-toggle-id="digital-payment"
                                                data-on-image="digital-payment-on.png" data-off-image="digital-payment-off.png"
                                                data-on-title="{{ translate('want_to_Turn_ON_the_Digital_Payment_option') }}"
                                                data-off-title="{{ translate('want_to_Turn_OFF_the_Digital_Payment_option') }}"
                                                data-on-message="<p>{{ translate('if_enabled_customers_can_select_Digital_Payment_during_checkout') }}</p>"
                                                data-off-message="<p>{{ translate('if_disabled_Digital_Payment_options_will_be_hidden_from_the_checkout_page') }}</p>">
                                            <span class="switcher_control"></span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        @endisset
                        @isset($offlinePayment)
                            <div class="col-xl-4 col-sm-6">
                                <div class="form-group">
                                    <div class="d-flex justify-content-between align-items-center gap-10 form-control">
                                        <span class="title-color">
                                            {{ translate('offline_payment') }}
                                            <span class="input-label-secondary cursor-pointer" data-toggle="tooltip"
                                                data-placement="top"
                                                title="{{ translate('offline_Payment_allows_customers_to_use_external_payment_methods.') }}
                                                {{ translate('They_must_share_payment_details_with_the_vendor_afterward.') }}
                                                {{ translate('Admin_can_set_whether_customers_can_make_offline_payments_by_enabling/disabling_this_button.') }}">
                                                <img width="16"
                                                    src="{{ dynamicAsset(path: 'public/assets/back-end/img/info-circle.svg') }}"
                                                    alt="">
                                            </span>
                                        </span>

                                        <label class="switcher" for="offline-payment">
                                            <input type="checkbox" class="switcher_input toggle-switch-message"
                                                name="offline_payment" id="offline-payment" value="1"
                                                {{ $offlinePayment['status'] == 1 ? 'checked' : '' }}
                                                data-modal-id="toggle-modal" data-toggle-id="offline-payment"
                                                data-on-image="digital-payment-on.png" data-off-image="digital-payment-off.png"
                                                data-on-title="{{ translate('want_to_Turn_ON_the_Offline_Payment_option') }}"
                                                data-off-title="{{ translate('want_to_Turn_OFF_the_Offline_Payment_option') }}"
                                                data-on-message="<p>{{ translate('if_enabled_customers_can_pay_through_external_payment_methods') }}</p>"
                                                data-off-message="<p>{{ translate('if_disabled_customers_have_to_use_the_system_added_payment_gateways') }}</p>">
                                            <span class="switcher_control"></span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        @endisset
                        <div class="col-12">
                            <div class="d-flex justify-content-end">
                                <button type="submit" class="btn btn--primary px-5 text-uppercase">
                                    {{ translate('save') }}
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade warning-modal" id="active-one-method-modal" tabindex="-1"
        aria-labelledby="active-one-method-modal" aria-modal="true" role="dialog">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content shadow-lg">
                <div class="modal-header border-0 pb-0 d-flex justify-content-end">
                    <button type="button" class="btn-close btn-close-danger" data-dismiss="modal" aria-label="Close"><i
                            class="tio-clear"></i></button>
                </div>
                <div class="modal-body px-4 px-sm-5 pt-0">
                    <div class="d-flex flex-column align-items-center text-center gap-20 mb-5">
                        <img src="{{ dynamicAsset(path: 'public/assets/back-end/img/modal/warning.png') }}"
                            width="80" alt="">
                        <h5 class="modal-title">
                            {{ translate('You_must_active_at_least_one_method') }}
                        </h5>
                        <div class="text-center">
                            <p>
                                {{ translate('you_can_not_turn_off_all_payment_methods_at_a_time.') }}
                                {{ translate('must_active_at_least_1_payment_methods_for_smooth_order_payment_system.') }}
                            </p>
                        </div>
                    </div>
                    <div class="d-flex justify-content-center gap-2 mb-3">
                        <button type="button" class="btn btn--primary min-w-120 font-weight-bold" data-dismiss="modal">
                            {{ translate('okay') }}, {{ translate('got_it') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade warning-modal" id="minimum-one-digital-payment" tabindex="-1" aria-labelledby="minimum-one-digital-payment"
        aria-modal="true" role="dialog">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content shadow-lg">
                <div class="modal-header border-0 pb-0 d-flex justify-content-end">
                    <button type="button" class="btn-close btn-close-danger" data-dismiss="modal" aria-label="Close"><i
                            class="tio-clear"></i></button>
                </div>
                <div class="modal-body px-4 px-sm-5 pt-0">
                    <div class="d-flex flex-column align-items-center text-center gap-20 mb-5">
                        <img src="{{ dynamicAsset(path: 'public/assets/back-end/img/modal/warning.png') }}"
                            width="80" alt="">
                        <h5 class="modal-title">
                            {{ translate('You must active one of digital payment methods.') }}
                        </h5>
                        <div class="text-center">
                            <p class="modal-message">
                            </p>
                        </div>
                    </div>

                    <div class="d-flex justify-content-center gap-2 mb-3">
                        <a class="btn btn--primary min-w-120 font-weight-bold minimum-one-digital-payment" href="{{ route('admin.business-settings.payment-method.index') }}" target="_blank">
                            {{ translate('go_to_3rd_party_payment_methods') }}
                        </a>
                        <a class="btn btn--primary min-w-120 font-weight-bold minimum-one-offline-payment-method" href="{{ route('admin.business-settings.offline-payment-method.index') }}" target="_blank">
                            {{ translate('Go_to_Offline_Payment_Methods') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
