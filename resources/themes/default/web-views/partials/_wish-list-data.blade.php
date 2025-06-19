 <div class="d-flex flex-column gap-10px">
        @foreach($wishlists as $key=>$wishlist)
            @php($product = $wishlist->productFullInfo)
            @if( $wishlist->productFullInfo)
                <div class="wishlist-item" id="row_id{{$product->id}}">
                    <div class="wishlist-img position-relative">
                        <a href="{{route('product',$product->slug)}}" class="d-block h-100">
                            <img class="__img-full"
                                 src="{{ getStorageImages(path: $product->thumbnail_full_url, type: 'product') }}"
                                 alt="{{ translate('wishlist') }}">
                        </a>

                        @if(getProductPriceByType(product: $product, type: 'discount', result: 'value') > 0)
                            <span class="for-discount-value px-1 font-bold fs-13 direction-ltr">
                                -{{ getProductPriceByType(product: $product, type: 'discount', result: 'string') }}
                            </span>
                        @endif

                    </div>
                    <div class="wishlist-cont align-items-end align-items-sm-center">
                        <div class="wishlist-text">
                            <div class="font-name">
                                <a class="fs-12 font-semibold line-height-16" href="{{route('product',$product['slug'])}}">{{$product['name']}}</a>
                            </div>
                            @if($brand_setting && $product->product_type != 'digital')
                                <span class="sellerName fs-12"> {{translate('brand')}} : <span
                                        class="text-base">{{$product->brand ? $product->brand['name'] : ''}}</span> </span>
                            @endif

                            <div class=" mt-sm-1">
                                <span class="font-weight-bold amount text-dark price-range d-flex align-items-center gap-2">
                                    <span class="flash-product-price text-dark fw-semibold">
                                        {{ getProductPriceByType(product: $product, type: 'discounted_unit_price', result: 'string') }}
                                    </span>
                                     @if(getProductPriceByType(product: $product, type: 'discount', result: 'value') > 0)
                                        <del class="category-single-product-price">
                                            {{ webCurrencyConverter(amount: $product->unit_price)}}
                                        </del>
                                    @endif
                                </span>
                            </div>
                        </div>
                        <a href="javascript:" class="remove--icon function-remove-wishList" data-id="{{ $product['id'] }}">
                            <i class="fa fa-heart web-text-primary"></i>
                        </a>

                    </div>
                </div>
            @else
                <span class="badge badge-danger">{{ translate('item_removed') }}</span>
            @endif
        @endforeach
    </div>

    @if($wishlists->count()==0)
        <div class="d-flex justify-content-center align-items-center h-100">
            <div class="login-card w-100 border-0 shadow-none">
                <div class="text-center py-3 text-capitalize">
                    <img src="{{ theme_asset(path: 'public/assets/front-end/img/icons/wishlist-empty-state.svg') }}" alt="" class="mb-4" width="70">
                    <h5 class="fs-14 text-muted">{{ translate('you_have_not_added_product_to_wishlist_yet') }}!</h5>

                    <a href="{{ route('products') }}" class="text-capitalize btn btn--primary btn-sm font-weight-bolder mt-3">
                        {{ translate('explore_more') }}
                    </a>
                </div>
            </div>
        </div>
    @endif

<div class="card-footer border-0">{{ $wishlists->links() }}</div>
