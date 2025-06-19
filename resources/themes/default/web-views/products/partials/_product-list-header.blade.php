<div class="search-page-header flex-wrap gap-3">
    @if(isset($shopViewPageHeader) && $shopViewPageHeader)
        <div>
            <nav>
                <div class="nav nav-tabs mb-0" id="nav-tab" role="tablist">
                    <a class="nav-link {{ request('offer_type') != 'clearance_sale' ? 'active' : '' }}"
                       href="{{ route('shopView',['id' => ($shopInfoArray['id'] != 0 ? $shopInfoArray['id'] : 0)]) }}">
                        <h3 class="widget-title align-self-center font-bold fs-16 text-capitalize my-0">{{translate('all_product')}}</h3>
                    </a>
                    @if($stockClearanceSetup && $stockClearanceProducts > 0)
                        <a class="nav-link {{ request('offer_type') == 'clearance_sale' ? 'active' : '' }}"
                           href="{{ route('shopView',['id' => ($shopInfoArray['id'] != 0 ? $shopInfoArray['id'] : 0), 'offer_type' => 'clearance_sale']) }}">
                            <h3 class="widget-title align-self-center font-bold fs-16 text-capitalize my-0">{{translate('clearance_sale')}}</h3>
                        </a>
                    @endif
                </div>
            </nav>
        </div>
    @else
        <div>
            @if(isset($pageTitleContent) && $pageTitleContent)
                <h5 class="font-semibold mb-1 text-capitalize">
                    {{ $pageTitleContent }}
                </h5>
            @endif

            <div>
                <span class="view-page-item-count clearance-sale-count">{{ $pageProductsCount }}</span>
                {{ translate('items_found') }}
            </div>
        </div>
    @endif



    <div class="d-flex flex-wrap gap-3">
        @if(isset($searchBarSection) && $searchBarSection)
            @if(!request()->has('global_search_input'))
                <div class="d-flex align-items-center gap-2 position-relative">
                    <input class="form-control appended-form-control pe-5rem search-page-button-input" type="search" autocomplete="off"
                           placeholder="{{ translate('Search_for_items...') }}" name="product_name" value="{{ request('product_name') }}">
                    <button class="input-group-append-overlay search_button d-md-block search-page-button" data-name="name">
                    <span class="input-group-text">
                        <i class="czi-search text-white"></i>
                    </span>
                    </button>
                </div>
            @endif
        @endif

        @if(isset($sortBySection) && $sortBySection)
        <div id="search-form" class="d-none d-lg-block">
            <div class="sorting-item">
                @include('web-views.partials._svg-icon-container', ['iconType' => 'sorting'])
                <label class="for-sorting" for="sorting">{{ translate('sort_by') }}</label>

                <select class="product-list-filter-input" name="sort_by">
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
        </div>
        @endif

        @if(isset($showProductsFilter) && $showProductsFilter)
        <div class="d-none d-lg-block">
            <div class="sorting-item">
                @include('web-views.partials._svg-icon-container', ['iconType' => 'sorting'])
                <label class="for-sorting" for="sorting">
                    <span>{{ translate('Filter_By') }}</span>
                </label>
                <select class="product-list-filter-input" name="data_from">
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
            </div>
        </div>
        @endif

    </div>
    <div class="d-lg-none">
        <div class="filter-show-btn btn btn--primary py-1 px-2 m-0">
            <i class="tio-filter"></i>
        </div>
    </div>
</div>
