<div class="container">
    <div class="rounded ov-hidden mb-3">
        @if($shopInfoArray['id'] != 0)
            <div class="store-banner dark-support bg-badge overflow-hidden" data-bg-img="">
                <img class="w-100" alt=""
                     src="{{ getStorageImages(path: $shopInfoArray['banner_full_url'], type:'shop-banner') }}">
            </div>
        @else
            @php($banner = getWebConfig(name: 'shop_banner'))
            <div class="store-banner dark-support bg-badge overflow-hidden" data-bg-img="">
                <img class="w-100" alt=""
                     src="{{ getStorageImages(path: $banner, type: 'shop-banner') }}">
            </div>
        @endif
        <div class="bg-primary-light p-3">
            <div class="d-flex gap-4 flex-wrap">
                @if($shopInfoArray['id'] != 0)
                    <div class="media gap-3">
                        <div class="avatar rounded store-avatar overflow-hidden">
                            <div class="position-relative">
                                <img src="{{ getStorageImages(path:$shopInfoArray['image_full_url'], type:'shop') }}"
                                     class="dark-support rounded img-fit" alt="">
                                @if($shopInfoArray['temporary_close'])
                                    <span class="temporary-closed position-absolute">
                                                <span class="text-center px-1">{{translate('Temporary_OFF')}}</span>
                                            </span>
                                @elseif(($seller_id==0 && $shopInfoArray['vacation_status'] && $shopInfoArray['current_date'] >= $shopInfoArray['vacation_start_date'] && $shopInfoArray['current_date'] <= $shopInfoArray['vacation_end_date']) ||
                                    $seller_id!=0 && $shopInfoArray['vacation_status'] && $shopInfoArray['current_date'] >= $shopInfoArray['vacation_start_date'] && $shopInfoArray['current_date'] <= $shopInfoArray['vacation_end_date'])
                                    <span class="temporary-closed position-absolute">
                                                <span class="text-center px-1">{{translate('closed_Now')}}</span>
                                            </span>
                                @endif
                            </div>
                        </div>
                        <div class="media-body d-flex flex-column gap-2">
                            <h4>{{ $shopInfoArray['name']}}</h4>
                            <div class="d-flex gap-2 align-items-center">
                                        <span class="star-rating text-gold fs-12">
                                            @for ($index = 1; $index <= 5; $index++)
                                                @if ($index <= $shopInfoArray['average_rating'])
                                                    <i class="bi bi-star-fill"></i>
                                                @elseif ($shopInfoArray['average_rating'] != 0 && $index <= (int)$shopInfoArray['average_rating'] + 1 && $shopInfoArray['average_rating'] >= ((int)$shopInfoArray['average_rating']+.30))
                                                    <i class="bi bi-star-half"></i>
                                                @else
                                                    <i class="bi bi-star"></i>
                                                @endif
                                            @endfor
                                        </span>
                                <span
                                    class="text-muted fw-semibold">({{round($shopInfoArray['average_rating'],1)}})</span>
                            </div>
                            <ul class="list-unstyled list-inline-dot fs-12">
                                <li>{{ $shopInfoArray['total_review']}} {{translate('Reviews')}} </li>
                                <li>{{ $shopInfoArray['total_order']}} {{translate('Orders')}} </li>
                                @php($minimumOrderAmount=getWebConfig(name: 'minimum_order_amount_status'))
                                @php($minimumOrderAmountBySeller=getWebConfig(name: 'minimum_order_amount_by_seller'))
                                @if ($minimumOrderAmount ==1 && $minimumOrderAmountBySeller ==1)
                                    <li>{{ webCurrencyConverter($shopInfoArray['minimum_order_amount'])}} {{translate('minimum_order_amount')}} </li>
                                @endif
                            </ul>
                        </div>
                    </div>
                @else
                    <div class="media gap-3">
                        <div class="avatar rounded store-avatar overflow-hidden">
                            <div class="position-relative">
                                <img class="dark-support rounded img-fit" alt=""
                                     src="{{ getStorageImages(path: $web_config['fav_icon'], type:'shop') }}">

                                @if($shopInfoArray['temporary_close'])
                                    <span class="temporary-closed position-absolute">
                                            <span>{{translate('Temporary_OFF')}}</span>
                                        </span>
                                @elseif(($seller_id==0 && $shopInfoArray['vacation_status'] && $shopInfoArray['current_date'] >= $shopInfoArray['vacation_start_date'] && $shopInfoArray['current_date'] <= $shopInfoArray['vacation_end_date']) ||
                                    $seller_id!=0 && $shopInfoArray['vacation_status'] && $shopInfoArray['current_date'] >= $shopInfoArray['vacation_start_date'] && $shopInfoArray['current_date'] <= $shopInfoArray['vacation_end_date'])
                                    <span class="temporary-closed position-absolute">
                                                <span>{{translate('closed_Now')}}</span>
                                            </span>
                                @endif
                            </div>
                        </div>
                        <div class="media-body d-flex flex-column gap-2">
                            <h4>{{ $web_config['company_name'] }}</h4>
                            <div class="d-flex gap-2 align-items-center">
                                        <span class="star-rating text-gold fs-12">
                                            @for ($index = 1; $index <= 5; $index++)
                                                @if ($index <= $shopInfoArray['average_rating'])
                                                    <i class="bi bi-star-fill"></i>
                                                @elseif ($shopInfoArray['average_rating'] != 0 && $index <= (int)$shopInfoArray['average_rating'] + 1 && $shopInfoArray['average_rating'] >= ((int)$shopInfoArray['average_rating']+.30))
                                                    <i class="bi bi-star-half"></i>
                                                @else
                                                    <i class="bi bi-star"></i>
                                                @endif
                                            @endfor
                                        </span>
                                <span
                                    class="text-muted fw-semibold">({{round($shopInfoArray['average_rating'], 1)}})</span>
                            </div>
                            <ul class="list-unstyled list-inline-dot fs-12 mb-1">
                                <li>{{ $shopInfoArray['total_review']}} {{translate('reviews')}} </li>
                                <li>{{ $shopInfoArray['total_order']}} {{translate('orders')}} </li>
                            </ul>
                            @php($minimumOrderAmountStatus=getWebConfig(name: 'minimum_order_amount_status'))
                            @php($minimumOrderAmountBySeller=getWebConfig(name: 'minimum_order_amount_by_seller'))
                            @if ($minimumOrderAmountStatus ==1 && $minimumOrderAmountBySeller ==1)
                                <span
                                    class="text-sm-nowrap">{{ webCurrencyConverter($shopInfoArray['minimum_order_amount'])}} {{translate('minimum_order_amount')}}</span>
                            @endif
                        </div>
                    </div>
                @endif
                <div class="d-flex gap-3 flex-wrap flex-grow-1">
                    <div class="card flex-grow-1">
                        <div class="card-body grid-center">
                            <div class="text-center">
                                <h2 class="fs-28 text-primary fw-extra-bold mb-2">
                                    {{ round($rattingStatusArray['positive']) }}%</h2>
                                <p class="text-muted text-capitalize">{{translate("positive_review")}}</p>
                            </div>
                        </div>
                    </div>
                    <div class="card flex-grow-1">
                        <div class="card-body grid-center">
                            <div class="text-center">
                                <h2 class="fs-28 text-primary fw-extra-bold mb-2">{{$products_for_review}}</h2>
                                <p class="text-muted">{{translate('products')}}</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="d-flex flex-wrap flex-lg-column flex-lg-down-grow-1 justify-content-center gap-3">
                    @if (auth('customer')->check())
                        <button class="btn btn-primary flex-lg-down-grow-1 fs-16" data-bs-toggle="modal"
                                data-bs-target="#contact_sellerModal">
                            <i class="bi bi-chat-square-fill text-capitalize"></i> {{translate('chat_with_vendor')}}
                        </button>
                        @include('theme-views.layouts.partials.modal._chat-with-seller',['shop'=>$shopInfoArray, 'user_type' => ($shopInfoArray['id'] == 0 ? 'admin':'seller')])
                    @else
                        <button class="btn btn-primary flex-lg-down-grow-1 fs-16" data-bs-toggle="modal"
                                data-bs-target="#loginModal">
                            <i class="bi bi-chat-square-fill text-capitalize"></i> {{translate('chat_with_vendor')}}
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @if($shopInfoArray['id'] != 0 && $shopInfoArray['bottom_banner'])
        <div class="max-height-420px overflow-hidden d-flex align-items-center">
            <img src="{{ getStorageImages(path: $shopInfoArray['bottom_banner_full_url'], type:'shop-banner') }}"
                 class="dark-support rounded img-fit" alt="">
        </div>
    @elseif($shopInfoArray['id'] == 0 && $shopInfoArray['bottom_banner'])
        <div class="max-height-420px overflow-hidden d-flex align-items-center">
            <img src="{{ getStorageImages(path: $shopInfoArray['bottom_banner_full_url'], type:'shop-banner') }}"
                 class="dark-support rounded img-fit" alt="">
        </div>
    @endif
</div>
