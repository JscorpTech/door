@php($overallRating = getOverallRating($product->reviews))

<div class="product-single-hover style--card" style="padding:0; margin:0;">
    <div class="overflow-hidden position-relative" style="padding:0; margin:0;">
        <div class="inline_product clickable d-flex justify-content-center" style="margin:0; padding:0; width:100%; flex-direction: column;">

            {{-- Discount Badge --}}
            @if(getProductPriceByType(product: $product, type: 'discount', result: 'value') > 0)
                <span class="for-discount-value p-1 pl-2 pr-2 font-bold fs-13" style="position:absolute; top:8px; left:8px; z-index:10;">
                    -{{ getProductPriceByType(product: $product, type: 'discount', result: 'string') }}
                </span>
            @endif

            {{-- Product Image --}}
            <a href="{{ route('product', $product->slug) }}" style="display:block; position:relative; width:100%; max-width:220px; margin:0 auto;">
                <div style="
                    width:100%;
                    aspect-ratio: 3/4;
                    overflow:hidden;
                    border-radius:12px;
                    box-shadow:0 4px 12px rgba(0,0,0,0.1);
                    position:relative;
                ">
                    <img 
                        src="{{ getStorageImages(path: $product->thumbnail_full_url, type: 'product') }}"
                        alt="{{ $product->name ?? '' }}"
                        style="
                            width:100%;
                            height:100%;
                            object-fit:cover;
                            object-position:center;
                            display:block;
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
                            width:50px;
                            height:50px;
                            border-radius:50%;
                            overflow:hidden;
                            border:2px solid #fff;
                            box-shadow:0 2px 8px rgba(0,0,0,0.2);
                            background:#fff;
                        ">
                            <img 
                                src="{{ getStorageImages(path: $product->brand->image_full_url, type: 'shop') }}"
                                alt="Brand"
                                style="width:100%; height:100%; object-fit:cover;"
                            >
                        </div>
                    @endif
                </div>
            </a>

            {{-- Quick View --}}
            <div class="quick-view" style="position:absolute; top:8px; right:8px; z-index:10;">
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
        <div class="single-product-details" style="margin-top:8px; padding:0; text-align:center;">
            @if($overallRating[0] != 0 )
                <div class="rating-show justify-content-center mb-1">
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

            <h3 class="mb-1 letter-spacing-0" style="font-size:15px;">
                <a href="{{route('product',$product->slug)}}" class="text-capitalize fw-semibold">
                    {{ $product['name'] }}
                </a>
            </h3>

            <div class="product-price d-flex justify-content-center align-items-center gap-2" style="font-size:14px; font-weight:600;">
                @if(getProductPriceByType(product: $product, type: 'discount', result: 'value') > 0)
                    <del style="font-size:13px; color:#888;">
                        {{ webCurrencyConverter(amount: $product->unit_price) }}
                    </del>
                @endif
                <span class="text-accent">
                    {{ getProductPriceByType(product: $product, type: 'discounted_unit_price', result: 'string') }}
                </span>
            </div>
        </div>
    </div>
</div>
