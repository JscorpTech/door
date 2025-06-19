<div class="bg-white product-details-sticky product-details-sticky-section pt-4 pt-md-3 pb-3 {{ $productDetails->variation && count(json_decode($productDetails->variation)) > 0 ? 'multi-variation-product' : '' }}">
    <div class="btn-circle product-details-sticky-collapse-btn d-md-none cursor-pointer shadow-sm position-absolute translate-middle-custom top-0 left-50 justify-content-center align-items-center {{ $productDetails->variation && count(json_decode($productDetails->variation)) > 0 ? 'd-flex' : 'd-none' }}" style="--size: 34px">
        <i class="bi bi-chevron-up"></i>
    </div>
    <div class="container product-cart-option-container">
        <form class="add-to-cart-sticky-form addToCartDynamicForm" action="{{ route('cart.add') }}"
              data-errormessage="{{translate('please_choose_all_the_options')}}"
              data-outofstock="{{translate('Sorry_Out_of_stock')}}.">
            @csrf
            <input type="hidden" name="id" value="{{ $productDetails->id }}">
            <input type="hidden" name="position" value="bottom">

            <div class="product-details-sticky-top">
                <div class="border-bottom d-flex flex-column gap-3 mb-3 pb-3">
                    @if (count(json_decode($productDetails->colors)) > 0)
                    <div class="position-relative">
                        <h6 class="fs-14 mb-2">
                            {{ translate('color')}}
                            <span class="text-muted font-weight-light product-details-sticky-color-name"></span>
                        </h6>
                        <div>
                            <ul class="option-select-btn custom_01_option flex-wrap weight-style--two gap-2 pt-2 p-1 flex-nowrap text-nowrap overflow-x-auto overflow-y-hidden scrollbar-none">
                                @foreach (json_decode($productDetails->colors) as $key => $color)
                                    <li>
                                        <label>
                                            <input type="radio" hidden=""
                                                   id="sticky-{{ $productDetails->id }}-color-{{ str_replace('#','',$color) }}"
                                                   name="color" value="{{ $color }}"
                                                {{ $key == 0 ? 'checked' : '' }}
                                            >
                                            <span
                                                class="color_variants rounded-circle focus-preview-image-by-color p-0 {{ $key == 0 ? 'color_variant_active':''}}"
                                                style="background: {{ $color }};"
                                                data-slide-id="preview-box-{{ str_replace('#','',$color) }}"
                                                id="color_variants_preview-box-{{ str_replace('#','',$color) }}"
                                            ></span>
                                        </label>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                    @endif

                    @foreach (json_decode($productDetails->choice_options) as  $choice)
                    <div class="product-details-content">
                        <h6 class="fs-14 mb-2">{{ translate($choice->title) }}</h6>
                        <ul class="option-select-btn custom_01_option flex-wrap weight-style--two gap-2 p-1 flex-nowrap text-nowrap overflow-x-auto overflow-y-hidden scrollbar-none">
                            @foreach ($choice->options as $key =>$option)
                                <li>
                                    <label>
                                        <input type="radio" hidden=""
                                               id="sticky-{{ $choice->name }}-{{ $option }}"
                                               name="{{ $choice->name }}"
                                               value="{{ $option }}"
                                               @if($key == 0) checked @endif >
                                        <span>{{$option}}</span>
                                    </label>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                    @endforeach

                    @php($extensionIndex=0)
                    @if($productDetails['product_type'] == 'digital' && $productDetails['digital_product_file_types'] && count($productDetails['digital_product_file_types']) > 0 && $productDetails['digital_product_extensions'])
                        @foreach($productDetails['digital_product_extensions'] as $extensionKey => $extensionGroup)
                        <div class="product-details-content">
                            <h6 class="fs-14 mb-2">
                                {{ translate($extensionKey) }}
                            </h6>

                            @if(count($extensionGroup) > 0)
                                <ul class="option-select-btn custom_01_option flex-wrap weight-style--two gap-2">
                                    @foreach($extensionGroup as $index => $extension)
                                        <li>
                                            <label>
                                                <input type="radio" hidden
                                                       name="variant_key"
                                                       value="{{ $extensionKey.'-'.preg_replace('/\s+/', '-', $extension) }}"
                                                    {{ $extensionIndex == 0 ? 'checked' : ''}}>
                                                <span class="text-transform-none">{{ $extension }}</span>
                                            </label>
                                        </li>
                                        @php($extensionIndex++)
                                    @endforeach
                                </ul>
                            @endif
                        </div>
                        @endforeach
                    @endif

                </div>
            </div>

            <div class="d-flex flex-column flex-lg-row justify-content-between gap-3 product-details-sticky-bottom">
            <div class="media gap-3">
                <img width="48" class="rounded d-none d-sm-block aspect-1 object-cover"
                     src="{{ getStorageImages(path: $productDetails->thumbnail_full_url, type: 'product') }}"
                     alt="">
                <div class="media-body">
                    <h6 class="mb-2 fs-14 line--limit-1">{{ $productDetails->name }}</h6>
                    <div>
                        <input type="hidden" class="product-generated-variation-code" name="product_variation_code" data-product-id="{{ $productDetails['id'] }}">
                        <input type="hidden" value="" class="product-exist-in-cart-list form-control w-50" name="key">
                    </div>
                    <div class="d-flex flex-wrap align-items-center mb-2 pro">
                        <span class="fs-12 text-muted line--limit-1 text-capitalize product-generated-variation-text">
                        </span>
                        <div class="d-none d-sm-flex flex-wrap align-items-center">
                            <span class="__inline-25 mx-2 mt-0"></span>
                            <div class="fs-12">
                                <div class="d-flex flex-wrap gap-2 align-items-center row-gap-0 fs-12">
                                    {!! getPriceRangeWithDiscount(product: $productDetails) !!}
                                </div>
                            </div>
                            <span class="py-1 px-2 font-bold fs-13 mx-2 bg-primary rounded text-absolute-white fw-bold discounted-badge-element">
                                <span class="direction-ltr d-block discounted_badge">
                                    {{ webCurrencyConverter(amount: 0) }}
                                </span>
                            </span>
                        </div>
                    </div>
                </div>

                <div class="d-sm-none flex-column flex-sm-row d-flex gap-1 fs-12">
                    {!! getPriceRangeWithDiscount(product: $productDetails) !!}
                </div>
            </div>

            <div class="d-flex align-items-center gap-2 gap-sm-3 gap-xl-4">
                <div class="quantity quantity--style-two d-flex align-items-center">
                        <span class="quantity__minus single-quantity-minus form-control h-42px bg--light px-3" data-form=".add-to-cart-sticky-form">
                            <i class="bi bi-dash"></i>
                        </span>
                    <input type="text"
                           data-details-page="1"
                           class="quantity__qty product_quantity__qty"
                           name="quantity"
                           value="{{ $productDetails?->minimum_order_qty ?? 1 }}"
                           min="{{ $productDetails?->minimum_order_qty ?? 1 }}"
                           max="{{$productDetails['product_type'] == 'physical' ? $productDetails->current_stock : 100}}">

                    <span class="quantity__plus single-quantity-plus form-control h-42px bg--light px-3" data-form=".add-to-cart-sticky-form">
                            <i class="bi bi-plus"></i>
                        </span>
                </div>

                <div class="font-weight-normal text-accent align-items-end gap-2 d-none d-lg-flex">
                    <span class="text-primary fs-5 fw-bold product-details-chosen-price-amount user-select-none"></span>
                </div>

                @if(($product->added_by == 'seller' && ($sellerTemporaryClose || (isset($product->seller->shop) && $product->seller->shop->vacation_status && $currentDate >= $sellerVacationStartDate && $currentDate <= $sellerVacationEndDate))) ||
                                                        ($product->added_by == 'admin' && ($inHouseTemporaryClose || ($inHouseVacationStatus && $currentDate >= $inHouseVacationStartDate && $currentDate <= $inHouseVacationEndDate))))
                    <div class="alert alert-danger m-0" role="alert">
                        {{ translate('you_cannot_add_product_to_cart_from_this_shop_for_now') }}
                    </div>
                @else
                    <div class="mx-w d-flex gap-3 width--24rem">
                        <div class="product-add-and-buy-section d--flex gap-2 gap-sm-3" {!! $firstVariationQuantity <= 0 ? 'style="display: none;"' : '' !!}>
                            @if(($productDetails->added_by == 'seller' && ($sellerTemporaryClose || (isset($productDetails->seller->shop) && $productDetails->seller->shop->vacation_status && $currentDate >= $sellerVacationStartDate && $currentDate <= $sellerVacationEndDate))) ||
                        ($productDetails->added_by == 'admin' && ($inHouseTemporaryClose || ($inHouseVacationStatus && $currentDate >= $inHouseVacationStartDate && $currentDate <= $inHouseVacationEndDate))))
                                <button type="button"
                                        class="btn btn-secondary fs-16 flex-grow-1"
                                        disabled>
                                    {{translate('buy_now')}}
                                </button>
                                <button type="button"
                                        class="btn btn-primary fs-16 flex-grow-1 text-capitalize"
                                        disabled>
                                    {{translate('add_to_cart')}}
                                </button>
                            @else
                                <button type="button"
                                        class="btn btn-secondary fs-16 product-buy-now-button"
                                        data-form=".add-to-cart-details-form"
                                        data-auth="{{( getWebConfig(name: 'guest_checkout') == 1 || Auth::guard('customer')->check() ? 'true':'false')}}"
                                        data-route="{{ route('shop-cart') }}"
                                >
                                    {{translate('buy_now')}}
                                </button>
                                <button class="btn btn-primary fs-16 text-capitalize product-add-to-cart-button"
                                        type="button"
                                        data-form=".add-to-cart-sticky-form"
                                        data-update="{{ translate('update_cart') }}"
                                        data-add="{{ translate('add_to_cart') }}">
                                    {{ translate('add_to_cart') }}
                                </button>
                            @endif
                        </div>

                        @if(($productDetails['product_type'] == 'physical'))
                            <div class="product-restock-request-section collapse" {!! $firstVariationQuantity <= 0 ? 'style="display: block;"' : '' !!}>
                                <button type="button"
                                        class="btn request-restock-btn btn-outline-primary fw-semibold product-restock-request-button"
                                        data-auth="{{ auth('customer')->check() }}"
                                        data-form=".addToCartDynamicForm"
                                        data-default="{{ translate('Request_Restock') }}"
                                        data-requested="{{ translate('Request_Sent') }}"
                                >
                                    {{ translate('Request_Restock')}}
                                </button>
                            </div>
                        @endif
                    </div>
                @endif

            </div>
        </div>
        </form>
    </div>
</div>
