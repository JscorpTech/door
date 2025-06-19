<div class="d-lg-none">
    <h6 class="font-semibold fs-15 mb-2">{{ translate('Sort_By') }}</h6>
    <select class="form-control real-time-action-update" name="sort_by">
        <option value="latest">{{ translate('Default') }}</option>
        <option value="low-high">
            {{ translate('Price') }} ({{ translate('Low_to_High') }})
        </option>
        <option value="high-low">
            {{ translate('Price') }} ({{ translate('High_to_Low') }})
        </option>
        <option value="rating-low-high">
            {{ translate('Rating') }} ({{ translate('Low_to_High') }})
        </option>
        <option value="rating-high-low">
            {{ translate('Rating') }} ({{ translate('High_to_Low') }})
        </option>
        <option value="a-z">
            {{ translate('Alphabetical') }} ({{ 'A '.translate('to').' Z' }})
        </option>
        <option value="z-a">
            {{ translate('Alphabetical') }} ({{ 'Z '.translate('to').' A' }})
        </option>
    </select>
</div>
