@php
    use Carbon\Carbon;
    use Illuminate\Support\Facades\Session;
@endphp
@extends('layouts.back-end.app')

@section('title', translate('Vendor_Offers'))

@section('content')
    @php($direction = Session::get('direction'))
    <div class="content container-fluid">
        <div class="d-flex justify-content-between align-items-center gap-3 mb-3">
            <h2 class="h1 mb-0 text-capitalize d-flex align-items-center gap-2">
                <img width="20" src="{{ dynamicAsset(path: 'public/assets/back-end/img/note.png') }}" alt="">
                {{ translate('clearance_sale') }}
            </h2>
        </div>

        @include('admin-views.deal.clearance-sale.partials.clearance-sale-inline-menu')

        <div class="card mb-3">
            <div class="card-body">
                <div class="row g-2 align-items-center">
                    <div class="col-md-8 col-xl-9">
                        <h3>{{ translate('Show Clearance Offer in Home Page') }}</h3>
                        <p class="m-0">
                            {{ translate('You_can_highlight_all clearance offer products in home page to increase customer reach') }}
                        </p>
                    </div>
                    <div class="col-md-4 col-xl-3">
                        <div class="d-flex justify-content-between align-items-center border rounded px-3 py-2">
                            <h5 class="mb-0 font-weight-normal">{{ translate('Show Offer in home page ?') }}</h5>
                            <form action="{{ route('admin.deal.clearance-sale.update-vendor-offer-status') }}" data-from="clearance-sale"
                                  method="post" id="clearance-sale-vendor-offer-status-form" data-id="clearance-vendor-offer-status-form">
                                @csrf
                                <label class="switcher mx-auto">
                                    <input type="hidden" name="show-offer-id" value="{{ isset($clearanceConfig) ? $clearanceConfig['id'] : null}}">
                                    @php($showInHomepage = getWebConfig('stock_clearance_vendor_offer_in_homepage'))
                                    <input type="checkbox" class="switcher_input toggle-switch-message" value="1"
                                           {{  $showInHomepage == 1 ? 'checked':'' }}
                                           id="clearance-sale-vendor-offer-status" name="homepage-status"
                                           data-modal-id="toggle-status-modal"
                                           data-toggle-id="clearance-sale-vendor-offer-status"
                                           data-on-image="clearance-sale-on.png"
                                           data-off-image="clearance-sale-off.png"
                                           data-on-title="{{ translate('Want_to_show_clearance_offer_in_homepage') }}"
                                           data-off-title="{{ translate('Want_to_hide_clearance_offer_in_homepage') }}"
                                           data-on-message="<p>{{ translate('if_enabled_this_product_will_be_available_on_the_website_and_customer_app') }}</p>"
                                           data-off-message="<p>{{ translate('if_disabled_this_product_will_be_hidden_from_the_website_and_customer_app') }}</p>">
                                    <span class="switcher_control"></span>
                                </label>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card mb-3">
            <div class="card-body">
                <div class="border-bottom">
                    <h3>{{ translate('Add Vendor') }}</h3>
                    <p>
                        {{ translate('Alongside with your in-house product , you can highlight vendor’s product who has activate their clearance offer.') }}
                    </p>
                </div>
                <div class="mt-3">
                    <div class="position-relative">
                        <input type="text" class="form-control pl-5 search-vendor-for-clearance-sale" placeholder="{{ translate('Search_Vendors') }}">
                        <span
                            class="tio-search position-absolute left-0 top-0 h-42px d-flex align-items-center pl-2"></span>
                        <div class="dropdown-menu select-clearance-vendor-search w-100 px-2">
                            <div class="d-flex flex-column max-h-200 overflow-y-auto overflow-x-hidden search-result-box">
                                @include('admin-views.deal.clearance-sale.partials._search-vendor', ['vendorList' => $vendorList])
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <h3 class="mb-3">
                    {{ translate('Vendor_List') }}
                    <span class="badge badge-soft-dark radius-50 fz-14 ml-1">{{ $vendorList->count() > 0? count($vendorList) : null }}</span>
                </h3>
                @if($vendorList->count() > 0)
                    <div class="table-responsive datatable-custom">
                        <table
                            class="table table-hover table-borderless table-thead-bordered table-nowrap table-align-middle card-table w-100">
                            <thead class="thead-light thead-50 text-capitalize">
                            <tr>
                                <th>{{ translate('sl') }}</th>
                                <th>{{translate('shop_info')}}</th>
                                <th>{{ translate('valid_until') }}</th>
                                <th class="text-center">{{ translate('total_products') }}</th>
                                <th class="text-center">{{ translate('status') }}</th>
                                <th class="text-center">{{ translate('action') }}</th>
                            </tr>
                            </thead>
                            <tbody>
                                @foreach($vendorList as $key => $vendor)
                                    <tr>
                                        <td>{{ $key + 1 }}</td>
                                        <td>
                                            <a href="{{ route('admin.vendors.view', ['id' => $vendor['user_id'], 'tab' => 'clearance_sale']) }}" class="title-color hover-c1 d-flex align-items-center gap-10">
                                                <img src="{{ getStorageImages(path:$vendor?->shop?->image_full_url , type: 'shop') }}"
                                                     class="rounded" alt="" width="50">
                                                <div class="max-w-200">
                                                    <h6 class="fz-14">
                                                        {{$vendor?->shop?->name}}
                                                    </h6>
                                                    <div class="fs-13 title-color opacity--70 border-between wrap">
                                                        <span class="parent">
                                                             <span class="opacity--70">({{$vendor->review_count}} {{ translate('review') }})</span>
                                                         </span>
                                                        <span class="parent">
                                                             <span class="opacity--70"><i class="tio-star text-F5A200"></i>{{number_format($vendor['average_rating'],1)}}</span>
                                                        </span>
                                                    </div>
                                                </div>
                                            </a>
                                        </td>
                                        <td>
                                            <span class="fs-14 title-color font-medium">{{ $vendor->duration_end_date->format('d F Y, h:i A') }}</span>
                                        </td>
                                        <td class="text-center"><span class="fs-14 title-color font-medium"></span>{{ $vendor->products_count }}</td>
                                        <td>
                                            <div class="d-flex justify-content-center">
                                                <form action="{{ route('admin.deal.clearance-sale.update-vendor-status') }}" method="post" data-from="vendor-status"
                                                      id="vendor-status{{ $vendor['id']}}-form">
                                                    @csrf
                                                    <input type="hidden" name="id" value="{{ $vendor['id']}}">
                                                    <label class="switcher mx-auto">
                                                        <input type="checkbox" class="switcher_input toggle-switch-message"
                                                               name="status"
                                                               id="vendor-status{{ $vendor['id'] }}" value="1"
                                                               {{ $vendor['show_in_homepage'] == 1 ? 'checked' : '' }}
                                                               data-modal-id="toggle-status-modal"
                                                               data-toggle-id="vendor-status{{ $vendor['id'] }}"
                                                               data-on-image="clearance-sale-on.png"
                                                               data-off-image="clearance-sale-off.png"
                                                               data-on-title="{{ translate('Want_to_show').' '.$vendor?->shop?->name.' '.translate('clearance_offer_in_homepage') }}"
                                                               data-off-title="{{ translate('Want_to_hide').' '.$vendor?->shop?->name.' '.translate('clearance_offer_in_homepage') }}"
                                                               data-on-message="<p>{{ translate('if_enabled_this_product_will_be_available_on_the_website_and_customer_app') }}</p>"
                                                               data-off-message="<p>{{ translate('if_disabled_this_product_will_be_hidden_from_the_website_and_customer_app') }}</p>">
                                                        <span class="switcher_control"></span>
                                                    </label>
                                                </form>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex justify-content-center gap-2">
                                                <a title="Delete" class="btn btn-outline-danger square-btn delete-data" data-id="vendor-{{ $vendor['id']}}" href="javascript:">
                                                    <i class="tio-delete"></i>
                                                </a>
                                                <a class="btn btn-outline-primary btn-sm square-btn" title="View"
                                                   href="{{ route('admin.vendors.view', ['id' => $vendor['user_id'], 'tab' => 'clearance_sale']) }}">
                                                    <i class="tio-invisible"></i>
                                                </a>
                                            </div>
                                            <form action="{{ route('admin.deal.clearance-sale.vendor-delete',[$vendor['id']]) }}"
                                                  method="post" id="vendor-{{ $vendor['id']}}">
                                                @csrf @method('delete')
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="p-4 bg-chat rounded text-center mt-3">
                        <div class="py-5">
                            <img src="{{ dynamicAsset('public/assets/back-end/img/empty-vendor.png') }}" width="58"
                                 alt="">
                            <div class="mx-auto my-3 max-w-353px">
                                {{ translate('No vendors are added') }}
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>

    </div>
@endsection

@push('script')
    <script src="{{ dynamicAsset(path: 'public/assets/back-end/js/admin/deal.js') }}"></script>
    <script src="{{ dynamicAsset(path: 'public/assets/back-end/js/admin/clearance-sale-script.js') }}"></script>
@endpush
