@php use App\Utils\BrandManager;use App\Utils\CategoryManager; @endphp
@extends('theme-views.layouts.app')

@section('title', translate(str_replace(['-', '_', '/'],' ',$data['data_from'])).' '.translate('products'))

@push('css_or_js')
    <meta property="og:image" content="{{$web_config['web_logo']['path']}}"/>
    <meta property="og:title" content="Products of {{$web_config['company_name']}} "/>
    <meta property="og:url" content="{{env('APP_URL')}}">
    <meta property="og:description" content="{{ $web_config['meta_description'] }}">
    <meta property="twitter:card" content="{{$web_config['web_logo']['path']}}"/>
    <meta property="twitter:title" content="Products of {{$web_config['company_name']}}"/>
    <meta property="twitter:url" content="{{env('APP_URL')}}">
    <meta property="twitter:description" content="{{ $web_config['meta_description'] }}">
@endpush

@section('content')
    <main class="main-content d-flex flex-column gap-3 pt-3">
        <section>
            <div class="container">

                <form method="POST" action="{{ route('products') }}" class="product-list-filter">
                    @csrf
                    <input hidden name="offer_type" value="{{ $data['offer_type'] }}">
                    <input hidden name="data_from" value="{{ request()->is('flash-deals*') ? 'flash-deals' : request('data_from') }}">

                    @include('theme-views.product.partials._product-list-header', [
                        'pageTitleContent' => $pageTitleContent,
                        'pageProductsCount' => $products->total(),
                        'searchBarSection' => true,
                        'sortBySection' => true,
                        'showProductsFilter' => true,
                    ])

                    <div class="flexible-grid lg-down-1 gap-3 width--16rem">
                    <div class="card filter-toggle-aside mb-5">
                        <div class="d-flex d-lg-none pb-0 p-3 justify-content-end">
                            <button class="filter-aside-close border-0 bg-transparent">
                                <i class="bi bi-x-lg"></i>
                            </button>
                        </div>
                        <div class="card-body d-flex flex-column gap-4">
                            @include('theme-views.product.partials._filter-product-type')
                            @include('theme-views.product.partials._filter-product-price')
                            @include('theme-views.product.partials._filter-product-categories', [
                                'productCategories' => $categories,
                                'dataFrom' => 'flash-deals',
                            ])
                            @include('theme-views.product.partials._filter-product-brands', [
                                'productBrands' => $activeBrands,
                                'dataFrom' => 'flash-deals',
                            ])
                            @include('theme-views.product.partials._filter-publishing-houses', [
                                'productPublishingHouses' => $web_config['publishing_houses'],
                                'dataFrom' => 'flash-deals',
                            ])
                            @include('theme-views.product.partials._filter-product-authors', [
                                'productAuthors' => $web_config['digital_product_authors'],
                                'dataFrom' => 'flash-deals',
                            ])

                            @include('theme-views.product.partials._filter-product-reviews', [
                                'productRatings' => $ratings,
                                'selectedRatings' => $selectedRatings
                            ])
                        </div>
                    </div>

                    <div class="">
                        <div class="d-flex flex-wrap flex-lg-nowrap align-items-start justify-content-between gap-3 mb-2">

                            <div class="d-flex gap-10 align-items-center overflow-hidden product-list-selected-tags">
                                @include("theme-views.product._selected_filter_tags")
                            </div>

                            <div class="d-flex align-items-center mb-3 mb-md-0 flex-wrap flex-md-nowrap gap-3">
                                <ul class="product-view-option option-select-btn gap-3">
                                    <li>
                                        <label>
                                            <input type="radio" name="product_view" value="grid-view" hidden=""
                                                   {{(!session('product_view_style') || session('product_view_style') == 'grid-view' ? 'checked' : '' )}}>
                                            <span class="py-2 d-flex align-items-center gap-2 text-capitalize">
                                                <i class="bi bi-grid-fill"></i>
                                                <span class="text-nowrap">{{ translate('grid_view') }}</span>
                                            </span>
                                        </label>
                                    </li>
                                    <li>
                                        <label>
                                            <input type="radio" name="product_view" value="list-view" hidden=""
                                                   {{(session('product_view_style') == 'list-view'?'checked':'')}}>
                                            <span class="py-2 d-flex align-items-center gap-2 text-capitalize">
                                                <i class="bi bi-list-ul"></i>
                                                <span class="text-nowrap">{{ translate('list_view') }}</span>
                                            </span>
                                        </label>
                                    </li>
                                </ul>
                                <button class="toggle-filter square-btn btn btn-outline-primary rounded d-lg-none">
                                    <i class="bi bi-funnel"></i>
                                </button>
                            </div>
                        </div>
                        @php($decimal_point_settings = getWebConfig(name: 'decimal_point_settings'))
                        <div id="ajax-products-view">
                            @include('theme-views.product._ajax-products',['products'=>$products,'decimal_point_settings'=>$decimal_point_settings])
                        </div>
                    </div>
                </div>
                </form>
            </div>
        </section>
    </main>

    <span id="filter-url" data-url="{{url('/').'/products'}}"></span>
    <span id="product-view-style-url" data-url="{{ route('product_view_style') }}"></span>

@endsection

@push('script')
    <script src="{{ theme_asset(path: 'assets/js/product-view.js') }}"></script>
@endpush
