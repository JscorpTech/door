@extends('theme-views.layouts.app')

@section('title', translate('flash_Deal_Products').' | '.$web_config['company_name'].' '.translate('ecommerce'))

@section('content')
    <main class="main-content d-flex flex-column gap-3 py-3">
        <section>
            <div class="container">

                <form method="POST" action="{{ url()->current() }}" class="product-list-filter">
                    @csrf

                    <input hidden name="offer_type" value="{{ request()->is('flash-deals*') ? 'flash-deals' : request('offer_type') }}">
                    <input hidden name="flash_deals_id" value="{{ $web_config['flash_deals']['id'] }}">

                    <div class="lg-down-1 gap-3 wid width--16rem">
                        <div class="">
                            <div class="card my-3">
                                <div class=" d-flex gap-4 flex-column flex-md-row align-items-center justify-content-between flex-wrap px-4 pt-4 pb-4">
                                    <div class="flash-deal-countdown text-center ">
                                        <div class="mb-2 text-primary">
                                            <img width="122" src="{{ theme_asset('assets/img/media/flash-deal.svg') }}" loading="lazy" alt="" class="dark-support svg">
                                        </div>
                                        <div class="d-flex justify-content-center  align-items-end gap-2">
                                            <h2 class="text-primary fw-medium">{{ translate('hurry_up').'!' }}</h2>
                                            <div class="text-muted">{{ translate('offer_ends_in').':' }}</div>
                                        </div>
                                    </div>
                                    <div class="countdown-timer justify-content-center d-flex gap-3 gap-sm-4 flex-wrap align-content-center order-lg-last" data-date="{{$web_config['flash_deals']?$web_config['flash_deals']['end_date']:''}}">
                                        <div class="days d-flex flex-column gap-2 gap-sm-3 text-center"></div>
                                        <div class="hours d-flex flex-column gap-2 gap-sm-3 text-center"></div>
                                        <div class="minutes d-flex flex-column gap-2 gap-sm-3 text-center"></div>
                                        <div class="seconds d-flex flex-column gap-2 gap-sm-3 text-center"></div>
                                    </div>
                                    <div class="search-box search-box-2 position-relative">
                                        <div class="d-flex">
                                            <div class="select-wrap focus-border border border--gray border-end-logical-0 d-flex align-items-center">
                                                <input
                                                    type="search"
                                                    class="form-control border-0 focus-input search-page-button-input" name="product_name"
                                                    value="{{ request('product_name') }}"
                                                    placeholder="{{ translate('search_for_items').'...' }}"
                                                />
                                            </div>
                                            <input name="page" value="1" hidden>
                                            <button type="submit" class="btn btn-primary search-page-button" aria-label="{{ translate('Search') }}">
                                                <i class="bi bi-search"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="flexible-grid lg-down-1 gap-3 width--16rem">
                        <div class="card filter-toggle-aside mb-5">
                            <div class="d-flex d-lg-none pb-0 p-3 justify-content-end">
                                <button class="filter-aside-close border-0 bg-transparent">
                                    <i class="bi bi-x-lg"></i>
                                </button>
                            </div>
                            <div class="card-body d-flex flex-column gap-4">
                                @include('theme-views.product.partials._filter-product-filter')
                                @include('theme-views.product.partials._filter-product-type')
                                @include('theme-views.product.partials._filter-product-price')
                                @include('theme-views.product.partials._filter-product-categories', [
                                    'productCategories' => $productCategories,
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
                            <div class="d-flex flex-wrap flex-lg-nowrap align-items-start justify-content-between gap-4 mb-2">

                                <div class="d-flex gap-10 align-items-center overflow-hidden product-list-selected-tags"></div>

                                <div class="d-flex align-items-center mb-3 mb-md-0 flex-wrap flex-md-nowrap gap-3">
                                    <ul class="product-view-option option-select-btn gap-3 text-nowrap">
                                        <li>
                                            <label>
                                                <input type="radio" name="product_view" value="grid-view" hidden=""
                                                       {{ session('product_view_style') == 'grid-view' ? 'checked' : '' }}>
                                                <span class="py-2 d-flex align-items-center gap-2 text-capitalize">
                                                    <i class="bi bi-grid-fill"></i> {{translate('grid_view')}}
                                                </span>
                                            </label>
                                        </li>
                                        <li>
                                            <label>
                                                <input type="radio" name="product_view" value="list-view" hidden=""
                                                       {{ session('product_view_style') == 'list-view' ? 'checked' : '' }}>
                                                <span class="py-2 d-flex align-items-center gap-2 text-capitalize"><i
                                                        class="bi bi-list-ul"></i> {{translate('list_view')}}</span>
                                            </label>
                                        </li>
                                    </ul>
                                    <button class="toggle-filter square-btn btn btn-outline-primary rounded d-lg-none">
                                        <i class="bi bi-funnel"></i>
                                    </button>
                                </div>
                            </div>
                            <div id="ajax-products-view">
                                @include('theme-views.product._ajax-products', ['products' => $products])
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </section>
    </main>
@endsection

@push('script')
    <script src="{{ theme_asset(path: 'assets/js/product-view.js') }}"></script>
@endpush
