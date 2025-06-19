<div class="col-lg-6 px-max-md-0">
    <div class="card card __shadow h-100">
        <div class="card-body p-xl-35">
            <div class="row d-flex justify-content-between align-items-center mx-1 mb-3">
                <div class="d-flex gap-1 align-items-center">
                    <img class="size-30" src="{{theme_asset(path: "public/assets/front-end/png/best-sellings.png")}}"
                         alt="">
                    <h2 class="font-bold pl-1 mb-0 fs-16">{{ translate('best_sellings')}}</h2>
                </div>
                <div>
                    <a class="text-capitalize view-all-text web-text-primary"
                       href="{{route('products',['data_from'=>'best-selling','page'=>1])}}">{{ translate('view_all')}}
                        <i class="czi-arrow-{{Session::get('direction') === "rtl" ? 'left mr-1 ml-n1 mt-1 float-left' : 'right ml-1 mr-n1'}}"></i>
                    </a>
                </div>
            </div>
            <div class="row g-3">
                @foreach($bestSellProduct as $key=> $bestSellItem)
                    @if($bestSellItem && $key<6)
                        <div class="col-sm-6">
                            <a class="__best-selling" href="{{route('product',$bestSellItem->slug)}}">
                                @if(getProductPriceByType(product: $bestSellItem, type: 'discount', result: 'value') > 0)
                                    <div class="d-flex">
                                        <span class="for-discount-value p-1 pl-2 pr-2 font-bold fs-13">
                                            <span class="direction-ltr d-block">
                                                -{{ getProductPriceByType(product: $bestSellItem, type: 'discount', result: 'string') }}
                                            </span>
                                        </span>
                                    </div>
                                @endif
                                <div class="d-flex flex-wrap">
                                    <div class="best-selleing-image">
                                        <img class="rounded"
                                             src="{{ getStorageImages(path: $bestSellItem?->thumbnail_full_url, type: 'product') }}"
                                             alt="{{ translate('product') }}"/>
                                    </div>
                                    <div class="best-selling-details">
                                        <h3 class="widget-product-title h6">
                                        <span class="ptr fw-semibold">
                                            {{ Str::limit($bestSellItem['name'],100) }}
                                        </span>
                                        </h3>
                                        @php($overallRating = getOverallRating($bestSellItem['reviews']))
                                        @if($overallRating[0] != 0 )
                                            <div class="rating-show">
                                            <span class="d-inline-block font-size-sm text-body">
                                                @for($inc=1;$inc<=5;$inc++)
                                                    @if ($inc <= (int)$overallRating[0])
                                                        <i class="tio-star text-warning"></i>
                                                    @elseif ($overallRating[0] != 0 && $inc <= (int)$overallRating[0] + 1.1 && $overallRating[0] > ((int)$overallRating[0]))
                                                        <i class="tio-star-half text-warning"></i>
                                                    @else
                                                        <i class="tio-star-outlined text-warning"></i>
                                                    @endif
                                                @endfor
                                                <label class="badge-style">( {{ count($bestSellItem['reviews']) }} )</label>
                                            </span>
                                            </div>
                                        @endif
                                        <h4 class="widget-product-meta d-flex flex-wrap gap-8 align-items-center row-gap-0 mb-0 letter-spacing-0">
                                            <span>
                                                @if(getProductPriceByType(product: $bestSellItem, type: 'discount', result: 'value') > 0)
                                                    <del class="__color-9B9B9B __text-12px">
                                                        {{ webCurrencyConverter(amount: $bestSellItem->unit_price) }}
                                                    </del>
                                                @endif
                                            </span>
                                            <span class="text-accent text-dark">
                                                {{ getProductPriceByType(product: $bestSellItem, type: 'discounted_unit_price', result: 'string') }}
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
