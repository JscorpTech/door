@php
    use Illuminate\Support\Facades\Session;
@endphp
@extends('layouts.back-end.app')

@section('title', translate('priority_setup'))

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

        <div class="card mt-2 brand">
            <div class="card-body">
                <div class="row">
                    <div class="col-lg-6">
                        <div class="">
                            <h3 class="mb-3 text-capitalize">{{ translate('Stock_Clearance_Product') }}</h3>
                            <p class="max-w-400">{{ translate('stock_clearance_products_are_items_specifically_selected_and_listed_to_be_sold_at_discounted_prices_to_customers.') }}</p>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <form action="{{ route('admin.deal.clearance-sale.priority-setup-config', ['type' => 'stock_clearance_product_list_priority']) }}" method="post">
                            @csrf
                            <div class="border rounded p-3 d-flex gap-4 flex-column">
                                <div class="d-flex gap-2 justify-content-between pb-3 border-bottom">
                                    <div class="d-flex flex-column">
                                        <h5 class="text-capitalize">{{ translate('use_default_sorting_list') }}</h5>
                                        <div class="d-flex gap-2 align-items-center">
                                            <img width="14"
                                                 src="{{dynamicAsset(path: 'public/assets/back-end/img/icons/info.svg') }}"
                                                 alt="">
                                            <span
                                                class="text-dark fz-12">{{translate('currently_sorting_this_section_based_on_latest_add')}}</span>
                                        </div>
                                    </div>
                                    <label class="switcher">
                                        <input type="checkbox" class="switcher_input switcher-input-js"
                                               data-parent-class="brand" data-from="default-sorting"
                                            {{ isset($stockClearancePriority['custom_sorting_status']) && $stockClearancePriority['custom_sorting_status'] == 1 ? '' : 'checked' }}>
                                        <span class="switcher_control"></span>
                                    </label>
                                </div>
                                <div class="">
                                    <div class="d-flex gap-2 justify-content-between">
                                        <div class="d-flex flex-column">
                                            <h5 class="text-capitalize">{{ translate('use_custom_sorting_list') }}</h5>
                                            <div class="d-flex gap-2 align-items-center">
                                                <img width="14"
                                                     src="{{dynamicAsset(path: 'public/assets/back-end/img/icons/info.svg') }}"
                                                     alt="">
                                                <span
                                                    class="text-dark fz-12">{{ translate('you_can_sorting_this_section_by_others_way') }}</span>
                                            </div>
                                        </div>
                                        <label class="switcher">
                                            <input type="checkbox" class="switcher_input switcher-input-js"
                                                   name="custom_sorting_status" value="1" data-parent-class="brand"
                                                   data-from="custom-sorting"
                                                {{isset($stockClearancePriority['custom_sorting_status']) && $stockClearancePriority['custom_sorting_status'] == 1 ? 'checked' : ''}}>
                                            <span class="switcher_control"></span>
                                        </label>
                                    </div>

                                    <div class="custom-sorting-radio-list user-select-none {{isset($stockClearancePriority['custom_sorting_status']) && $stockClearancePriority['custom_sorting_status'] == 1 ? '' : 'd--none'}}">
                                        <div class="custom-sorting-radio-list checked-dark">
                                            <div class="border rounded p-3 d-flex flex-column gap-2 mt-4">
                                                <div class="d-flex gap-2 align-items-center">
                                                    <input type="radio" class="show" name="sort_by"
                                                           value="latest_created" id="brand-sort-by-latest-created"
                                                        {{ !isset($stockClearancePriority['sort_by']) || $stockClearancePriority['sort_by'] == 'latest_created' ? 'checked' : '' }}>
                                                    <label class="mb-0 cursor-pointer"
                                                           for="brand-sort-by-latest-created">
                                                        {{ translate('sort_by_latest_created') }}
                                                    </label>
                                                </div>

                                                <div class="d-flex gap-2 align-items-center">
                                                    <input type="radio" class="show" name="sort_by"
                                                           value="clearance_expiration_date" id="brand-sort-by-clearance-expiration-date"
                                                        {{ isset($stockClearancePriority['sort_by']) && $stockClearancePriority['sort_by'] == 'clearance_expiration_date' ? 'checked' : '' }}>
                                                    <label class="mb-0 cursor-pointer"
                                                           for="brand-sort-by-clearance-expiration-date">
                                                        {{ translate('based_on_the_clearance_expiration_date') }}
                                                    </label>
                                                </div>

                                                <div class="d-flex gap-2 align-items-center">
                                                    <input type="radio" class="show" name="sort_by"
                                                           value="most_order" id="brand-sort-by-most-order"
                                                        {{ isset($stockClearancePriority['sort_by']) && $stockClearancePriority['sort_by'] == 'most_order' ? 'checked' : '' }}>
                                                    <label class="mb-0 cursor-pointer"
                                                           for="brand-sort-by-most-order">
                                                        {{ translate('sort_by_most_order') }}
                                                    </label>
                                                </div>
                                                <div class="d-flex gap-2 align-items-center">
                                                    <input type="radio" class="show" name="sort_by"
                                                           value="reviews_count" id="brand-sort-by-review-count"
                                                        {{ isset($stockClearancePriority['sort_by']) && $stockClearancePriority['sort_by'] == 'reviews_count' ? 'checked' : '' }}>
                                                    <label class="mb-0 cursor-pointer" for="brand-sort-by-review-count">
                                                        {{ translate('sort_by_review_count') }}
                                                    </label>
                                                </div>
                                                <div class="d-flex gap-2 align-items-center">
                                                    <input type="radio" class="show" name="sort_by" value="rating"
                                                           id="brand-average-ratings"
                                                        {{ isset($stockClearancePriority['sort_by']) && $stockClearancePriority['sort_by'] == 'rating' ? 'checked' : '' }}>
                                                    <label class="mb-0 cursor-pointer" for="brand-average-ratings">
                                                        {{ translate('sort_by_average_ratings') }}
                                                    </label>
                                                </div>
                                                <div class="d-flex gap-2 align-items-center">
                                                    <input type="radio" class="show" name="sort_by" value="a_to_z"
                                                           id="brand-alphabetic-order-reverse-asc"
                                                        {{ isset($stockClearancePriority['sort_by']) && $stockClearancePriority['sort_by'] == 'a_to_z' ? 'checked' : '' }}>
                                                    <label class="mb-0 cursor-pointer"
                                                           for="brand-alphabetic-order-reverse-asc">
                                                        {{ translate('sort_by_Alphabetical') }}
                                                        ({{ 'A ' . translate('to') . ' Z' }})
                                                    </label>
                                                </div>
                                                <div class="d-flex gap-2 align-items-center">
                                                    <input type="radio" class="show" name="sort_by" value="z_to_a"
                                                           id="brand-alphabetic-order-reverse-desc"
                                                        {{ isset($stockClearancePriority['sort_by']) && $stockClearancePriority['sort_by'] == 'z_to_a' ? 'checked' : '' }}>
                                                    <label class="mb-0 cursor-pointer"
                                                           for="brand-alphabetic-order-reverse-desc">
                                                        {{ translate('sort_by_Alphabetical') }}
                                                        ({{ 'Z ' . translate('to') . ' A' }})
                                                    </label>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="custom-sorting-radio-list checked-dark">
                                            <div class="border rounded p-3 d-flex flex-column gap-2 mt-4">
                                                <div class="d-flex gap-2 align-items-center">
                                                    <input type="radio" class="show" name="out_of_stock_product"
                                                           value="desc" id="stock-out-products"
                                                        {{ !isset($stockClearancePriority['out_of_stock_product']) || $stockClearancePriority['out_of_stock_product'] == 'desc' ? 'checked' : '' }}>
                                                    <label class="mb-0 cursor-pointer" for="stock-out-products">
                                                        {{ translate('show_stock_out_products_in_the_last') }}
                                                    </label>
                                                </div>
                                                <div class="d-flex gap-2 align-items-center">
                                                    <input type="radio" class="show" name="out_of_stock_product"
                                                           value="hide" id="remove-stock-out-products"
                                                        {{ isset($stockClearancePriority['out_of_stock_product']) && $stockClearancePriority['out_of_stock_product'] == 'hide' ? 'checked' : '' }}>
                                                    <label class="mb-0 cursor-pointer" for="remove-stock-out-products">
                                                        {{ translate('remove_stock_out_products_from_the_list') }}
                                                    </label>
                                                </div>
                                                <div class="d-flex gap-2 align-items-center">
                                                    <input type="radio" class="show" name="out_of_stock_product"
                                                           value="default"
                                                           id="stock-none"
                                                        {{ isset($stockClearancePriority['out_of_stock_product']) && $stockClearancePriority['out_of_stock_product'] == 'default' ? 'checked' : '' }}>
                                                    <label class="mb-0 cursor-pointer" for="stock-none">
                                                        {{ translate('none') }}
                                                    </label>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="custom-sorting-radio-list checked-dark">
                                            <div class="border rounded p-3 d-flex flex-column gap-2 mt-4">
                                                <div class="d-flex gap-2 align-items-center">
                                                    <input type="radio" class="show" name="temporary_close_sorting"
                                                           value="desc"
                                                           id="time-off"
                                                        {{ !isset($stockClearancePriority['temporary_close_sorting']) || $stockClearancePriority['temporary_close_sorting'] == 'desc' ? 'checked' : '' }}>
                                                    <label class="mb-0 cursor-pointer" for="time-off">
                                                        {{ translate('show_product_in_the_last_if_the_store_is_temporarily_off') }}
                                                    </label>
                                                </div>
                                                <div class="d-flex gap-2 align-items-center">
                                                    <input type="radio" class="show" name="temporary_close_sorting"
                                                           value="hide"
                                                           id="time-off-for-remove-product"
                                                        {{ isset($stockClearancePriority['temporary_close_sorting']) && $stockClearancePriority['temporary_close_sorting'] == 'hide' ? 'checked' : '' }}>
                                                    <label class="mb-0 cursor-pointer" for="time-off-for-remove-product">
                                                        {{ translate('remove_the_product_from_the_list_if_the_store_is_temporary_off') }}
                                                    </label>
                                                </div>
                                                <div class="d-flex gap-2 align-items-center">
                                                    <input type="radio" class="show" name="temporary_close_sorting"
                                                           value="default"
                                                           id="time-none"
                                                        {{ isset($stockClearancePriority['temporary_close_sorting']) && $stockClearancePriority['temporary_close_sorting'] == 'default' ? 'checked' : '' }}>
                                                    <label class="mb-0 cursor-pointer" for="time-none">
                                                        {{ translate('none') }}
                                                    </label>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="border rounded p-3 d-flex gap-4 flex-column mt-20 user-select-none">
                                            <div class="d-flex gap-2 justify-content-between mb-20">
                                                <div class="d-flex flex-column">
                                                    <h5 class="text-capitalize text-start">{{ translate('Vendor_Priorities') }}
                                                    </h5>
                                                    <div class="d-flex gap-2 align-items-center">
                                                        <img width="14"
                                                             src="{{ dynamicAsset(path: 'public/assets/back-end/img/icons/info.svg') }}"
                                                             alt="">
                                                        <span class="text-dark fz-12">
                                                            {{ translate('if_you_want_to_show_vendors_priority_wise_search_&_select_them.') }}
                                                            {{ translate('the_vendor_you_select_1st_it_will_show_at_1st.') }}
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="multiple-tags-with-image">
                                                <select name="vendor_priorities[]" id="vendor_priorities" class="form-control multiple-select2" multiple="multiple">
                                                        @foreach($availableVendors as $availableVendor)
                                                            @if($availableVendor == null)
                                                                <option value="0" data-value="0" {{ !is_null($stockClearanceVendors) && in_array(0, $stockClearanceVendors) ? 'selected' : ''}}
                                                                data-image="{{ getStorageImages(path: getWebConfig(name: 'company_fav_icon'), type: 'shop') }}"
                                                                data-serial="{{ array_search(0, $stockClearanceVendors) }}"
                                                                >
                                                                    {{ getWebConfig(name: 'company_name') }}
                                                                </option>
                                                            @else
                                                                <option value="{{ $availableVendor['id'] }}" data-value="{{ $availableVendor['id'] }}"
                                                                        data-serial="{{ array_search($availableVendor['id'], $stockClearanceVendors) }}"
                                                                        {{ !is_null($stockClearanceVendors) && in_array($availableVendor['id'], $stockClearanceVendors) ? 'selected' : ''}}
                                                                        data-image="{{ getStorageImages(path: $availableVendor->image_full_url, type: 'shop') }}"
                                                                >
                                                                    {{ $availableVendor['name'] }}
                                                                </option>
                                                            @endif
                                                        @endforeach
                                                </select>
                                                <ul class="show-tags">
                                                    <?php
                                                        $sortedVendors = collect($availableVendors)->sortBy(function ($vendor) use ($stockClearanceVendors) {
                                                            if (is_null($vendor)) {
                                                                return PHP_INT_MAX;
                                                            }
                                                            return array_search($vendor['id'], $stockClearanceVendors) !== false
                                                                ? array_search($vendor['id'], $stockClearanceVendors)
                                                                : PHP_INT_MAX; // Push non-matching vendors to the end
                                                        })->toArray();
                                                    ?>

                                                    @if (!empty($sortedVendors))
                                                        @foreach($sortedVendors as $key => $availableVendor)
                                                            @if (is_null($availableVendor) && in_array(0, $stockClearanceVendors))
                                                                <li class="name d-flex gap-2">
                                                                    <span>
                                                                        <img class="rounded-circle tag-image-20px" src="{{ getStorageImages(path: getWebConfig(name: 'company_fav_icon'), type: 'shop') }}">
                                                                        {{ getWebConfig(name: 'company_name') }}
                                                                    </span>
                                                                    <span class="close-icon" data-id="0"><i class="tio-clear cursor-pointer"></i></span>
                                                                    <input value="0" name="vendor_priorities_id[]" class="d-none">
                                                                </li>
                                                            @elseif (!is_null($availableVendor) && in_array($availableVendor['id'], $stockClearanceVendors))
                                                                <li class="name d-flex gap-2">
                                                                    <span>
                                                                        <img class="rounded-circle tag-image-20px" src="{{ getStorageImages(path: $availableVendor['image_full_url'], type: 'shop') }}">
                                                                        {{ $availableVendor['name'] }}
                                                                    </span>
                                                                    <span class="close-icon" data-id="{{ $availableVendor['id'] }}"><i class="tio-clear cursor-pointer"></i></span>
                                                                    <input value="{{ $availableVendor['id'] }}" name="vendor_priorities_id[]" class="d-none">
                                                                </li>
                                                            @endif
                                                        @endforeach
                                                    @endif

                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex justify-content-end mt-4">
                                <button type="submit" class="btn btn--primary-2 px-5">
                                    {{ translate('save') }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    <span id="type-shop-name-text" data-text="{{ translate('Type_shop_name') }}"></span>
    </div>
@endsection

@push('script')
    <script src="{{ dynamicAsset(path: 'public/assets/back-end/js/admin/deal.js') }}"></script>

    <script>
        'use strict';
        let selectedVendorObj = [];
    </script>

    @if (!empty($sortedVendors))
        @foreach($sortedVendors as $key => $availableVendor)
            @if (is_null($availableVendor) && in_array(0, $stockClearanceVendors))
                <script>
                    selectedVendorObj.push({
                        id: 0,
                        name: "{{ getWebConfig(name: 'company_name') }}",
                        img_src: "{{ getStorageImages(path: getWebConfig(name: 'company_fav_icon'), type: 'shop') }}",
                    });
                </script>
            @elseif (!is_null($availableVendor) && in_array($availableVendor['id'], $stockClearanceVendors))
                <script>
                    selectedVendorObj.push({
                        id: "{{ $availableVendor['id'] }}",
                        name: "{{ $availableVendor['name'] }}",
                        img_src: "{{ getStorageImages(path: $availableVendor['image_full_url'], type: 'shop') }}",
                    });
                </script>
            @endif
        @endforeach
    @endif
@endpush
