@extends('theme-views.layouts.app')

@section('title', translate('Restock_Requests').' | '.$web_config['company_name'].' '.translate('ecommerce'))
@section('content')
    <main class="main-content d-flex flex-column gap-3 py-3 mb-4">
        <div class="container">
            <div class="row g-3">
                @include('theme-views.partials._profile-aside')
                <div class="col-lg-9">
                    <div class="card h-lg-100">
                        <div class="card-body p-lg-4">
                            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
                                @if($restockProducts->count() > 0)
                                    <h5>{{translate('my_request_list')}}</h5>
                                    <a href="javascript:" class="btn btn-outline-danger call-route-alert" type="button"
                                       data-route="{{ route('user-restock-request-delete') }}"
                                       data-message="{{translate('want_to_clear_all_restock_request_data')}}?">
                                        {{translate('clear_all')}}
                                    </a>
                                @endif
                            </div>
                            @if($restockProducts->count()>0)
                                <div class="table-responsive d-none d-md-block">
                                    <table class="table align-middle table-striped">
                                        <thead class="text-primary">
                                            <tr>
                                                <th>SL</th>
                                                <th class="text-capitalize">Product details</th>
                                                <th>Review</th>
                                                <th class="text-center">Price</th>
                                                <th class="text-center">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        @foreach($restockProducts as $key => $restockProduct)
                                            <tr>
                                                <td> {{ $restockProducts ->firstItem()+$key }}</td>
                                                <td>
                                                    <div class="media gap-3 align-items-center mn-w200">
                                                        <div class="avatar rounded size-3-75rem aspect-1 overflow-hidden d-flex align-items-center">
                                                            <img class="img-fit dark-support rounded" alt="" src="{{ getStorageImages(path: $restockProduct?->product?->thumbnail_full_url, type: 'backend-product') }}">
                                                        </div>
                                                        <div class="media-body">
                                                            <h6>
                                                                <a class="line-limit-1 max-w-200px"
                                                                   href="{{ $restockProduct?->product?->slug ? route('product', $restockProduct?->product?->slug) : 'javascript' }}">
                                                                    {{ $restockProduct?->product?->name }}
                                                                </a>
                                                            </h6>
                                                            <div class="fs-12">
                                                                @if($restockProduct['variant'])
                                                                    <div>
                                                                        <span class="text-muted">{{ translate('Variant :') }}</span>
                                                                        <span>:</span>
                                                                        <span class="text-muted">{{$restockProduct['variant']  }}</span>
                                                                    </div>
                                                                @else
                                                                    <div>
                                                                        <span class="text-muted">{{ translate('Brand :') }}</span>
                                                                        <span>:</span>
                                                                        <span class="text-muted">
                                                                        @if(getWebConfig(name: 'product_brand'))
                                                                                {{ $restockProduct?->product?->brand?->name ?? ''  }}
                                                                            @endif
                                                                        </span>
                                                                    </div>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </div>
                                                </td>
                                                @php
                                                    $overallRating = $restockProduct?->product?->reviews ? getOverallRating($restockProduct?->product?->reviews) : 0;
                                                @endphp
                                                <td>
                                                    <div class="d-inline-flex align-items-center text-gold mb-2">
                                                        <i class="bi bi-star-fill"></i>
                                                        <div class="text-dark ms-1">
                                                            <span>{{ isset($overallRating[0]) ? $overallRating[0] : 0 }}</span>
                                                            <span class="text-muted ms-1">({{ count($restockProduct?->product?->reviews) }}&nbsp;{{ translate('review') }})</span>
                                                        </div>
                                                    </div>
                                                </td>
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
                                                <td class="text-center">
                                                    @if($restockProduct?->product?->discount > 0)
                                                        <div class="text-dark">
                                                            {{ getProductPriceByType(product: $restockProduct?->product, type: 'discounted_unit_price', result: 'string', price: $productPrices) }}
                                                        </div>
                                                        <div class="text-muted fs-12">
                                                            <del>
                                                                {{ webCurrencyConverter(amount: $productPrices) }}
                                                            </del>
                                                        </div>
                                                    @else
                                                        <div class="text-dark">
                                                            {{ webCurrencyConverter(amount: $productPrices) }}
                                                        </div>
                                                    @endif
                                                </td>
                                                <td>
                                                    <div class="d-flex justify-content-center gap-2 align-items-center">
                                                        <a href="{{ $restockProduct?->product?->slug ? route('product', $restockProduct?->product?->slug) : 'javascript' }}"
                                                           class="btn btn-outline-primary btn-action">
                                                            <i class="bi bi-eye-fill"></i>
                                                        </a>
                                                        <a href="{{ route('user-restock-request-delete', ['id' => $restockProduct['id']]) }}" class="btn btn-outline-danger btn-action">
                                                            <i class="bi bi-trash3-fill"></i>
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="d-flex flex-column justify-content-center align-items-center gap-2 py-3 w-100">
                                    <img width="80" class="mb-3" src="{{ theme_asset('assets/img/empty-state/empty-wishlist.svg') }}" alt="">
                                    <h5 class="text-center text-muted">
                                        {{ translate('You_have_not_added_product_to_restock_request_yet') }}!
                                    </h5>
                                </div>
                            @endif
                            @foreach($restockProducts as $key => $restockProduct)
                            <div class="d-flex d-md-none gap-3 flex-column">
                                <div class="media gap-3 bg-light p-3 rounded">
                                    <div class="avatar border rounded size-3-437rem">
                                        <img src="{{ getStorageImages(path: $restockProduct?->product->thumbnail_full_url, type: 'backend-product') }}" class="img-fit dark-support rounded" alt="">
                                    </div>
                                    <div class="media-body d-flex flex-column gap-1">
                                        <a href="{{ $restockProduct?->product?->slug ? route('product', $restockProduct?->product?->slug) : 'javascript' }}">
                                            <h6 class="text-capitalize width--20ch line-limit-2 link-hover-base">
                                                {{ $restockProduct?->product?->name}}
                                            </h6>
                                        </a>
                                        @php
                                            $overallRating = $restockProduct?->product?->reviews ? getOverallRating($restockProduct?->product?->reviews) : 0
                                        @endphp
                                        <div class="d-inline-flex align-items-center text-gold mb-2">
                                            <i class="bi bi-star-fill"></i>
                                            <div class="text-dark ms-1">
                                                <span>{{ isset($overallRating[0]) ? $overallRating[0] : 0}}</span>
                                                <span class="text-muted ms-1">({{count($restockProduct?->product?->reviews)}}&nbsp;{{ translate('review') }}))</span>
                                            </div>
                                        </div>
                                        <div>
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
                                            <div class="text-dark mb-1">
                                                <span>{{ translate('Total_price :') }}</span>
                                                    <strong>{{ webCurrencyConverter(amount: $productPrices) }} </strong>
                                            </div>
                                            <div class="fs-12">
                                                    @if($restockProduct['variant'])
                                                        <div>
                                                            <span class="text-muted">{{ translate('Variant :') }}</span>
                                                            <span>:</span>
                                                            <span class="text-muted">{{$restockProduct['variant']  }}</span>
                                                        </div>
                                                    @else
                                                        <div>
                                                            <span class="text-muted">{{ translate('Brand :') }}</span>
                                                            <span>:</span>
                                                            <span class="text-muted">
                                                                @if(getWebConfig(name: 'product_brand'))
                                                                    {{ $restockProduct?->product?->brand?->name ?? ''  }}
                                                                @endif
                                                            </span>
                                                        </div>
                                                    @endif
                                            </div>
                                        </div>

                                        <div class="d-flex gap-2 align-items-center mt-1">
                                            <a href="{{ $restockProduct?->product?->slug ? route('product', $restockProduct?->product?->slug) : 'javascript' }}" class="btn btn-outline-success rounded-circle btn-action">
                                                <i class="bi bi-eye-fill"></i>
                                            </a>
                                            <a href="{{ route('user-restock-request-delete', ['id' => $restockProduct['id']]) }}" type="button" class="btn btn-outline-danger rounded-circle btn-action">
                                                <i class="bi bi-trash3-fill"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                            @if (count($restockProducts) > 0)
                                <div class="my-4" id="paginator-ajax">
                                    {!! $restockProducts->links() !!}
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
@endsection
