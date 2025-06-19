<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="_token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>{{ translate('vendor_Login')}}</title>
    <link rel="shortcut icon" href="{{getStorageImages(path: getWebConfig(name: 'company_fav_icon'), type:'backend-logo')}}">
    <link rel="stylesheet" href="{{ dynamicAsset(path: 'public/assets/back-end/css/google-fonts.css') }}">
    <link rel="stylesheet" href="{{ dynamicAsset(path: 'public/assets/back-end/css/vendor.min.css') }}">
    <link rel="stylesheet" href="{{ dynamicAsset(path: 'public/assets/back-end/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ dynamicAsset(path: 'public/assets/back-end/vendor/icon-set/style.css') }}">
    <link rel="stylesheet" href="{{ dynamicAsset(path: 'public/assets/back-end/css/theme.minc619.css?v=1.0') }}">
    <link rel="stylesheet" href="{{ dynamicAsset(path: 'public/assets/back-end/css/style.css') }}">
    <link rel="stylesheet" href="{{ dynamicAsset(path: 'public/assets/back-end/css/toastr.css') }}">

    <style>
        :root {
            --c1: {{ $web_config['primary_color'] }};
        }
    </style>

</head>

<body>
<main id="content" role="main" class="main">
    <div class="auth-wrapper">
        <div class="auth-wrapper-left" style="background: url('{{ dynamicAsset(path: 'public/assets/back-end/img/login-bg.png') }}') no-repeat center center / cover">
            <div class="auth-left-cont">
                @php($eCommerceLogo = getWebConfig(name: 'company_web_logo'))
                <a class="d-inline-flex mb-5" href="{{ route('home') }}">
                    <img width="310" src="{{ getStorageImages(path: $eCommerceLogo, type:'backend-logo') }}" alt="Logo">
                </a>
                <h2 class="title">{{translate('Make Your Business')}} <span class="font-weight-bold c1 d-block text-capitalize">{{translate('Profitable...')}}</span></h2>
            </div>
        </div>
        <div class="auth-wrapper-right">
            <div class="auth-wrapper-form">
                <div class="d-block d-lg-none">
                    <a class="d-inline-flex mb-3" href="{{ route('home') }}">
                        <img width="100" src="{{ getStorageImages(path: $eCommerceLogo, type:'backend-logo') }}"
                             alt="Logo">
                    </a>
                </div>

                <form id="form-id" action="{{route('vendor.auth.login')}}" method="post" id="admin-login-form">
                    @csrf
                    <div>
                        <div class="mb-5">
                            <h1 class="display-4">{{translate('sign_in')}}</h1>
                            <h1 class="h4 text-gray-900 mb-4">
                                {{translate('welcome_back_to')}} {{translate('Vendor_Login')}}
                            </h1>
                        </div>
                    </div>

                    <div class="js-form-message form-group">
                        <label class="input-label" for="signingVendorEmail">{{translate('your_email')}}</label>

                        <input type="email" class="form-control form-control-lg" name="email" id="signingVendorEmail"
                               tabindex="1" placeholder="email@address.com" aria-label="email@address.com"
                               required data-msg="Please enter a valid email address.">
                    </div>
                    <div class="js-form-message form-group">
                        <label class="input-label" for="signingVendorPassword" tabindex="0">
                            <span class="d-flex justify-content-between align-items-center">
                              {{translate('password')}}
                                    <a href="{{route('vendor.auth.forgot-password.index')}}">
                                        {{translate('forgot_password')}}
                                    </a>
                            </span>
                        </label>

                        <div class="input-group input-group-merge">
                            <input type="password" class="js-toggle-password form-control form-control-lg"
                                   name="password" id="signingVendorPassword"
                                   placeholder="{{ translate('8+_characters_required') }}"
                                   aria-label="8+ characters required" required
                                   data-msg="Your password is invalid. Please try again."
                                   data-hs-toggle-password-options='{
                                                "target": "#changePassTarget",
                                    "defaultClass": "tio-hidden-outlined",
                                    "showClass": "tio-visible-outlined",
                                    "classChangeTarget": "#changePassIcon"
                                    }'>
                            <div id="changePassTarget" class="input-group-append">
                                <a class="input-group-text" href="javascript:">
                                    <i id="changePassIcon" class="tio-visible-outlined"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="form-group mb-1">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="termsCheckbox"
                                   name="remember">
                            <label class="custom-control-label text-muted" for="termsCheckbox">
                                {{translate('remember_me')}}
                            </label>
                        </div>
                    </div>
                    @if(isset($recaptcha) && $recaptcha['status'] == 1)
                        <div id="recaptcha_element" class="w-100;" data-type="image"></div>
                        <br/>
                    @else
                        <div class="row p-2">
                            <div class="col-6 pr-0">
                                <input type="text" class="form-control form-control-lg form-control-focus-none h-50px"
                                       id="vendor-login-recaptcha-input"
                                       name="vendorRecaptchaKey" value="" required
                                       placeholder="{{translate('enter_captcha_value')}}">
                            </div>
                            <div class="col-6 input-icons bg-white rounded">
                                <a class="get-login-recaptcha-verify cursor-pointer get-session-recaptcha-auto-fill"
                                   data-link="{{ URL('vendor/auth/recaptcha/') }}"
                                   data-session="{{ 'vendorRecaptchaSessionKey' }}"
                                   data-input="#vendor-login-recaptcha-input"
                                >
                                    <img src="{{ URL('/vendor/auth/recaptcha/1?captcha_session_id=vendorRecaptchaSessionKey') }}"
                                         class="input-field w-90 h-50px img-fit p-0 rounded" id="default_recaptcha_id" alt="">
                                    <i class="tio-refresh icon"></i>
                                </a>
                            </div>
                        </div>
                    @endif

                    <button type="submit" class="btn btn-lg btn-block btn--primary">
                        {{ translate('sign_in')}}
                    </button>
                </form>
                @if(env('APP_MODE')=='demo')
                    <div class="card-footer">
                        <div class="row">
                            <div class="col-10">
                                <span id="vendor-email" data-email="{{ \App\Enums\DemoConstant::VENDOR['email'] }}">{{translate('email')}} : {{ \App\Enums\DemoConstant::VENDOR['email'] }}</span><br>
                                <span id="vendor-password" data-password="{{ \App\Enums\DemoConstant::VENDOR['password'] }}">{{translate('password')}} : {{ \App\Enums\DemoConstant::VENDOR['password'] }}</span>
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

<span id="message-please-check-recaptcha" data-text="{{ translate('please_check_the_recaptcha') }}"></span>
<span id="message-copied_success" data-text="{{ translate('copied_successfully') }}"></span>
<span id="route-get-session-recaptcha-code"
      data-route="{{ route('get-session-recaptcha-code') }}"
      data-mode="{{ env('APP_MODE') }}"
></span>

<script src="{{dynamicAsset(path: 'public/assets/back-end/js/vendor.min.js')}}"></script>
<script src="{{dynamicAsset(path: 'public/assets/back-end/js/theme.min.js')}}"></script>
<script src="{{dynamicAsset(path: 'public/assets/back-end/js/toastr.js')}}"></script>
<script src="{{dynamicAsset(path: 'public/assets/back-end/js/vendor/login.js')}}"></script>
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

</body>
</html>

