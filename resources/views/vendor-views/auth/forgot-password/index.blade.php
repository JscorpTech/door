<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="_token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>{{ translate('forgot_password')}}</title>
    <link rel="shortcut icon"
          href="{{getStorageImages(path: getWebConfig(name: 'company_fav_icon'), type:'backend-logo')}}">
    <link rel="stylesheet" href="{{ dynamicAsset(path: 'public/assets/back-end/css/google-fonts.css') }}">
    <link rel="stylesheet" href="{{ dynamicAsset(path: 'public/assets/back-end/css/vendor.min.css') }}">
    <link rel="stylesheet" href="{{ dynamicAsset(path: 'public/assets/back-end/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ dynamicAsset(path: 'public/assets/back-end/vendor/icon-set/style.css') }}">
    <link rel="stylesheet" href="{{ dynamicAsset(path: 'public/assets/back-end/css/theme.minc619.css?v=1.0') }}">
    <link rel="stylesheet" href="{{ dynamicAsset(path: 'public/assets/back-end/css/style.css') }}">
    <link rel="stylesheet" href="{{ dynamicAsset(path: 'public/assets/back-end/css/toastr.css') }}">
    <link rel="stylesheet" href="{{ dynamicAsset(path: 'public/assets/back-end/plugins/intl-tel-input/css/intlTelInput.css') }}">

    <style>
        :root {
            --c1: {{ $web_config['primary_color'] }};
        }
    </style>

</head>

<body>
<main id="content" role="main" class="main">
    <div class="auth-wrapper">
        <div class="auth-wrapper-left"
             style="background: url('{{ dynamicAsset(path: 'public/assets/back-end/img/login-bg.png') }}') no-repeat center center / cover">
            <div class="auth-left-cont">
                @php($eCommerceLogo = getWebConfig(name: 'company_web_logo'))
                <a class="d-inline-flex mb-5" href="{{ route('home') }}">
                    <img width="310" src="{{ getStorageImages(path: $eCommerceLogo, type:'backend-logo') }}" alt="Logo">
                </a>
                <h2 class="title">{{translate('Make Your Business')}} <span
                        class="font-weight-bold c1 d-block text-capitalize">{{translate('Profitable...')}}</span></h2>
            </div>
        </div>
        <div class="auth-wrapper-right">
            <div class="auth-wrapper-form">
                <div>
                    <div class="d-block d-lg-none">
                        <a class="d-inline-flex mb-3" href="{{ route('home') }}">
                            <img width="100" src="{{ getStorageImages(path: $eCommerceLogo, type:'backend-logo') }}"
                                 alt="Logo">
                        </a>
                    </div>

                    <div class="mb-5">
                        <h1 class="display-4">{{ translate('forgot_password').'?' }}</h1>
                        <h1 class="h4 text-gray-900 mb-4">
                            {{ translate('Follow_steps_to_reset_vendor_password') }}
                        </h1>
                    </div>

                    @php($verificationBy = getWebConfig('vendor_forgot_password_method'))
                    @if ($verificationBy == 'email')
                        <ol class="list-unstyled font-size-md text-start">
                            <li>
                                <span class="text-primary mr-2">1.</span>
                                {{ translate('enter_your_email_address_below') . '.' }}
                            </li>
                            <li>
                                <span class="text-primary mr-2">2.</span>
                                {{ translate('we_will_send_you_a_temporary_link_via_email') . '.' }}
                            </li>
                            <li>
                                <span class="text-primary mr-2">3.</span>
                                {{ translate('by_clicking_the_link_to_change_your_password_on_our_secure_website') . '.' }}
                            </li>
                        </ol>

                        <form id="form-id" action="{{route('vendor.auth.forgot-password.index')}}" method="post"
                              id="admin-login-form">
                            @csrf
                            <div class="js-form-message form-group mt-5">
                                <label class="input-label" for="signingVendorPassword" tabindex="0">
                                    <span class="d-flex justify-content-between align-items-center">
                                            {{translate('Email_Address')}}
                                            <a href="{{route('vendor.auth.login')}}">
                                                {{translate('back_to_login')}}
                                            </a>
                                    </span>
                                </label>

                                <input type="email" class="form-control form-control-lg" name="identity"
                                       value="{{ old('identity') }}"
                                       tabindex="1" placeholder="{{ translate('enter_email_address') }}"
                                       aria-label="{{ translate('enter_email_address') }}"
                                       required>
                            </div>
                            <button type="submit" class="btn btn-lg btn-block btn--primary">
                                {{ translate('Send')}}
                            </button>
                        </form>
                    @else
                        <ol class="list-unstyled font-size-md text-start">
                            <li>
                                <span class="text-primary mr-2">1.</span>
                                {{ translate('fill_in_your_phone_number_below') . '.' }}
                            </li>
                            <li>
                                <span class="text-primary mr-2">2.</span>
                                {{ translate('we_will_send_you_a_temporary_OTP_via_phone') . '.' }}
                            </li>
                            <li>
                                <span class="text-primary mr-2">3.</span>
                                {{ translate('use_the_OTP_to_change_your_password_on_our_secure_website') . '.' }}
                            </li>
                        </ol>

                        <form id="form-id" action="{{route('vendor.auth.forgot-password.index')}}" method="post"
                              id="admin-login-form">
                            @csrf
                            <div class="js-form-message form-group mt-5">
                                <label class="input-label" for="forgotVendorPassword" tabindex="0">
                                    <span class="d-flex justify-content-between align-items-center">
                                            {{translate('phone')}}
                                            <a href="{{route('vendor.auth.login')}}">
                                                {{translate('back_to_login')}}
                                            </a>
                                    </span>
                                </label>

                                <div class="form-group mb-3">
                                    <input
                                        type="tel"
                                        id="forgotVendorPassword"
                                        value="{{old('identity')}}"
                                        class="form-control phone-input-with-country-picker-forgot-password"
                                        placeholder="{{ translate('enter_phone_number') }}"
                                    />
                                    <input type="hidden" class="country-picker-phone-number-forgot-password w-100" name="identity" readonly>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-lg btn-block btn--primary">
                                {{ translate('Get_OTP')}}
                            </button>
                        </form>
                    @endif
                </div>

                @if(env('APP_MODE')=='demo')
                    <div class="card-footer">
                        <div class="row">
                            <div class="col-10">
                                <span id="admin-email" data-email="{{ \App\Enums\DemoConstant::VENDOR['email'] }}">{{translate('email')}} : {{ \App\Enums\DemoConstant::VENDOR['email'] }}</span><br>
                                <span id="admin-password"
                                      data-password="{{ \App\Enums\DemoConstant::VENDOR['password'] }}">{{translate('password')}} : {{ \App\Enums\DemoConstant::VENDOR['password'] }}</span>
                            </div>
                            <div class="col-2">
                                <button class="btn btn--primary" id="copyLoginInfo"><i class="tio-copy"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</main>

<div class="modal fade password-reset-successfully-modal" tabindex="-1" aria-labelledby="toggle-modal"
     aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow-lg">
            <div class="modal-header border-0 pb-0 d-flex justify-content-end">
                <button type="button" class="btn-close border-0" data-dismiss="modal" aria-label="Close"><i
                        class="tio-clear"></i></button>
            </div>
            <div class="modal-body px-4 px-sm-5 pt-0">
                <div class="d-flex flex-column align-items-center text-center gap-2 mb-2">
                    <img src="{{dynamicAsset(path: 'public/assets/back-end/img/password-reset.png') }}" width="70"
                         class="mb-3 mb-20" alt="">
                    <h5 class="modal-title">{{ translate('password_reset_successfully') }}</h5>
                    <div
                        class="text-center">{{ translate('a_password_reset_mail_has_sent_to_your_email').'. '.translate('please_check_your_email').'.'}}</div>
                </div>
            </div>
        </div>
    </div>
</div>

<span class="system-default-country-code" data-value="{{ getWebConfig(name: 'country_code') ?? 'us' }}"></span>
<span id="message-please-check-recaptcha" data-text="{{ translate('please_check_the_recaptcha') }}"></span>
<span id="message-copied_success" data-text="{{ translate('copied_successfully') }}"></span>
<span id="route-get-session-recaptcha-code"
      data-route="{{ route('get-session-recaptcha-code') }}"
      data-mode="{{ env('APP_MODE') }}"
></span>

<script src="{{dynamicAsset(path: 'public/assets/back-end/js/vendor.min.js')}}"></script>
<script src="{{dynamicAsset(path: 'public/assets/back-end/js/theme.min.js')}}"></script>
<script src="{{dynamicAsset(path: 'public/assets/back-end/js/toastr.js')}}"></script>
<script src="{{dynamicAsset(path: 'public/assets/back-end/js/admin/login.js')}}"></script>
{!! Toastr::message() !!}

@if ($errors->any())
    <script>
        "use strict";
        @foreach($errors->all() as $error)
        toastr.error('{{$error}}', Error, {
            CloseButton: true,
            ProgressBar: true
        });
        @endforeach
    </script>
@endif

@if(isset($recaptcha) && $recaptcha['status'] == 1)
    <script type="text/javascript">
        "use strict";
        var onloadCallback = function () {
            grecaptcha.render('recaptcha_element', {
                'sitekey': '{{ getWebConfig(name: 'recaptcha')['site_key'] }}'
            });
        };
    </script>
    <script src="https://www.google.com/recaptcha/api.js?onload=onloadCallback&render=explicit" async defer></script>
@endif

<script src="{{ dynamicAsset(path: 'public/assets/back-end/plugins/intl-tel-input/js/intlTelInput.js') }}"></script>
<script src="{{ dynamicAsset(path: 'public/assets/back-end/js/country-picker-init.js') }}"></script>

<script>
    'use strict';
    try {
        initializePhoneInput(".phone-input-with-country-picker-forgot-password", ".country-picker-phone-number-forgot-password");
    } catch (e) {
    }
</script>

</body>
</html>



