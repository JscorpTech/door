@extends('theme-views.layouts.app')

@section('title', translate('customer_Verify').' | '.$web_config['company_name'].' '.translate('ecommerce'))

@section('content')
    <main class="main-content d-flex flex-column gap-3 py-3 mb-30">
        @if($user_verify == 0)
            <div class="container" id="otp_form_section">
                <div class="card mb-5">
                    <div class="card-body py-5 px-lg-5">
                        <div class="row align-items-center pb-5">
                            <div class="col-lg-6 mb-5 mb-lg-0">
                                <h2 class="text-center mb-2 text-capitalize">{{ translate('OTP_verification') }}</h2>
                                <p class="text-center mb-5 text-muted">{{ translate('please_Verify_that_its_you').'.' }}</p>
                                <div class="d-flex justify-content-center">
                                    <img width="283" class="dark-support"
                                         src="{{theme_asset('assets/img/media/otp.png')}}"
                                         alt="{{translate('image')}}">
                                </div>
                            </div>
                            <div class="col-lg-6 text-center">
                                <div class="d-flex justify-content-center mb-3">
                                    <img width="50" class="dark-support"
                                         src="{{theme_asset('assets/img/media/otp-lock.png')}}"
                                         alt="{{translate('image')}}">
                                </div>
                                <p class="text-muted mx-w mx-auto width--27-5rem">
                                    @if(base64_decode(request('type')) == 'phone_verification')
                                        * {{ translate('please_provide_OTP_sent_in_your_phone') }}
                                    @elseif(base64_decode(request('type')) == 'email_verification')
                                        * {{ translate('please_provide_verification_token_sent_in_your_email') }}
                                    @else
                                        * {{ translate('verification_code') }} / {{ translate('OTP')}}
                                    @endif
                                </p>
                                <div class="resend-otp-custom">
                                    <p class="text-primary mb-2">{{ translate('resend_code_within') }}</p>
                                    <h6 class="text-primary mb-5 verifyTimer">
                                        <span class="verifyCounter" data-second="{{$get_time}}"></span>{{translate('s')}}
                                    </h6>
                                </div>
                                <form action="{{ route('customer.auth.verify') }}"
                                      data-verify="{{ route('customer.auth.verify') }}"
                                      data-resend="{{ route('customer.auth.resend_otp') }}"
                                      class="otp-form" method="POST">
                                    @csrf
                                    <div class="d-flex gap-2 gap-sm-3 align-items-end justify-content-center">
                                        <input class="otp-field" type="text" name="opt-field[]" maxlength="1"
                                               autocomplete="off">
                                        <input class="otp-field" type="text" name="opt-field[]" maxlength="1"
                                               autocomplete="off">
                                        <input class="otp-field" type="text" name="opt-field[]" maxlength="1"
                                               autocomplete="off">
                                        <input class="otp-field" type="text" name="opt-field[]" maxlength="1"
                                               autocomplete="off">
                                        <input class="otp-field" type="text" name="opt-field[]" maxlength="1"
                                               autocomplete="off">
                                        <input class="otp-field" type="text" name="opt-field[]" maxlength="1"
                                               autocomplete="off">
                                    </div>
                                    <input class="otp-value" type="hidden" name="token">
                                    <input type="hidden" value="{{$user->id}}" name="id">
                                    <input type="hidden" name="identity" value="{{ request('identity') }}">
                                    <input type="hidden" name="type" value="{{ request('type') }}">

                                    @if($web_config['firebase_otp_verification'] && $web_config['firebase_otp_verification']['status'])
                                        <div id="recaptcha-container-verify-token" class="d-flex justify-content-center my-4"></div>
                                    @endif

                                    <div class="d-flex justify-content-center gap-3 mt-2">
                                        <button class="btn btn-outline-primary resendVerifyForm" type="button"
                                            data-url="{{ route('customer.auth.resend_otp') }}">
                                            {{ translate('resend_OTP') }}
                                        </button>
                                        <button class="btn btn-primary px-sm-5 button-type-submit submitVerifyForm" type="button" disabled>
                                            {{ translate('verify') }}
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @else
            <div class="container" id="success_message">
                <div class="card">
                    <div class="card-body p-md-5">
                        <div class="row justify-content-center">
                            <div class="col-xl-6 col-md-10">
                                <div class="text-center d-flex flex-column align-items-center gap-3">
                                    <img width="46" src="{{theme_asset('assets/img/icons/check.png')}}" class="dark-support"
                                         alt="{{translate('image')}}">
                                    <h3 class="text-capitalize">{{translate('verification_successfully_completed')}}</h3>
                                    <p class="text-muted">
                                        {{ translate('thank_you_for_your_verification').'!'.translate('now_you_can_login_your_account_is_ready_to_use')}}</p>
                                    <div class="d-flex flex-wrap justify-content-center gap-3">
                                        <button class="btn btn-outline-primary bg-primary-light border-transparent"
                                                data-bs-toggle="modal"
                                                data-bs-target="#loginModal">{{ translate('login') }}
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </main>
@endsection

@push('script')
    <script src="{{ theme_asset('assets/js/auth.js') }}"></script>
@endpush
