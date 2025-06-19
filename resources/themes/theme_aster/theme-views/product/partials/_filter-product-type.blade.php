@if($web_config['digital_product_setting'])
    <div>
        <h6 class="font-semibold fs-15 mb-2">{{ translate('Product_Type') }}</h6>
        <label class="w-100 opacity-75 text-nowrap for-sorting d-block mb-0 ps-0" for="sorting">
            <select class="form-select custom-select real-time-action-update" name="product_type">
                <option value="all" {{ request('product_type') != 'physical' && request('product_type') != 'digital' ? 'selected' : '' }}>
                    {{ translate('All') }}
                </option>
                <option value="physical" {{ request('product_type') == 'physical' ? 'selected' : '' }}>
                    {{ translate('physical') }}
                </option>
                <option value="digital" {{ request('product_type') == 'digital' ? 'selected' : '' }}>
                    {{ translate('Digital') }}
                </option>
            </select>
        </label>
    </div>
@endif
