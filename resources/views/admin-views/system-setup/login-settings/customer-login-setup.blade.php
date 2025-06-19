@extends('layouts.back-end.app')

@section('title', translate('Login_Settings'))

@section('content')
    <div class="content container-fluid">
        <div class="mb-4 pb-2">
            <h2 class="h1 mb-0 text-capitalize d-flex align-items-center gap-2">
                <img src="{{ dynamicAsset(path: 'public/assets/back-end/img/system-setting.png') }}" alt="">
                {{ translate('Customer_Login_Settings') }}
            </h2>
        </div>
        @include('admin-views.system-setup.login-settings.partials.login-settings-menu')
        <form action="{{ route('admin.system-setup.login-settings.customer-login-setup') }}" method="post"
              enctype="multipart/form-data" id="customer-login-setup-update">
            @csrf
            <div class="card">
                <div class="card-header">
                    <div>
                        <h4 class="mb-1">
                            {{ translate('Setup_Login_Option') }}
                        </h4>
                        <p class="fs-12 m-0">
                            {{ translate('the_option_you_select_customer_will_have_the_to_option_to_login') }}
                        </p>
                    </div>
                </div>
                <div class="card-body pt-3">
                    <form>
                        <div class="row g-3 mb-4">
                            <div class="col-sm-6 col-md-4">
                                <label class="form-check form--check form--check--inline border rounded">
                                    <span class="user-select-none form-check-label flex-grow-1">
                                        {{ translate('Manual_Login') }}
                                        <span data-toggle="tooltip" data-placement="top"
                                              title="{{ translate('by_enabling_manual_login_customers_will_get_the_option_to_create_account_and_log_in_using_necessary_credentials_and_password_in_the_app_and_website') }}">
                                            <i class="tio-info-outined"></i>
                                        </span>
                                    </span>
                                    <input class="form-check-input login-option-type" type="checkbox"
                                           name="manual_login"
                                           id="customer-manual-login"
                                           value="1"
                                        {{ $loginOptions['manual_login'] ? 'checked' : '' }}
                                    >
                                </label>
                            </div>
                            <div class="col-sm-6 col-md-4">
                                <label class="form-check form--check form--check--inline border rounded">
                                    <span class="user-select-none form-check-label flex-grow-1">
                                        {{ translate('OTP_Login') }}
                                        <span data-toggle="tooltip" data-placement="top"
                                              title="{{ translate('with_otp_login_customers_can_log_in_using_their_phone_number.') }} {{ translate('while_new_customers_can_create_accounts_instantly') }}">
                                            <i class="tio-info-outined"></i>
                                        </span>
                                    </span>
                                    <input type="checkbox"
                                           name="otp_login"
                                           id="customer-otp-login"
                                           value="1"
                                           class="form-check-input social-media-status-checkbox"
                                           data-route="{{ route('admin.system-setup.login-settings.config-status-validation') }}"
                                           data-key="otp-login"
                                        {{ $loginOptions['otp_login'] ? 'checked' : '' }}
                                    >
                                </label>
                            </div>
                            <div class="col-sm-6 col-md-4">
                                <label class="form-check form--check form--check--inline border rounded">
                                    <span class="user-select-none form-check-label flex-grow-1">
                                        {{ translate('Social_Media_Login') }}
                                        <span data-toggle="tooltip" data-placement="top"
                                              title="{{ translate('with_social_login_customers_can_log_in_using_social_media_credentials.') }} {{ translate('while_new_customers_can_create_accounts_instantly.') }}">
                                            <i class="tio-info-outined"></i>
                                        </span>
                                    </span>
                                    <input class="form-check-input login-option-type" type="checkbox"
                                           name="social_login"
                                           id="customer-social-login"
                                           value="1"
                                        {{ $loginOptions['social_login'] ? 'checked' : '' }}
                                    >
                                </label>
                            </div>
                        </div>

                        <div class="mb-4 social-media-login-container {{ $loginOptions['social_login'] ? '' : 'd--none' }}">
                            <div class="mb-3">
                                <h4 class="mb-1">
                                    {{ translate('social_media_login_setup') }}
                                </h4>
                                <a href="{{ route('admin.social-login.view') }}" class="fs-12 c1 text-underline fw-semibold" target="_blank">
                                    {{ translate('connect_3rd_party_login_system_from_here') }}
                                </a>
                            </div>
                            <div class="bg-light p-4 rounded">
                                <h4 class="mb-1">
                                    {{ translate('choose_social_media') }}
                                </h4>
                                <div class="row g-3">
                                    <div class="col-sm-6 col-md-4">
                                        <label class="form-check form--check form--check--inline border rounded">
                                            <span class="user-select-none form-check-label flex-grow-1">
                                                {{ translate('Google') }}
                                                <span data-toggle="tooltip" data-placement="top"
                                                      title="{{ translate('enabling_google_login_customers_can_log_in_to_the_site_using_their_existing_gmail_credentials.') }}">
                                                    <i class="tio-info-outined"></i>
                                                </span>
                                            </span>
                                            <input type="checkbox"
                                                   name="google_login"
                                                   data-status="{{ $configStatus['google'] ? 'true' : 'false' }}"
                                                   id="google_login"
                                                   value="1"
                                                   class="form-check-input social-media-status-checkbox"
                                                   data-route="{{ route('admin.system-setup.login-settings.config-status-validation') }}"
                                                   data-key="google"
                                                {{ $socialMediaLoginOptions['google'] ? 'checked' : '' }}
                                            >
                                        </label>
                                    </div>
                                    <div class="col-sm-6 col-md-4">
                                        <label class="form-check form--check form--check--inline border rounded">
                                            <span class="user-select-none form-check-label flex-grow-1">
                                                {{ translate('Facebook') }}
                                                <span data-toggle="tooltip" data-placement="top"
                                                      title="{{ translate('enabling_facebook_login_customers_can_log_in_to_the_site_using_their_existing_facebook_credentials.') }}">
                                                    <i class="tio-info-outined"></i>
                                                </span>
                                            </span>
                                            <input type="checkbox"
                                                   name="facebook_login"
                                                   data-status="{{ $configStatus['facebook'] ? 'true' : 'false' }}"
                                                   id="facebook_login"
                                                   value="1"
                                                   class="form-check-input social-media-status-checkbox"
                                                   data-route="{{ route('admin.system-setup.login-settings.config-status-validation') }}"
                                                   data-key="facebook"
                                                {{ $socialMediaLoginOptions['facebook'] ? 'checked' : '' }}
                                            >
                                        </label>
                                    </div>
                                    <div class="col-sm-6 col-md-4">
                                        <label class="form-check form--check form--check--inline border rounded">
                                            <span class="user-select-none form-check-label flex-grow-1">
                                                {{ translate('Apple') }}
                                                <span data-toggle="tooltip" data-placement="top"
                                                      title="{{ translate('enabling_apple_login_customers_can_log_in_to_the_site_using_their_existing_apple_login_credentials.') }}">
                                                    <i class="tio-info-outined"></i>
                                                </span>
                                            </span>
                                            <input type="checkbox"
                                                   name="apple_login"
                                                   data-status="{{ $configStatus['apple'] ? 'true' : 'false' }}"
                                                   id="apple_login"
                                                   value="1"
                                                   class="form-check-input social-media-status-checkbox"
                                                   data-route="{{ route('admin.system-setup.login-settings.config-status-validation') }}"
                                                   data-key="apple"
                                                {{ $socialMediaLoginOptions['apple'] ? 'checked' : '' }}
                                            >
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="mb-4">
                            <div class="mb-3">
                                <h4 class="mb-1">
                                    {{ translate('OTP_Verification') }}
                                </h4>
                                <p class="fs-12">
                                    {{ translate('the_option_you_select_will_need_to_be_verified_by_the_customer') }}
                                </p>
                            </div>
                            <div class="bg-light p-4 rounded">
                                <div class="row g-3">
                                    <div class="col-sm-6 col-md-4">
                                        <label class="form-check form--check form--check--inline border rounded">
                                            <span class="user-select-none form-check-label flex-grow-1">
                                                {{ translate('Email_Verification') }}
                                                <span data-toggle="tooltip" data-placement="top"
                                                      title="{{ translate('if_email_verification_is_on_customers_must_verify_their_email_address_with_an_otp_to_complete_the_signup_process') }}">
                                                    <i class="tio-info-outined"></i>
                                                </span>
                                            </span>
                                            <input type="checkbox"
                                                   name="email_verification"
                                                   value="1"
                                                   class="form-check-input social-media-status-checkbox {{ env('APP_MODE') != 'demo' ? '' : 'call-demo' }}"
                                                   data-route="{{ route('admin.system-setup.login-settings.config-status-validation') }}"
                                                   data-key="email"
                                                {{ env('APP_MODE') != 'demo' ? '' : 'disabled' }}
                                                {{ $emailVerification ? 'checked' : '' }}
                                            >
                                        </label>
                                    </div>
                                    <div class="col-sm-6 col-md-4">
                                        <label class="form-check form--check form--check--inline border rounded">
                                            <span class="user-select-none form-check-label flex-grow-1">
                                                {{ translate('Phone_Number_Verification') }}
                                                <span data-toggle="tooltip" data-placement="top"
                                                      title="{{ translate('if_phone_number_verification_is_on_customers_must_verify_their_phone_number_with_an_otp_to_complete_the_signup_process') }}">
                                                    <i class="tio-info-outined"></i>
                                                </span>
                                            </span>
                                            <input type="checkbox"
                                                    name="phone_verification"
                                                   id="phone_verification"
                                                   value="1"
                                                   class="form-check-input social-media-status-checkbox {{ env('APP_MODE') != 'demo' ? '' : 'call-demo' }}"
                                                   data-route="{{ route('admin.system-setup.login-settings.config-status-validation') }}"
                                                   data-key="otp"
                                                {{ env('APP_MODE') != 'demo' ? '' : 'disabled' }}
                                                {{ $phoneVerification ? 'checked' : '' }}
                                            >
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="btn--container">
                            <button type="reset" class="btn btn-secondary">{{ translate('Reset') }}</button>
                            <button type="submit" class="btn btn--primary">{{ translate('Submit') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </form>
    </div>

    <div class="modal fade" id="customerLoginConfigValidation" tabindex="-1" aria-labelledby="toggle-modal" aria-hidden="true">
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

    <span id="customer-login-setup-validation-msg"
          data-title="{{ translate("no_login_option_selected") }}!"
          data-text="{{ translate("please_select_at_least_one_login_option.") }}"
          data-ok="{{ translate("ok") }}"
    ></span>
    <span class="select-google-or-facebook"
          data-text="{{ translate("please_select_at_least_one_between_Google_or_Facebook.") }}"
          data-text-two="{{ translate("please_select_at_least_one_between_Google_or_Facebook_or_Apple.") }}"
    ></span>
@endsection

@push('script')
    <script src="{{dynamicAsset(path: 'public/assets/back-end/js/admin/customer-login-setup.js')}}"></script>
@endpush
