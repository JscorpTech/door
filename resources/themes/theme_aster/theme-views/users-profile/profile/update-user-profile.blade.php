@extends('theme-views.layouts.app')

@section('title', translate('Personal_Details').' | '.$web_config['company_name'].' '.translate('ecommerce'))
@section('content')
    <main class="main-content d-flex flex-column gap-3 py-3 mb-5">
        <div class="container">
            <div class="row g-3">
                @include('theme-views.partials._profile-aside')
                <div class="col-lg-9">
                    <div class="card h-100">
                        <div class="card-body p-lg-4">
                            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                                <h5>{{translate('edit_personal_details')}}</h5>
                                <a href="{{ route('user-profile') }}" class="btn-link text-secondary d-flex align-items-baseline">
                                    <i class="bi bi-chevron-left fs-12"></i> {{translate('go_back')}}
                                </a>
                            </div>
                            <div class="mt-4">
                                <form  action="{{route('user-update')}}" method="post" enctype="multipart/form-data">
                                    @csrf
                                    <div class="row gy-4">
                                        <div class="col-sm-6">
                                            <div class="form-group">
                                                <label class="text-capitalize" for="f_name2">{{translate('first_name')}}</label>
                                                <input type="text" id="f_name" class="form-control" value="{{$customerDetail['f_name']}}" name="f_name" placeholder="{{translate('ex').':'.translate('jhon')}}" required>
                                            </div>
                                        </div>
                                        <div class="col-sm-6">
                                            <div class="form-group">
                                                <label class="text-capitalize" for="l_name2">{{translate('last_name')}}</label>
                                                <input type="text" id="l_name" class="form-control" value="{{$customerDetail['l_name']}}" name="l_name" placeholder="{{translate('ex').':'.translate('doe')}}" required>
                                            </div>
                                        </div>
                                        <div class="col-sm-6">
                                            <div class="form-group">
                                                <label for="phone2">{{translate('phone')}}</label>
                                                <div class="position-relative d-flex align-items-center">
                                                    <input type="text" id="phone" class="form-control profile-phone-with-country-picker" value="{{$customerDetail['phone']}}" placeholder="{{translate('ex').':'.'01xxxxxxxxx'}}" {{ $customerDetail['is_phone_verified'] ? 'disabled' : '' }}>
                                                    <input type="hidden" name="phone" class="profile-phone-country-picker-hidden" value="{{$customerDetail['phone']}}">

                                                    @if($customerDetail['phone'] && getLoginConfig(key: 'phone_verification'))
                                                        @if($customerDetail['is_phone_verified'])
                                                            <span class="position-absolute inset-inline-end-10px cursor-pointer" data-bs-toggle="tooltip" data-bs-placement="top" title="{{ translate('Your_phone_is_verified') }}">
                                                                <img width="16"
                                                                     src="{{theme_asset('assets/img/icons/verified.svg')}}"
                                                                     class="dark-support" alt="">
                                                            </span>
                                                        @else
                                                            <span class="position-absolute inset-inline-end-10px cursor-pointer" data-bs-toggle="tooltip" data-bs-placement="top"
                                                                  title="{{ translate('Phone_not_verified.') }} {{ translate('Please_verify_through_the_user_app') }}">
                                                                <img width="16"
                                                                     src="{{ theme_asset('assets/img/icons/verified-error.svg') }}"
                                                                     class="dark-support" alt="">
                                                            </span>
                                                        @endif
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-sm-6">
                                            <div class="form-group">
                                                <label for="email2">{{translate('email')}}</label>
                                                <div class="position-relative d-flex align-items-center">
                                                    <input type="email" id="email2" class="form-control" value="{{$customerDetail['email']}}" name="email">

                                                    @if($customerDetail['email'] && getLoginConfig(key: 'email_verification'))
                                                        @if($customerDetail['is_email_verified'])
                                                            <span class="position-absolute inset-inline-end-10px cursor-pointer" data-bs-toggle="tooltip" data-bs-placement="top" title="{{ translate('Your_email_is_verified') }}">
                                                            <img width="16"
                                                                 src="{{theme_asset('assets/img/icons/verified.svg')}}"
                                                                 class="dark-support" alt="">
                                                            </span>
                                                        @else
                                                            <span class="position-absolute inset-inline-end-10px cursor-pointer" data-bs-toggle="tooltip" data-bs-placement="top"
                                                                  title="{{ translate('Email_not_verified.') }} {{ translate('Please_verify_through_the_user_app.') }}">
                                                            <img width="16"
                                                                 src="{{theme_asset('assets/img/icons/verified-error.svg')}}"
                                                                 class="dark-support" alt="">
                                                            </span>
                                                        @endif
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-sm-6">
                                            <div class="form-group">
                                                <label for="password2">{{translate('password')}}</label>
                                                <div class="input-inner-end-ele">
                                                    <input type="password" minlength="6" id="password" class="form-control" name="password" placeholder="{{translate('ex').':'.'7+'.translate('character')}}">
                                                    <i class="bi bi-eye-slash-fill togglePassword"></i>
                                                </div>
                                                <span class="text-danger mx-1 password-error"></span>
                                            </div>
                                        </div>
                                        <div class="col-sm-6">
                                            <div class="form-group">
                                                <label class="text-capitalize" for="confirm_password2">{{translate('confirm_password')}}</label>
                                                <div class="input-inner-end-ele">
                                                    <input type="password" minlength="6" id="confirm-password" name="confirm_password" class="form-control" placeholder="{{translate('ex').':'.'7+'.translate('character')}}">
                                                    <i class="bi bi-eye-slash-fill togglePassword"></i>
                                                </div>
                                            </div>
                                            <div id='message'></div>
                                        </div>
                                        <div class="col-sm-6">
                                            <div class="form-group">
                                                <label>{{translate('attachment')}}</label>
                                                <div class="d-flex flex-column gap-3">
                                                    <div class="upload-file">
                                                        <input type="file" class="upload-file__input --size-8-75rem"  name="image" multiple aria-required="true" accept="image/*">
                                                        <div class="upload-file__img">
                                                            <div class="temp-img-box">
                                                                <div class="d-flex align-items-center flex-column gap-2">
                                                                    <i class="bi bi-upload fs-30"></i>
                                                                    <div class="fs-12 text-muted">{{translate('change_your_profile')}}</div>
                                                                </div>
                                                            </div>
                                                            <img src="#" class="dark-support img-fit-contain border" alt="" hidden="">
                                                        </div>
                                                    </div>

                                                    <div class="text-muted">{{translate('image_ratio_should_be').'1:1'}}</div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <div class="d-flex justify-content-end gap-3">
                                                <button type="reset" class="btn btn-secondary" id="profile-reset-button">{{translate('reset')}}</button>
                                                <button type="submit" class="btn btn-primary text-capitalize">{{translate('update_profile')}}</button>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </main>
@endsection
@push('script')
    <script>
        "use strict";
        initializePhoneInput('.profile-phone-with-country-picker', '.profile-phone-country-picker-hidden');
    </script>
    <script src="{{theme_asset('assets/js/password-strength.js')}}"></script>
@endpush

