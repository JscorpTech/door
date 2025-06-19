<div class="card mb-3">
    <div class="card-body">
        <div class="row g-2 align-items-center">
            <div class="col-md-8 col-xl-9">
                <h3>{{ translate('active_clearance_sale_offer') }} ?</h3>
                <p class="m-0">
                    {{ translate('show_your_offer_in_the_store_details_page_in_customer_website_and_apps') }}
                </p>
            </div>
            <div class="col-md-4 col-xl-3">
                <div class="d-flex justify-content-between align-items-center border rounded px-3 py-2">
                    <h5 class="mb-0 font-weight-normal">{{ translate('Active_Offer') }}</h5>

                    <form action="{{ route('admin.deal.clearance-sale.status-update') }}" data-from="clearance-sale"
                          method="post" id="clearance-sale-status-form" data-id="clearance-sale-status-form">
                        @csrf
                        <label class="switcher mx-auto">
                            <input type="checkbox" class="switcher_input toggle-switch-message" value="1"
                                   {{  $clearanceConfig?->is_active == 1 ? 'checked':'' }}
                                   id="clearance-sale-status" name="status"
                                   data-modal-id="toggle-status-modal"
                                   data-toggle-id="clearance-sale-status"
                                   data-on-image="clearance-sale-on.png"
                                   data-off-image="clearance-sale-off.png"
                                   data-on-title="{{translate('Are_you_sure_to_turn_on_the_Clearance_Sale').'?'}}"
                                   data-off-title="{{translate('Are_you_sure_to_turn_off_the_Clearance_Sale').'?'}}"
                                   data-on-message="<p>{{translate('when_you_turn_on_the_clearance_sale_customers_can_get_the_clearance_offer_of_your_products_when_they_want_to_purchase_from_your_store')}}</p>"
                                   data-off-message="<p>{{translate('if_you_turn_off_the_clearance_sale_customers_will_not_get_the_clearance_offer_of_your_products_when_they_want_to_purchase_from_your_store.')}}</p>">
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
        <form action="{{ route('admin.deal.clearance-sale.update-config') }}" method="POST">
            @csrf
            <h3 class="">{{ translate('Setup_Offer_Logics') }}</h3>
            <div class="row g-2 align-items-end">
                <div class="col-md-6">
                    <input type="hidden" name="setup_by" value="admin">
                    <label class="form-label title-color font-weight-medium">{{ translate('Duration') }}</label>
                    <div class="position-relative">
                        <span class="tio-calendar icon-absolute-on-right"></span>
                        @if($clearanceConfig?->duration_start_date && $clearanceConfig?->duration_end_date)
                            <input type="text" class="js-daterangepicker-times-sec form-control" name="clearance_sale_duration"
                                   value="{{ $clearanceConfig?->duration_start_date?->format('m/d/Y h:i:s A') }} - {{ $clearanceConfig?->duration_end_date?->format('m/d/Y h:i:s A') }}">
                        @else
                            <input type="text" class="js-daterangepicker-times-sec form-control" name="clearance_sale_duration">
                        @endif
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="form-label title-color font-weight-medium">{{ translate('Discount_Type') }}</label>
                    <div class="form-control d-flex gap-2 h-auto">
                        <div class="custom-control custom-radio flex-grow-1">
                            <input type="radio" class="custom-control-input clearance-sale-discount" value="flat" name="discount_type"
                                   id="flat" {{ is_null($clearanceConfig) || $clearanceConfig?->discount_type == 'flat' ? 'checked' : '' }}>
                            <label class="custom-control-label" for="flat">
                                {{ translate('Flat_Discount') }}
                            </label>
                        </div>
                        <div class="custom-control custom-radio flex-grow-1">
                            <input type="radio" class="custom-control-input clearance-sale-discount" value="product_wise" name="discount_type"
                                   id="product" {{ $clearanceConfig?->discount_type == 'product_wise' ? 'checked' : '' }}>
                            <label class="custom-control-label" for="product">
                                {{ translate('Product_wise_discount') }}
                            </label>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 clearance-sale-discount-flat {{ $clearanceConfig?->discount_type == 'product_wise' ? 'd--none' : '' }}">
                    <label class="form-label title-color font-weight-medium">
                        {{ translate('Discount_Amount') }} (%)
                    </label>

                    <div class="input-group">
                        <input type="text" class="form-control" max="100" pattern="[0-9]*"
                               name="discount_amount" placeholder="{{ translate('ex') }} : {{ '10' }}"
                               value="{{ $clearanceConfig?->discount_amount ?? 0 }}">
                        <div class="input-group-append">
                            <span class="input-group-text font-weight-bolder" id="basic-addon2">{{ '%' }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <label
                        class="form-label title-color font-weight-medium">
                        {{ translate('Offer_Active_Time') }}
                    </label>
                    <div class="form-control d-flex gap-2 h-auto">
                        <div class="custom-control custom-radio flex-grow-1">
                            <input type="radio" class="custom-control-input offer-active-time" value="always" name="offer_active_time"
                                   id="always" {{ is_null($clearanceConfig) || $clearanceConfig?->offer_active_time == 'always' ? 'checked' : '' }}>
                            <label class="custom-control-label" for="always">{{ translate('Always') }}</label>
                        </div>
                        <div class="custom-control custom-radio flex-grow-1">
                            <input type="radio" class="custom-control-input offer-active-time" value="specific_time" name="offer_active_time"
                                   id="specificTime" {{ $clearanceConfig?->offer_active_time == 'specific_time' ? 'checked' : '' }}>
                            <label class="custom-control-label" for="specificTime">
                                {{ translate('specific_time_in_a_day') }}
                            </label>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 offer-active-time-section {{ $clearanceConfig?->offer_active_time != 'specific_time' ? 'd--none' : '' }}">
                    <label class="form-label title-color font-weight-medium">
                        {{ translate('Start_&_End_Time') }}
                    </label>
                    <div class="position-relative">
                        <span class="tio-calendar icon-absolute-on-right"></span>
                        @if($clearanceConfig?->offer_active_range_start && $clearanceConfig?->offer_active_range_end)
                            <input type="text" class="js-daterangepicker-time-only form-control" name="offer_active_range"
                                   value="{{ $clearanceConfig?->offer_active_range_start?->format('h:i:s A') }} - {{ $clearanceConfig?->offer_active_range_end?->format('h:i:s A') }}">
                        @else
                            <input type="text" class="js-daterangepicker-time-only form-control" name="offer_active_range">
                        @endif
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="form-check form--check form--check--inline border rounded py-2 min-h-40px">
                            <span class="user-select-none form-check-label flex-grow-1">
                                {{ translate('also_show_in_home_page') }}
                                <span data-toggle="tooltip" data-placement="top" title="{{ translate('enable_this_option_to_display_clearance_products_with_other_stores_product') }}">
                                    <i class="tio-info-outined"></i>
                                </span>
                            </span>
                        <input type="checkbox" name="show_in_homepage" class="form-check-input" value="1"
                            {{ $clearanceConfig?->show_in_homepage == 1 ? 'checked' : '' }}>
                    </label>
                </div>
                <div class="col-md-6 ml-auto">
                    <label class="form-label d-none d-md-block">&nbsp;</label>
                    <div class="btn--container justify-content-end">
                        <a class="btn btn-secondary" href="{{ route('admin.deal.clearance-sale.index') }}">
                            {{ translate('Reset') }}
                        </a>
                        @if($clearanceConfig)
                            <button class="btn btn--primary" type="submit">{{ translate('Update') }}</button>
                        @else
                            <button class="btn btn--primary" type="submit">{{ translate('Save') }}</button>
                        @endif
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
