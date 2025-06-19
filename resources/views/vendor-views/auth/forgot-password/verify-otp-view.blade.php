<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="_token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>{{ translate('Verify_OTP')}}</title>
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
            <div class="auth-wrapper-form text-center">
                <div class="__inline-20">
                    <div class="d-block d-lg-none">
                        <a class="d-inline-flex mb-3" href="{{ route('home') }}">
                            <img width="100" src="{{ getStorageImages(path: $eCommerceLogo, type:'backend-logo') }}"
                                 alt="Logo">
                        </a>
                    </div>

                    <div class="mb-5">
                        <h1 class="display-4">{{ translate('Verify_OTP').'?' }}</h1>
                        <h1 class="h4 text-gray-900 mb-4">
                            {{ translate('provide_your_otp_and_proceed') }}
                        </h1>
                    </div>

                    <form id="form-id" action="{{route('vendor.auth.forgot-password.otp-verification')}}" method="post"
                          id="admin-login-form">
                        @csrf
                        <div class="js-form-message form-group mt-5">
                            <div class="form-group text-center">
                                <label>{{translate('enter_your_OTP')}}</label>
                                <div id="divOuter" class="m-auto">
                                    <div id="divInner" class="my-3">
                                        <input id="partitioned" class="form-control font-weight-bold fs-24" name="otp" type="text" maxlength="4"/>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="d-flex justify-content-center align-items-baseline flex-wrap gap-2">
                            <a class="btn btn-lg btn-block btn-secondary w-auto flex-grow-1" href="{{ route('vendor.auth.forgot-password.index') }}">
                                {{ translate('back')}}
                            </a>
                            <button type="submit" class="btn btn-lg btn-block btn--primary w-auto flex-grow-1" disabled>
                                {{ translate('proceed')}}
                            </button>
                        </div>
                    </form>
                </div>
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

<script>
    'use strict';
    var obj = document.getElementById('partitioned');
    obj.addEventListener('keydown', stopCarret);
    obj.addEventListener('keyup', stopCarret);

    function stopCarret() {
        if (obj.value.length > 3) {
            setCaretPosition(obj, 3);
            $('[type="submit"]').attr('disabled', false);
        } else {
            $('[type="submit"]').attr('disabled', true);
        }
    }

    function setCaretPosition(elem, caretPos) {
        if (elem != null) {
            if (elem.createTextRange) {
                var range = elem.createTextRange();
                range.move('character', caretPos);
                range.select();
            } else {
                if (elem.selectionStart) {
                    elem.focus();
                    elem.setSelectionRange(caretPos, caretPos);
                } else
                    elem.focus();
            }
        }
    }
</script>

</body>
</html>



