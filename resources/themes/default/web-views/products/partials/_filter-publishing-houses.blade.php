@if($web_config['digital_product_setting'] && count($productPublishingHouses) > 0)
    <div class="product-type-digital-section search-product-attribute-container">
        <h6 class="font-semibold fs-13 mb-2">{{ translate('Publishing_House') }}</h6>
        <div class="pb-2">
            <div class="input-group-overlay input-group-sm">
                <input placeholder="{{ translate('search_by_name') }}"
                       class="__inline-38 cz-filter-search form-control form-control-sm appended-form-control search-product-attribute"
                       type="text">
                <div class="input-group-append-overlay">
                    <span class="input-group-text">
                        <i class="czi-search"></i>
                    </span>
                </div>
            </div>
        </div>

        <ul class="__brands-cate-wrap attribute-list" data-simplebar
            data-simplebar-auto-hide="false">
            <div class="no-data-found text-muted" style="display:none;">{{ translate('No_Data_Found') }}</div>
            @foreach($productPublishingHouses as $publishingHouseItem)
                <?php
                    if (isset($dataFrom) && $dataFrom == 'shop-view' && isset($shopSlug)) {
                        $publishingHouseRoute = route('shopView', ['id' => $shopSlug, 'publishing_house_id' => $publishingHouseItem['id'], 'product_type'=> 'digital', 'offer_type' => ($data['offer_type'] ?? ''), 'page' => 1]);
                    } else if (isset($dataFrom) && $dataFrom == 'flash-deals') {
                        $publishingHouseRoute = route('flash-deals', ['id' => ($web_config['flash_deals']['id'] ?? 0), 'publishing_house_id' => $publishingHouseItem['id'], 'product_type'=> 'digital', 'offer_type' => ($data['offer_type'] ?? ''), 'page' => 1]);
                    } else {
                        $publishingHouseRoute = route('products', ['publishing_house_id' => $publishingHouseItem['id'], 'product_type' => 'digital', 'offer_type' => ($data['offer_type'] ?? ''), 'page' => 1]);
                    }
                ?>
                <ul class="brand mt-2 p-0 for-brand-hover {{ session('direction') === "rtl" ? 'mr-2' : ''}}" id="brand">
                    <li class="flex-between get-view-by-onclick cursor-pointer pe-2 {{ request('publishing_house_id') != '' && request('publishing_house_id') == $publishingHouseItem['id'] ? 'text-primary' : '' }}"
                        data-link="{{ $publishingHouseRoute }}">
                        <div class="text-start">
                            {{ $publishingHouseItem['name'] }}
                        </div>
                        <div class="__brands-cate-badge">
                            <span>
                                {{ $publishingHouseItem['publishing_house_products_count'] }}
                            </span>
                        </div>
                    </li>
                </ul>
            @endforeach
        </ul>
    </div>
@endif
