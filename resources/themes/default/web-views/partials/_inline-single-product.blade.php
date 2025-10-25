@php($overallRating = getOverallRating($product->reviews))

<div class="product-single-hover style--card">
    <div class="overflow-hidden position-relative">
        <div class=" inline_product clickable d-flex justify-content-center">
            @if(getProductPriceByType(product: $product, type: 'discount', result: 'value') > 0)
                <div class="d-flex">
                    <span class="for-discount-value p-1 pl-2 pr-2 font-bold fs-13">
                        <span class="direction-ltr d-block">
                            -{{ getProductPriceByType(product: $product, type: 'discount', result: 'string') }}
                        </span>
                    </span>
                </div>
            @else
                <div class="d-flex justify-content-end">
                    <span class="for-discount-value-null"></span>
                </div>
            @endif
           <div>
    <a href="{{ route('product', $product->slug) }}" class="d-block w-100" style="position: relative; display: block;">
        <div style="
            width: 100%;
            aspect-ratio: 3 / 4;
            overflow: hidden;
            border-radius: 12px;
            background: #fff;
            box-shadow: 0 2px 6px rgba(0,0,0,0.05);
            position: relative;
        ">
            <img 
                alt="{{ $product->name ?? '' }}"
                src="{{ getStorageImages(path: $product->thumbnail_full_url, type: 'product') }}"
                style="
                    width: 100%;
                    height: 100%;
                    object-fit: cover;
                    object-position: center;
                    display: block;
                    image-rendering: auto;
                    transition: transform 0.3s ease;
                "
            >

            <!-- Brend logotipi: pastki o'ng -->
            <div style="
                position: absolute;
                bottom: 10px;
                right: 10px;
                width: 60px;       /* kattaroq o'lcham */
                height: 60px;
                border-radius: 50%;
                overflow: hidden;
                border: 2px solid #fff;
                box-shadow: 0 2px 8px rgba(0,0,0,0.2);
                background: #fff;
            ">
                <img 
                    src="{{ getStorageImages(path: $product?->seller?->shop->image_full_url, type: 'shop') }}" 
                    alt="Brand" 
                    style="width: 100%; height: 100%; object-fit: cover;"
                >
            </div>
        </div>
    </a>
</div>



            <div class="quick-view">
                <a class="btn-circle stopPropagation action-product-quick-view" href="{{route('product',$product->slug)}}" >
                    <i class="czi-eye align-middle"></i>
                </a>
            </div>
            @if($product->product_type == 'physical' && $product->current_stock <= 0)
                <span class="out_fo_stock">{{translate('out_of_stock')}}</span>
            @endif
        </div>
        <div class="single-product-details">
            @if($overallRating[0] != 0 )
                <div class="rating-show justify-content-between text-center">
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
            <h3 class="text-center mb-1 letter-spacing-0">
                <a href="{{route('product',$product->slug)}}">
                    {{ $product['name'] }}
                </a>
            </h3>
            <div class="justify-content-between text-center">
                <h4 class="product-price text-center d-flex flex-wrap justify-content-center align-items-center gap-8 mb-0 letter-spacing-0">
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


