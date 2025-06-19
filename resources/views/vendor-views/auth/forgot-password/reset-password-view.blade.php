<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="_token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>{{ translate('vendor') }} | {{ translate('reset_Password') }}</title>
    <link rel="shortcut icon"
          href="{{getStorageImages(path: getWebConfig(name: 'company_fav_icon'), type:'backend-logo')}}">
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
                        <h1 class="display-4">{{ translate('Reset_your_password').'?' }}</h1>
                        <h1 class="h4 text-gray-900 mb-4">
                            {{ translate('Give_new_password_to_reset_vendor_password') }}
                        </h1>
                    </div>

                    <ol class="list-unstyled font-size-md text-start">
                        <li>
                            <span class="text-primary mr-2">1.</span>
                            {{ translate('enter_your_new_password') . '.' }}
                        </li>
                        <li>
                            <span class="text-primary mr-2">2.</span>
                            {{ translate('enter_your_confirm_password') . '.' }}
                        </li>
                    </ol>

                    <form class="needs-validation" novalidate method="POST" action="{{ route('vendor.auth.forgot-password.reset-password') }}">
                            @csrf

                        <div class="py-2 mt-4">
                            <div class="form-group d-none">
                                <input type="text" name="reset_token" value="{{$token}}" required>
                            </div>
                            <div class="form-group">
                                <label for="si-password" class="d-flex align-items-center">
                                    {{translate('new_password')}}
                                    <small class="text-danger mx-1 password-error"></small>
                                </label>

                                <div class="input-group input-group-merge">
                                    <input type="password" class="js-toggle-password form-control password-check"
                                           name="password" required id="user_password"
                                           placeholder="{{ translate('password_minimum_8_characters') }}"
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
                            <div class="form-group">
                                <label for="si-password">{{translate('confirm_password')}}</label>

                                <div class="input-group input-group-merge">
                                    <input type="password" class="js-toggle-password form-control"
                                           name="confirm_password" required id="confirm_password"
                                           placeholder="{{ translate('confirm_password') }}"
                                           data-hs-toggle-password-options='{
                                                     "target": "#changeConfirmPassTarget",
                                                    "defaultClass": "tio-hidden-outlined",
                                                    "showClass": "tio-visible-outlined",
                                                    "classChangeTarget": "#changeConfirmPassIcon"
                                            }'>
                                    <div id="changeConfirmPassTarget" class="input-group-append">
                                        <a class="input-group-text" href="javascript:">
                                            <i id="changeConfirmPassIcon" class="tio-visible-outlined"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-lg btn-block btn--primary forget-password-form">
                            {{ translate('reset_password')}}
                        </button>
                    </form>
                </div>

            </div>
        </div>
    </div>
</main>

<span id="password-error-message" data-max-character="{{translate('at_least_8_characters').'.'}}" data-uppercase-character="{{translate('at_least_one_uppercase_letter_').'(A...Z)'.'.'}}" data-lowercase-character="{{translate('at_least_one_uppercase_letter_').'(a...z)'.'.'}}"
      data-number="{{translate('at_least_one_number').'(0...9)'.'.'}}" data-symbol="{{translate('at_least_one_symbol').'(!...%)'.'.'}}"></span>


<span id="message-please-check-recaptcha" data-text="{{ translate('please_check_the_recaptcha') }}"></span>
<span id="message-copied_success" data-text="{{ translate('copied_successfully') }}"></span>
<span id="route-get-session-recaptcha-code"
      data-route="{{ route('get-session-recaptcha-code') }}"
      data-mode="{{ env('APP_MODE') }}"
></span>

<span id="message-copied_success" data-text="{{ translate('copied_successfully') }}"></span>
<script src="{{dynamicAsset(path: 'public/assets/back-end/js/vendor.min.js')}}"></script>
<script src="{{dynamicAsset(path: 'public/assets/back-end/js/theme.min.js')}}"></script>
<script src="{{dynamicAsset(path: 'public/assets/back-end/js/toastr.js')}}"></script>
<script src="{{dynamicAsset(path: 'public/assets/back-end/js/vendor/forgot-password.js')}}"></script>

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




