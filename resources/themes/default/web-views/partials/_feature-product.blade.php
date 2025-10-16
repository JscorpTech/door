@php($overallRating = getOverallRating($product->reviews))
<div class="product-single-hover shadow-none rtl">
    <div class="overflow-hidden position-relative">
        <div class="inline_product clickable">
            @if(getProductPriceByType(product: $product, type: 'discount', result: 'value') > 0)
                <span class="for-discount-value p-1 pl-2 pr-2 font-bold fs-13">
                    <span class="direction-ltr d-block">
                       -{{ getProductPriceByType(product: $product, type: 'discount', result: 'string') }}
                    </span>
                </span>
            @else
                <span class="for-discount-value-null"></span>
            @endif
            <div class="p-10px pb-0">
                <a href="{{ route('product', $product->slug) }}" class="w-100 d-block">
                    <div style="aspect-ratio: 3 / 4; width: 100%; overflow: hidden; border-radius: 8px; display: flex; justify-content: center; align-items: center; background: #f9f9f9;">
                        <img 
                            alt="{{ $product->name ?? '' }}"
                            src="{{ getStorageImages(path: $product->thumbnail_full_url, type: 'product') }}"
                            style="max-width: 100%; max-height: 100%; object-fit: contain; object-position: center; transition: all 0.3s ease;"
                        >
                    </div>
                </a>
            </div>


            <div class="quick-view">
                <a class="btn-circle stopPropagation action-product-quick-view" href="javascript:" data-product-id="{{ $product->id }}">
                    <i class="czi-eye align-middle"></i>
                </a>
            </div>
            @if($product->product_type == 'physical' && $product->current_stock <= 0)
                <span class="out_fo_stock">{{translate('out_of_stock')}}</span>
            @endif
        </div>
        <div class="single-product-details">
            @if($overallRating[0] != 0 )
                <div class="rating-show justify-content-between">
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
                        <label class="badge-style">( {{ count($product->reviews) }} )</label>
                    </span>
                </div>
            @endif
            <h3 class="mb-1 letter-spacing-0">
                <a href="{{route('product',$product->slug)}}" class="text-capitalize fw-semibold">
                    {{ $product['name'] }}
                </a>
            </h3>
            <div class="justify-content-between">
                <h4 class="product-price lh-1 mb-0 letter-spacing-0">
                    @if(getProductPriceByType(product: $product, type: 'discount', result: 'value') > 0)
                        <del class="category-single-product-price">
                            {{ webCurrencyConverter(amount: $product->unit_price) }}
                        </del>
                    @endif
                    <span class="text-accent text-dark">
                       {{ getProductPriceByType(product: $product, type: 'discounted_unit_price', result: 'string') }}
                    </span>
                </h4>
            </div>
        </div>
    </div>
</div>

