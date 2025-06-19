@extends('layouts.front-end.app')

@section('title',translate('shop_Page'))

@push('css_or_js')
    @if($shopInfoArray['id'] != 0)
        <meta property="og:image" content="{{ $shopInfoArray['image_full_url']['path'] }}"/>
        <meta property="og:title" content="{{ $shopInfoArray['name']}} "/>
        <meta property="og:url" content="{{route('shopView',[$shopInfoArray['id']])}}">
    @else
        <meta property="og:image" content="{{$web_config['fav_icon']['path']}}"/>
        <meta property="og:title" content="{{ $shopInfoArray['name']}} "/>
        <meta property="og:url" content="{{route('shopView',[$shopInfoArray['id']])}}">
    @endif

    @if($shopInfoArray['id'] != 0)
        <meta property="twitter:card" content="{{$shopInfoArray['image_full_url']['path']}}"/>
        <meta property="twitter:title" content="{{route('shopView',[$shopInfoArray['id']])}}"/>
        <meta property="twitter:url" content="{{route('shopView',[$shopInfoArray['id']])}}">
    @else
        <meta property="twitter:card" content="{{$web_config['fav_icon']['path']}}"/>
        <meta property="twitter:title" content="{{route('shopView',[$shopInfoArray['id']])}}"/>
        <meta property="twitter:url" content="{{route('shopView',[$shopInfoArray['id']])}}">
    @endif

    <meta property="og:description" content="{{ $web_config['meta_description'] }}">
    <meta property="twitter:description" content="{{ $web_config['meta_description'] }}">
@endpush

@section('content')
    @php($decimalPointSettings = getWebConfig(name: 'decimal_point_settings'))

    <div class="container py-4 __inline-67">
        <div class="rtl">
            <div class="bg-white __shop-banner-main">
                @if($shopInfoArray['id'] != 0)
                    <img class="__shop-page-banner" alt=""
                         src="{{ getStorageImages(path: $shopInfoArray['banner_full_url'], type: 'wide-banner') }}">
                @else
                    @php($banner=getWebConfig(name: 'shop_banner'))
                    <img class="__shop-page-banner" alt=""
                         src="{{ getStorageImages(path: $banner, type: 'wide-banner') }}">
                @endif
                @include('web-views.seller-view.shop-info-card', ['displayClass' => 'd-none d-md-block max-width-500px'])

            </div>
        </div>

        @include('web-views.seller-view.shop-info-card', ['displayClass' => 'd-md-none border mt-3'])

        <form method="POST" action="{{ url()->current() }}" class="product-list-filter">
            <input hidden name="offer_type" value="{{ $data['offer_type'] }}">
            <input hidden name="data_from" value="{{ request('data_from') }}">
            <input hidden name="category_id" value="{{ request('category_id') }}">
            <input hidden name="brand_id" value="{{ request('brand_id') }}">

            @csrf
            @include('web-views.products.partials._product-list-header', [
                    'pageProductsCount' => $products->total(),
                    'searchBarSection' => true,
                    'sortBySection' => true,
                    'showProductsFilter' => true,
                    'shopViewPageHeader' => true,
            ])

            <div class="py-3 mb-2 mb-md-4 rtl __inline-35" dir="{{ session('direction') }}">
                <div class="row">
                    <aside class="col-lg-3 hidden-xs col-md-3 col-sm-4 SearchParameters __search-sidebar" id="SearchParameters">
                        <div class="cz-sidebar __inline-35 p-4 overflow-hidden" id="shop-sidebar">
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
                                        'productCategories' => $categories,
                                        'dataFrom' => 'shop-view',
                                        'shopSlug' => ($shopInfoArray['id'] != 0 ? $shopInfoArray['id'] : 0),
                                    ])
                                    @include('web-views.products.partials._filter-product-brands', [
                                        'productBrands' => $activeBrands,
                                        'dataFrom' => 'shop-view',
                                        'shopSlug' => ($shopInfoArray['id'] != 0 ? $shopInfoArray['id'] : 0),
                                    ])
                                    @include('web-views.products.partials._filter-publishing-houses', [
                                        'productPublishingHouses' => $shopPublishingHouses,
                                        'dataFrom' => 'shop-view',
                                        'shopSlug' => ($shopInfoArray['id'] != 0 ? $shopInfoArray['id'] : 0),
                                    ])
                                    @include('web-views.products.partials._filter-product-authors', [
                                        'productAuthors' => $digitalProductAuthors,
                                        'dataFrom' => 'shop-view',
                                        'shopSlug' => ($shopInfoArray['id'] != 0 ? $shopInfoArray['id'] : 0),
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


    <span id="shop-sort-by-filter-url" data-url="{{url('/')}}/shopView/{{$shopInfoArray['id']}}"></span>

    {{-- Modal --}}
    <div class="modal fade" id="exampleModal" tabindex="-1" role="dialog"
         aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header bg-faded-info">
                    <h5 class="modal-title" id="exampleModalLongTitle">{{translate('Send_Message_to_vendor')}}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form action="{{route('messages')}}" method="post" id="shop-view-chat-form">
                        @csrf
                        <input value="{{$shopInfoArray['id'] != 0 ? $shopInfoArray['id'] : 0}}" name="vendor_id" hidden>
                        <textarea name="message" class="form-control min-height-100px max-height-200px" required
                                  placeholder="{{ translate('Write_here') }}..."></textarea>
                        <br>
                        <div class="justify-content-end gap-2 d-flex flex-wrap">
                            <a href="{{route('chat', ['type' => 'vendor'])}}"
                               class="btn btn-soft-primary bg--secondary border">
                                {{translate('go_to_chatbox')}}
                            </a>
                            <button class="btn btn--primary text-white">
                                {{translate('send')}}
                            </button>
                        </div>
                    </form>
                </div>

            </div>
        </div>
    </div>

    <span id="products-search-data-backup"
          data-page="{{ request('page') ?? 1 }}"
          data-url="{{ route('shopView', ['id' => ($shopInfoArray['id'] != 0 ? $shopInfoArray['id'] : 0)]) }}"
          data-brand="{{ $data['brand_id'] ?? '' }}"
          data-category="{{ $data['category_id'] ?? '' }}"
          data-name="{{ request('search') ?? request('name') }}"
          data-from="{{ request('data_from') }}"
          data-offer-type = "{{ request('offer_type') }}"
          data-product-check="clearance_sale"
          data-sort="{{ request('sort_by') }}"
          data-product-type="{{ request('product_type') ?? 'all' }}"
          data-message="{{ translate('items_found') }}"
          data-publishing-house-id="{{ request('publishing_house_id') }}"
          data-author-id="{{ request('author_id') }}"
          data-offer="{{ request('offer_type') ?? '' }}"
    ></span>

@endsection
@push('script')
    <script src="{{ theme_asset(path: 'public/assets/front-end/js/product-list-filter.js') }}"></script>
    <script>
        $('.close-icon').on('click', function () {
            $("#shop-sidebar").toggleClass("show active");
        });
    </script>
@endpush


