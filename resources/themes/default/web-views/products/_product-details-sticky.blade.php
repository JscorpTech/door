<div class="bg-white product-details-sticky product-details-sticky-section pt-4 pt-md-3 pb-3 {{ $productDetails->variation && count(json_decode($productDetails->variation)) > 0 ? 'multi-variation-product' : '' }}">
    <div class="btn-circle product-details-sticky-collapse-btn d-md-none transition cursor-pointer shadow-sm position-absolute translate-middle top-0 left-50 justify-content-center align-items-center {{ $productDetails->variation && count(json_decode($productDetails->variation)) > 0 ? 'd-flex' : 'd-none' }}" style="--size: 34px">
        <i class="czi-arrow-up"></i>
    </div>

    <div class="container product-cart-option-container">
        <form class="add-to-cart-sticky-form addToCartDynamicForm">
            @csrf
            <input type="hidden" name="id" value="{{ $productDetails->id }}">
            <input type="hidden" name="position" value="bottom">
            <div class="product-details-sticky-top">
                <div class="border-bottom d-flex flex-column gap-3 mb-3 pb-3">
                    @if (count(json_decode($productDetails->colors)) > 0)
                    <div class="position-relative ps-1">
                        <h6 class="fs-14 mb-2">
                            {{ translate('color')}}
                            <span class="text-muted font-weight-light product-details-sticky-color-name"></span>
                        </h6>
                        <div>
                            <ul class="list-inline checkbox-color mb-0 flex-start ps-0">
                                @foreach (json_decode($productDetails->colors) as $key => $color)
                                    <li class="user-select-none">
                                        <input type="radio"
                                               id="sticky-{{ str_replace(' ', '', ($productDetails->id. '-color-'. str_replace('#','',$color))) }}"
                                               name="color" value="{{ $color }}"
                                               @if($key == 0) checked @endif>
                                        <label style="background: {{ $color }};"
                                               class="focus-preview-image-by-color shadow-border m-0"
                                               for="sticky-{{ str_replace(' ', '', ($productDetails->id. '-color-'. str_replace('#','',$color))) }}"
                                               data-toggle="tooltip"
                                               data-key="{{ str_replace('#','',$color) }}"
                                               data-colorid="preview-box-{{ str_replace('#','',$color) }}" data-title="{{ getColorNameByCode(code: $color) }}">
                                            <span class="outline"></span></label>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                    @endif

                    @php($extensionIndex=0)
                    @if($productDetails['product_type'] == 'digital' && $productDetails['digital_product_file_types'] && count($productDetails['digital_product_file_types']) > 0 && $productDetails['digital_product_extensions'])
                        @foreach($productDetails['digital_product_extensions'] as $extensionKey => $extensionGroup)
                        <div>
                            <h6 class="fs-14 mb-2 text-capitalize">
                                {{ translate($extensionKey) }}
                            </h6>

                            @if(count($extensionGroup) > 0)
                            <div class="list-inline checkbox-alphanumeric checkbox-alphanumeric--style-1 mb-0 flex-start row ps-0 overflow-x-auto flex-nowrap overflow-y-hidden scrollbar-none">
                                @foreach($extensionGroup as $index => $extension)
                                    <div class="user-select-none">
                                        <div class="for-mobile-capacity">
                                            <input type="radio" hidden
                                                   id="sticky-extension_{{ str_replace(' ', '-', $extension) }}"
                                                   name="variant_key"
                                                   value="{{ $extensionKey.'-'.preg_replace('/\s+/', '-', $extension) }}"
                                                {{ $extensionIndex == 0 ? 'checked' : ''}}>
                                            <label for="sticky-extension_{{ str_replace(' ', '-', $extension) }}"
                                                   class="__text-12px max-content">
                                                {{ $extension }}
                                            </label>
                                        </div>
                                    </div>
                                    @php($extensionIndex++)
                                @endforeach
                            </div>
                            @endif
                        </div>
                        @endforeach
                    @endif

                    @foreach (json_decode($productDetails->choice_options) as $key => $choice)
                    <div>
                        <h6 class="fs-14 mb-2 text-capitalize">
                            {{ $choice->title }}
                        </h6>
                        <div class="list-inline checkbox-alphanumeric checkbox-alphanumeric--style-1 mb-0 flex-start ps-0 overflow-x-auto flex-nowrap overflow-y-hidden scrollbar-none">
                            @foreach ($choice->options as $index => $option)
                                <div class="user-select-none">
                                    <div class="for-mobile-capacity">
                                        <input type="radio"
                                               id="sticky-{{ str_replace(' ', '', ($choice->name. '-'. $option)) }}"
                                               name="{{ $choice->name }}" value="{{ $option }}"
                                               @if($index == 0) checked @endif >
                                        <label class="__text-12px max-content"
                                               for="sticky-{{ str_replace(' ', '', ($choice->name. '-'. $option)) }}">{{ $option }}</label>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            <div class="d-flex flex-column flex-lg-row justify-content-between gap-3 product-details-sticky-bottom">
                <div class="media gap-sm-3 d-flex flex-column flex-sm-row">
                    <img width="48" class="rounded d-none d-sm-block aspect-1 object-cover"
                         src="{{ getStorageImages(path: $productDetails->thumbnail_full_url, type: 'product') }}"
                         alt=""
                    >
                    <div class="media-body">
                        <h6 class="mb-1 fs-14 line--limit-1">
                            {{ $productDetails->name }}
                        </h6>
                        <div>
                            <input type="hidden" class="product-generated-variation-code" name="product_variation_code" data-product-id="{{ $productDetails['id'] }}">
                            <input type="hidden" value="" class="product-exist-in-cart-list form-control w-50" name="key">
                        </div>
                        <div class="d-flex flex-wrap align-items-center mb-2 pro">
                            <span class="fs-12 text-muted line--limit-1 text-capitalize product-generated-variation-text"></span>
                            <div class="d-none d-sm-flex flex-wrap align-items-center">
                                <span class="{{ count(json_decode($productDetails->variation, true)) > 0 ? '__inline-25' : '' }} {{ count(json_decode($productDetails->variation, true)) > 0 ? 'mx-2' : '' }} mt-0"></span>

                                <span class="fs-12">
                                    <span class="d-flex flex-wrap gap-8 align-items-center row-gap-0">
                                        {!! getPriceRangeWithDiscount(product: $productDetails) !!}
                                    </span>
                                </span>
                                <span class="for-discount-value position-static p-1 px-2 font-bold fs-13 mx-2 discounted-badge-element">
                                    <span class="direction-ltr d-block discounted_badge">
                                        {{ webCurrencyConverter(amount: 0) }}
                                    </span>
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="d-sm-none d-flex gap-1 fs-12">
                        {!! getPriceRangeWithDiscount(product: $productDetails) !!}
                    </div>
                </div>

                <div class="d-flex align-items-center gap-2 gap-sm-3 gap-xl-4">
                    <div class="d-flex justify-content-between align-items-center quantity-box quantity-box-120 border rounded border-base web-text-primary">
                        <button class="flex-grow-1 btn btn-number __p-10 web-text-primary product-quantity-minus bg-count-light border-0 shadow-none px-3" type="button" data-type="minus" data-field="quantity" disabled="disabled">-</button>
                        <input type="number" name="quantity"
                               class="flex-grow-1 form-control input-number text-center product-details-cart-qty __inline-29 border-0 "
                               placeholder="{{ translate('1') }}"
                               value="{{ $productDetails->minimum_order_qty ?? 1 }}"
                               data-producttype="{{ $productDetails->product_type }}"
                               min="{{ $productDetails->minimum_order_qty ?? 1 }}"
                               max="{{$productDetails['product_type'] == 'physical' ? $productDetails->current_stock : 100}}">

                        <button class="flex-grow-1 btn btn-number __p-10 web-text-primary product-quantity-plus bg-count-light border-0 shadow-none px-3" type="button" data-producttype="physical" data-type="plus" data-field="quantity">+</button>
                    </div>

                    <div class="font-weight-normal text-accent align-items-end gap-2 d-none d-lg-flex">
                        <span class="product-bottom-section-price fs-24 font-bold user-select-none text-nowrap"></span>
                    </div>


                    @if(($product->added_by == 'seller' && ($sellerTemporaryClose || (isset($product->seller->shop) && $product->seller->shop->vacation_status && $currentDate >= $sellerVacationStartDate && $currentDate <= $sellerVacationEndDate))) ||
                                         ($product->added_by == 'admin' && ($inHouseTemporaryClose || ($inHouseVacationStatus && $currentDate >= $inHouseVacationStartDate && $currentDate <= $inHouseVacationEndDate))))
                        <div class="alert alert-danger m-0 font-semi-bold fs-12 ms-2" role="alert">
                            {{translate('you_cannot_add_product_to_cart_from_this_shop_for_now')}}
                        </div>
                    @else
                        <div class="product-add-and-buy-section d-flex gap-2">
                            <button type="button" class="btn btn-secondary element-center btn-gap-right product-buy-now-button"
                                    data-form=".add-to-cart-sticky-form"
                                    data-auth="{{( getWebConfig(name: 'guest_checkout') == 1 || Auth::guard('customer')->check() ? 'true':'false')}}"
                                    data-route="{{ route('shop-cart') }}"
                            >
                                <span class="string-limit">{{ translate('buy_now') }}</span>
                            </button>

                            <button class="btn btn--primary element-center product-add-to-cart-button"
                                    type="button"
                                    data-form=".add-to-cart-sticky-form"
                                    data-update="{{ translate('update_cart') }}"
                                    data-add="{{ translate('add_to_cart') }}"
                            >
                                {{ translate('add_to_cart') }}
                            </button>
                        </div>

                        @if(($productDetails['product_type'] == 'physical'))
                            <div class="product-restock-request-section collapse" {!! $firstVariationQuantity <= 0 ? 'style="display: block;"' : '' !!}>
                                <button type="button"
                                        class="btn request-restock-btn btn-outline-primary fw-semibold product-restock-request-button"
                                        data-auth="{{ auth('customer')->check() }}"
                                        data-form=".add-to-cart-sticky-form"
                                        data-default="{{ translate('Request_Restock') }}"
                                        data-requested="{{ translate('Request_Sent') }}"
                                >
                                    {{ translate('Request_Restock')}}
                                </button>
                            </div>
                        @endif
                    @endif
                </div>
            </div>
        </form>
    </div>
</div>
