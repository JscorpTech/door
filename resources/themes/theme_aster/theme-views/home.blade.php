@extends('theme-views.layouts.app')

@section('title', $web_config['company_name'].' '.translate('Online_Shopping').' | '.$web_config['company_name'].' '.translate('ecommerce'))

@push('css_or_js')
    <meta property="og:image" content="{{$web_config['web_logo']['path']}}"/>
    <meta property="og:title" content="Welcome To {{$web_config['company_name']}} Home"/>
    <meta property="og:url" content="{{env('APP_URL')}}">
    <meta property="og:description" content="{{ $web_config['meta_description'] }}">
    <meta name="description" content="{{ $web_config['meta_description'] }}">
    <meta property="twitter:card" content="{{$web_config['web_logo']['path']}}"/>
    <meta property="twitter:title" content="Welcome To {{$web_config['company_name']}} Home"/>
    <meta property="twitter:url" content="{{env('APP_URL')}}">
    <meta property="twitter:description" content="{{ $web_config['meta_description'] }}">
@endpush

@section('content')
    <main class="main-content d-flex flex-column gap-3 py-3">
        @include('theme-views.partials._main-banner')

        @if ($flashDeal['flashDeal'] && $flashDeal['flashDealProducts']  && count($flashDeal['flashDealProducts']) > 0)
            @include('theme-views.partials._flash-deals')
        @endif

        @include('theme-views.partials._find-what-you-need')

        @include('theme-views.partials._clearance-sale', ['clearanceSaleProducts' => $clearanceSaleProducts])

        @if ($web_config['business_mode'] == 'multi' && count($topVendorsList) > 0 && $topVendorsListSectionShowingStatus)
            @include('theme-views.partials._top-stores')
        @endif

        @if ($web_config['featured_deals']->count() > 0)
            @include('theme-views.partials._featured-deals')
        @endif

        @include('theme-views.partials._recommended-product')
        @if($web_config['business_mode'] == 'multi')
            @include('theme-views.partials._more-stores')
        @endif

        @include('theme-views.partials._top-rated-products')

        @include('theme-views.partials._best-deal-just-for-you')

        @include('theme-views.partials._home-categories')
        @if (!empty($bannerTypeMainSectionBanner))
        <section class="">
            <div class="container">
                <div class="py-5 rounded position-relative">
                    <img src="{{ getStorageImages(path: $bannerTypeMainSectionBanner->photo_full_url??null, type:'banner') }}"
                         alt="" class="rounded position-absolute dark-support img-fit start-0 top-0 index-n1 flipX-in-rtl">
                    <div class="row justify-content-center">
                        <div class="col-10 py-4">
                            <h6 class="text-primary mb-2 text-capitalize">{{ translate('do_not_miss_today`s_deal') }}!</h6>
                            <h2 class="fs-2 mb-4 absolute-dark text-capitalize">{{ translate('let_us_shopping_today') }}</h2>
                            <div class="d-flex">
                                <a href="{{$bannerTypeMainSectionBanner ? $bannerTypeMainSectionBanner->url:''}}" class="btn btn-primary fs-16 text-capitalize">{{ translate('shop_now') }}</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        @endif
    </main>
@endsection

