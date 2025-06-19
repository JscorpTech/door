<div class="modal fade" id="maintenance-mode-modal" tabindex="-1" aria-labelledby="exampleModalLabel"
     aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <form method="post" action="{{ route('admin.business-settings.maintenance-mode') }}" class="modal-content" id="maintenance-mode-form">
            @csrf
            <div class="modal-header p-4 pb-0">
                <h4 class="mb-0">
                    <i class="tio-notifications-alert mr-1"></i>
                    {{ translate('System_Maintenance') }}
                </h4>
                <button type="button" class="close maintenance-cancel-button" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="d-flex flex-column gap-4">
                    <div class="row g-2 align-items-center">
                        <div class="col-lg-8">
                            <p class="m-0">
                                *{{ translate('by_turning_on_maintenance_mode_control_your_all_system_&_function') }}</p>
                        </div>
                        <div class="col-lg-4">
                            <div
                                class="d-flex justify-content-between align-items-center border rounded mb-2 px-3 py-2">
                                <h5 class="mb-0">{{ translate('Maintenance_Mode') }}</h5>
                                <label class="switcher ml-auto mb-0">
                                    <input type="checkbox" class="switcher_input" name="maintenance_mode"
                                            id="maintenance-mode-checkbox" value="1"
                                        {{ $businessSetting['maintenance_mode'] ? 'checked' : '' }}>
                                    <span class="switcher_control"></span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="row g-2 align-items-center">
                        @php($businessMode = getWebConfig(name: 'business_mode'))
                        <div class="col-xl-4">
                            <h5 class="mb-2">{{ translate('Select_System') }}</h5>
                            <p class="m-0">{{ translate('select_the_systems_you_want_to_temporarily_deactivate_for_maintenance') }}</p>
                        </div>
                        <div class="col-xl-8">
                            <div class="border p-3 rounded">
                                <div class="row flex-wrap g-2">
                                    <div class="col-md-3">

                                        <?php
                                        $totalSystemInMaintenance = 0;
                                        if (array_key_exists('user_app', $maintenanceSystemSetup) && $maintenanceSystemSetup['user_app']) {
                                            $totalSystemInMaintenance++;
                                        }
                                        if (array_key_exists('user_website', $maintenanceSystemSetup) && $maintenanceSystemSetup['user_website']) {
                                            $totalSystemInMaintenance++;
                                        }
                                        if ($businessMode == 'multi' && array_key_exists('vendor_app', $maintenanceSystemSetup) && $maintenanceSystemSetup['vendor_app']) {
                                            $totalSystemInMaintenance++;
                                        }
                                        if ($businessMode == 'multi' && array_key_exists('vendor_panel', $maintenanceSystemSetup) && $maintenanceSystemSetup['vendor_panel']) {
                                            $totalSystemInMaintenance++;
                                        }
                                        if (array_key_exists('deliveryman_app', $maintenanceSystemSetup) && $maintenanceSystemSetup['deliveryman_app']) {
                                            $totalSystemInMaintenance++;
                                        }
                                        ?>

                                        <div class="form-check form--check">
                                            <input class="form-check-input system-checkbox" name="all_system"
                                                    type="checkbox"
                                                    {{ $businessMode == 'multi' && $totalSystemInMaintenance == 5 ? 'checked' : '' }}
                                                    {{ $businessMode == 'single' && $totalSystemInMaintenance == 3 ? 'checked' : '' }}
                                                    id="allSystemSelection">
                                            <label class="form-check-label cursor-pointer" for="allSystemSelection">
                                                {{ translate('All_System') }}
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-check form--check">
                                            <input class="form-check-input system-checkbox" name="user_app"
                                                    type="checkbox" value="1"
                                                    {{ array_key_exists('user_app', $maintenanceSystemSetup) && $maintenanceSystemSetup['user_app'] == 1 ? 'checked' :'' }}
                                                    id="userAppCheckbox">
                                            <label class="form-check-label cursor-pointer" for="userAppCheckbox">
                                                {{ translate('user_app') }}
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-check form--check">
                                            <input class="form-check-input system-checkbox" name="user_website"
                                                    type="checkbox" value="1"
                                                    {{ array_key_exists('user_website', $maintenanceSystemSetup) && $maintenanceSystemSetup['user_website'] == 1 ? 'checked' :'' }}
                                                    id="userWebsiteCheckbox">
                                            <label class="form-check-label cursor-pointer"
                                                    for="userWebsiteCheckbox">
                                                {{ translate('user_website') }}
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div
                                            class="form-check form--check {{ $businessMode == 'single' ? 'd-none':'' }}">
                                            <input class="form-check-input system-checkbox" name="vendor_app"
                                                    type="checkbox" value="1"
                                                    {{ array_key_exists('vendor_app', $maintenanceSystemSetup) && $maintenanceSystemSetup['vendor_app'] == 1 ? 'checked' :'' }}
                                                    id="vendorAppCheckbox">
                                            <label class="form-check-label cursor-pointer" for="vendorAppCheckbox">
                                                {{ translate('vendor_app') }}
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-check form--check">
                                            <input class="form-check-input system-checkbox" name="deliveryman_app"
                                                    type="checkbox" value="1"
                                                    {{ array_key_exists('deliveryman_app', $maintenanceSystemSetup) && $maintenanceSystemSetup['deliveryman_app'] == 1 ? 'checked' :'' }}
                                                    id="deliverymanAppCheckbox">
                                            <label class="form-check-label cursor-pointer"
                                                    for="deliverymanAppCheckbox">
                                                {{ translate('deliveryman_app') }}
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div
                                            class="form-check form--check {{ $businessMode == 'single' ? 'd-none':'' }}">
                                            <input class="form-check-input system-checkbox" name="vendor_panel"
                                                    type="checkbox" value="1"
                                                    {{ array_key_exists('vendor_panel', $maintenanceSystemSetup) && $maintenanceSystemSetup['vendor_panel'] == 1 ? 'checked' :'' }}
                                                    id="vendorPanelCheckbox">
                                            <label class="form-check-label cursor-pointer"
                                                    for="vendorPanelCheckbox">
                                                {{ translate('vendor_panel') }}
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row g-2 align-items-center">
                        <div class="col-xl-4">
                            <h5 class="mb-2">{{ translate('Maintenance_Date_and_Time') }}</h5>
                            <p class="m-0">{{ translate('choose_the_maintenance_mode_duration_for_your_selected_system.') }}</p>
                        </div>
                        <div class="col-xl-8">
                            <div class="border p-3 rounded">
                                <div class="d-flex flex-wrap gap-5 mb-3">
                                    <div class="form-check form--check">
                                        <input class="form-check-input" type="radio" name="maintenance_duration"
                                                {{ isset($selectedMaintenanceDuration['maintenance_duration']) && $selectedMaintenanceDuration['maintenance_duration'] == 'one_day' ? 'checked' : '' }}
                                                value="one_day" id="one_day">
                                        <label class="form-check-label cursor-pointer" for="one_day">
                                            {{ translate('For_24_Hours') }}
                                        </label>
                                    </div>
                                    <div class="form-check form--check">
                                        <input class="form-check-input" type="radio" name="maintenance_duration"
                                                {{ isset($selectedMaintenanceDuration['maintenance_duration']) && $selectedMaintenanceDuration['maintenance_duration'] == 'one_week' ? 'checked' : '' }}
                                                value="one_week" id="one_week">
                                        <label class="form-check-label cursor-pointer" for="one_week">
                                            {{ translate('For_1_Week') }}
                                        </label>
                                    </div>
                                    <div class="form-check form--check">
                                        <input class="form-check-input" type="radio" name="maintenance_duration"
                                                {{ isset($selectedMaintenanceDuration['maintenance_duration']) && $selectedMaintenanceDuration['maintenance_duration'] == 'until_change' ? 'checked' : '' }}
                                                value="until_change" id="until_change">
                                        <label class="form-check-label cursor-pointer" for="until_change">
                                            {{ translate('Until_I_change') }}
                                        </label>
                                    </div>
                                    <div class="form-check form--check">
                                        <input class="form-check-input" type="radio" name="maintenance_duration"
                                                {{ isset($selectedMaintenanceDuration['maintenance_duration']) && $selectedMaintenanceDuration['maintenance_duration'] == 'customize' ? 'checked' : '' }}
                                                value="customize" id="customize">
                                        <label class="form-check-label cursor-pointer" for="customize">
                                            {{ translate('Customize') }}
                                        </label>
                                    </div>
                                </div>
                                <div class="row start-and-end-date">
                                    <div class="col-md-6">
                                        <label>{{ translate('Start_Date') }}</label>
                                        <input type="datetime-local" class="form-control" name="start_date"
                                                id="startDate"
                                                value="{{ old('start_date', $selectedMaintenanceDuration['start_date'] ?? '') }}"
                                                required>
                                    </div>
                                    <div class="col-md-6">
                                        <label>{{ translate('End_Date') }}</label>
                                        <input type="datetime-local" class="form-control" name="end_date"
                                                id="endDate"
                                                value="{{ old('end_date', $selectedMaintenanceDuration['end_date'] ?? '') }}"
                                                required>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12">
                                        <small id="dateError" class="form-text text-danger" style="display: none;">
                                            {{ translate('start_date_cannot_be_greater_than_end_date.') }}
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="advanceFeatureButtonDiv">
                    <div class="d-flex justify-content-center mt-3">
                        <a href="#" id="advanceFeatureToggle"
                            class="d-block font-weight-bold text-underline maintenance-advance-feature-button">
                            {{ translate('Advance_Feature') }}
                        </a>
                    </div>
                </div>

                <div class="row g-2 align-items-center mt-3" id="advanceFeatureSection" style="display: none;">
                    <div class="col-xl-4">
                        <h5 class="mb-2">{{ translate('Maintenance_Massage') }}</h5>
                        <p class="m-0">{{ translate('select_&_type_what_massage_you_want_to_see_your_selected_system_when_maintenance_mode_is_active.') }}</p>
                    </div>
                    <div class="col-xl-8">
                        <div class="border p-3 rounded">
                            <div class="form-group">
                                <label>{{ translate('Show_Contact_Info') }}</label>
                                <div class="d-flex flex-wrap gap-5 mb-3">
                                    <div class="form-check form--check">
                                        <input class="form-check-input" type="checkbox" name="business_number"
                                                value="1"
                                                {{ isset($selectedMaintenanceMessage['business_number']) && $selectedMaintenanceMessage['business_number'] == 1 ? 'checked' : '' }}
                                                id="businessNumber">
                                        <label class="form-check-label cursor-pointer" for="businessNumber">
                                            {{ translate('Business_Number') }}
                                        </label>
                                    </div>
                                    <div class="form-check form--check">
                                        <input class="form-check-input" type="checkbox" name="business_email"
                                                value="1"
                                                {{ isset($selectedMaintenanceMessage['business_email']) && $selectedMaintenanceMessage['business_email'] == 1 ? 'checked' : '' }}
                                                id="businessEmail">
                                        <label class="form-check-label cursor-pointer" for="businessEmail">
                                            {{ translate('Business_Email') }}
                                        </label>
                                    </div>
                                </div>

                            </div>
                            <div class="form-group">
                                <label>{{ translate('Maintenance_Message') }}
                                    <i class="tio-info-outined"
                                        data-toggle="tooltip"
                                        data-placement="top"
                                        title="{{ translate('the_maximum_character_limit_is_200') }}">
                                    </i>
                                </label>
                                <input type="text" class="form-control" name="maintenance_message"
                                        placeholder="{{ translate('we_are_working_on_something_special!') }}'"
                                        maxlength="100"
                                        value="{{ $selectedMaintenanceMessage['maintenance_message'] ?? '' }}">
                            </div>
                            <div class="form-group m-0">
                                <label>{{ translate('Message_Body') }}
                                    <i class="tio-info-outined"
                                        data-toggle="tooltip"
                                        data-placement="top"
                                        title="{{ translate('the_maximum_character_limit_is_200') }}">
                                    </i>
                                </label>
                                <textarea class="form-control" name="message_body" maxlength="255" rows="3"
                                          placeholder="{{ translate('sorry_for_the_inconvenience!') }} {{ translate('we_are_currently_undergoing_scheduled_maintenance_to_improve_our_services.') }} {{ translate('we_will_be_back_shortly.') }} {{ translate('thank_you_for_your_patience.') }}">{{ isset($selectedMaintenanceMessage['message_body']) && $selectedMaintenanceMessage['message_body'] ? $selectedMaintenanceMessage['message_body'] : ''}}</textarea>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 d-flex justify-content-center mt-4">
                        <a href="#" id="seeLessToggle"
                            class="d-block font-weight-bold text-underline maintenance-advance-feature-button">
                            {{ translate('See_Less') }}
                        </a>
                    </div>
                </div>
            </div>
            <div class="modal-footer p-3 p-sm-4">
                <div class="btn--container justify-content-end">
                    <button type="button" class="btn btn-secondary maintenance-cancel-button" data-dismiss="modal" id="cancelButton">
                        {{ translate('Cancel') }}
                    </button>
                    <button
                        type="{{ env('APP_MODE') != 'demo' ? 'submit' : 'button' }}"
                        class="btn btn-primary {{ env('APP_MODE') != 'demo' ? '' : 'call-demo' }}"
                    >
                        {{ translate('Save') }}
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
