@extends('layouts.front-end.app')

@section('title', translate('forgot_Password'))

@section('content')
    <div class="container py-4 py-lg-5 my-4 rtl">
        <div class="row justify-content-center">
            <div class="col-lg-8 col-md-10 text-start">
                <h2 class="h3 mb-4">{{ translate('forgot_your_password')}}?</h2>
                <p class="font-size-md">
                    <!-- {{ translate('change_your_password_in_three_easy_steps.')}} {{ translate('this_helps_to_keep_your_new_password_secure.')}} -->
                    Измените ваш пароль за три простых шага:
                </p>
                <ol class="list-unstyled font-size-md p-0">
                    <li>
                        <span class="text-primary mr-2">{{ translate('1')}}.</span>
                        Укажите вашу зарегистрированную электронную почту.
                    </li>
                    <li>
                        <span class="text-primary mr-2">{{ translate('2')}}.</span>
                        Мы отправим на неё временный OTP-код.
                    </li>
                    <li>
                        <span class="text-primary mr-2">{{ translate('3')}}.</span>
                        Используйте этот код, чтобы изменить пароль на сайте.
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
                                   placeholder="Введите адрес вашей электронной почты">
                            <span class="fs-12 text-muted">* {{ translate('must_use_country_code_before_phone_number') }}</span>
                            <div class="invalid-feedback">
                                {{ translate('please_provide_valid_phone_number')}}
                            </div>
                        </div>
                        @if($web_config['firebase_otp_verification'] && $web_config['firebase_otp_verification']['status'])
                            <div id="recaptcha-container-verify-token" class="my-2"></div>
                        @endif
                        <button class="btn btn--primary" type="submit">Отправить код</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
