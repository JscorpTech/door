@php
    use App\Utils\Helpers;
    use Carbon\Carbon;
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{Session::get('direction')}}"
      style="text-align: {{Session::get('direction') === "rtl" ? 'right' : 'left'}};">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="robots" content="nofollow, noindex ">
    <title>@yield('title')</title>
    <meta name="_token" content="{{ csrf_token() }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="shortcut icon" href="{{getStorageImages(path: getWebConfig(name: 'company_fav_icon'), type:'backend-logo')}}">
    <link rel="stylesheet" href="{{dynamicAsset(path: 'public/assets/back-end/css/vendor.min.css')}}">
    <link rel="stylesheet" href="{{dynamicAsset(path: 'public/assets/back-end/css/bootstrap.min.css')}}">
    <link rel="stylesheet" href="{{dynamicAsset(path: 'public/assets/back-end/css/google-fonts.css')}}">
    <link rel="stylesheet" href="{{dynamicAsset(path: 'public/assets/back-end/css/custom.css')}}">
    <link rel="stylesheet" href="{{dynamicAsset(path: 'public/assets/back-end/vendor/icon-set/style.css')}}">
    <link rel="stylesheet" href="{{dynamicAsset(path: 'public/assets/back-end/css/theme.minc619.css?v=1.0')}}">
    <link rel="stylesheet" href="{{dynamicAsset(path: 'public/assets/back-end/css/daterangepicker.css')}}">
    <link rel="stylesheet" href="{{dynamicAsset(path: 'public/assets/back-end/css/style.css')}}">
    <link rel="stylesheet" href="{{dynamicAsset(path: 'public/assets/back-end/css/toastr.css')}}">
    @if(Session::get('direction') === "rtl")
        <link rel="stylesheet" href="{{dynamicAsset(path: 'public/assets/back-end/css/menurtl.css')}}">
    @endif
    <link rel="stylesheet" href="{{dynamicAsset(path: 'public/css/lightbox.css')}}">
    @stack('css_or_js')
    <script
        src="{{dynamicAsset(path: 'public/assets/back-end/vendor/hs-navbar-vertical-aside/hs-navbar-vertical-aside-mini-cache.js')}}"></script>
    <style>
        select {
            background-image: url('{{dynamicAsset(path: 'public/assets/back-end/img/arrow-down.png')}}');
            background-size: 7px;
            background-position: 96% center;
        }
    </style>
    @if(Request::is('admin/payment/configuration/addon-payment-get'))
        <style>
            .form-floating > label {
                position: relative;
                display: block;
                margin-bottom: 12px;
                padding: 0;
                inset-inline: 0 !important;
            }
        </style>
    @endif

<!-- DMARKET.KG — SEO ДОБАВЛЕНИЕ (append-only, безопасно) -->
  <link rel="canonical" href="https://dmarket.kg">
  <link rel="alternate" hreflang="ru" href="https://dmarket.kg/">
  <link rel="alternate" hreflang="x-default" href="https://dmarket.kg/">

  <meta name="twitter:card" content="summary_large_image">

  <script type="application/ld+json">
  {
    "@context": "https://schema.org",
    "@type": "Organization",
    "@id": "https://dmarket.kg/#organization",
    "name": "Dmarket.kg",
    "url": "https://dmarket.kg/",
    "logo": "https://dmarket.kg/storage/app/public/company/2025-07-17-68789491c139d.webp",
    "sameAs": [
      "https://www.instagram.com/dmarket.kg",
      "https://t.me/dmarketkgs",
      "https://www.youtube.com/@Dmarket-kg"
    ],
    "contactPoint": [{
      "@type": "ContactPoint",
      "telephone": "+996552844777",
      "contactType": "customer service",
      "areaServed": "KG",
      "availableLanguage": ["ru","ky"]
    }]
  }
  </script>

  <script type="application/ld+json">
  {
    "@context": "https://schema.org",
    "@type": "LocalBusiness",
    "@id": "https://dmarket.kg/#localbusiness",
    "name": "Dmarket.kg",
    "url": "https://dmarket.kg/",
    "image": "https://dmarket.kg/storage/app/public/company/2025-07-17-68789491c139d.webp",
    "telephone": ["+996552844777","+996556844777"],
    "address": {
      "@type": "PostalAddress",
      "streetAddress": "г. Бишкек",
      "addressLocality": "Бишкек",
      "addressRegion": "Чуйская область",
      "postalCode": "720038",
      "addressCountry": "KG"
    },
    "areaServed": "KG",
    "openingHoursSpecification": [{
      "@type": "OpeningHoursSpecification",
      "dayOfWeek": ["Monday","Tuesday","Wednesday","Thursday","Friday","Saturday","Sunday"],
      "opens": "00:00",
      "closes": "23:59"
    }]
  }
  </script>
  <!-- /append-only -->



</head>

<body class="footer-offset">

@include('layouts.back-end.partials._front-settings')
<span class="d-none" id="placeholderImg" data-img="{{dynamicAsset(path: 'public/assets/back-end/img/400x400/img3.png')}}"></span>
<div class="row">
    <div class="col-12 position-fixed z-9999 mt-10rem">
        <div id="loading" class="d--none">
            <div id="loader"></div>
        </div>
    </div>
</div>
@include('layouts.back-end.partials._header')
@include('layouts.back-end.partials._side-bar')
@include('layouts.back-end._translator-for-js')
<span id="get-root-path-for-toggle-modal-image" data-path="{{dynamicAsset(path: 'public/assets/back-end/img/modal')}}"></span>

<main id="content" role="main" class="main pointer-event">
    @yield('content')
    @include('layouts.back-end.partials._footer')
    @include('layouts.back-end.partials._modals')
    @include('layouts.back-end.partials._toggle-modal')
    @include('layouts.back-end.partials._sign-out-modal')
    @include('layouts.back-end._alert-message')
</main>
<span class="please_fill_out_this_field" data-text="{{ translate('please_fill_out_this_field') }}"></span>
<span class="get-application-environment-mode" data-value="{{ env('APP_MODE') == 'demo' ? 'demo':'live' }}"></span>
<span id="get-currency-symbol"
      data-currency-symbol="{{ getCurrencySymbol(currencyCode: getCurrencyCode(type: 'default')) }}"></span>

<span id="message-select-word" data-text="{{ translate('select') }}"></span>
<span id="message-yes-word" data-text="{{ translate('yes') }}"></span>
<span id="message-no-word" data-text="{{ translate('no') }}"></span>
<span id="message-cancel-word" data-text="{{ translate('cancel') }}"></span>
<span id="message-are-you-sure" data-text="{{ translate('are_you_sure') }} ?"></span>
<span id="message-invalid-date-range" data-text="{{ translate('invalid_date_range') }}"></span>
<span id="message-status-change-successfully" data-text="{{ translate('status_change_successfully') }}"></span>
<span id="message-are-you-sure-delete-this" data-text="{{ translate('are_you_sure_to_delete_this') }} ?"></span>
<span id="message-you-will-not-be-able-to-revert-this"
      data-text="{{ translate('you_will_not_be_able_to_revert_this') }}"></span>
<span id="exceeds10MBSizeLimit" data-text="{{ translate('File_exceeds_10MB_size_limit') }}"></span>

<span id="get-customer-list-route" data-action="{{route('admin.customer.customer-list-search')}}"></span>
<span id="get-customer-list-without-all-customer-route" data-action="{{route('admin.customer.customer-list-without-all-customer')}}"></span>

<span id="get-search-product-route" data-action="{{route('admin.products.search-product')}}"></span>
<span id="get-search-product-for-clearnace-route" data-action="{{route('admin.deal.clearance-sale.search-product-for-clearance')}}"></span>
<span id="get-search-vendor-for-clearnace-route" data-action="{{route('admin.deal.clearance-sale.search-vendor-for-clearance')}}"></span>
<span id="get-multiple-product-details-route" data-action="{{route('admin.products.multiple-product-details')}}"></span>
<span id="get-multiple-clearance-product-details-route" data-action="{{route('admin.deal.clearance-sale.multiple-clearance-product-details')}}"></span>
<span id="get-clearance-vendor-add-route" data-action="{{route('admin.deal.clearance-sale.vendor-add')}}"></span>
<span id="get-orders-list-route" data-action="{{ route('admin.orders.list', ['status'=>'all'])}}"></span>

@if(Helpers::module_permission_check('product_management'))
    <span id="get-stock-limit-status" data-action="{{route('admin.products.stock-limit-status',['type'=>'in_house'])}}"></span>
@endif

<span id="get-product-stock-limit-title" data-title="{{translate('warning')}}"></span>
<span id="get-product-stock-limit-image" data-warning-image="{{ dynamicAsset(path: 'public/assets/back-end/img/warning-2.png') }}"></span>
<span id="get-product-stock-limit-message"
      data-message-for-multiple="{{ translate('there_is_not_enough_quantity_on_stock').' . '.translate('please_check_products_in_limited_stock').'.' }}"
      data-message-for-three-plus-product="{{translate('_more_products_have_low_stock') }}"
      data-message-for-one-product="{{translate('this_product_is_low_on_stock')}}">
</span>
<span id="get-product-stock-view"
      data-stock-limit-page="{{route('admin.products.stock-limit-list',['in_house'])}}"
>
</span>
<span id="getChattingNewNotificationCheckRoute" data-route="{{ route('admin.messages.new-notification') }}"></span>
<span id="route-for-real-time-activities" data-route="{{ route('admin.dashboard.real-time-activities') }}"></span>
<span class="system-default-country-code" data-value="{{ getWebConfig(name: 'country_code') ?? 'us' }}"></span>
<span id="get-confirm-and-cancel-button-text-for-delete-all-products" data-sure ="{{translate('are_you_sure').'?'}}"
      data-text="{{translate('want_to_clear_all_stock_clearance_products?').'!'}}"
      data-confirm="{{translate('yes_delete_it')}}" data-cancel="{{translate('cancel')}}"></span>

<audio id="myAudio">
    <source src="{{ dynamicAsset(path: 'public/assets/back-end/sound/notification.mp3') }}" type="audio/mpeg">
</audio>
<div id="mobileBanner" role="banner" aria-live="polite" aria-label="Ilovani o'rnatish banneri">
  <div class="content">
    <div class="left">
      <img src="https://dmarket.kg/storage/app/public/company/2025-05-19-682b0dd2e0a15.webp" alt="Lalafo logo" class="logo" />
      <div class="text">Установка приложения Dmarket</div>
    </div>

    <div class="store-contents">
      <!-- iOS -->
      @if(isset($web_config['ios']) && $web_config['ios']['status'])
      <div class="store-link">
        <a href="{{ $web_config['ios']['link'] }}" role="button" aria-label="iOS ilovasini yuklab olish">
          <img src="{{theme_asset(path: "public/assets/front-end/png/apple_app.png")}}" alt="App Store" />
        </a>
      </div>
      @endif

      <!-- Android -->
      @if(isset($web_config['android']) && $web_config['android']['status'])
      <div class="store-link">
        <a href="{{ $web_config['android']['link'] }}" role="button" aria-label="Android ilovasini yuklab olish">
          <img src="{{theme_asset(path: "public/assets/front-end/png/google_app.png")}}" alt="Google Play" />
        </a>
      </div>
      @endif
    </div>

    <button class="close-btn" aria-label="Bannerni yopish" onclick="closeBanner()">×</button>
  </div>
</div>


<style>
#mobileBanner {
  display: none; /* dastlab yashirin */
  position: fixed;
  bottom: 0;
  left: 0;
  width: 100%;
  background: #fff;
  box-shadow: 0 -2px 8px rgba(0,0,0,0.1);
  z-index: 9999;
  padding: 10px;
  transition: transform 0.3s ease;
}
#mobileBanner.show {
  display: flex;
  transform: translateY(0);
}
#mobileBanner .content {
  display: flex;
  justify-content: space-between;
  align-items: center;
  width: 100%;
}
.close-btn {
  background: transparent;
  border: none;
  font-size: 20px;
  cursor: pointer;
}
</style>

<script>
function closeBanner() {
  document.getElementById('mobileBanner').style.display = 'none';
}

window.addEventListener('scroll', function() {
  const banner = document.getElementById('mobileBanner');
  const scrollY = window.scrollY || window.pageYOffset;

  if (scrollY > 200) { // sahifa 200px pastga scroll qilganda
    banner.style.display = 'flex';
  } else {
    banner.style.display = 'none';
  }
});
</script>



<script>
 function closeBanner() {
  document.getElementById('mobileBanner').style.display = 'none';
}

</script>

<script src="{{dynamicAsset(path: 'public/assets/back-end/js/vendor.min.js')}}"></script>
<script src="{{dynamicAsset(path: 'public/assets/back-end/js/theme.min.js')}}"></script>
<script src="{{dynamicAsset(path: 'public/assets/back-end/js/bootstrap.min.js')}}"></script>
<script src="{{dynamicAsset(path: 'public/assets/back-end/js/sweet_alert.js')}}"></script>
<script src="{{dynamicAsset(path: 'public/assets/back-end/js/toastr.js')}}"></script>
<script src="{{dynamicAsset(path: 'public/js/lightbox.min.js')}}"></script>

<script src="{{dynamicAsset(path: 'public/assets/back-end/js/moment.min.js')}}"></script>
<script src="{{dynamicAsset(path: 'public/assets/back-end/js/daterangepicker.min.js')}}"></script>

<script src="{{dynamicAsset(path: 'public/assets/back-end/js/custom.js')}}"></script>
<script src="{{dynamicAsset(path: 'public/assets/back-end/js/app-script.js')}}"></script>
<script src="{{dynamicAsset(path: 'public/assets/back-end/js/blog.js')}}"></script>

{!! Toastr::message() !!}

@if ($errors->any())
    <script>
        'use strict';
        @foreach($errors->all() as $error)
        toastr.error('{{$error}}', Error, {
            CloseButton: true,
            ProgressBar: true
        });
        @endforeach
    </script>
@endif

@stack('script')

@if(Helpers::module_permission_check('order_management') && env('APP_MODE') != 'dev')
<script>
    'use strict'
    setInterval(function () {
        getInitialDataForPanel();
    }, 5000);
</script>
@endif

@if(env('APP_MODE') == 'dev')
    <script>
        'use strict'
        function checkDemoResetTime() {
            let currentMinute = new Date().getMinutes();
            if (currentMinute > 55 && currentMinute <= 60) {
                $('#demo-reset-warning').addClass('active');
            } else {
                $('#demo-reset-warning').removeClass('active');
            }
        }
        checkDemoResetTime();
        setInterval(checkDemoResetTime, 60000);
    </script>
@endif

<script src="{{ dynamicAsset(path: 'public/assets/back-end/js/admin/common-script.js') }}"></script>

@stack('script_2')

</body>
</html>
