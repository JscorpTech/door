@extends('layouts.front-end.app')

@section('title', translate('Restock_Requests'))

@section('content')
    <div class="container py-2 py-md-4 p-0 p-md-2 user-profile-container px-5px">
        <div class="row">
            @include('web-views.partials._profile-aside')

            <section class="col-lg-9 __customer-profile customer-profile-wishlist px-0">
                <div class="card h-100">
                    <div class="card-body">
                        @if($restockProducts->count() > 0)
                            <div class="d-flex align-items-center justify-content-between gap-2 mb-3">
                                <h5 class="font-bold m-0 fs-16">{{translate('Restock_Requests')}}</h5>
                                <div class="d-flex flex-wrap gap-3 justify-content-end flex-grow-1">
                                        <a href="javascript:" class="btn btn-soft-danger text-danger call-route-alert"
                                           data-route="{{ route('user-restock-request-delete') }}"
                                           data-message="{{translate('want_to_clear_all_restock_request_data')}}?">
                                            {{translate('clear_all')}}
                                        </a>
                                    <button class="profile-aside-btn btn btn--primary px-2 rounded px-2 py-1 d-lg-none">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 15 15" fill="none">
                                            <path fill-rule="evenodd" clip-rule="evenodd"
                                                d="M7 9.81219C7 9.41419 6.842 9.03269 6.5605 8.75169C6.2795 8.47019 5.898 8.31219 5.5 8.31219C4.507 8.31219 2.993 8.31219 2 8.31219C1.602 8.31219 1.2205 8.47019 0.939499 8.75169C0.657999 9.03269 0.5 9.41419 0.5 9.81219V13.3122C0.5 13.7102 0.657999 14.0917 0.939499 14.3727C1.2205 14.6542 1.602 14.8122 2 14.8122H5.5C5.898 14.8122 6.2795 14.6542 6.5605 14.3727C6.842 14.0917 7 13.7102 7 13.3122V9.81219ZM14.5 9.81219C14.5 9.41419 14.342 9.03269 14.0605 8.75169C13.7795 8.47019 13.398 8.31219 13 8.31219C12.007 8.31219 10.493 8.31219 9.5 8.31219C9.102 8.31219 8.7205 8.47019 8.4395 8.75169C8.158 9.03269 8 9.41419 8 9.81219V13.3122C8 13.7102 8.158 14.0917 8.4395 14.3727C8.7205 14.6542 9.102 14.8122 9.5 14.8122H13C13.398 14.8122 13.7795 14.6542 14.0605 14.3727C14.342 14.0917 14.5 13.7102 14.5 13.3122V9.81219ZM12.3105 7.20869L14.3965 5.12269C14.982 4.53719 14.982 3.58719 14.3965 3.00169L12.3105 0.915687C11.725 0.330188 10.775 0.330188 10.1895 0.915687L8.1035 3.00169C7.518 3.58719 7.518 4.53719 8.1035 5.12269L10.1895 7.20869C10.775 7.79419 11.725 7.79419 12.3105 7.20869ZM7 2.31219C7 1.91419 6.842 1.53269 6.5605 1.25169C6.2795 0.970186 5.898 0.812187 5.5 0.812187C4.507 0.812187 2.993 0.812187 2 0.812187C1.602 0.812187 1.2205 0.970186 0.939499 1.25169C0.657999 1.53269 0.5 1.91419 0.5 2.31219V5.81219C0.5 6.21019 0.657999 6.59169 0.939499 6.87269C1.2205 7.15419 1.602 7.31219 2 7.31219H5.5C5.898 7.31219 6.2795 7.15419 6.5605 6.87269C6.842 6.59169 7 6.21019 7 5.81219V2.31219Z"
                                                fill="white"/>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        @endif
                        @if(count($restockProducts) > 0)
                            <div class="row g-3">
                            @foreach($restockProducts as $key => $restockProduct)
                                <div class="col-md-6">
                                    <div class="wishlist-item restock-request-item">
                                        <div class="wishlist-img position-relative">
                                            <a href="" class="d-block h-100">
                                                <img class="__img-full" src="{{ getStorageImages(path: $restockProduct?->product?->thumbnail_full_url, type: 'backend-product') }}" alt="Wishlist">
                                            </a>
                                            @if(getProductPriceByType(product: $restockProduct?->product, type: 'discount', result: 'value') > 0)
                                                <span class="for-discount-value px-1 font-bold fs-13 direction-ltr">
                                                     -{{ getProductPriceByType(product: $restockProduct?->product, type: 'discount', result: 'string') }}
                                                </span>
                                            @endif
                                        </div>
                                        @php
                                            $overallRating = $restockProduct?->product?->reviews ? getOverallRating($restockProduct?->product?->reviews) : 0;
                                        @endphp
                                        <div class="wishlist-cont align-items-end align-items-sm-center">
                                            <div class="wishlist-text">
                                                <div class="font-name">
                                                    <a class="fs-12 font-semibold line-height-16"
                                                       href="{{ $restockProduct?->product?->slug ? route('product', $restockProduct?->product?->slug) : 'javascript' }}"
                                                    >
                                                        {{ $restockProduct?->product?->name}}
                                                    </a>
                                                </div>
                                                <div class="star-rating me-2">
                                                    @for($inc=1;$inc<=5;$inc++)
                                                        @if ($inc <= (int)$overallRating[0])
                                                            <i class="tio-star text-warning"></i>
                                                        @elseif ($overallRating[0] != 0 && $inc <= (int)$overallRating[0] + 1.1 && $overallRating[0] > ((int)$overallRating[0]))
                                                            <i class="tio-star-half text-warning"></i>
                                                        @else
                                                            <i class="tio-star-outlined text-warning"></i>
                                                        @endif
                                                    @endfor
                                                        <span class="text-muted">({{count($restockProduct?->product?->reviews)}})</span>
                                                </div>
                                                <br>
                                                @if($restockProduct['variant'])
                                                    <span class="sellerName fs-12"> {{ translate('Variant :') }}
                                                        <span class="text-dark">
                                                            {{$restockProduct['variant']  }}
                                                        </span>
                                                    </span>
                                                @else
                                                    <span class="sellerName fs-12"> {{ translate('Brand :') }}
                                                        <span class="text-dark">
                                                            @if(getWebConfig(name: 'product_brand'))
                                                                {{ $restockProduct?->product?->brand?->name ?? ''  }}
                                                            @endif
                                                        </span>
                                                    </span>
                                                @endif

                                                <div class=" mt-sm-1">
                                                <span class="font-weight-bold amount text-dark price-range d-flex align-items-center gap-2">
                                                     @php
                                                         $productPrices = $restockProduct?->product?->unit_price;
                                                         $restockProductsList = json_decode($restockProduct?->product?->variation, true);
                                                         if(!empty($restockProductsList) && count($restockProductsList) > 0) {
                                                             foreach ($restockProductsList as $item) {
                                                                 if ($item['type'] === $restockProduct->variant) {
                                                                     $productPrices = $item['price'];
                                                                 }
                                                             }
                                                         }
                                                     @endphp
                                                    @if(getProductPriceByType(product: $restockProduct?->product, type: 'discount', result: 'value') > 0)
                                                        <span class="discounted-unit-price font-bold">
                                                            {{ getProductPriceByType(product: $restockProduct?->product, type: 'discounted_unit_price', result: 'string', price: $productPrices) }}
                                                        </span>
                                                        <del class="product-total-unit-price align-middle text-muted fs-18 font-semibold">
                                                            {{ webCurrencyConverter(amount: $productPrices) }}
                                                        </del>
                                                    @else
                                                        <span class="product-total-unit-price align-middle fs-15 font-bold">{{ webCurrencyConverter(amount: $productPrices) }}</span>
                                                    @endif
                                                </span>
                                                </div>
                                            </div>
                                            <a href="{{ route('user-restock-request-delete', ['id' => $restockProduct['id']]) }}" class="close-stock-req cursor-pointer" data-id="product-{{ $restockProduct['id']}}">
                                                <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    <path d="M15.8346 5.34163L14.6596 4.16663L10.0013 8.82496L5.34297 4.16663L4.16797 5.34163L8.8263 9.99996L4.16797 14.6583L5.34297 15.8333L10.0013 11.175L14.6596 15.8333L15.8346 14.6583L11.1763 9.99996L15.8346 5.34163Z" fill="#061C3D" fill-opacity="0.5"/>
                                                </svg>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        @else
                            <div class="d-flex justify-content-center align-items-center h-100">
                                <div class="login-card w-100 border-0 shadow-none">
                                    <div class="text-center py-3 text-capitalize">
                                        <img src="{{ theme_asset(path: 'public/assets/front-end/img/icons/wishlist.png') }}" alt="" class="mb-4" width="70">
                                        <h5 class="fs-14">{{ translate('no_product_found_in_restock_request') }}!</h5>
                                    </div>
                                </div>
                            </div>
                        @endif
                        @if (count($restockProducts) > 0)
                            <div class="my-4 d-flex justify-content-center" id="paginator-ajax">
                                {!! $restockProducts->links() !!}
                            </div>
                        @endif

                    </div>
                </div>
            </section>

        </div>
    </div>
@endsection
