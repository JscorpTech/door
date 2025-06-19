@extends('layouts.back-end.app')

@section('title', translate('Firebase_Auth'))

@section('content')
    <div class="content container-fluid">
        <div class="mb-4 pb-2">
            <h2 class="h1 mb-0 text-capitalize d-flex align-items-center gap-2">
                <img src="{{ dynamicAsset(path: 'public/assets/back-end/img/3rd-party.png') }}" alt="">
                {{ translate('3rd_party') }}
            </h2>
        </div>
        @include('admin-views.business-settings.third-party-inline-menu')

        <div class="card border-0">
            <div class="card-header justify-content-between flex-wrap gap-3">
                <div>
                    <h4 class="mb-1">
                        {{ translate('Firebase_Auth') }}
                    </h4>
                    <p class="m-0">
                        {{ translate('please_ensure_that_your_firebase_configuration_is_set_up_before_using_these_features.') }}
                        <a target="_blank" class="text-underline" href="{{ route('admin.push-notification.firebase-configuration') }}">
                            {{ translate('Check_Firebase_Configuration') }}
                        </a>
                        <span class="input-label-secondary cursor-pointer" data-toggle="tooltip" data-placement="top" title="" data-original-title="{{ translate('please_ensure_that_your_firebase_configuration_contains_the_service_account_content,_api_keys,_domains,_project_id,_storage_bucket,_messaging_sender_id,_and_app_id,_and_that_all_are_properly_configured.') }}">
                            <img width="16" src="{{dynamicAsset('public/assets/back-end/img/info-circle.svg')}}" alt="">
                        </span>
                    </p>
                </div>

                <button class="btn-link text-capitalize d-flex align-items-center gap-2" type="button" data-toggle="modal" data-target="#firebase-auth-modal">
                    {{translate('credential_setup')}}
                    <img width="16" class="svg" src="{{dynamicAsset(path: 'public/assets/back-end/img/info-circle.svg')}}" loading="lazy" alt="">
                </button>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.firebase-otp-verification.update') }}" method="post">
                    @csrf
                    <div class="row align-items-end g-2">
                        <div class="col-md-6">
                            <div class="form-group m-0">
                                <div class="d-flex justify-content-between align-items-center gap-10 form-control">
                                <span class="title-color text-capitalize">
                                    {{ translate('Firebase_Auth_Verification_Status') }}
                                    <span class="input-label-secondary cursor-pointer" data-toggle="tooltip" data-placement="top" title="" data-original-title="{{ translate('if_this_field_is_active_customers_get_the_otp_through_firebase.') }}">
                                        <img width="16" src="{{dynamicAsset('public/assets/back-end/img/info-circle.svg')}}" alt="">
                                    </span>
                                </span>
                                    <label class="switcher" for="otp-verification-status">
                                        <input type="checkbox" class="switcher_input toggle-switch-message {{ env('APP_MODE') != 'demo' ? '' : 'call-demo' }} firebase-auth-verification"
                                               name="status" id="otp-verification-status"
                                               value="1"
                                               {{ env('APP_MODE') != 'demo' ? '' : 'disabled' }}
                                               data-status="{{ $configStatus ? 'true' : 'false' }}"
                                               {{ $firebaseOTPVerification && $firebaseOTPVerification['status'] ? 'checked' : '' }}
                                               data-route="{{ route('admin.firebase-otp-verification.config-status-validation') }}"
                                               data-verification="firebase-auth"
                                               data-key = "firebase"
                                               data-modal-id = "toggle-modal"
                                               data-toggle-id = "otp-verification-status"
                                               data-on-image = "otp-verification-on.png"
                                               data-off-image = "otp-verification-off.png"
                                               data-on-title = "{{translate('want_To_Turn_ON_Firebase_Auth_Verification').'?'}}"
                                               data-off-title = "{{translate('want_To_Turn_OFF_Firebase_Auth_Verification').'?'}}"
                                               data-on-message = "<p>{{translate('Firebase_Auth_Verification')}}</p>"
                                               data-off-message = "<p>{{translate('Firebase_Auth_Verification')}}</p>"
                                        >
                                        <span class="switcher_control"></span>
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group m-0">
                                <label class="title-color font-weight-bold d-flex">{{ translate('Web_Api_Key') }}</label>
                                <input type="text" class="form-control" name="web_api_key"
                                       placeholder="{{ translate('Enter_api_key') }}"
                                       {{ env('APP_MODE') != 'demo' ? '' : 'disabled' }}
                                       value="{{ $firebaseOTPVerification && $firebaseOTPVerification['web_api_key'] ? $firebaseOTPVerification['web_api_key'] : '' }}">
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="d-flex flex-wrap justify-content-end gap-3">
                                <button type="reset" class="btn btn-secondary px-5">{{translate('reset')}}</button>
                                <button type="{{env('APP_MODE') != 'demo' ? 'submit' : 'button'}}" class="btn btn--primary px-5 {{env('APP_MODE')!= 'demo'? '' : 'call-demo'}}">{{translate('save')}}</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="modal fade" id="firebaseAuthConfigValidation" tabindex="-1" aria-labelledby="toggle-modal" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content shadow-lg">
                    <div class="modal-header border-0 pb-0 d-flex justify-content-end">
                        <button type="button" class="btn-close border-0" data-dismiss="modal" aria-label="Close">
                            <i class="tio-clear"></i>
                        </button>
                    </div>
                    <div class="modal-body px-4 px-sm-5 pt-0">

                    </div>
                </div>
            </div>
        </div>
        <div class="modal fade" id="firebase-auth-modal" data-backdrop="static" data-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body pt-0">
                        <h5 class="modal-title my-3 text-center" id="instructionsModalLabel">{{translate('Instructions')}}</h5>
                        <p>{{ translate('for_configuring_otp_in_the_firebase,_you_must_create_a_firebase_project_first.') }}
                        {{ translate('if_you_have_not_created_any_project_for_your_application_yet,_please_create_a_project_first.') }}
                        </p>
                        <p>{{ translate('Now go the') }} <a href="https://console.firebase.google.com/" target="_blank">Firebase console </a>{{ translate('and follow the instructions below') }} -</p>
                        <ol class="d-flex flex-column __gap-5px __instructions">
                            <li>{{ translate('go_to_your_firebase_project.') }}</li>
                            <li>{{ translate('navigate_to_the_build_menu_from_the_left_sidebar_and_select_authentication.') }}</li>
                            <li>{{ translate('get_started_the_project_and_go_to_the_sign-in_method_tab.') }}</li>
                            <li>{{ translate('from_the_sign_in_providers_section,_select_the_phone_option.') }}</li>
                            <li>{{ translate('ensure_to_enable_the_method_phone_and_press_save.') }}</li>
                        </ol>
                        <div class="d-flex justify-content-center mt-4">
                            <button type="button" class="btn btn--primary text-capitalize px-5 px-sm-10" data-dismiss="modal">{{translate('got_it')}}</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
