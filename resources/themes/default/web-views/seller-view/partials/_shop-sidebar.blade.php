<aside class="col-lg-3 hidden-xs col-md-3 col-sm-4 SearchParameters __search-sidebar {{Session::get('direction') === "rtl" ? 'pl-2' : 'pr-2'}}" id="SearchParameters">
    <div class="cz-sidebar __inline-35 overflow-hidden" id="shop-sidebar">
        <div class="bg-white p-3 border-bottom">
            <h6 class="font-semibold mb-0 fs-16">{{ translate('Filter_By') }}</h6>
            <button class="close ms-auto fs-18-mobile position-relative top-n6 d-lg-none close-icon"
                    type="button" data-dismiss="sidebar" aria-label="Close">
                <i class="tio-clear"></i>
            </button>
        </div>
        <div class="p-3 shop-sidebar-scroll">
            <div class="d-flex gap-3 flex-column">
                @if($web_config['digital_product_setting'])
                    <div class="">
                        <h6 class="font-semibold fs-15 mb-2">{{ translate('Product_Type') }}</h6>
                        <label class="w-100 opacity-75 text-nowrap for-sorting d-block mb-0 ps-0" for="sorting">
                            <select class="form-control filter-on-product-type-change">
                                <option value="all" {{ !request('product_type') ? 'selected' : '' }}>{{ translate('All') }}</option>
                                <option value="physical" {{ request('product_type') == 'physical' ? 'selected' : '' }}>
                                    {{ translate('physical') }}
                                </option>
                                <option value="digital" {{ request('product_type') == 'digital' ? 'selected' : '' }}>
                                    {{ translate('digital') }}
                                </option>
                            </select>
                        </label>
                    </div>
                @endif

                <div class="d-lg-none">
                    <h6 class="font-semibold fs-15 mb-2">{{ translate('Sort_By') }}</h6>
                    <form>
                        <select class="form-control product-list-filter-on-viewpage">
                            <option value="latest">{{translate('latest')}}</option>
                            <option value="low-high">{{translate('low_to_High_Price')}} </option>
                            <option value="high-low">{{translate('High_to_Low_Price')}}</option>
                            <option value="a-z">{{translate('A_to_Z_Order')}}</option>
                            <option value="z-a">{{translate('Z_to_A_Order')}}</option>
                        </select>
                    </form>
                </div>

                <div>
                    <h6 class="font-semibold fs-15 mb-3">{{ translate('categories') }}</h6>
                    <div class="accordion mt-n1 product-categories-list" id="shop-categories">
                        @foreach($categories as $category)
                            <div class="menu--caret-accordion">
                                <div class="card-header flex-between">
                                    <div>
                                        <label class="for-hover-label cursor-pointer get-view-by-onclick"
                                               data-link="{{ route('shopView', ['id'=> $seller_id, 'category_id' => $category['id'], 'data_from' => 'category', 'offer_type' => request('offer_type') ?? '', 'page' => 1]) }}">
                                            {{ $category['name'] }}
                                        </label>
                                    </div>
                                    <div class="px-2 cursor-pointer menu--caret">
                                        <strong class="pull-right for-brand-hover">
                                            @if($category->childes->count()>0)
                                                <i class="tio-next-ui fs-13"></i>
                                            @endif
                                        </strong>
                                    </div>
                                </div>
                                <div
                                    class="card-body p-0 ms-2 d--none"
                                    id="collapse-{{$category['id']}}">
                                    @foreach($category->childes as $child)
                                        <div class="menu--caret-accordion">
                                            <div class="for-hover-label card-header flex-between">
                                                <div>
                                                    <label class="cursor-pointer get-view-by-onclick"
                                                           data-link="{{ route('shopView', ['id'=> $seller_id, 'sub_category_id' => $child['id'], 'data_from' => 'category', 'offer_type' => request('offer_type') ?? '', 'page' => 1]) }}">
                                                        {{$child['name']}}
                                                    </label>
                                                </div>
                                                <div class="px-2 cursor-pointer menu--caret">
                                                    <strong class="pull-right">
                                                        @if($child->childes->count()>0)
                                                            <i class="tio-next-ui fs-13"></i>
                                                        @endif
                                                    </strong>
                                                </div>
                                            </div>
                                            <div
                                                class="card-body p-0 ms-2 d--none"
                                                id="collapse-{{$child['id']}}">
                                                @foreach($child->childes as $ch)
                                                    <div class="card-header">
                                                        <label
                                                            class="for-hover-label d-block cursor-pointer text-left get-view-by-onclick"
                                                            data-link="{{ route('shopView', ['id' => $seller_id, 'sub_category_id' => $ch['id'], 'data_from' => 'category', 'offer_type' => request('offer_type') ?? '', 'page' => 1])}}">
                                                            {{$ch['name']}}
                                                        </label>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                @if($web_config['brand_setting'])
                    <div class="product-type-physical-section search-product-attribute-container">
                        <h6 class="font-semibold fs-15 mb-2">{{ translate('brands') }}</h6>
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
                        <div class="__brands-cate-wrap attribute-list" data-simplebar
                            data-simplebar-auto-hide="false">
                            @foreach($activeBrands as $brand)
                                <ul
                                    class="brand mt-2 p-0 for-brand-hover {{Session::get('direction') === "rtl" ? 'mr-2' : ''}}"
                                    id="brand">
                                    <li class="flex-between get-view-by-onclick cursor-pointer"
                                        data-link="{{ route('shopView', ['id'=> $seller_id, 'brand_id' => $brand['id'], 'data_from' => 'brand', 'offer_type' => request('offer_type') ?? '', 'page' => 1]) }}"
                                    >
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
                        </div>
                    </div>
                @endif

                @if($web_config['digital_product_setting'] && count($shopPublishingHouses) > 0)
                    <div class="product-type-digital-section search-product-attribute-container">
                        <h6 class="font-semibold fs-15 mb-2">{{ translate('Publishing_House') }}</h6>
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
                        <div class="__brands-cate-wrap attribute-list" data-simplebar
                            data-simplebar-auto-hide="false">
                            @foreach($shopPublishingHouses as $publishingHouseItem)
                                <ul class="brand mt-2 p-0 for-brand-hover {{Session::get('direction') === "rtl" ? 'mr-2' : ''}}"
                                     id="brand">
                                    <li class="flex-between get-view-by-onclick cursor-pointer"
                                        data-link="{{ route('shopView', ['id'=> $seller_id, 'publishing_house_id' => $publishingHouseItem['id'], 'offer_type' => request('offer_type') ?? '', 'product_type' => 'digital', 'page' => 1]) }}"
                                    >
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
                        </div>
                    </div>
                @endif

                @if($web_config['digital_product_setting'] && count($digitalProductAuthors) > 0)
                    <div class="product-type-digital-section search-product-attribute-container">
                        <h6 class="font-semibold fs-15 mb-2">
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
                        <div class="__brands-cate-wrap attribute-list" data-simplebar
                            data-simplebar-auto-hide="false">
                            @foreach($digitalProductAuthors as $productAuthor)
                                <ul class="brand mt-2 p-0 for-brand-hover {{Session::get('direction') === "rtl" ? 'mr-2' : ''}}"
                                     id="brand">
                                    <li class="flex-between get-view-by-onclick cursor-pointer"
                                        data-link="{{ route('shopView', ['id'=> $seller_id, 'author_id' => $productAuthor['id'], 'offer_type' => request('offer_type') ?? '','product_type' => 'digital', 'page' => 1]) }}">
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
                        </div>
                    </div>
                @endif

            </div>
        </div>

    </div>
    <div class="sidebar-overlay"></div>
</aside>
