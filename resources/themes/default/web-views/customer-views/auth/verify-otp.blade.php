@extends('layouts.front-end.app')

@section('title',  translate('OTP_verification'))

@section('content')
    <div class="container py-4 py-lg-5 my-4 __inline-8">
        <div class="row justify-content-center">
            <div class="col-lg-4 col-md-6">
                <div class="card py-2 mt-4">
                    <form class="card-body needs-validation otp-form" action="{{route('customer.auth.otp-verification')}}"
                          method="post">
                        @csrf
                        <div class="form-group">
                            <div class="text-center">
                                <img src="{{asset('/public/assets/front-end/img/icons/otp-login-icon.svg')}}" width="50" height="50" alt="" class="mb-4">
                            </div>
                            <div class="resend_otp_custom text-center {{ $time_count <= 0 ? 'd--none' : ''}}">
                                <p class="text-primary mb-2 ">{{ translate('resend_code_within') }}</p>
                                <h6 class="text-primary mb-5 verifyTimer">
                                    <span class="verifyCounter" data-second="{{$time_count}}"></span>s
                                </h6>
                            </div>

                            <div class="text-center mb-4 pb-2 fs-13 max-w-320 mx-auto text-body">
                                {{translate('We’ve sent a verification code to ')}} {{ request('identity') }}
                                {{translate('& your OTP will be expired within 2min.')}}
                            </div>

                            <div class="d-flex gap-2 gap-sm-3 align-items-end justify-content-center forget-password-otp mb-4">
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
                            <input class="otp-value" type="hidden" name="otp">
                        </div>
                        <button class="btn btn--primary w-100" type="submit">{{ translate('verify')}}</button>
                        <div class="d-flex flex-wrap justify-content-between align-items-center mt-3">
                            <span class="fs-14">
                                {{translate("Didn't receive the code?")}}
                            </span>
                            <button class="btn p-0 resend-otp-button text-primary" type="button" id="customerOtpVerify"
                                    data-identity="{{ request('identity') }}" data-url="{{ route('customer.auth.resend-otp-reset-password') }}">
                                {{ translate('resend_OTP') }}
                            </button>
                        </div>
                    </form>
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
