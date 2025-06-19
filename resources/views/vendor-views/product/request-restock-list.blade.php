@extends('layouts.back-end.app-seller')

@section('title', translate('restock_product_List'))

@section('content')
    <div class="content container-fluid">

        <div class="mb-3">
            <h2 class="h1 mb-0 text-capitalize d-flex gap-2">
                <img src="{{ dynamicAsset(path: 'public/assets/back-end/img/inhouse-product-list.png') }}" alt="">
                {{ translate('Request_Restock_List') }}
                <span class="badge badge-soft-dark radius-50 fz-14 ml-1">{{ $totalRestockProducts }}</span>
            </h2>
        </div>

        <div class="card">
            <div class="card-body">
                <form action="{{ url()->current() }}" method="GET">
                    <input type="hidden" value="{{ request('status') }}" name="status">
                    <div class="row gx-2">
                        <div class="col-12">
                            <h4 class="mb-3">{{ translate('filter_Products') }}</h4>
                        </div>
                        @if (request('type') == 'seller')
                            <div class="col-sm-6 col-lg-4 col-xl-3">
                                <div class="form-group">
                                    <label class="title-color" for="store">{{ translate('store') }}</label>
                                    <select name="seller_id" class="form-control text-capitalize">
                                        <option value="" selected>{{ translate('all_store') }}</option>
                                        @foreach ($sellers as $seller)
                                            <option value="{{ $seller->id}}"{{request('seller_id')==$seller->id ? 'selected' :''}}>
                                                {{ $seller->shop->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        @endif
                        <div class="col-sm-6 col-lg-4 col-xl-3">
                            <div class="form-group">
                                <label class="title-color" for="store">{{ translate('Request_Restock_Date') }}</label>
                                <div class="position-relative">
                                    <span class="tio-calendar icon-absolute-on-right"></span>
                                    <input type="text" name="restock_date" class="js-daterangepicker-with-range form-control" placeholder="{{ translate('Select_Date') }}" value="{{request('restock_date')}}" autocomplete="off">
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6 col-lg-4 col-xl-3">
                            <div class="form-group">
                                <label for="name" class="title-color">{{ translate('category') }}</label>
                                <select class="js-select2-custom form-control action-get-request-onchange" name="category_id"
                                        data-url-prefix="{{ url('/admin/products/get-categories?parent_id=') }}"
                                        data-element-id="sub-category-select"
                                        data-element-type="select">
                                    <option value="{{ old('category_id') }}" selected
                                            disabled>{{ translate('select_category') }}</option>
                                    @foreach ($categories as $category)
                                        <option value="{{ $category['id'] }}"
                                            {{ request('category_id') == $category['id'] ? 'selected' : '' }}>
                                            {{ $category['defaultName'] }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-sm-6 col-lg-4 col-xl-3">
                            <div class="form-group">
                                <label for="name" class="title-color">{{ translate('sub_Category') }}</label>
                                <select class="js-select2-custom form-control action-get-request-onchange" name="sub_category_id"
                                        id="sub-category-select"
                                        data-url-prefix="{{ url('/admin/products/get-categories?parent_id=') }}"
                                        data-element-id="sub-sub-category-select"
                                        data-element-type="select">
                                    <option value="{{request('sub_category_id') != null ? request('sub_category_id') : null}}"
                                            selected {{request('sub_category_id') != null ? '' : 'disabled'}}>{{request('sub_category_id') != null ? $subCategory['defaultName']: translate('select_Sub_Category') }}</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-sm-6 col-lg-4 col-xl-3">
                            <div class="form-group">
                                <label class="title-color" for="store">{{ translate('brand') }}</label>
                                <select name="brand_id" class="js-select2-custom form-control text-capitalize">
                                    <option value="" selected>{{ translate('select_brand') }}</option>
                                    @foreach ($brands as $brand)
                                        <option value="{{ $brand->id}}" {{request('brand_id')==$brand->id ? 'selected' :''}}>{{ $brand->default_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="d-flex gap-3 justify-content-end">
                                <a href="{{ route('vendor.products.request-restock-list') }}"
                                   class="btn btn-secondary px-5">
                                    {{ translate('reset') }}
                                </a>
                                <button type="submit" class="btn btn--primary px-5 action-get-element-type">
                                    {{ translate('show_data') }}
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="row mt-20">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header gap-3 align-items-center">
                        <h5 class="mb-0 mr-auto">
                            {{translate('request_list')}}
                            <span class="badge badge-soft-dark radius-50 fz-14 ml-1">{{ $restockProducts->total() }}</span>
                        </h5>

                        <form action="{{ url()->current() }}" method="GET">
                            <input type="hidden" name="restock_date" value="{{request('restock_date')}}">
                            <input type="hidden" name="category_id" value="{{request('category_id')}}">
                            <input type="hidden" name="sub_category_id" value="{{request('sub_category_id')}}">
                            <input type="hidden" name="brand_id" value="{{request('brand_id')}}">
                            <div class="input-group input-group-merge input-group-custom">
                                <div class="input-group-prepend">
                                    <div class="input-group-text">
                                        <i class="tio-search"></i>
                                    </div>
                                </div>
                                <input id="datatableSearch_" type="search" name="searchValue" class="form-control"
                                       placeholder="{{ translate('search_by_Product_Name')}}"  aria-label="Search orders" value="{{ request('searchValue') }}">
                                <button type="submit" class="btn btn--primary">{{ translate('search')}}</button>
                            </div>
                        </form>
                        <div class="dropdown">
                            <a type="button" class="btn btn-outline--primary text-nowrap" href="{{route('vendor.products.restock-export', ['restock_date' => request('restock_date'),'brand_id' => request('brand_id'), 'category_id' => request('category_id'), 'sub_category_id' => request('sub_category_id'),  'searchValue' => request('searchValue')])}}">
                                <img width="14" src="{{dynamicAsset(path: 'public/assets/back-end/img/excel.png')}}" class="excel" alt="">
                                <span class="ps-2">{{ translate('export') }}</span>
                            </a>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table id="datatable"
                               class="table table-hover table-borderless table-thead-bordered table-nowrap table-align-middle card-table w-100 text-start">
                            <thead class="thead-light thead-50 text-capitalize">
                            <tr>
                                <th>{{ translate('SL') }}</th>
                                <th>{{ translate('product_name') }}</th>
                                <th class="text-center">{{ translate('selling_price') }}</th>
                                <th class="text-center">{{ translate('last_request_date') }}</th>
                                <th class="text-center">{{ translate('number_of_request') }}</th>
                                <th class="text-center">{{ translate('action') }}</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($restockProducts as $key=>$restockProduct)
                                <tr>
                                    <th scope="row">{{ ++$key }}</th>
                                    <td>
                                        <a href="{{ route('vendor.products.view',['id'=>$restockProduct->product['id'] ?? 0]) }}"
                                           class="media align-items-center gap-2">
                                            <img src="{{ getStorageImages(path: $restockProduct?->product?->thumbnail_full_url, type: 'backend-product') }}"
                                                 class="avatar border" alt="">
                                            <span class="media-body title-color hover-c1">
                                                {{ Str::limit($restockProduct->product['name'] ?? '', 20) }}
                                                @if($restockProduct['variant'])
                                                    <p class="small font-weight-bold m-0">{{ translate('Variant:') }} {{ $restockProduct['variant'] }}</p>
                                                @endif
                                            </span>
                                        </a>
                                    </td>
                                    <td class="text-center">
                                        {{setCurrencySymbol(amount: usdToDefaultCurrency(amount: $restockProduct->product['unit_price'] ?? 0), currencyCode: getCurrencyCode()) }}
                                    </td>
                                    <td class="text-center">
                                        {{ $restockProduct->updated_at->format('d F Y, h:i A') }}
                                    </td>
                                    <td class="text-center">
                                        {{ $restockProduct?->restockProductCustomers?->count() ?? 0 }}
                                    </td>
                                    <td>
                                        <div class="d-flex justify-content-center gap-2">
                                            <a class="btn btn-outline-info btn-sm square-btn" title="View"
                                               href="{{ route('vendor.products.view',['id'=>$restockProduct->product['id'] ?? 0]) }}">
                                                <img src="{{ dynamicAsset(path: 'public/assets/back-end/img/icons/restock_view.svg') }}" alt="">
                                            </a>
                                            <a class="btn btn-outline--primary btn-sm square-btn action-update-product-quantity"
                                               title="{{ translate('edit') }}"
                                               id="{{ $restockProduct->product['id'] }}"
                                               data-url="{{ route('vendor.products.get-variations', ['id'=> $restockProduct->product['id'], 'restock_id' => $restockProduct->id]) }}"
                                               data-target="#update-stock">
                                                <img src="{{ dynamicAsset(path: 'public/assets/back-end/img/icons/restock_update.svg') }}" alt="">
                                            </a>
                                            <span class="btn btn-outline-danger btn-sm square-btn delete-data"
                                                  title="{{ translate('delete') }}"
                                                  data-id="product-{{ $restockProduct->id}}">
                                                <img src="{{ dynamicAsset(path: 'public/assets/back-end/img/icons/restock_delete.svg') }}" alt="">
                                            </span>
                                        </div>
                                        <form action="{{ route('vendor.products.restock-delete',[$restockProduct->id]) }}"
                                              method="post" id="product-{{ $restockProduct->id}}">
                                            @csrf @method('delete')
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="table-responsive mt-4">
                        <div class="px-4 d-flex justify-content-lg-end">
                            {{ $restockProducts->links() }}
                        </div>
                    </div>

                    @if(count($restockProducts)==0)
                        @include('layouts.back-end._empty-state',['text'=>'no_product_found'],['image'=>'default'])
                    @endif
                </div>
            </div>
        </div>
    </div>
    <span id="message-select-word" data-text="{{ translate('select') }}"></span>
    <div class="modal fade update-stock-modal restock-stock-update" id="update-stock" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form action="{{ route('vendor.products.update-quantity') }}" method="post" class="row">
                    <div class="modal-body">
                        @csrf
                        <div class="rest-part-content"></div>
                        <div class="d-flex justify-content-end gap-10 flex-wrap align-items-center">
                            <button type="button" class="btn btn-danger px-4" data-dismiss="modal" aria-label="Close">
                                {{ translate('close') }}
                            </button>
                            <button class="btn btn--primary" class="btn btn--primary px-4" type="submit">
                                {{ translate('update') }}
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
@push('script')
    <script type="text/javascript">
        changeInputTypeForDateRangePicker($('input[name="restock_date"]'));
    </script>
@endpush
