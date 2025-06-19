@extends('layouts.back-end.app')

@section('title', $seller?->shop->name ?? translate("shop_name_not_found"))

@section('content')
    <div class="content container-fluid">
        <div class="mb-3">
            <h2 class="h1 mb-0 text-capitalize d-flex align-items-center gap-2">
                <img src="{{dynamicAsset(path: 'public/assets/back-end/img/add-new-seller.png')}}" alt="">
                {{translate('Vendor_details')}}
            </h2>
        </div>
        <div class="flex-between d-sm-flex row align-items-center justify-content-between mb-2 mx-1">
            <div>
                @if ($seller->status=="pending")
                    <div class="mt-4">
                        <div class="flex-start">
                            <div class="mx-1"><h4><i class="tio-shop-outlined"></i></h4></div>
                            <div>{{translate('vendor_request_for_open_a_shop')}}</div>
                        </div>
                        <div class="text-center">
                            <form class="d-inline-block" action="{{route('admin.vendors.updateStatus')}}" method="POST">
                                @csrf
                                <input type="hidden" name="id" value="{{$seller->id}}">
                                <input type="hidden" name="status" value="approved">
                                <button type="submit"
                                        class="btn btn--primary btn-sm">{{translate('approve')}}</button>
                            </form>
                            <form class="d-inline-block" action="{{route('admin.vendors.updateStatus')}}" method="POST">
                                @csrf
                                <input type="hidden" name="id" value="{{$seller->id}}">
                                <input type="hidden" name="status" value="rejected">
                                <button type="submit"
                                        class="btn btn-danger btn-sm">{{translate('reject')}}</button>
                            </form>
                        </div>
                    </div>
                @endif
            </div>
        </div>
        <div class="page-header">
            <div class="flex-between row mx-1">
                <div>
                    <h1 class="page-header-title">{{ $seller?->shop->name ?? translate("shop_Name")." : ".translate("update_Please") }}</h1>
                </div>
            </div>
            <div class="js-nav-scroller hs-nav-scroller-horizontal">
                <ul class="nav nav-tabs flex-wrap page-header-tabs">
                    <li class="nav-item">
                        <a class="nav-link "
                           href="{{ route('admin.vendors.view',$seller->id) }}">{{translate('shop')}}</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link"
                           href="{{ route('admin.vendors.view',['id'=>$seller->id, 'tab'=>'order']) }}">{{translate('order')}}</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link"
                           href="{{ route('admin.vendors.view',['id'=>$seller->id, 'tab'=>'product']) }}">{{translate('product')}}</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active"
                           href="{{ route('admin.vendors.view',['id'=>$seller['id'], 'tab'=>'clearance_sale']) }}">{{translate('clearance_sale_products')}}</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link"
                           href="{{ route('admin.vendors.view',['id'=>$seller->id, 'tab'=>'setting']) }}">{{translate('setting')}}</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link"
                           href="{{ route('admin.vendors.view',['id'=>$seller->id, 'tab'=>'transaction']) }}">{{translate('transaction')}}</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link"
                           href="{{ route('admin.vendors.view',['id'=>$seller->id, 'tab'=>'review']) }}">{{translate('review')}}</a>
                    </li>
                </ul>
            </div>
        </div>
        <div class="tab-content">
            <div class="tab-pane fade show active" id="clearance_sale">
                <div class="row g-1">
                    <div class="col-md-12">
                        @if(!$clearanceConfig)
                            <div class="card">
                                <div class="card-body">
                                    <div class="p-4 bg-chat rounded text-center">
                                        <div class="py-5">
                                            <img src="{{ dynamicAsset('public/assets/back-end/img/empty-clearance.png') }}" width="64"
                                                 alt="">
                                            <div class="mx-auto my-3 max-w-353px">
                                                {{ translate('No_Clearance_Sale_available_now') }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="card">
                                <div class="card-body">
                                    <div class="d-flex gap-3 flex-wrap flex-lg-nowrap font-size-sm align-items-center justify-content-center">
                                        <div class="w-170 text-center">
                                            <img src="{{ dynamicAsset('public/assets/back-end/img/clearance-sale.png') }}" width="100"
                                                 alt="">
                                        </div>
                                        <div class="row g-2 flex-grow-1 w-100">
                                            <div class="col-md-6">
                                                <div class="bg-light p-3 border border-primary-light rounded h-100">
                                                    <div class="d-flex gap-5">
                                                        <div class="pair-list w-100 key-basis-100">
                                                            <div>
                                                                <span class="key">{{translate('duration')}}</span>
                                                                <span class="px-2">:</span>
                                                                <span class="value font-weight-semibold">{{ $clearanceConfig?->duration_start_date->format('d M Y') }} - {{ $clearanceConfig?->duration_end_date->format('d M Y') }}</span>
                                                            </div>

                                                            <div>
                                                                <span class="key">{{ translate('offer_active') }}</span>
                                                                <span class="px-2">:</span>
                                                                <span class="value font-weight-semibold">{{ ucwords(str_replace('_', " ", $clearanceConfig?->offer_active_time)) . ' '. ($clearanceConfig?->offer_active_time == 'always' ? '' : translate('in_a_day')) }}</span>
                                                            </div>
                                                            @if($clearanceConfig?->offer_active_time == 'specific_time')
                                                                <div>
                                                                    <span class="key">{{translate('time')}}</span>
                                                                    <span class="px-2">:</span>
                                                                    <span class="value font-weight-semibold">{{ $clearanceConfig?->offer_active_range_start->format('h:i A') }} - {{ $clearanceConfig?->offer_active_range_end->format('h:i A') }}</span>
                                                                </div>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="bg-light p-3 border border-primary-light rounded h-100">
                                                    <div class="d-flex gap-5">
                                                        <div class="pair-list w-100 key-basis-100">
                                                            <div>
                                                                <span class="key">{{ translate('discount_type') }}</span>
                                                                <span class="px-2">:</span>
                                                                <span class="value font-weight-semibold">{{ ucwords(str_replace('_', " ", $clearanceConfig?->discount_type)) . ' ' .  ($clearanceConfig?->discount_type == 'flat' ? translate('discount') : '')}}</span>
                                                            </div>
                                                            @if($clearanceConfig?->discount_type == 'flat')
                                                                <div>
                                                                    <span class="key">{{ translate('discount_amount') }}</span>
                                                                    <span class="px-2">:</span>
                                                                    <span class="value font-weight-semibold">{{ $clearanceConfig?->discount_amount }}%</span>
                                                                </div>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                    <div class="col-md-12">
                        @if(count($stockClearanceProduct) == 0 && request()->has('searchValue'))
                            <div class="card">
                                <div class="card-header d-flex gap-10 flex-wrap">
                                    <h4 class="m-0">
                                        {{ translate('Product_List') }}
                                        @if($stockClearanceProduct->total() > 0)
                                            <span class="badge badge-soft-dark radius-50 fz-14 ml-1">
                                                {{ $stockClearanceProduct->total() }}
                                            </span>
                                        @endif
                                    </h4>
                                    <div class="d-flex flex-wrap justify-content-end gap-3">
                                        <form action="{{ url()->current() }}" method="GET">
                                            <div class="input-group input-group-custom input-group-merge">
                                                <div class="input-group-prepend">
                                                    <div class="input-group-text">
                                                        <i class="tio-search"></i>
                                                    </div>
                                                </div>
                                                <input id="datatableSearch_" type="search" name="searchValue" class="form-control"
                                                       placeholder="{{ translate('search_by_product_name') }}..." aria-label="Search by Order ID" value="{{ request('searchValue') }}">
                                                <button type="submit" class="btn btn--primary input-group-text">
                                                    {{ translate('Search') }}
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                                <div class="card-body p-0">
                                    <div class="table-responsive datatable-custom">
                                        <table
                                            class="table table-hover table-borderless table-thead-bordered table-nowrap table-align-middle card-table w-100">
                                            <thead class="thead-light thead-50 text-capitalize">
                                            <tr>
                                                <th>{{ translate('sl') }}</th>
                                                <th>
                                                    <div class="d-flex">
                                                        <div class="w-60px">{{translate('Image')}}</div>
                                                        <div>{{translate('name')}}</div>
                                                    </div>
                                                </th>
                                                <th class="text-center">
                                                    {{ translate('unit_price') }}
                                                    ({{ getCurrencySymbol(currencyCode: getCurrencyCode()) }})
                                                </th>
                                                @if(isset($clearanceConfig->discount_type) && $clearanceConfig->discount_type == 'product_wise')
                                                    <th class="text-center">{{ translate('discount_amount') }} </th>
                                                @else
                                                    <th class="text-center">{{ translate('discount_amount') . ' (%)' }} </th>
                                                @endif
                                                <th class="text-center">
                                                    {{ translate('discount_price') }}
                                                    ({{ getCurrencySymbol(currencyCode: getCurrencyCode()) }})
                                                </th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            </tbody>
                                        </table>
                                    </div>
                                    @include('layouts.back-end._empty-state',['text'=>'no_product_found'],['image'=>'default'])
                                </div>
                            </div>
                        @elseif(count($stockClearanceProduct) < 1 && $clearanceConfig)
                            <div class="card mb-3">
                                <div class="card-body">
                                    <div class="p-4 bg-chat rounded text-center">
                                        <div class="py-5">
                                            <img src="{{ dynamicAsset('public/assets/back-end/img/empty-product.png') }}" width="64"
                                                 alt="">
                                            <div class="mx-auto my-3 max-w-353px">
                                                {{ translate('no_products_Are_added') }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @else
                            @if($clearanceConfig)
                                <div class="card">
                                    <div class="card-header d-flex gap-10 flex-wrap">
                                        <h4 class="m-0">
                                            {{ translate('Product_List') }}
                                            <span class="badge badge-soft-dark radius-50 fz-14 ml-1">{{ $stockClearanceProduct->total() }}</span>
                                        </h4>
                                        <div class="d-flex flex-wrap justify-content-end gap-3">
                                            <form action="{{ url()->current() }}" method="GET">
                                                <div class="input-group input-group-custom input-group-merge">
                                                    <div class="input-group-prepend">
                                                        <div class="input-group-text">
                                                            <i class="tio-search"></i>
                                                        </div>
                                                    </div>
                                                    <input id="datatableSearch_" type="search" name="searchValue" class="form-control"
                                                           placeholder="{{ translate('search_by_product_name') }}..." aria-label="Search by Order ID" value="{{ request('searchValue') }}">
                                                    <button type="submit" class="btn btn--primary input-group-text">
                                                        {{ translate('Search') }}
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                    <div class="card-body p-0">
                                        <div class="table-responsive datatable-custom">
                                            <table
                                                class="table table-hover table-borderless table-thead-bordered table-nowrap table-align-middle card-table w-100">
                                                <thead class="thead-light thead-50 text-capitalize">
                                                <tr>
                                                    <th>{{ translate('sl') }}</th>
                                                    <th>
                                                        <div class="d-flex">
                                                            <div class="w-60px">{{translate('Image')}}</div>
                                                            <div>{{translate('name')}}</div>
                                                        </div>
                                                    </th>
                                                    <th class="text-center">
                                                        {{ translate('unit_price') }}
                                                        ({{ getCurrencySymbol(currencyCode: getCurrencyCode()) }})
                                                    </th>
                                                    @if(isset($clearanceConfig->discount_type) && $clearanceConfig->discount_type == 'product_wise')
                                                        <th class="text-center">{{ translate('discount_amount') }} </th>
                                                    @else
                                                        <th class="text-center">{{ translate('discount_amount') . ' (%)' }} </th>
                                                    @endif
                                                    <th class="text-center">
                                                        {{ translate('discount_price') }}
                                                        ({{ getCurrencySymbol(currencyCode: getCurrencyCode()) }})
                                                    </th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                @foreach($stockClearanceProduct as $key => $clearanceProduct)
                                                    <tr>
                                                        <td>{{ $key+1 }}</td>
                                                        <td>
                                                            <a href="{{ route('admin.products.view', ['addedBy' => ($clearanceProduct?->product['added_by'] == 'seller' ? 'vendor' : 'in-house'), 'id' => $clearanceProduct?->product['id']]) }}"
                                                               class="title-color hover-c1 d-flex align-items-center gap-10">
                                                                <img src="{{ getStorageImages(path:$clearanceProduct?->product?->thumbnail_full_url , type: 'backend-product') }}"
                                                                     class="rounded border-gray-op aspect-1" alt="" width="60">
                                                                <div class="max-w-200">
                                                                    <h6 class="text-truncate">
                                                                        {{ $clearanceProduct?->product?->name }}
                                                                    </h6>
                                                                    <div class="fs-12 title-color">
                                                                        <div class="border-between wrap gap-1">

                                                                            @if($clearanceProduct?->product?->product_type !== 'digital')
                                                                                <span class="parent">
                                                                                    <span class="opacity--70">
                                                                                        {{ translate('current_stock') }}
                                                                                    </span>
                                                                                    {{ $clearanceProduct?->product?->current_stock }}
                                                                                </span>
                                                                            @endif
                                                                            <span class="parent">
                                                                                <span class="opacity--70">
                                                                                    {{ translate('category') }}:
                                                                                </span>
                                                                                {{ $clearanceProduct?->product?->category->name ?? translate('not_found') }}
                                                                            </span>
                                                                            @if($clearanceProduct?->product?->product_type !== 'digital')
                                                                                <span class="parent">
                                                                                    <span class="opacity--70">
                                                                                        {{translate('brand')}}:
                                                                                    </span>
                                                                                    {{ $clearanceProduct?->product?->brand->name ?? translate('not_found') }}
                                                                                </span>
                                                                            @endif
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </a>
                                                        </td>
                                                        <td>
                                                            <div class="text-center">
                                                                {{setCurrencySymbol(amount: usdToDefaultCurrency(amount: $clearanceProduct?->product?->unit_price), currencyCode: getCurrencyCode()) }}
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <div class="d-flex justify-content-center align-items-center gap-2">
                                                                <div class="text-center">
                                                                    @if($clearanceConfig?->discount_type == 'flat')
                                                                        <div class="text-center">
                                                                            {{ $clearanceProduct->discount_amount }}
                                                                        </div>
                                                                    @else
                                                                        @if($clearanceProduct?->discount_type == 'flat')
                                                                            {{ setCurrencySymbol(amount: usdToDefaultCurrency(amount: $clearanceProduct->discount_amount), currencyCode: getCurrencyCode()) }}
                                                                        @else
                                                                            {{ $clearanceProduct->discount_amount. ' %' }}
                                                                        @endif
                                                                    @endif
                                                                </div>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <div class="text-center">
                                                                @if($clearanceConfig?->discount_type == 'flat')
                                                                    {{setCurrencySymbol(amount: usdToDefaultCurrency(amount: $clearanceProduct?->product?->unit_price - ($clearanceProduct?->product?->unit_price * $clearanceProduct->discount_amount) / 100), currencyCode: getCurrencyCode()) }}
                                                                @else
                                                                    @php($discountAmount = $clearanceProduct->discount_type === 'percentage'? ($clearanceProduct?->product?->unit_price - ($clearanceProduct?->product?->unit_price * $clearanceProduct->discount_amount) / 100): ($clearanceProduct?->product?->unit_price - $clearanceProduct->discount_amount))
                                                                    {{setCurrencySymbol(amount: usdToDefaultCurrency(amount: $discountAmount), currencyCode: getCurrencyCode()) }}
                                                                @endif
                                                            </div>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="discount-update-modal">
        <div class="modal-dialog">
            <div class="modal-content" style="max-height: 80vh; overflow-y: auto;">
                <div class="modal-header">
                    <button type="button" class="close p-0 fz-22" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true"><i class="tio-clear"></i></span>
                    </button>
                </div>
                <div class="modal-body">
                    <form action="{{route('vendor.clearance-sale.update-discount')}}" method="post" class="discount-amount-update">
                        @csrf
                        <input type="hidden" name="product_id">
                        <input type="hidden" name="id">
                        <div class="mb-30">
                            <a href="" class="title-color hover-c1 d-flex align-items-center gap-10">
                                <img src="{{ asset('/public/assets/back-end/img/160x160/img2.jpg') }}"
                                     class="rounded border-gray-op" alt="" width="60">
                                <h6 class="fz-14 font-medium">
                                    {{ translate('Family Size Trolley Case Long Lasting and 8 Wheel Waterproof Travel bag') }}

                                </h6>
                            </a>
                            <div class="mt-30">
                                <label class="form-label title-color font-weight-medium fz-14 title-color font-medium">{{ translate('Discount Amount') }}
                                    <span id="discount-symbol">(%)</span>
                                </label>
                                <div class="input-group">
                                    <input type="number" name="discount_amount" class="form-control" placeholder="Ex : 10" placeholder="">
                                    <div class="input-group-append">
                                        <select name="discount_type" id="discount_type" class="form-control js-select2-custom">
                                            <option value="percentage">%</option>
                                            <option value="flat">$</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="btn--container justify-content-end">
                            <button class="btn btn-danger-light font-weight-semibold" data-dismiss="modal"
                                    type="reset">{{ translate('Cancel') }}</button>
                            <button class="btn btn--primary font-weight-semibold discount-amount-submit"
                                    type="button">{{ translate('Update') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

@endsection
