@if($web_config['brand_setting'])
    <div class="product-type-physical-section search-product-attribute-container">
        <h6 class="font-semibold fs-13 mb-2">{{ translate('brands') }}</h6>
        <div class="pb-2">
            <div class="input-group-overlay input-group-sm">
                <input placeholder="{{ translate('search_by_brands') }}"
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
            @foreach($productBrands as $brand)
                <?php
                    if (isset($dataFrom) && $dataFrom == 'shop-view' && isset($shopSlug)) {
                        $brandRoute = route('shopView', ['id' => $shopSlug, 'brand_id' => $brand['id'],'data_from' => 'brand', 'offer_type' => ($data['offer_type'] ?? ''), 'page' => 1]);
                    } else if (isset($dataFrom) && $dataFrom == 'flash-deals') {
                        $brandRoute = route('flash-deals', ['id' => ($web_config['flash_deals']['id'] ?? 0), 'brand_id' => $brand['id'],'data_from'=>'brand', 'offer_type' => ($data['offer_type'] ?? ''), 'page' => 1]);
                    } else {
                        $brandRoute = route('products', ['brand_id' => $brand['id'], 'data_from'=>'brand', 'offer_type' => ($data['offer_type'] ?? ''), 'page' => 1]);
                    }
                ?>
                <ul class="brand mt-2 p-0 for-brand-hover {{ session('direction') === "rtl" ? 'mr-2' : ''}}" id="brand">
                    <li class="flex-between get-view-by-onclick cursor-pointer {{ request('brand_id') == $brand['id'] ? 'text-primary' : '' }}"
                        data-link="{{ $brandRoute }}">
                        <div class="text-start">
                            {{ $brand['name'] }}
                        </div>
                        <div class="__brands-cate-badge">
                            <span>
                                {{ $brand['brand_products_count'] }}
                            </span>
                        </div>
                    </li>
                </ul>
            @endforeach
        </ul>
    </div>
@endif
