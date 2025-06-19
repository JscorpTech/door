@extends('theme-views.layouts.app')

@section('title', translate('Forgot_Password').' | '.$web_config['company_name'].' '.translate('ecommerce'))

@section('content')
    <main class="main-content d-flex flex-column gap-3 py-3 mb-sm-5">
        <div class="container">
            <div class="card">
                <div class="card-body py-5 px-lg-5">
                    <div class="row align-items-center pb-5">
                        <div class="col-lg-6 mb-5 mb-lg-0">
                            <h2 class="text-center mb-5 text-capitalize">{{ translate('forget_password') }}</h2>
                            <div class="d-flex justify-content-center">
                                <img width="283" class="dark-support" src="{{ theme_asset('assets/img/otp.png') }}"
                                     alt="{{translate('image')}}">
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="d-flex justify-content-center mb-3">
                                <img width="50" class="dark-support" src="{{ theme_asset('assets/img/otp-lock.png') }}"
                                     alt="">
                            </div>
                            <p class="text-muted mx-w mx-auto text-center mb-4 width--18-75rem">
                                {{ translate('we_will_send_you_a_temporary_OTP_in_your_phone') }}
                            </p>
                            <form action="{{route('customer.auth.forgot-password')}}" class="forget-password-form"
                                  method="post">
                                @csrf
                                <div class="form-group">
                                    <label for="recover-email">
                                        {{ translate('Phone') }}
                                    </label>
                                    <input class="form-control clean-phone-input-value" type="text" name="identity" id="recover-email"
                                           autocomplete="off" required placeholder="{{ translate('enter_your_phone_number') }}">
                                    <span class="fs-12 text-muted">* {{ translate('must_use_country_code_before_phone_number') }}</span>
                                    <div class="invalid-feedback">
                                        {{translate('please_provide_valid_identity').'.'}}
                                    </div>
                                </div>

                                @if($web_config['firebase_otp_verification'] && $web_config['firebase_otp_verification']['status'])
                                    <div id="recaptcha-container-verify-token" class="d-flex justify-content-center my-4"></div>
                                @endif

                                <div class="d-flex justify-content-center gap-3 mt-2">
                                    <button class="btn btn-outline-primary get-view-by-onclick"
                                            data-link="{{ route('home') }}"
                                            type="button">{{ translate('back_again') }}</button>
                                    <button class="btn btn-primary px-sm-5"
                                            type="submit">{{ translate('verify') }}</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
@endsection
