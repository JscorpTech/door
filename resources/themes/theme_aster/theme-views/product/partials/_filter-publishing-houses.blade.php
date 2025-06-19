@if($web_config['digital_product_setting'] && count($productPublishingHouses) > 0)

    <div class="product-type-digital-section">
        <h6 class="mb-3">{{translate('Publishing_House')}}</h6>
        <div class="products_aside_brands">
            <ul class="common-nav nav flex-column pe-2">
                @foreach($productPublishingHouses as $publishingHouseItem)
                    <li>
                        <div class="flex-between-gap-3 align-items-center">
                            <label class="custom-checkbox">
                                <input type="checkbox" name="publishing_house_ids[]" value="{{ $publishingHouseItem['id'] }}" class="real-time-action-update publishing_house_class_for_tag_{{ $publishingHouseItem['id'] }}">
                                <span>{{ $publishingHouseItem['name'] }}</span>
                            </label>
                            <span class="badge bg-badge rounded-pill text-dark ms-auto">
                                {{ $publishingHouseItem['publishing_house_products_count'] }}
                            </span>
                        </div>
                    </li>
                @endforeach
            </ul>
        </div>

        @if($web_config['digital_product_setting'] && count($productPublishingHouses) > 10)
            <div class="d-flex justify-content-center">
                <button
                    class="btn-link text-primary btn_products_aside_brands text-capitalize">{{translate('more_publishing_house').'...'}}
                </button>
            </div>
        @endif
    </div>
@endif
