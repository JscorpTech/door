@extends('layouts.front-end.app')

@section('title', translate('forgot_Password'))

@section('content')
    <div class="container py-4 py-lg-5 my-4 rtl">
        <div class="row justify-content-center">
            <div class="col-lg-8 col-md-10 text-start">
                <h2 class="h3 mb-4">{{ translate('forgot_your_password')}}?</h2>
                <p class="font-size-md">
                    {{ translate('change_your_password_in_three_easy_steps.')}} {{ translate('this_helps_to_keep_your_new_password_secure.')}}
                </p>
                <ol class="list-unstyled font-size-md p-0">
                    <li>
                        <span class="text-primary mr-2">{{ translate('1')}}.</span>
                        {{ translate('use_your_registered_phone.')}}
                    </li>
                    <li>
                        <span class="text-primary mr-2">{{ translate('2')}}.</span>
                        {{ translate('we_will_send_you_a_temporary_OTP_in_your_phone') }}.
                    </li>
                    <li>
                        <span class="text-primary mr-2">{{ translate('3')}}.</span>
                        {{ translate('use_the_OTP_code_to_change_your_password_on_our_secure_website.')}}
                    </li>
                </ol>

                <div class="card py-2 mt-4">
                    <form class="card-body needs-validation" action="{{route('customer.auth.forgot-password')}}"
                          method="post">
                        @csrf
                        <div class="form-group">
                            <label for="recover-email">
                                {{ translate('Email') }}
                            </label>
                            <input class="form-control clean-phone-input-value" type="text" name="identity" required
                                   placeholder="{{ translate('enter_your_phone_number') }}">
                            <span class="fs-12 text-muted">* {{ translate('must_use_country_code_before_phone_number') }}</span>
                            <div class="invalid-feedback">
                                {{ translate('please_provide_valid_phone_number')}}
                            </div>
                        </div>
                        @if($web_config['firebase_otp_verification'] && $web_config['firebase_otp_verification']['status'])
                            <div id="recaptcha-container-verify-token" class="my-2"></div>
                        @endif
                        <button class="btn btn--primary" type="submit">{{ translate('send_OTP')}}</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
