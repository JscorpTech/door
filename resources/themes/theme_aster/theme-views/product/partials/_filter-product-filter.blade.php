<div>
    <h6 class="font-semibold fs-15 mb-2">{{ translate('filter') }}</h6>
    <label class="w-100 opacity-75 text-nowrap for-sorting d-block mb-0 ps-0" for="sorting">
        <select class="form-select custom-select real-time-action-update" name="data_from">
            <option value="default" {{ $data['data_from'] == '' ? 'selected':'' }}>
                {{ translate('Default') }}
            </option>
            <option value="best-selling" {{ $data['data_from']=='best-selling'?'selected':'' }}>
                {{ translate('Best_Selling') }}
            </option>
            <option value="top-rated" {{ $data['data_from']=='top-rated'?'selected':'' }}>
                {{ translate('Top_Rated') }}
            </option>
            <option value="most-favorite" {{ $data['data_from']=='most-favorite'?'selected':''}}>
                {{ translate('Most_Favorite') }}
            </option>

        </select>
    </label>
</div>
