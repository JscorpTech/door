<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ session()->get('direction') }}">
<head>

    <meta name="base-url" content="{{ url('/') }}">
    <meta property="og:site_name" content="{{ $web_config['company_name'] }}" />

    <meta name="google-site-verification" content="{{getWebConfig('google_search_console_code')}}">
    <meta name="msvalidate.01" content="{{getWebConfig('bing_webmaster_code')}}">
    <meta name="baidu-site-verification" content="{{getWebConfig('baidu_webmaster_code')}}">
    <meta name="yandex-verification" content="{{getWebConfig('yandex_webmaster_code')}}">

    <meta http-equiv="X-UA-Compatible" content="IE=edge"/>
    <meta http-equiv="content-type" content="text/html; charset=utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
    <meta name="robots" content="index, follow">
    <meta name="_token" content="{{csrf_token()}}">
    <link rel="shortcut icon" href="{{$web_config['fav_icon']['path']}}"/>

    <link rel="stylesheet" href="{{ theme_asset('assets/css/fonts-init.css') }}"/>
    <link rel="stylesheet" href="{{ theme_asset('assets/css/bootstrap.min.css') }}"/>
    <link rel="stylesheet" href="{{ theme_asset('assets/css/bootstrap-icons.min.css') }}"/>
    <link rel="stylesheet" href="{{ theme_asset('assets/plugins/magnific-popup-1.1.0/magnific-popup.css') }}" />
    <link rel="stylesheet" href="{{ theme_asset('assets/plugins/swiper/swiper-bundle.min.css') }}"/>
    <link rel="stylesheet" href="{{ theme_asset('assets/plugins/sweet_alert/sweetalert2.css') }}">
    <link rel="stylesheet" href="{{ theme_asset('assets/css/toastr.css') }}"/>

    <link rel="stylesheet" href="{{ theme_asset('assets/plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ theme_asset('assets/plugins/intl-tel-input/css/intlTelInput.css') }}">
    <link rel="stylesheet" href="{{ theme_asset('assets/css/style.css') }}"/>

    @stack('css_or_js')
    @include(VIEW_FILE_NAMES['robots_meta_content_partials'])
    <title>@yield('title')</title>

    <link rel="stylesheet" href="{{ theme_asset('assets/css/custom.css') }}"/>
    <style>
        :root {
            --bs-primary: {{ $web_config['primary_color'] }};
            --bs-primary-rgb: {{ getHexToRGBColorCode($web_config['primary_color']) }};
            --primary-dark: {{ $web_config['primary_color'] }};
            --primary-light: {{ $web_config['primary_color_light'] }};
            --bs-secondary: {{ $web_config['secondary_color'] }};
            --bs-secondary-rgb: {{ getHexToRGBColorCode($web_config['secondary_color']) }};
        }

        .announcement-color {
            background-color: {{ $web_config['announcement']['color'] }};
            color: {{$web_config['announcement']['text_color']}};
        }
        .btn-outline-success {
            --bs-btn-hover-bg: {{ $web_config['primary_color'] }} !important;
            --bs-btn-active-bg: {{ $web_config['primary_color'] }} !important;
            --bs-btn-border-color: {{ $web_config['primary_color'] }} !important;
        }
        .btn-outline-success:active {
            background-color: var(--bg-color) !important;
            color: {{ $web_config['primary_color'] }} !important;
            --bs-btn-border-color: {{ $web_config['primary_color'] }} !important;
        }
    </style>

    {!! getSystemDynamicPartials(type: 'analytics_script') !!}
</head>
<body class="toolbar-enabled">
<script>
    'use strict';
    function setThemeMode() {
        if (localStorage.getItem('theme') === null) {
            document.body.setAttribute('theme', 'light');
        } else {
            document.body.setAttribute('theme', localStorage.getItem('theme'));
        }
    }
    setThemeMode();
</script>

<div class="preloader d--none" id="loading">
    <img width="200" alt="" src="{{ getStorageImages(path: getWebConfig(name: 'loader_gif'), type: 'source', source: theme_asset('assets/img/loader.gif')) }}">
</div>

@include('theme-views.layouts.partials._alert-message')

@yield('content')

<span class="customize-text"
      data-textno="{{ translate('no') }}"
      data-textyes="{{ translate('yes') }}"
      data-textnow="{{ translate('now') }}"
      data-textsuccessfullycopied="{{ translate('successfully_copied') }}"
      data-text-no-discount="{{ translate('no_discount') }}"
      data-stock-available="{{ translate('stock_available') }}"
      data-stock-not-available="{{ translate('stock_not_available') }}"
      data-update-this-address="{{ translate('update_this_Address') }}"
      data-password-characters-limit="{{ translate('your_password_must_be_at_least_8_characters') }}"
      data-password-not-match="{{ translate('password_does_not_Match') }}"
      data-textpleaseselectpaymentmethods="{{ translate('please_select_a_payment_Methods') }}"
      data-reviewmessage="{{ translate('you_can_review_after_the_product_is_delivered') }}"
      data-refundmessage="{{ translate('you_can_refund_request_after_the_product_is_delivered') }}"
      data-textshoptemporaryclose="{{ translate('This_shop_is_temporary_closed_or_on_vacation').' '.translate('You_cannot_add_product_to_cart_from_this_shop_for_now') }}"
></span>
<span class="system-default-country-code" data-value="{{ getWebConfig(name: 'country_code') ?? 'us' }}"></span>
<span class="cannot_use_zero" data-text="{{ translate('cannot_Use_0_only') }}"></span>
<span class="system-default-country-code" data-value="{{ getWebConfig(name: 'country_code') ?? 'us' }}"></span>

@include('theme-views.layouts.partials._translate-text-for-js')
@include('theme-views.layouts.partials._route-for-js')
@include('theme-views.layouts.main-script')
@include('theme-views.layouts._firebase-script')

{!! Toastr::message() !!}
<script>
    function route_alert(route, message) {
        Swal.fire({
            title: "{{translate('are_you_sure')}}?",
            text: message,
            type: 'warning',
            showCancelButton: true,
            cancelButtonColor: 'default',
            confirmButtonColor: '{{$web_config['primary_color']}}',
            cancelButtonText: '{{translate('no')}}',
            confirmButtonText: '{{translate('yes')}}',
            reverseButtons: true
        }).then((result) => {
            if (result.value) {
                location.href = route;
            }
        })
    }
</script>
@stack('script')

</body>
</html>
