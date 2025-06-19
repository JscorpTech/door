<?php
    $customerManualLogin = $web_config['customer_login_options']['manual_login'] ?? 0;
    $customerOTPLogin = $web_config['customer_login_options']['otp_login'] ?? 0;
    $customerSocialLogin = $web_config['customer_login_options']['social_login'] ?? 0;

    if (!$customerOTPLogin && $customerManualLogin && $customerSocialLogin) {
        $multiColumn = 1;
    } elseif ($customerOTPLogin && !$customerManualLogin && $customerSocialLogin) {
        $multiColumn = 1;
    } elseif ($customerOTPLogin && $customerManualLogin && !$customerSocialLogin) {
        $multiColumn = 1;
    } elseif ($customerOTPLogin && $customerManualLogin && $customerSocialLogin) {
        $multiColumn = 1;
    } else {
        $multiColumn = 0;
    }
?>
<div class="modal fade" id="loginModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered {{ $multiColumn ? 'modal-lg' : '' }}">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="modal"
                    aria-label="Close"
                ></button>
            </div>
            <div class="modal-body px-4 px-sm-5">
                <div class="mb-4 text-center">
                    <img alt="" class="dark-support"
                        width="200" src="{{ getStorageImages(path: $web_config['web_logo'], type:'logo') }}">
                </div>

                <div class="mb-4">
                    <h2 class="mb-2">{{ translate('login') }}</h2>
                    <p class="text-muted">
                        {{ translate('login_to_your_account.') }}
                        @if($customerManualLogin)
                            {{ translate('do_not_have_account').'?' }}
                            <span class="text-primary fw-bold text-capitalize" data-bs-toggle="modal"
                                  data-bs-target="#registerModal">
                                {{translate('sign_up')}}
                            </span>
                        @endif
                    </p>
                </div>

                <div class="{{ $multiColumn ? 'row align-items-center or-sign-in-with-row' : ''}}">
                    <div class="{{ $multiColumn ? 'col-md-6' : ''}}">
                        @if($customerOTPLogin && !$customerManualLogin && !$customerSocialLogin)
                            <form action="{{route('customer.auth.login')}}" method="post"
                                  class="customer-centralize-login-form"
                                  autocomplete="off">
                                @csrf
                                <input type="hidden" name="login_type" value="otp-login">
                                @include('theme-views.layouts.auth-partials._phone')
                                @include("theme-views.layouts.auth-partials._firebase-recaptcha-container")
                                <div class="d-flex justify-content-center mb-3">
                                    <button type="submit" class="fs-16 btn btn-primary px-5 w-100">
                                        {{ translate('Get_OTP') }}
                                    </button>
                                </div>
                            </form>
                        @elseif(!$customerOTPLogin && $customerManualLogin && !$customerSocialLogin)
                            <form action="{{route('customer.auth.login')}}" method="post"
                                  class="customer-centralize-login-form"
                                  autocomplete="off">
                                @csrf
                                <input type="hidden" name="login_type" value="manual-login">
                                @include("theme-views.layouts.auth-partials._email")
                                @include("theme-views.layouts.auth-partials._password")
                                @include("theme-views.layouts.auth-partials._remember-me", ['forgotPassword' => true])
                                @include("theme-views.layouts.auth-partials._recaptcha")
                                <div class="d-flex justify-content-center mb-3">
                                    <button type="submit" class="fs-16 btn btn-primary px-5 w-100">
                                        {{ translate('login') }}
                                    </button>
                                </div>
                                @if(!$multiColumn)
                                    @include("theme-views.layouts.auth-partials._sign-up-instruction")
                                @endif
                            </form>
                        @elseif(!$customerOTPLogin && $customerManualLogin && $customerSocialLogin)
                            <form action="{{route('customer.auth.login')}}" method="post"
                                  class="customer-centralize-login-form"
                                  autocomplete="off">
                                @csrf
                                <input type="hidden" name="login_type" value="manual-login">
                                @include("theme-views.layouts.auth-partials._email")
                                @include("theme-views.layouts.auth-partials._password")
                                @include("theme-views.layouts.auth-partials._remember-me", ['forgotPassword' => true])
                                @include("theme-views.layouts.auth-partials._recaptcha")
                                <div class="d-flex justify-content-center mb-3">
                                    <button type="submit" class="fs-16 btn btn-primary px-5 w-100">
                                        {{ translate('login') }}
                                    </button>
                                </div>
                                @if(!$multiColumn)
                                    @include("theme-views.layouts.auth-partials._sign-up-instruction")
                                @endif

                            </form>
                        @elseif($customerOTPLogin && !$customerManualLogin && $customerSocialLogin)
                            <form action="{{route('customer.auth.login')}}" method="post"
                                  class="customer-centralize-login-form"
                                  autocomplete="off">
                                @csrf
                                <input type="hidden" name="login_type" value="otp-login">
                                @include("theme-views.layouts.auth-partials._phone")
                                @include("theme-views.layouts.auth-partials._firebase-recaptcha-container")
                                <div class="d-flex justify-content-center mb-3">
                                    <button type="submit" class="fs-16 btn btn-primary px-5 w-100">
                                        {{ translate('Get_OTP') }}
                                    </button>
                                </div>
                            </form>
                        @elseif($customerOTPLogin && $customerManualLogin)
                            <div class="manual-login-container">
                                <form action="{{route('customer.auth.login')}}" method="post"
                                      class="customer-centralize-login-form"
                                      autocomplete="off">
                                    @csrf
                                    <input type="hidden" name="login_type" class="auth-login-type-input" value="manual-login">

                                    <div class="manual-login-items">
                                        @include("theme-views.layouts.auth-partials._email")
                                        @include("theme-views.layouts.auth-partials._password")
                                        @include("theme-views.layouts.auth-partials._remember-me", ['forgotPassword' => true])
                                    </div>

                                    <div class="otp-login-items d-none">
                                        @include("theme-views.layouts.auth-partials._phone")
                                    </div>

                                    @include("theme-views.layouts.auth-partials._recaptcha")

                                    <div class="manual-login-items">
                                        <div class="d-flex justify-content-center mb-3">
                                            <button type="submit" class="fs-16 btn btn-primary px-5 w-100">
                                                {{ translate('login') }}
                                            </button>
                                        </div>
                                    </div>

                                    <div class="otp-login-items d-none">
                                        <div class="d-flex justify-content-center mb-3 w-100">
                                            <button type="submit" class="fs-16 btn btn-primary px-5 w-100">
                                                {{ translate('Get_OTP') }}
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        @endif
                    </div>

                    @if($multiColumn)
                        <div class="or-sign-in-with"><span>{{translate('Or_Sign_in_with')}}</span></div>
                    @endif

                    @if($multiColumn || $customerSocialLogin)
                        <div class="{{ $multiColumn ? 'col-md-6' : '' }}">
                            @if($multiColumn)
                                <p class="text-center text-muted d-none d-md-block">{{ translate('or_continue_with') }}</p>
                            @endif
                            <div class="d-flex justify-content-center flex-column align-items-center my-3 gap-3">
                                @if($customerSocialLogin)
                                    @foreach ($web_config['customer_social_login_options'] as $socialLoginServiceKey => $socialLoginService)
                                        @if ($socialLoginService && $socialLoginServiceKey != 'apple')
                                            <a class="social-media-login-btn"
                                               href="{{ route('customer.auth.service-login', $socialLoginServiceKey) }}">
                                                <img alt=""
                                                     src="{{ theme_asset('assets/img/svg/'.$socialLoginServiceKey.'.svg') }}">
                                                <span class="text">
                                                    {{ translate($socialLoginServiceKey) }}
                                                </span>
                                            </a>
                                        @endif
                                    @endforeach
                                @endif
                                @if($customerOTPLogin && $customerManualLogin)
                                    <a class="social-media-login-btn otp-login-btn" href="javascript:">
                                        <img alt="" src="{{ theme_asset('assets/img/svg/otp-login-icon.svg') }}">
                                        <span class="text">{{ translate('OTP_Sign_in') }}</span>
                                    </a>

                                    <a class="social-media-login-btn manual-login-btn d-none" href="javascript:">
                                        <img alt="" src="{{ theme_asset('assets/img/svg/otp-login-icon.svg') }}">
                                        <span class="text">{{ translate('Manual_Login') }}</span>
                                    </a>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>

            </div>
        </div>
    </div>
</div>

@push('script')

    @if($multiColumn)
    <script>
        "use strict";
        function resizeFunc() {
            $('.or-sign-in-with').css('width', $('.or-sign-in-with-row').height())
        }
        $('#loginModal').on('show.bs.modal', function () {
            resizeFunc();
            const resizeObserver = new ResizeObserver(resizeFunc);
            resizeObserver.observe(document.querySelector('.or-sign-in-with-row'));
        });
    </script>
    @endif

    <script>
        'use strict';
        try {
            initializePhoneInput(".phone-input-with-country-picker-login", ".country-picker-phone-number-login");
        } catch (e) {
        }
    </script>

    @if($web_config['firebase_otp_verification'] && $web_config['firebase_otp_verification']['status'])
        <script type="text/javascript">
            "use strict";
            // console.info('Firebase Auth Rendering...');
        </script>
    @elseif($web_config['recaptcha']['status'] == 1)
        <script src="https://www.google.com/recaptcha/api.js?onload=onloadCallbackCustomerLogin&render=explicit" async
                defer></script>
        <script type="text/javascript">
            'use strict';
            var onloadCallbackCustomerLogin = function () {
                let loginId = grecaptcha.render('recaptcha_element_customer_login', {
                    'sitekey': '{{ getWebConfig(name: 'recaptcha')['site_key'] }}'
                });
                $('#recaptcha_element_customer_login').attr('data-login-id', loginId);
            };
        </script>
    @else
        <script type="text/javascript">
            'use strict';
            $('#re-captcha-customer-login').on('click', function () {
                let url = "{{ URL('/customer/auth/code/captcha') }}";
                url = url + "/" + Math.random() + '?captcha_session_id=default_recaptcha_id_customer_login';
                document.getElementById('customer_login_recaptcha_id').src = url;
                console.log('url: ' + url);
            })
        </script>
    @endif
@endpush
