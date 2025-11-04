@push('script')
<script>
    $(document).ready(function() {
        $(".category-wise-product-slider").each(function () {
            let loopEnable = $(this).data('loop')?.toString() === 'true';

            $(this).owlCarousel({
                loop: loopEnable,
                autoplay: true,
                margin: 20,
                nav: true,
                navText:
                    directionFromSession === "rtl"
                        ? [
                            "<i class='czi-arrow-right'></i>",
                            "<i class='czi-arrow-left'></i>",
                        ]
                        : [
                            "<i class='czi-arrow-left'></i>",
                            "<i class='czi-arrow-right'></i>",
                        ],
                dots: false,
                autoplayHoverPause: true,
                rtl: directionFromSession === "rtl",
                responsive: {
                    0: {
                        items: 1.2,
                    },
                    375: {
                        items: 1.4,
                    },
                    425: {
                        items: 2,
                    },
                    576: {
                        items: 3,
                    },
                    768: {
                        items: 4,
                    },
                    992: {
                        items: 5,
                    },
                    1200: {
                        items: 5,
                    },
                },
                onInitialized: checkNavigationButtons,
            });
        });
    }); // ❗ Bu joyda yopish kerak edi
</script>
@endpush




@if(isset($product))
    @php($overallRating = getOverallRating($product->reviews))
    <div class="product-single-hover style--category shadow-none" style="border:none; padding:0; margin:0;">
        <div class="overflow-hidden position-relative">
            <div class="inline_product clickable d-flex justify-content-center" style="padding:0; margin:0;">
                @if(getProductPriceByType(product: $product, type: 'discount', result: 'value') > 0)
                    <div class="position-absolute top-0 start-0 m-2">
                        <span class="for-discount-value p-1 px-2 font-bold fs-13 bg-danger text-white rounded">
                            -{{ getProductPriceByType(product: $product, type: 'discount', result: 'string') }}
                        </span>
                    </div>
                @endif

                <a href="{{ route('product', $product->slug) }}" class="d-block w-100">
                    <div style="
                        width: 100%;
                        aspect-ratio: 3 / 4;
                        overflow: hidden;
                        border-radius: 10px;
                    ">
                        <img 
                            alt="{{ $product->name }}"
                            src="{{ getStorageImages(path: $product->thumbnail_full_url, type: 'product') }}"
                            style="
                                width: 100%;
                                height: 100%;
                                object-fit: cover;
                                display: block;
                                transition: transform 0.3s ease;
                            "
                            onmouseover="this.style.transform='scale(1.05)'"
                            onmouseout="this.style.transform='scale(1)'"
                        >
                    </div>
                </a>

                <div class="quick-view position-absolute bottom-0 end-0 m-2">
                    <a class="btn-circle stopPropagation action-product-quick-view bg-white shadow" href="{{route('product',$product->slug)}}">
                        <i class="czi-eye align-middle"></i>
                    </a>
                </div>

                @if($product->product_type == 'physical' && $product->current_stock <= 0)
                    <span class="out_fo_stock position-absolute top-0 end-0 m-2 bg-dark text-white px-2 py-1 rounded">
                        {{translate('out_of_stock')}}
                    </span>
                @endif
            </div>

            <div class="single-product-details text-center mt-2" style="padding:0; margin:0;">
                @if($overallRating[0] != 0)
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
                        <label class="badge-style">( {{ count($product->reviews) }} )</label>
                    </div>
                @endif

                <h3 class="mb-1" style="font-size: 15px; font-weight: 600;">
                    <a href="{{route('product',$product->slug)}}" class="text-capitalize text-dark text-decoration-none">
                        {{ $product['name'] }}
                    </a>
                </h3>

                <div>
                    <h4 class="product-price mb-0" style="font-size: 14px;">
                        @if(getProductPriceByType(product: $product, type: 'discount', result: 'value') > 0)
                            <del style="color:#888; font-size:13px; margin-right:4px;">
                                {{ webCurrencyConverter(amount: $product->unit_price) }}
                            </del>
                        @endif
                        <span class="text-accent text-dark" style="font-weight:600;">
                            {{ getProductPriceByType(product: $product, type: 'discounted_unit_price', result: 'string') }}
                        </span>
                    </h4>
                </div>
            </div>
        </div>
    </div>
@endif
