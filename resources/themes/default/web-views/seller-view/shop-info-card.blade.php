<div class="position-relative z-index-99 rtl w-100 text-align-direction {{ $displayClass }}">
    <div class="__rounded-10 bg-white position-relative">
        <div class="d-flex flex-wrap justify-content-between seller-details">
            <div class="d-flex align-items-center p-2 flex-grow-1">
                <div class="">
                    @if($shopInfoArray['id'] != 0)
                        <div class="position-relative overflow-hidden d-flex align-items-center aspect-1 rounded w-90px">
                            @if($shopInfoArray['temporary_close'] || $shopInfoArray['temporary_close'])
                                <span class="temporary-closed-details p-1">
                                    <span>{{translate('Temporary_OFF')}}</span>
                                </span>
                            @elseif(($seller_id==0 && $shopInfoArray['vacation_status'] && $shopInfoArray['current_date'] >= $shopInfoArray['vacation_start_date'] && $shopInfoArray['current_date'] <= $shopInfoArray['vacation_end_date']) ||
                            $seller_id!=0 && $shopInfoArray['vacation_status'] && $shopInfoArray['current_date'] >= $shopInfoArray['vacation_start_date'] && $shopInfoArray['current_date'] <= $shopInfoArray['vacation_end_date'])
                                <span class="temporary-closed-details p-1">
                                    <span>{{translate('closed_now')}}</span>
                                </span>
                            @endif
                            <img class="img-fluid" alt=""
                                 src="{{ getStorageImages(path: $shopInfoArray['image_full_url'], type: 'shop') }}">
                        </div>
                    @else
                        <div class="position-relative overflow-hidden d-flex align-items-center aspect-1 rounded w-90px">
                            @if($shopInfoArray['temporary_close'] || $shopInfoArray['temporary_close'])
                                <span class="temporary-closed-details">
                                    <span>{{translate('Temporary_OFF')}}</span>
                                </span>
                            @elseif(($seller_id==0 && $shopInfoArray['vacation_status'] && $shopInfoArray['current_date'] >= $shopInfoArray['vacation_start_date'] && $shopInfoArray['current_date'] <= $shopInfoArray['vacation_end_date']) ||
                            $seller_id!=0 && $shopInfoArray['vacation_status'] && $shopInfoArray['current_date'] >= $shopInfoArray['vacation_start_date'] && $shopInfoArray['current_date'] <= $shopInfoArray['vacation_end_date'])
                                <span class="temporary-closed-details">
                                    <span>{{translate('closed_now')}}</span>
                                </span>
                            @endif
                            <img class="img-fluid" alt=""
                                 src="{{ getStorageImages(path: $web_config['fav_icon'], type: 'shop') }}">
                        </div>
                    @endif
                </div>
                <div class="__w-100px flex-grow-1 {{Session::get('direction') === "rtl" ? ' pr-2 pr-sm-4' : ' pl-2 pl-sm-4'}}">
                    <div class="font-weight-bolder mb-2">
                        @if($shopInfoArray['id'] != 0)
                            {{ $shopInfoArray['name']}}
                        @else
                            {{ $web_config['company_name'] }}
                        @endif
                    </div>
                    <div class="d-flex flex-column gap-1">
                        <div class="fs-12">
                            @for ($i = 1; $i <= 5; $i++)
                                @if ($i <=$shopInfoArray['average_rating'])
                                    <i class="tio-star text-warning"></i>
                                @elseif ($shopInfoArray['average_rating'] != 0 && $i <= (int)$shopInfoArray['average_rating'] + 1 && $shopInfoArray['average_rating']>=((int)$shopInfoArray['average_rating']+.30))
                                    <i class="tio-star-half text-warning"></i>
                                @else
                                    <i class="tio-star-outlined text-warning"></i>
                                @endif
                            @endfor
                            <span class="ml-1">({{ round($shopInfoArray['average_rating'], 1) }})</span>
                            <span class="__inline-69"></span>
                            <span class="text-nowrap fs-13 font-semibold text-base">
                                {{ $shopInfoArray['total_review']}} {{translate('reviews')}}
                            </span>
                        </div>

                        <div class="d-flex flex-wrap py-1 fs-12 web-text-primary">

                            <span class="text-nowrap">{{ $shopInfoArray['total_order']}} {{translate('orders')}}</span>
                            @php($minimum_order_amount_status = getWebConfig(name: 'minimum_order_amount_status'))
                            @php($minimum_order_amount_by_seller = getWebConfig(name: 'minimum_order_amount_by_seller'))
                            @if ($minimum_order_amount_status ==1 && $minimum_order_amount_by_seller ==1)
                                <span class="__inline-69"></span>
                                <span>{{ webCurrencyConverter(amount: $shopInfoArray['minimum_order_amount']) }} {{translate('minimum_order_amount')}}</span>
                            @endif
                        </div>

                    </div>
                </div>
            </div>

            <div class="d-flex align-items-center">
                <div class="{{Session::get('direction') === "rtl" ? 'ml-sm-4' : 'mr-sm-4'}}">
                    @if (auth('customer')->check())
                        <div class="d-flex">
                            @if($seller_id == 0)
                                <button class="btn btn--primary __inline-70 rounded-10 btn-sm text-capitalize chat-with-seller-button d-none d-sm-inline-block" data-toggle="modal"
                                        data-target="#exampleModal"
                                        @if(($seller_id == 0 && $shopInfoArray['vacation_status'] && $shopInfoArray['current_date'] >= $shopInfoArray['vacation_start_date'] && $shopInfoArray['current_date'] <= $shopInfoArray['vacation_end_date']) ||
                            $seller_id!=0 && $shopInfoArray['vacation_status'] && $shopInfoArray['current_date'] >= $shopInfoArray['vacation_start_date'] && $shopInfoArray['current_date'] <= $shopInfoArray['vacation_end_date'])
                                            disabled
                                    @endif
                                >
                                    <img src="{{theme_asset(path: 'public/assets/front-end/img/shopview-chat.png')}}" loading="eager" class="" alt="">
                                    <span class="d-none d-sm-inline-block">
                                        {{translate('chat')}}
                                    </span>
                                </button>

                                <button class="btn bg-transparent border-0 __inline-70 rounded-10  text-capitalize chat-with-seller-button d-sm-inline-block d-md-none" data-toggle="modal"
                                        data-target="#exampleModal"
                                        @if(($seller_id == 0 && $shopInfoArray['vacation_status'] && $shopInfoArray['current_date'] >= $shopInfoArray['vacation_start_date'] && $shopInfoArray['current_date'] <= $shopInfoArray['vacation_end_date']) || $seller_id!=0 && $shopInfoArray['vacation_status'] && $shopInfoArray['current_date'] >= $shopInfoArray['vacation_start_date'] && $shopInfoArray['current_date'] <= $shopInfoArray['vacation_end_date']) disabled @endif >
                                    <img src="{{theme_asset(path: 'public/assets/front-end/img/icons/shopview-chat-blue.svg')}}" loading="eager" class="" alt="">
                                </button>

                            @else
                                <button class="btn btn--primary __inline-70 rounded-10 btn-sm text-capitalize chat-with-seller-button d-none d-sm-inline-block" data-toggle="modal"
                                        data-target="#exampleModal" {{ ($shopInfoArray['temporary_close'] || ($shopInfoArray['vacation_status'] && date('Y-m-d') >= date('Y-m-d', strtotime($shopInfoArray['vacation_start_date'])) && date('Y-m-d') <= date('Y-m-d', strtotime($shopInfoArray['vacation_end_date'])))) ? 'disabled' : '' }}>
                                    <img src="{{theme_asset(path: 'public/assets/front-end/img/shopview-chat.png')}}" loading="eager" class="" alt="">
                                    <span class="d-none d-sm-inline-block">
                                        {{translate('chat')}}
                                    </span>
                                </button>

                                <button class="btn bg-transparent border-0 __inline-70 rounded-10  text-capitalize chat-with-seller-button d-sm-inline-block d-md-none" data-toggle="modal"
                                        data-target="#exampleModal" {{ ($shopInfoArray['temporary_close'] || ($shopInfoArray['vacation_status'] && date('Y-m-d') >= date('Y-m-d', strtotime($shopInfoArray['vacation_start_date'])) && date('Y-m-d') <= date('Y-m-d', strtotime($shopInfoArray['vacation_end_date'])))) ? 'disabled' : '' }}>
                                    <img src="{{theme_asset(path: 'public/assets/front-end/img/icons/shopview-chat-blue.svg')}}" loading="eager" class="" alt="">
                                </button>
                            @endif
                        </div>
                    @else
                        <div class="d-flex">
                            <a href="{{route('customer.auth.login')}}"
                               class="btn btn--primary __inline-70 rounded-10 btn-sm text-capitalize chat-with-seller-button">
                                <img src="{{theme_asset(path: 'public/assets/front-end/img/shopview-chat.png')}}" loading="eager" class="" alt="">
                                <span class="d-none d-sm-inline-block">
                                    {{translate('chat')}}
                                </span>
                            </a>
                        </div>
                    @endif

                </div>
            </div>
        </div>
    </div>
</div>
