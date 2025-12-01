@if(isset($product))
    @php($overallRating = getOverallRating($product->reviews))
    <div class="product-single-hover style--card h-100" 
         style=" border-radius:12px; padding:0; margin:0;">
        <div class="overflow-hidden position-relative" style="padding:0; margin:0;">

            <div class="inline_product clickable d-flex justify-content-center flex-column" style="padding:0; margin:0;">

                {{-- Discount Badge --}}
                @if(getProductPriceByType(product: $product, type: 'discount', result: 'value') > 0)
                    <span class="for-discount-value fs-13 font-bold" 
                          style="position:absolute; top:8px; left:8px; z-index:10;">
                        -{{ getProductPriceByType(product: $product, type: 'discount', result: 'string') }}
                    </span>
                @endif

                {{-- Product Image --}}
                <a href="{{ route('product', $product->slug) }}" 
                   class="d-block w-100" 
                   style="margin:0; padding:0;">
                    <div style="
                        width:100%;
                        aspect-ratio:3/4;
                        overflow:hidden;
                        border-radius:12px;
                        background:#fff;
                        position:relative;
                        margin:0;
                        padding:0;
                    ">
                        <img 
                            alt="{{ $product->name ?? '' }}"
                            src="{{ getStorageImages(path: $product->thumbnail_full_url, type: 'product') }}"
                            style="
                                width:100%;
                                height:98%;
                                object-fit:cover;
                                object-position:center;
                                display:block;
                                margin:0;
                                padding:0;
                                transition:transform 0.3s ease;
                            "
                        >

                        {{-- Brand Logo --}}
                        @if(!empty($product->brand->image_full_url['path']) 
                        && $product->brand->image_full_url['status'] != 404
                        && $product->brand->image_full_url['path'] != 'https://dmarket.kg/storage/app/public/brand/2025-06-24-685acb68c0b0c.webp')
                            <div style="
                                position:absolute;
                                bottom:8px;
                                right:8px;
                                width:60px;
                                height:60px;
                                border-radius:50%;
                                overflow:hidden;
                                box-shadow:0 2px 8px rgba(0,0,0,0.2);
                                background:#fff;
                            ">
                                <img 
                                    src="{{ getStorageImages(path: $product?->brand->image_full_url, type: 'shop') }}" 
                                    alt="Brand" 
                                    style="width:100%; height:100%; object-fit:cover;"
                                >
                            </div>
                        @endif
                    </div>
                </a>

                {{-- Quick View --}}
                <div class="quick-view" style="position:absolute; top:0px; right:0px; width:100%;
        height:100%; z-index:10;">
                    <a class="btn-circle stopPropagation action-product-quick-view" href="{{route('product',$product->slug)}}">
                        <i class="czi-eye align-middle"></i>
                    </a>
                </div>

                {{-- Out of Stock --}}
                @if($product->product_type == 'physical' && $product->current_stock <= 0)
                    <span class="out_fo_stock" style="position:absolute; top:8px; right:8px; z-index:9;">
                        {{translate('out_of_stock')}}
                    </span>
                @endif
            </div>

            {{-- Product Details --}}
            <div class="single-product-details" style="margin:0; padding:0; text-align:center;">
                @if($overallRating[0] != 0 )
                    <div class="rating-show justify-content-center mb-1" style="margin:0; padding:0;">
                        @for($inc=1;$inc<=5;$inc++)
                            @if ($inc <= (int)$overallRating[0])
                                <i class="tio-star text-warning"></i>
                            @elseif ($overallRating[0] != 0 && $inc <= (int)$overallRating[0] + 1.1 && $overallRating[0] > ((int)$overallRating[0]))
                                <i class="tio-star-half text-warning"></i>
                            @else
                                <i class="tio-star-outlined text-warning"></i>
                            @endif
                        @endfor
                        <span class="badge-style">( {{ count($product->reviews) }} )</span>
                    </div>
                @endif

                <h4 class="text-center lh-1 letter-spacing-0" style="margin:0; padding:0;">
                    <a href="{{route('product',$product->slug)}}" style="text-decoration:none;">
                        {{ $product['name'] }}
                    </a>
                </h4>

                <div class="text-center" style="margin:0; padding:0;">
                    <h5 class="product-price d-flex flex-wrap justify-content-center align-items-baseline gap-8 lh-1 letter-spacing-0" 
                        style="margin:0; padding:0;">
                        @if(getProductPriceByType(product: $product, type: 'discount', result: 'value') > 0)
                            <del style="font-size:13px; color:#888; margin:0 4px 0 0; padding:0;">
                                {{ webCurrencyConverter(amount: $product->unit_price) }}
                            </del>
                        @endif
                        <span class="text-accent text-dark" style="font-weight:600; margin:0; padding:0;">
                            {{ getProductPriceByType(product: $product, type: 'discounted_unit_price', result: 'string') }}
                        </span>
                    </h5>
                </div>

            </div>
        </div>
    </div>
@endif
