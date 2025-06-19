@if($web_config['brand_setting'])
    <div class="product-type-physical-section">
        <h6 class="mb-3">{{translate('Brands')}}</h6>
        <div class="products_aside_brands">
            <ul class="common-nav nav flex-column pe-2">
                @foreach($productBrands as $brand)
                    <li class="overflow-hidden w-100">
                        <div class="flex-between-gap-3 align-items-center">
                            <div class="custom-checkbox w-75">
                                <label class="d-flex gap-2 align-items-center">
                                    <input type="checkbox" name="brand_ids[]" value="{{ $brand['id'] }}" class="real-time-action-update brand_class_for_tag_{{ $brand['id'] }}">
                                    <span class="text-truncate">{{ $brand['name'] }}</span>
                                </label>
                            </div>
                            <span class="badge bg-badge rounded-pill text-dark ms-auto">{{ $brand['brand_products_count'] }}</span>
                        </div>
                    </li>
                @endforeach
            </ul>
        </div>

        @if($productBrands->count() > 10)
            <div class="d-flex justify-content-center">
                <button
                    class="btn-link text-primary btn_products_aside_brands text-capitalize">{{translate('more_brands').'...'}}
                </button>
            </div>
        @endif
    </div>
@endif
