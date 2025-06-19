@extends('layouts.front-end.app')

@section('title', translate('flash_Deal_Products'))

@push('css_or_js')
    <meta property="og:image" content="{{ $web_config['web_logo']['path'] }}"/>
    <meta property="og:title" content="Deals of {{ $web_config['company_name'] }} "/>
    <meta property="og:url" content="{{ env('APP_URL') }}">
    <meta property="og:description" content="{{ $web_config['meta_description'] }}">
    <meta property="twitter:card" content="{{ $web_config['web_logo']['path'] }}"/>
    <meta property="twitter:title" content="Deals of {{ $web_config['company_name'] }}"/>
    <meta property="twitter:url" content="{{env('APP_URL')}}">
    <meta property="twitter:description" content="{{ $web_config['meta_description'] }}">
@endpush

@section('content')
    @php($deal_banner = getStorageImages(path: $deal['banner_full_url'], type: 'banner' , source: theme_asset(path: 'public/assets/front-end/img/flash-deals.png')))

    <div class="container">
        @include('web-views.products.partials._flash-deal-countdown', ['products' => $products])
    </div>

    <div class="container py-3" dir="{{ session('direction') }}">

        <form method="POST" action="{{ url()->current() }}" class="product-list-filter">
            @csrf

            <input hidden name="offer_type" value="{{ request()->is('flash-deals*') ? 'flash-deals' : request('offer_type') }}">
            <input hidden name="flash_deals_id" value="{{ $web_config['flash_deals']['id'] }}">

            @include('web-views.products.partials._product-list-header', [
                    'pageTitleContent' => $pageTitleContent,
                    'pageProductsCount' => $products->total(),
                    'searchBarSection' => true,
                    'sortBySection' => true,
                    'showProductsFilter' => true,
            ])
            <div class="py-3 mb-2 mb-md-4 rtl __inline-35" dir="{{ session('direction') }}">
                <div class="row">
                    <aside class="col-lg-3 hidden-xs col-md-3 col-sm-4 SearchParameters __search-sidebar" id="SearchParameters">
                        <div class="cz-sidebar __inline-35 p-4 overflow-hidden mt-8" id="shop-sidebar">
                            <div class="cz-sidebar-header p-0">
                                <button class="close ms-auto fs-18-mobile" type="button" data-dismiss="sidebar" aria-label="Close">
                                    <i class="tio-clear"></i>
                                </button>
                            </div>

                            <div class="pb-0 shop-sidebar-scroll">
                                <div class="d-flex gap-3 flex-column">
                                    <h5 class="fs-16 font-weight-bold m-0">{{ translate('Filter_By') }}</h5>
                                    <hr>
                                    @include('web-views.products.partials._filter-product-type')
                                    @include('web-views.products.partials._filter-product-sort')
                                    @include('web-views.products.partials._filter-product-price')
                                    @include('web-views.products.partials._filter-product-categories', [
                                        'productCategories' => $productCategories,
                                        'dataFrom' => 'flash-deals',
                                    ])
                                    @include('web-views.products.partials._filter-product-brands', [
                                        'productBrands' => $activeBrands,
                                        'dataFrom' => 'flash-deals',
                                    ])
                                    @include('web-views.products.partials._filter-publishing-houses', [
                                        'productPublishingHouses' => $web_config['publishing_houses'],
                                        'dataFrom' => 'flash-deals',
                                    ])
                                    @include('web-views.products.partials._filter-product-authors', [
                                        'productAuthors' => $web_config['digital_product_authors'],
                                        'dataFrom' => 'flash-deals',
                                    ])
                                </div>
                            </div>

                        </div>
                        <div class="sidebar-overlay"></div>
                    </aside>

                    <section class="col-lg-9">
                        <div class="row" id="ajax-products-view">
                            @include('web-views.products._ajax-products', ['products' => $products])
                        </div>
                    </section>
                </div>
            </div>

        </form>

    </div>

    <span id="products-search-data-backup"
          data-page="{{ request('page') ?? 1 }}"
          data-url="{{ url()->current() }}"
          data-brand="{{ $data['brand_id'] ?? '' }}"
          data-category="{{ $data['category_id'] ?? '' }}"
          data-name="{{ $data['name'] }}"
          data-offer-type="{{ request()->is('flash-deals*') ? 'flash-deals' : $data['offer_type'] }}"
          data-from="{{ request('data_from') ?? $data['product_type'] }}"
          data-sort="{{ $data['sort_by'] }}"
          data-product-type="{{ $data['product_type'] }}"
          data-min-price="{{ $data['min_price'] }}"
          data-max-price="{{ $data['max_price'] }}"
          data-message="{{ translate('items_found') }}"
          data-publishing-house-id="{{ request('publishing_house_id') }}"
          data-author-id="{{ request('author_id') }}"
          data-offer="{{ request('offer_type') ?? '' }}"
          data-flash-deals-id="{{ $web_config['flash_deals']['id'] }}"
    ></span>
@endsection

@push('script')
    <script src="{{ theme_asset(path: 'public/assets/front-end/js/deals.js') }}"></script>
    <script src="{{ theme_asset(path: 'public/assets/front-end/js/product-list-filter.js') }}"></script>
@endpush
