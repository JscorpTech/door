@if($web_config['digital_product_setting'] && count($productAuthors) > 0)
    <div class="product-type-digital-section search-product-attribute-container">
        <h6 class="font-semibold fs-13 mb-2">
            {{ translate('authors') }}/{{ translate('Creator') }}/{{ translate('Artist') }}
        </h6>
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
            @foreach($productAuthors as $productAuthor)
                <?php
                    if (isset($dataFrom) && $dataFrom == 'shop-view' && isset($shopSlug)) {
                        $productAuthorRoute = route('shopView', ['id' => $shopSlug, 'author_id' => $productAuthor['id'], 'product_type'=> 'digital', 'offer_type' => ($data['offer_type'] ?? ''), 'page' => 1]);
                    } else if (isset($dataFrom) && $dataFrom == 'flash-deals') {
                        $productAuthorRoute = route('flash-deals', ['id' => ($web_config['flash_deals']['id'] ?? 0), 'author_id' => $productAuthor['id'], 'product_type'=> 'digital', 'offer_type' => ($data['offer_type'] ?? ''), 'page' => 1]);
                    } else {
                        $productAuthorRoute = route('products', ['author_id' => $productAuthor['id'], 'product_type' => 'digital', 'offer_type' => ($data['offer_type'] ?? ''), 'page' => 1]);
                    }
                ?>
                <ul class="brand mt-2 p-0 for-brand-hover {{Session::get('direction') === "rtl" ? 'mr-2' : ''}}"
                    id="brand">
                    <li class="flex-between get-view-by-onclick cursor-pointer pe-2 {{ request('author_id') != '' && request('author_id') == $productAuthor['id'] ? 'text-primary' : '' }}"
                        data-link="{{ $productAuthorRoute }}">
                        <div class="text-start">
                            {{ $productAuthor['name'] }}
                        </div>
                        <div class="__brands-cate-badge">
                            <span>
                                {{ $productAuthor['digital_product_author_count'] }}
                            </span>
                        </div>
                    </li>
                </ul>
            @endforeach
        </ul>
    </div>
@endif
