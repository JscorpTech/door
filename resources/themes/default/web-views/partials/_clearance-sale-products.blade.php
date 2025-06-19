@if (count($clearanceSaleProducts) > 0)
<section class="container rtl pb-4 px-max-sm-0">
    <div class="__shadow-2">
        <div class="__p-20px rounded bg-white overflow-hidden">
            <div class="d-flex __gap-6px flex-between align-items-center">
                <div>
                    <div class="clearance-sale-title-bg" data-bg-img="{{ theme_asset(path: 'public/assets/front-end/img/media/clearance-sale-title-bg.svg') }}">
                        <h2 class="sub-title mb-0 letter-spacing-0">
                            <span>{{ translate('Save_More') }}</span>
                        </h2>
                        <h3 class="title mb-0 letter-spacing-0">
                            <span>{{ translate('Clearance_Sale') }}</span>
                        </h3>
                    </div>
                </div>
                <div>
                    <a class="text-capitalize view-all-text text-nowrap web-text-primary"
                       href="{{ route('products', ['offer_type' => 'clearance_sale', 'page'=> 1]) }}">
                        {{ translate('view_all')}}
                        <i class="czi-arrow-{{Session::get('direction') === "rtl" ? 'left mr-1 ml-n1 mt-1 float-left' : 'right ml-1 mr-n1'}}"></i>
                    </a>
                </div>
            </div>

            <div class="mt-2">
                <div class="carousel-wrap-2 d-none d-sm-block">
                    <div class="owl-carousel owl-theme category-wise-product-slider clearance-sale-slider"
                         data-loop="{{ count($clearanceSaleProducts) >= 6 ? 'true' : 'false' }}">
                        @foreach($clearanceSaleProducts as $key => $product)
                            @include('web-views.partials._filter-single-product', ['product'=> $product])
                        @endforeach
                    </div>
                </div>
                <div class="d-sm-none">
                    <div class="row g-2 h-100">
                        @foreach($clearanceSaleProducts as $key => $product)
                            @if(count($clearanceSaleProducts) >= 4 ? ($key < 4) : ($key < 2))
                                <div class="col-6">
                                    @include('web-views.partials._filter-single-product', ['product' => $product])
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endif
