<div class="col-lg-6 px-max-md-0">
    <div class="card card __shadow h-100">
        <div class="card-body p-xl-35">
            <div class="row d-flex justify-content-between align-items-center mx-1 mb-3">
                <div class="d-flex gap-1 align-items-center">
                    <img class="size-30" src="{{theme_asset(path: "public/assets/front-end/png/top-rated.png")}}"
                         alt="">
                    <h2 class="font-bold pl-1 mb-0 fs-16">{{ translate('top_rated')}}</h2>
                </div>
                <div>
                    <a class="text-capitalize view-all-text web-text-primary"
                       href="{{route('products',['data_from'=>'top-rated','page'=>1])}}">{{ translate('view_all')}}
                        <i class="czi-arrow-{{Session::get('direction') === "rtl" ? 'left mr-1 ml-n1 mt-1 float-left' : 'right ml-1 mr-n1'}}"></i>
                    </a>
                </div>
            </div>
            <div class="row g-3">
                @foreach($topRatedProducts as $key => $product)
                    @if($key < 6)
                        <div class="col-sm-6">
                            <a class="__best-selling" href="{{route('product', $product->slug)}}">
                                @if(getProductPriceByType(product: $product, type: 'discount', result: 'value') > 0)
                                    <div class="d-flex">
                                    <span class="for-discount-value p-1 pl-2 pr-2 font-bold fs-13">
                                        <span class="direction-ltr d-block">
                                            -{{ getProductPriceByType(product: $product, type: 'discount', result: 'string') }}
                                        </span>
                                    </span>
                                    </div>
                                @endif
                                <div class="d-flex flex-wrap">
                                    <div class="top-rated-image">
                                        <img class="rounded"
                                             src="{{ getStorageImages(path: $product->thumbnail_full_url, type: 'product') }}"
                                             alt="{{ translate('product') }}"/>
                                    </div>
                                    <div class="top-rated-details">
                                        <h3 class="widget-product-title h6">
                                            <span class="ptr">
                                                {{ Str::limit($product['name'],100) }}
                                            </span>
                                        </h3>
                                        @php($overallRating = getOverallRating($product['reviews']))
                                        @if($overallRating[0] != 0 )
                                            <div class="rating-show">
                                                <span class="d-inline-block font-size-sm text-body">
                                                    @for ($inc = 1; $inc <= 5; $inc++)
                                                        @if ($inc <= (int)$overallRating[0])
                                                            <i class="sr-star czi-star-filled "></i>
                                                        @elseif ($overallRating[0] != 0 && $inc <= (int)$overallRating[0] + 1.1)
                                                            <i class="tio-star-half text-warning"></i>
                                                        @else
                                                            <i class="sr-star czi-star "></i>
                                                        @endif
                                                    @endfor
                                                    <label class="badge-style">
                                                        ( {{ count($product['reviews']) }} )
                                                    </label>
                                                </span>
                                            </div>
                                        @endif
                                        <h4 class="widget-product-meta d-flex flex-wrap gap-8 align-items-center row-gap-0 mb-0 letter-spacing-0">
                                            <span>
                                                @if(getProductPriceByType(product: $product, type: 'discount', result: 'value') > 0)
                                                    <del class="__text-12px __color-9B9B9B">
                                                        {{ webCurrencyConverter(amount: $product->unit_price) }}
                                                    </del>
                                                @endif
                                            </span>
                                            <span class="text-accent text-dark">
                                               {{ getProductPriceByType(product: $product, type: 'discounted_unit_price', result: 'string') }}
                                            </span>
                                        </h4>
                                    </div>
                                </div>
                            </a>
                        </div>
                    @endif
                @endforeach
            </div>
        </div>
    </div>
</div>
