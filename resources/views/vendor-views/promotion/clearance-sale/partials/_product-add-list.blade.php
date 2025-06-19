@if(count($stockClearanceProduct) == 0 && request()->has('searchValue'))
    <div class="card mb-3">
        <div class="card-header">
            <h4
                class="m-0">{{ translate('Product_List') }}
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
                               placeholder="{{ translate('search') }}..." aria-label="Search by Order ID" value="{{ request('searchValue') }}">
                        <button type="submit" class="btn btn--primary input-group-text">
                            {{ translate('Search') }}
                        </button>
                    </div>
                </form>
                <a href="#product-add-modal" data-toggle="modal" class="btn btn--primary">+
                    {{ translate('Add_Product') }}</a>
                @if(count($stockClearanceProduct) > 0)
                    <a  class="btn btn--danger stock-clearance-delete-all-products" data-id="clearance-product">{{ translate('clear_all') }}</a>
                    <form action="{{ route('vendor.clearance-sale.clearance-delete-all-product') }}"
                          method="post" id="clearance-product">
                        @csrf @method('delete')
                    </form>
                @endif
            </div>
        </div>
        <div class="card-body">
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
                        <th class="text-center">{{ translate('status') }}</th>
                        <th class="text-center">{{translate('action')}}</th>
                    </tr>
                    </thead>
                </table>
            </div>
            @include('layouts.back-end._empty-state',['text'=>'no_product_found'],['image'=>'default'])
        </div>
    </div>
@elseif(count($stockClearanceProduct) == 0)
    <div class="card mb-3">
        <div class="card-header">
            <h4
                class="m-0">{{ translate('Product_List') }}
            </h4>
            <a href="#product-add-modal" data-toggle="modal" class="btn btn--primary">+
                {{ translate('Add_Product') }}</a>
        </div>
        <div class="card-body">
            <div class="p-4 bg-chat rounded text-center">
                <div class="py-5">
                    <img src="{{ dynamicAsset('public/assets/back-end/img/empty-product.png') }}" width="64"
                         alt="">
                    <div class="mx-auto my-3 max-w-353px">
                        {{ translate('add_product_show_in_the_clearance_offer_section_in_customer_app_and_website') }}
                    </div>
                    <a href="#product-add-modal" data-toggle="modal" class="text-primary text-underline">+
                        {{ translate('Add_Product') }}</a>
                </div>
            </div>
        </div>
    </div>
    @else
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
                               placeholder="{{ translate('search_by_product_name') }}" aria-label="Search by Order ID" value="{{ request('searchValue') }}">
                        <button type="submit" class="btn btn--primary input-group-text">
                            {{ translate('Search') }}
                        </button>
                    </div>
                </form>
                <a href="#product-add-modal" data-toggle="modal" class="btn btn--primary">+
                    {{ translate('Add_Product') }}</a>
                @if(count($stockClearanceProduct) > 0)
                    <a  class="btn btn--danger stock-clearance-delete-all-products" data-id="clearance-product">{{ translate('clear_all') }}</a>
                    <form action="{{ route('vendor.clearance-sale.clearance-delete-all-product') }}"
                          method="post" id="clearance-product">
                        @csrf @method('delete')
                    </form>
                @endif
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
                                <div>{{ translate('name') }}</div>
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
                        <th class="text-center">{{ translate('status') }}</th>
                        <th class="text-center">{{translate('action')}}</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($stockClearanceProduct as $key => $clearanceProduct)
                        <tr>
                            <td>{{ $key+1 }}</td>
                            <td>
                                <a href="{{ route('vendor.products.view', [$clearanceProduct?->product['id']]) }}" class="title-color hover-c1 d-flex align-items-center gap-10">
                                    <img src="{{ getStorageImages(path:$clearanceProduct?->product?->thumbnail_full_url , type: 'backend-product') }}"
                                         class="rounded border-gray-op aspect-1 object-fit-cover" alt="" width="60">
                                    <div class="max-w-250">
                                        <h6 class="text-truncate">
                                            {{ $clearanceProduct?->product?->name }}
                                        </h6>
                                        <div class="fs-12 title-color">
                                            <div class="border-between gap-2 mb-2">
                                                @if($clearanceProduct?->product->product_type != 'digital')
                                                    <span class="parent">
                                                        <span class="opacity--70">
                                                            {{ translate('current_stock') }}
                                                        </span>
                                                        <span>{{ $clearanceProduct?->product?->current_stock }}</span>
                                                    </span>
                                                @endif
                                                <span class="parent text-truncate">
                                                    <span class="opacity--70">{{ translate('category') }}: </span>
                                                    <span>{{ $clearanceProduct?->product?->category->name ?? translate('not_found') }}</span>
                                                </span>
                                            </div>
                                            @if($clearanceProduct?->product?->product_type !== 'digital')
                                                <span class="parent text-truncate">
                                                        <span class="opacity--70">{{translate('brand')}}: </span><span>{{ $clearanceProduct?->product?->brand->name ?? translate('not_found') }}</span>
                                                    </span>
                                            @endif
                                        </div>
                                    </div>
                                </a>
                            </td>
                            <td>
                                @if($clearanceConfig?->discount_type == 'flat')
                                    @php($discountAmount = $clearanceProduct?->product?->unit_price - ($clearanceProduct?->product?->unit_price * $clearanceProduct->discount_amount) / 100)
                                @else
                                    @php($discountAmount = $clearanceProduct->discount_type === 'percentage' ? ($clearanceProduct?->product?->unit_price - ($clearanceProduct?->product?->unit_price * $clearanceProduct->discount_amount) / 100): ($clearanceProduct?->product?->unit_price - $clearanceProduct->discount_amount))
                                @endif

                                @if($discountAmount < 0)
                                    <div class="font-weight-bold text-center text-warning">
                                            <span>
                                                {{ setCurrencySymbol(amount: usdToDefaultCurrency(amount: $clearanceProduct?->product?->unit_price), currencyCode: getCurrencyCode()) }}
                                            </span>
                                        <span class="cursor-pointer" data-toggle="tooltip" data-placement="top" title="{{ translate('Your_products_unit_price_is_lower_then_offer_price') }}">
                                                <img src="{{ dynamicAsset('public/assets/back-end/img/icons/clearance-product-warning.svg') }}" alt="">
                                            </span>
                                    </div>
                                @else
                                    <div class="text-center">
                                        {{ setCurrencySymbol(amount: usdToDefaultCurrency(amount: $clearanceProduct?->product?->unit_price), currencyCode: getCurrencyCode()) }}
                                    </div>
                                @endif
                            </td>
                            <td>
                                <div class="d-flex justify-content-center align-items-center gap-2">
                                    @if($clearanceConfig?->discount_type == 'flat')
                                        <div class="text-center">
                                            {{ $clearanceProduct->discount_amount }}
                                        </div>
                                    @else
                                        <div class="discount-input-container position-relative d-inline-block">
                                            <div class="input-group w-140">
                                                <input type="number" class="form-control form-control-focus-none w-80px h-25 text-center px-1 disabled" value="{{ $clearanceProduct?->discount_type == 'flat' ? usdToDefaultCurrency(amount: $clearanceProduct->discount_amount) : $clearanceProduct->discount_amount }}" readonly>
                                                <div class="input-group-append">
                                                    <span class="input-group-text lh-1">{{ $clearanceProduct?->discount_type == 'flat' ? getCurrencySymbol(currencyCode: getCurrencyCode()) : '%' }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                    @if(isset($clearanceConfig->discount_type) && $clearanceConfig->discount_type == 'product_wise')
                                        <a href="#discount-update-modal" data-toggle="modal" class="btn text-primary discount-edit-btn" data-product-id="{{ $clearanceProduct->product_id }}"
                                           data-discount-amount="{{ $clearanceProduct?->discount_type == 'flat' ? usdToDefaultCurrency(amount: $clearanceProduct->discount_amount) : $clearanceProduct->discount_amount }}"
                                           data-image="{{ getStorageImages(path: $clearanceProduct?->product?->thumbnail_full_url, type: 'backend-product') }}"
                                           data-product-name="{{ $clearanceProduct?->product?->name }}"
                                           data-id="{{ $clearanceProduct->id }}"
                                           data-discount-type="{{ $clearanceProduct->discount_type }}"
                                        >
                                            <i class="tio-edit"></i>
                                        </a>
                                    @endif
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
                            <td>
                                <div class="d-flex justify-content-center">
                                    <form action="{{ route('vendor.clearance-sale.product-status-update') }}" data-from="clearance-sale"
                                          method="post" id="clearance-sale-product-status{{ $clearanceProduct['id'] }}-form" data-id="clearance-sale-product-status-form">
                                        @csrf
                                        <label class="switcher mx-auto">
                                            <input type="hidden" name="product_id" value="{{ $clearanceProduct?->product_id }}">
                                            <input type="checkbox" class="switcher_input toggle-switch-message" value="1"
                                                   {{  $clearanceProduct?->is_active == 1 ? 'checked':'' }}
                                                   id="clearance-sale-product-status{{ $clearanceProduct['id'] }}" name="status"
                                                   data-modal-id="toggle-status-modal"
                                                   data-toggle-id="clearance-sale-product-status{{ $clearanceProduct['id'] }}"
                                                   data-on-image="clearance-sale-on.png"
                                                   data-off-image="clearance-sale-off.png"
                                                   data-on-title="{{translate('Want_to_Turn_ON_Active_Clearance_Sale_product').'?'}}"
                                                   data-off-title="{{translate('Want_to_Turn_OFF_Active_Clearance_Sale_product').'?'}}"
                                                   data-on-message="<p>{{translate('if_enabled_this_clearance_sale_product_will_be_available_to_users_on_the_website_and_app')}}</p>"
                                                   data-off-message="<p>{{translate('if_disabled_this_clearance_sale_product_will_be_hidden_to_users_on_the_website_and_app')}}</p>">
                                            <span class="switcher_control"></span>
                                        </label>
                                    </form>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex justify-content-center">
                                    <a title="Delete" class="btn btn-outline-danger square-btn stock-clearance-delete-data" href="javascript:" data-id="clearance-product-{{ $clearanceProduct->id }}">
                                        <i class="tio-delete"></i>
                                    </a>
                                </div>
                                <form action="{{ route('vendor.clearance-sale.clearance-delete',[$clearanceProduct->product_id]) }}"
                                      method="post" id="clearance-product-{{ $clearanceProduct->id}}">
                                    @csrf @method('delete')
                                </form>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endif
