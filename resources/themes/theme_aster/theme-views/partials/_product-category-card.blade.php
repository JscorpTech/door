@php
    use App\Utils\Helpers;
    use App\Utils\ProductManager;
@endphp
<div class="swiper-slide">
    <a href="javascript:"
       class="store-product d-flex flex-column gap-2 align-items-center ov-hidden">
        <div class="store-product__top border rounded mb-2 aspect-1 overflow-hidden">
            @if(isset($product->flash_deal_status) && $product->flash_deal_status == 1)
                <div class="product__power-badge">
                    <img src="{{theme_asset('assets/img/svg/power.svg')}}" alt="" class="svg text-white">
                </div>
            @endif
            @if(getProductPriceByType(product: $product, type: 'discount', result: 'value') > 0)
                <span class="product__discount-badge">
                    <span>
                        -{{ getProductPriceByType(product: $product, type: 'discount', result: 'string') }}
                    </span>
                </span>
            @else
            @endif
            <span class="store-product__action preventDefault get-quick-view"
                  data-action="{{route('quick-view')}}"
                  data-product-id="{{$product['id']}}">
                <i class="bi bi-eye fs-12"></i>
            </span>
            <img alt="" loading="lazy" class="dark-support rounded aspect-1 img-fit"
                 src="{{ getStorageImages(path: $product?->thumbnail_full_url, type: 'product') }}">
        </div>
        <a class="fs-16 text-truncate text-muted text-capitalize width--9rem"  href="{{route('product',$product->slug)}}">
            {{ Str::limit($product['name'], 18) }}
            <div class="product__price d-flex justify-content-center align-items-center flex-wrap column-gap-2 mt-1">
                @if(getProductPriceByType(product: $product, type: 'discount', result: 'value') > 0)
                    <del class="product__old-price">
                        {{webCurrencyConverter($product->unit_price)}}
                    </del>
                @endif
                <ins class="product__new-price fs-14">
                    {{ getProductPriceByType(product: $product, type: 'discounted_unit_price', result: 'string') }}
                </ins>
            </div>
        </a>
    </a>
</div>

