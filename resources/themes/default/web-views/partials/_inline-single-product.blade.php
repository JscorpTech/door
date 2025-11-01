@php($overallRating = getOverallRating($product->reviews))
<div class="product-single-hover style--card" style="margin:0; padding:0;">
    <div class="overflow-hidden position-relative" style="margin:0; padding:0;">
        <div class="inline_product clickable d-flex justify-content-center" style="margin:0; padding:0; gap:0;">
            @if(getProductPriceByType(product: $product, type: 'discount', result: 'value') > 0)
                <div class="d-flex" style="margin:0; padding:0;">
                    <span class="for-discount-value font-bold fs-13">
                        <span class="direction-ltr d-block">
                            -{{ getProductPriceByType(product: $product, type: 'discount', result: 'string') }}
                        </span>
                    </span>
                </div>
            @else
                <div class="d-flex justify-content-end" style="margin:0; padding:0;">
                    <span class="for-discount-value-null"></span>
                </div>
            @endif
            <div style="margin:0; padding:0;">
                <a href="{{ route('product', $product->slug) }}" class="d-block w-100" style="position: relative; display: block; margin:0; padding:0;">
                    <div style="
                        width: 100%;
                        aspect-ratio: 3 / 5;
                        overflow: hidden;
                        border-radius: 12px;
                        background: #fff;
                        box-shadow: 0 2px 6px rgba(0,0,0,0.05);
                        position: relative;
                        margin:0;
                        padding:0;
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
                                margin:0;
                                padding:0;
                            "
                        >
                        @if(!empty($product->brand->image_full_url['path']) 
                            && $product->brand->image_full_url['status'] != 404
                            && $product->brand->image_full_url['path'] != 'https://dmarket.kg/storage/app/public/brand/2025-06-24-685acb68c0b0c.webp')
                            <div style="
                                position: absolute;
                                bottom: 10px;
                                right: 10px;
                                width: 80px;
                                height: 80px;
                                border-radius: 50%;
                                overflow: hidden;
                                border: 2px solid #fff;
                                box-shadow: 0 2px 8px rgba(0,0,0,0.2);
                                background: #fff;
                                margin:0;
                                padding:0;
                            ">
                                <img 
                                    src="{{ getStorageImages(path: $product?->brand->image_full_url, type: 'shop') }}" 
                                    alt="Brand" 
                                    style="width: 100%; height: 100%; object-fit: cover; margin:0; padding:0;"
                                >
                            </div>
                        @endif
                    </div>
                </a>
            </div>
            <div class="quick-view" style="margin:0; padding:0;">
                <a class="btn-circle stopPropagation action-product-quick-view" href="{{route('product',$product->slug)}}" >
                    <i class="czi-eye align-middle"></i>
                </a>
            </div>
            @if($product->product_type == 'physical' && $product->current_stock <= 0)
                <span class="out_fo_stock">{{translate('out_of_stock')}}</span>
            @endif
        </div>
        <div class="single-product-details" style="margin:0; padding:0;">
            @if($overallRating[0] != 0 )
                <div class="rating-show justify-content-between text-center" style="margin:0; padding:0;">
                    <span class="d-inline-block font-size-sm text-body" style="margin:0; padding:0;">
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
            <h3 class="text-center mb-1 letter-spacing-0" style="margin:0; padding:0;">
                <a href="{{route('product',$product->slug)}}">
                    {{ $product['name'] }}
                </a>
            </h3>
            <div class="justify-content-between text-center" style="margin:0; padding:0;">
                <h4 class="product-price text-center d-flex flex-wrap justify-content-center align-items-center gap-8 mb-0 letter-spacing-0" style="margin:0; padding:0;">
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
