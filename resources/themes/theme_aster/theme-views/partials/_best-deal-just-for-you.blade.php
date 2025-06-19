@php use App\Utils\Helpers;use App\Utils\ProductManager;use Illuminate\Support\Str; @endphp
<section>
    <div class="container">
        <div class="row g-3">
            @if(isset($dealOfTheDay->product))
                <div class="col-lg-6">
                    <div class="card h-100">
                        <div class="p-30">
                            @php($overall_rating = getOverallRating($dealOfTheDay->product->reviews))
                            <div class="today-best-deal d-flex justify-content-between gap-2 gap-sm-3">
                                <div class="d-flex flex-column gap-1 max-w-280px">
                                    <div class="mb-3 mb-sm-4">
                                        <div
                                            class="sub-title text-muted mb-1 text-capitalize">{{ translate('do_not_miss_the_chance').'!' }}
                                        </div>
                                        <h2 class="title text-primary fw-extra-bold text-capitalize">{{ translate('todays_best_deal') }}</h2>
                                    </div>
                                    <div class="mb-3 mb-sm-4 d-flex flex-column gap-1">
                                        <h6 class="text-capitalize line-limit-2">{{ $dealOfTheDay->product->name }}</h6>
                                        <div class="d-flex gap-2 align-items-center">
                                            <div class="star-rating text-gold fs-12">
                                                @for ($i = 1; $i <= 5; $i++)
                                                    @if ($i <= $overall_rating[0])
                                                        <i class="bi bi-star-fill"></i>
                                                    @elseif ($overall_rating[0] != 0 && $i <= $overall_rating[0] + 1.1)
                                                        <i class="bi bi-star-half"></i>
                                                    @else
                                                        <i class="bi bi-star"></i>
                                                    @endif
                                                @endfor
                                            </div>
                                            <span>({{ $dealOfTheDay->product->reviews->count() }})</span>
                                        </div>

                                        <div class="product__price d-flex flex-wrap align-items-end gap-2 mt-2">
                                            @if(getProductPriceByType(product: $dealOfTheDay->product, type: 'discount', result: 'value') > 0)
                                                <del
                                                    class="product__old-price">{{webCurrencyConverter($dealOfTheDay->product->unit_price)}}</del>
                                            @endif
                                            <ins class="product__new-price">
                                                {{ getProductPriceByType(product: $dealOfTheDay->product, type: 'discounted_unit_price', result: 'string') }}
                                            </ins>
                                        </div>
                                        <div class="mt-xl-2">
                                            <span class="product__save-amount">{{ translate('save') }}
                                                {{ getProductPriceByType(product: $dealOfTheDay->product, type: 'discount', result: 'string') }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="d-flex">
                                        <a href="{{route('product',$dealOfTheDay->product->slug)}}"
                                           class="btn btn-primary text-capitalize">{{ translate('buy_now') }}</a>
                                    </div>
                                </div>
                                <div class="text-center">
                                    <img width="309" alt="" class="dark-support rounded"
                                         src="{{ getStorageImages(path: $dealOfTheDay?->product?->thumbnail_full_url, type: 'product') }}">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @elseif (isset($recommendedProduct->discount_type))
                <div class="col-lg-6">
                    <div class="card h-100">
                        <div class="p-30">
                            @php($overall_rating = getOverallRating($recommendedProduct->reviews))
                            <div class="today-best-deal d-flex justify-content-between gap-2 gap-sm-3">
                                <div class="d-flex flex-column gap-1 max-w-280px">
                                    <div class="mb-3 mb-sm-4">
                                        <div
                                            class="sub-title text-muted mb-1 text-capitalize">{{ translate('do_not_miss_the_chance').'!' }}
                                        </div>
                                        <h2 class="title text-primary fw-extra-bold text-capitalize">{{ translate('todays_best_deal') }}</h2>
                                    </div>
                                    <div class="mb-3 mb-sm-4 d-flex flex-column gap-1">
                                        <h6 class="text-capitalize line-limit-2">{{ $recommendedProduct->name }}</h6>
                                        <div class="d-flex gap-2 align-items-center">
                                            <div class="star-rating text-gold fs-12">
                                                @for ($i = 1; $i <= 5; $i++)
                                                    @if ($i <= $overall_rating[0])
                                                        <i class="bi bi-star-fill"></i>
                                                    @elseif ($overall_rating[0] != 0 && $i <= $overall_rating[0] + 1.1)
                                                        <i class="bi bi-star-half"></i>
                                                    @else
                                                        <i class="bi bi-star"></i>
                                                    @endif
                                                @endfor
                                            </div>
                                            <span>({{ $recommendedProduct->reviews->count() }})</span>
                                        </div>

                                        <div class="product__price d-flex flex-wrap align-items-end gap-2 mt-2">
                                            @if(getProductPriceByType(product: $recommendedProduct, type: 'discount', result: 'value') > 0)
                                                <del class="product__old-price">{{webCurrencyConverter($recommendedProduct->unit_price)}}</del>
                                            @endif
                                            <ins class="product__new-price">
                                                {{ getProductPriceByType(product: $recommendedProduct, type: 'discounted_unit_price', result: 'string') }}
                                            </ins>
                                        </div>
                                        <div class="mt-xl-2">
                                        <span class="product__save-amount">{{ translate('save') }}
                                            {{ getProductPriceByType(product: $recommendedProduct, type: 'discount', result: 'string') }}
                                        </span>
                                        </div>
                                    </div>
                                    <div class="d-flex">
                                        <a href="{{route('product', $recommendedProduct->slug)}}"
                                           class="btn btn-primary text-capitalize">{{ translate('buy_now') }}</a>
                                    </div>
                                </div>
                                <div class="text-center">
                                    <img width="309" alt="" class="dark-support rounded"
                                         src="{{ getStorageImages(path: $recommendedProduct['thumbnail_full_url'], type: 'product') }}">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <div class="{{ (isset($dealOfTheDay->product) || isset($recommendedProduct->discount_type)) ? 'col-lg-6' : 'col-lg-12' }}">
                <div class="card h-100">
                    <div class="p-30">
                        <div class="d-flex flex-wrap justify-content-between gap-3 mb-3 align-items-center">
                            <h3 class="mb-1">
                                <span class="text-primary">{{translate('just')}}</span>
                                {{translate('for_you')}}
                            </h3>
                        </div>
                        <div class="auto-col just-for-you gap-3">
                            @foreach($justForYouProducts as $key => $product)
                                <a href="{{route('product',$product->slug)}}"
                                   class="hover-zoom-in d-flex flex-column gap-2 align-items-center">
                                    <div class="position-relative">
                                        @if(getProductPriceByType(product: $product, type: 'discount', result: 'value') > 0)
                                            <span class="product__discount-badge">
                                                <span>
                                                    -{{ getProductPriceByType(product: $product, type: 'discount', result: 'string') }}
                                                </span>
                                            </span>
                                        @endif
                                        <img width="100" alt="" loading="lazy" class="dark-support rounded aspect-1"
                                             src="{{ getStorageImages(path:$product->thumbnail_full_url, type: 'product') }}">
                                    </div>
                                    <div class="product__price d-flex flex-wrap justify-content-center column-gap-2">
                                        @if(getProductPriceByType(product: $product, type: 'discount', result: 'value') > 0)
                                            <del class="product__old-price">
                                                {{webCurrencyConverter($product->unit_price)}}
                                            </del>
                                        @endif
                                        <ins class="product__new-price">
                                            {{ getProductPriceByType(product: $product, type: 'discounted_unit_price', result: 'string') }}
                                        </ins>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
