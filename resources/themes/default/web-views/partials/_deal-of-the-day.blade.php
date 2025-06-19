@if(isset($product))
    <div class="container rtl">
        <div class="row g-4 pt-2 mt-0 pb-2 __deal-of align-items-start">
            <div class="col-xl-3 col-md-4">
                <div class="deal_of_the_day h-100 bg--light">
                    @if(isset($dealOfTheDay->product))
                        <div class="d-flex justify-content-center align-items-center py-4">
                            <h2 class="font-bold fs-16 m-0 align-items-center text-uppercase text-center px-2 web-text-primary h4">
                                {{ translate('deal_of_the_day') }}
                            </h2>
                        </div>
                        <div class="recommended-product-card mt-0 min-height-auto">
                            <div class="d-flex justify-content-center align-items-center __pt-20 __m-20-r">
                                <div class="position-relative">
                                    <img class="__rounded-top aspect-1 h-auto" alt=""
                                         src="{{ getStorageImages(path: $dealOfTheDay?->product?->thumbnail_full_url, type: 'product') }}">
                                    @if(getProductPriceByType(product: $dealOfTheDay?->product, type: 'discount', result: 'value') > 0)
                                        <span class="for-discount-value p-1 pl-2 pr-2 font-bold fs-13">
                                            <span class="direction-ltr d-block">
                                                -{{ getProductPriceByType(product: $dealOfTheDay?->product, type: 'discount', result: 'string') }}
                                            </span>
                                        </span>
                                    @endif
                                </div>
                            </div>
                            <div class="__i-1 bg-transparent text-center mb-0">
                                <div class="px-0">
                                    @php($overallRating = getOverallRating($dealOfTheDay->product['reviews']))
                                    @if($overallRating[0] != 0 )
                                        <div class="rating-show">
                                            <span class="d-inline-block font-size-sm text-body">
                                                @for($inc=1;$inc<=5;$inc++)
                                                    @if ($inc <= (int)$overallRating[0])
                                                        <i class="tio-star text-warning"></i>
                                                    @elseif ($overallRating[0] != 0 && $inc <= (int)$overallRating[0] + 1.1)
                                                        <i class="tio-star-half text-warning"></i>
                                                    @else
                                                        <i class="tio-star-outlined text-warning"></i>
                                                    @endif
                                                @endfor
                                                <label class="badge-style">( {{ count($dealOfTheDay->product['reviews']) }} )</label>
                                            </span>
                                        </div>
                                    @endif
                                    <h3 class="font-semibold pt-1 h6">
                                        {{ Str::limit($dealOfTheDay->product['name'], 80) }}
                                    </h3>
                                    <h4 class="mb-4 pt-1 d-flex flex-wrap justify-content-center align-items-center text-center gap-8 lh-1 letter-spacing-0">

                                        @if(getProductPriceByType(product: $dealOfTheDay?->product, type: 'discount', result: 'value') > 0)
                                            <del class="fs-14 font-semibold __color-9B9B9B">
                                                {{ webCurrencyConverter(amount: $dealOfTheDay?->product?->unit_price) }}
                                            </del>
                                        @endif
                                        <span class="text-accent fs-18 font-bold text-dark">
                                            {{ getProductPriceByType(product: $dealOfTheDay?->product, type: 'discounted_unit_price', result: 'string') }}
                                        </span>
                                    </h4>
                                    <button class="btn btn--primary font-bold px-4 rounded-10 text-uppercase get-view-by-onclick"
                                            data-link="{{ route('product',$dealOfTheDay->product->slug) }}">
                                            {{translate('buy_now')}}
                                    </button>

                                </div>
                            </div>
                        </div>
                    @elseif (isset($recommendedProduct->discount_type))
                        <div class="d-flex justify-content-center align-items-center py-4">
                            <h2 class="font-bold fs-16 m-0 align-items-center text-uppercase text-center px-2 web-text-primary h4">
                                {{ translate('recommended_product') }}
                            </h2>
                        </div>
                        <div class="recommended-product-card mt-0">

                            <div class="d-flex justify-content-center align-items-center __pt-20 __m-20-r">
                                <div class="position-relative">
                                    <img src="{{ getStorageImages(path: $recommendedProduct?->thumbnail_full_url, type: 'product') }}"
                                        alt="">
                                    @if($recommendedProduct->discount > 0)
                                        <span class="for-discount-value p-1 pl-2 pr-2 font-bold fs-13">
                                            <span class="direction-ltr d-block">
                                                @if ($recommendedProduct->discount_type == 'percent')
                                                    -{{ round($recommendedProduct->discount,(!empty($decimal_point_settings) ? $decimal_point_settings: 0))}}%
                                                @elseif($recommendedProduct->discount_type =='flat')
                                                    -{{ webCurrencyConverter(amount: $recommendedProduct->discount) }}
                                                @endif
                                            </span>
                                        </span>
                                    @endif
                                </div>
                            </div>
                            <div class="__i-1 bg-transparent text-center mb-0 min-height-auto">
                                <div class="px-0 pb-0">
                                    @php($overallRating = getOverallRating($recommendedProduct['reviews']))
                                    @if($overallRating[0] != 0 )
                                        <div class="rating-show">
                                            <span class="d-inline-block font-size-sm text-body">
                                                @for($inc=0;$inc<5;$inc++)
                                                    @if ($inc <= (int)$overallRating[0])
                                                        <i class="tio-star text-warning"></i>
                                                    @elseif ($overallRating[0] != 0 && $inc <= (int)$overallRating[0] + 1.1 && $overallRating[0] > ((int)$overallRating[0]))
                                                        <i class="tio-star-half text-warning"></i>
                                                    @else
                                                        <i class="tio-star-outlined text-warning"></i>
                                                    @endif
                                                @endfor
                                                <label class="badge-style">( {{ count($recommendedProduct->reviews) }} )</label>
                                            </span>

                                        </div>
                                    @endif
                                    <h3 class="font-semibold pt-1 h6">
                                        {{ Str::limit($recommendedProduct['name'],30) }}
                                    </h3>
                                    <h4 class="mb-4 pt-1 d-flex flex-wrap justify-content-center align-items-center text-center gap-8 lh-1 letter-spacing-0">
                                        @if($recommendedProduct->discount > 0)
                                            <del class="__text-12px __color-9B9B9B">
                                                {{ webCurrencyConverter(amount: $recommendedProduct->unit_price) }}
                                            </del>
                                        @endif
                                        <span class="text-accent __text-22px text-dark">
                                            {{ webCurrencyConverter(amount:
                                                $recommendedProduct->unit_price-(getProductDiscount(product: $recommendedProduct, price: $recommendedProduct->unit_price))
                                            ) }}
                                        </span>
                                    </h4>
                                    <button class="btn btn--primary font-bold px-4 rounded-10 text-uppercase get-view-by-onclick"
                                            data-link="{{ route('product',$recommendedProduct->slug) }}">
                                        {{translate('buy_now')}}
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <div class="col-xl-9 col-md-8">
                <div class="latest-product-margin">
                    <div class="d-flex justify-content-between align-items-baseline mb-14px">
                        <h2 class="text-center mb-0">
                            <span class="for-feature-title __text-22px font-bold text-center">
                                {{ translate('latest_products')}}
                            </span>
                        </h2>
                        <div class="mr-1">
                            <a class="text-capitalize view-all-text web-text-primary"
                               href="{{route('products',['data_from'=>'latest'])}}">
                                {{ translate('view_all')}}
                                <i class="czi-arrow-{{Session::get('direction') === "rtl" ? 'left mr-1 ml-n1 mt-1 float-left' : 'right ml-1 mr-n1'}}"></i>
                            </a>
                        </div>
                    </div>

                    <div class="row mt-0 g-2">
                        @foreach($latestProductsList as $product)
                            <div class="col-xl-3 col-sm-4 col-md-6 col-lg-4 col-6">
                                <div>
                                    @include('web-views.partials._inline-single-product',['product'=>$product,'decimal_point_settings'=>$decimal_point_settings])
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif
