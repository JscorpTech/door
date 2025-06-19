<div class="d-lg-none">
    <h6 class="font-semibold fs-13 mb-2">{{ translate('Sort_By') }}</h6>
    <select class="form-control product-list-filter-input" name="sort_by">
        <option value="latest" {{ request('sort_by') == 'latest' ? 'selected':'' }}>
            {{ translate('Default') }}
        </option>
        <option value="low-high" {{ request('sort_by') == 'low-high' ? 'selected':'' }}>
            {{ translate('Price') }} ({{ translate('Low_to_High') }})
        </option>
        <option value="high-low" {{ request('sort_by') == 'high-low' ? 'selected':'' }}>
            {{ translate('Price') }} ({{ translate('High_to_Low') }})
        </option>
        <option value="rating-low-high" {{ request('sort_by') == 'rating-low-high' ? 'selected':'' }}>
            {{ translate('Rating') }} ({{ translate('Low_to_High') }})
        </option>
        <option value="rating-high-low" {{ request('sort_by') == 'rating-high-low' ? 'selected':'' }}>
            {{ translate('Rating') }} ({{ translate('High_to_Low') }})
        </option>
        <option value="a-z" {{ request('sort_by') == 'a-z' ? 'selected':'' }}>
            {{ translate('Alphabetical') }} ({{ 'A '.translate('to').' Z' }})
        </option>
        <option value="z-a" {{ request('sort_by') == 'z-a' ? 'selected':'' }}>
            {{ translate('Alphabetical') }} ({{ 'Z '.translate('to').' A' }})
        </option>
    </select>
</div>
