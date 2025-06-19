@extends('layouts.front-end.app')

@section('title', translate('verify'))

@section('content')
    <div class="container py-4 py-lg-5 my-4 __inline-7">
        <div class="row justify-content-center">
            <div class="col-md-6 col-xl-5">
                <div class="card border-0 box-shadow">
                    @if ($user_verify == 0)
                        <div class="card-body">
                            <div class="text-center">
                                <img src="{{asset('/public/assets/front-end/img/icons/otp-login-icon.svg')}}" width="50" height="50" alt="" class="mb-4">
                            </div>
                            <div class="resend_otp_custom text-center overflow-hidden {{ $get_time <= 0 ? 'd--none' : ''}}">
                                <p class="text-primary mb-2">{{ translate('resend_code_within') }}</p>
                                <h6 class="text-primary m-0 pb-4 verifyTimer">
                                    <span class="verifyCounter" data-second="{{$get_time}}"></span>s
                                </h6>
                            </div>
                            <form class="needs-validation_ otp-form" id="sign-up-form"
                                  action="{{ route('customer.auth.verify') }}"
                                  data-verify="{{ route('customer.auth.verify') }}"
                                  data-resend="{{ route('customer.auth.resend_otp') }}"
                                  method="post">
                                @csrf
                                <div class="col-sm-12">
                                    <div class="form-group">
                                        <div class="text-center mb-4 pb-2 fs-13 max-w-320 mx-auto text-body">
                                            @if(base64_decode(request('type')) == 'phone_verification')
                                                <label for="reg-phone">
                                                    * {{ translate('please_provide_OTP_sent_in_your_phone') }}
                                                </label>
                                            @elseif(base64_decode(request('type')) == 'email_verification')
                                                <label for="reg-phone">
                                                    * {{ translate('please_provide_verification_token_sent_in_your_email') }}
                                                </label>
                                            @else
                                                <label for="reg-phone">
                                                    * {{ translate('verification_code') }} / {{ translate('OTP')}}
                                                </label>
                                            @endif
                                        </div>

                                        <div class="d-flex gap-2 gap-sm-3 align-items-end justify-content-center forget-password-otp my-4">
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
                                        <input class="otp-value" type="hidden" name="token" required>
                                    </div>
                                </div>
                                <input type="hidden" value="{{$user->id}}" name="id">
                                <input type="hidden" name="identity" value="{{ request('identity') }}">
                                <input type="hidden" name="type" value="{{ request('type') }}">

                                @if($web_config['firebase_otp_verification'] && $web_config['firebase_otp_verification']['status'])
                                    <div id="recaptcha-container-verify-token" class="my-2"></div>
                                @endif

                                <div class="d-flex gap-2 justify-content-between align-items-center mt-3">
                                    <button type="button" class="btn btn--primary w-100 submitVerifyForm">
                                        {{ translate('verify')}}
                                    </button>

                                    <button class="btn btn--primary w-100 resend-otp-button resendVerifyForm"
                                            type="button"
                                            data-url="{{ route('customer.auth.resend_otp') }}"
                                    >
                                        {{ translate('resend_OTP') }}
                                    </button>
                                </div>
                            </form>
                        </div>
                    @else
                        <div class=" p-5">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="text-center">
                                        <i class="fa fa-check-circle __text-100px __color-0f9d58"></i>
                                    </div>

                                    <span class="font-weight-bold d-block mt-4 __text-17px text-center">{{ translate('hello')}}, {{$user->f_name}}</span>
                                    <h5 class="font-black __text-20px text-center my-2">
                                        {{ translate('verification_Successfully_Done!')}}!
                                    </h5>
                                </div>
                            </div>

                            <div class="text-center mt-4">
                                <a href="{{route('customer.auth.login')}}" class="btn btn-sm btn--primary">
                                    {{ translate('sign_in')}}
                                </a>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script')
    <script>
        $(document).ready(function () {
            $(".otp-form .otp-value").focus();
            let otp_fields = $(".otp-form .otp-field"),
                otp_value_field = $(".otp-form .otp-value");
            otp_fields
                .on("input", function (e) {
                    $(this).val(
                        $(this)
                            .val()
                            .replace(/[^0-9]/g, "")
                    );
                    let opt_value = "";
                    otp_fields.each(function () {
                        let field_value = $(this).val();
                        if (field_value != "") opt_value += field_value;
                    });
                    otp_value_field.val(opt_value);
                })
                .on("keyup", function (e) {
                    let key = e.keyCode || e.charCode;
                    if (key == 8 || key == 46 || key == 37 || key == 40) {
                        $(this).prev().focus();
                    } else if (key == 38 || key == 39 || $(this).val() != "") {
                        $(this).next().focus();
                    }
                })
                .on("paste", function (e) {
                    let paste_data = e.originalEvent.clipboardData.getData("text");
                    let paste_data_splitted = paste_data.split("");
                    $.each(paste_data_splitted, function (index, value) {
                        otp_fields.eq(index).val(value);
                    });
                });
        });
    </script>
    <script src="{{ theme_asset(path: 'public/assets/front-end/js/verify-otp.js') }}"></script>
@endpush
