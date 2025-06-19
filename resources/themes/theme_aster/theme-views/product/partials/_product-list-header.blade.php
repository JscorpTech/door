<div class="card mb-3">
    <div class="card-body">
        <div class="row gy-2 align-items-center">
            <div class="col-lg-4">

                @if(isset($pageTitleContent) && $pageTitleContent)
                    <h3 class="mb-1">{{ $pageTitleContent }}</h3>
                @endif

                @if(isset($pageProductsShow) && $pageProductsShow)
                    <div class="text-primary fw-semibold">
                        {{ $products->total() }} {{ $products->total() > 1 ? translate('items') : translate('item') }}
                    </div>
                @else
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb fs-12 mb-0">
                            <li class="breadcrumb-item">
                                <a href="{{ route('home') }}">
                                    {{ translate('home') }}
                                </a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">
                                {{ translate(str_replace(['-', '_', '/'],' ', $data['offer_type'])) }} {{ translate('products') }} {{ isset($data['brand_name']) ? ' / '.$data['brand_name'] : ''}} {{ request('name') ? '('.request('name').')' : ''}}
                            </li>
                        </ol>
                    </nav>
                @endif
            </div>

            <div class="col-lg-8">
                <div class="d-flex justify-content-lg-end flex-wrap gap-3">

                    @if(isset($searchBarSection) && $searchBarSection)
                        @if(!request()->has('global_search_input'))
                            <div class="search-box search-box-2 position-relative">
                                <div class="d-flex">
                                    <div class="select-wrap focus-border border border-end-logical-0 d-flex align-items-center">
                                        <input
                                            type="search"
                                            class="form-control border-0 focus-input search-bar-input" name="product_name"
                                            value="{{ request('product_name') }}"
                                            placeholder="{{ translate('search_for_items').'...' }}"
                                        />
                                    </div>
                                    <button type="submit" class="btn btn-primary" aria-label="{{ translate('Search') }}">
                                        <i class="bi bi-search"></i>
                                    </button>
                                </div>
                            </div>
                        @endif
                    @endif

                    @if(isset($sortBySection) && $sortBySection)
                    <div class="border rounded custom-ps-3 py-2">
                        <div class="d-flex gap-2">
                            <div class="flex-middle gap-2">
                                <i class="bi bi-sort-up-alt"></i>
                                <span class="d-none d-sm-inline-block">{{ translate('sort_by').':' }}</span>
                            </div>
                            <div class="dropdown product-view-sort-by" data-default="{{ translate('default') }}">
                                <button type="button" data-bs-toggle="dropdown" aria-expanded="false"
                                        class="border-0 bg-transparent dropdown-toggle text-dark p-0 custom-pe-3">
                                    @if (!$data['sort_by'] || $data['sort_by'] == 'latest')
                                        {{ translate('default') }}
                                    @elseif($data['sort_by'] == 'low-high')
                                        {{ translate('Price') }} ({{ translate('Low_to_High') }})
                                    @elseif($data['sort_by'] == 'high-low')
                                        {{ translate('Price') }} ({{ translate('High_to_Low') }})
                                    @elseif($data['sort_by'] == 'rating-low-high')
                                        {{ translate('Rating') }} ({{ translate('Low_to_High') }})
                                    @elseif($data['sort_by'] == 'rating-high-low')
                                        {{ translate('Price') }} ({{ translate('High_to_Low') }})
                                    @elseif($data['sort_by'] == 'a-z')
                                        {{ translate('Alphabetical') }} ({{ 'A '.translate('to').' Z' }})
                                    @elseif($data['sort_by'] == 'z-a')
                                        {{ translate('Alphabetical') }} ({{ 'Z '.translate('to').' A' }})
                                    @endif

                                </button>
                                <input hidden id="data_from" value="{{ request('data_from') }}">
                                <input hidden id="category_id" value="{{ request('category_id') }}">
                                <input hidden id="brand_id" value="{{ request('brand_id') }}">
                                <ul class="dropdown-menu dropdown-menu-end" id="sort-by-list">
                                    <li class="link-hover-base product-list-filter-on-sort-by selected" data-value="latest">
                                        <label>
                                            <input type="radio" class="real-time-action-update" name="sort_by" value="latest" {{ !$data['sort_by'] || $data['sort_by'] == 'latest' ? 'checked' : '' }}>
                                            {{ translate('default') }}
                                        </label>
                                    </li>
                                    <li class="link-hover-base product-list-filter-on-sort-by" data-value="low-high">
                                        <label>
                                            <input type="radio" class="real-time-action-update" name="sort_by" value="low-high" {{ $data['sort_by'] == 'low-high' ? 'checked' : '' }}>
                                            {{ translate('Price') }} ({{ translate('Low_to_High') }})
                                        </label>
                                    </li>
                                    <li class="link-hover-base product-list-filter-on-sort-by" data-value="high-low">
                                        <label>
                                            <input type="radio" class="real-time-action-update" name="sort_by" value="high-low" {{ $data['sort_by'] == 'high-low' ? 'checked' : '' }}>
                                            {{ translate('Price') }} ({{ translate('High_to_Low') }})
                                        </label>
                                    </li>
                                    <li class="link-hover-base product-list-filter-on-sort-by" data-value="rating-low-high">
                                        <label>
                                            <input type="radio" class="real-time-action-update" name="sort_by" value="rating-low-high" {{ $data['sort_by'] == 'rating-low-high' ? 'checked' : '' }}>
                                            {{ translate('Rating') }} ({{ translate('Low_to_High') }})
                                        </label>
                                    </li>
                                    <li class="link-hover-base product-list-filter-on-sort-by" data-value="rating-high-low">
                                        <label>
                                            <input type="radio" class="real-time-action-update" name="sort_by" value="rating-high-low" {{ $data['sort_by'] == 'rating-high-low' ? 'checked' : '' }}>
                                            {{ translate('Rating') }} ({{ translate('High_to_Low') }})
                                        </label>
                                    </li>
                                    <li class="link-hover-base product-list-filter-on-sort-by" data-value="a-z">
                                        <label>
                                            <input type="radio" class="real-time-action-update" name="sort_by" value="a-z" {{ $data['sort_by'] == 'a-z' ? 'checked' : '' }}>
                                            {{ translate('Alphabetical') }} ({{ 'A '.translate('to').' Z' }})
                                        </label>
                                    </li>
                                    <li class="link-hover-base product-list-filter-on-sort-by" data-value="z-a">
                                        <label>
                                            <input type="radio" class="real-time-action-update" name="sort_by" value="z-a" {{ $data['sort_by'] == 'z-a' ? 'checked' : '' }}>
                                            {{ translate('Alphabetical') }} ({{ 'Z '.translate('to').' A' }})
                                        </label>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    @endif

                    @if(isset($showProductsFilter) && $showProductsFilter)
                    <div class="border rounded custom-ps-3 py-2">
                        <div class="d-flex gap-2">
                            <div class="flex-middle gap-2">
                                <i class="bi bi-sort-up-alt"></i>
                                <span class="d-none d-sm-inline-block">{{ translate('Filter_By') }} :</span>
                            </div>
                            <div class="dropdown filter-on-product-filter">
                                <button type="button"
                                        class="border-0 bg-transparent dropdown-toggle p-0 custom-pe-3"
                                        data-bs-toggle="dropdown" aria-expanded="false">
                                    {{$data['data_from']=="best-selling"||$data['data_from']=="top-rated"||$data['data_from']=="featured_deal"||$data['data_from']=="latest"|| $data['data_from']=="most-favorite" || $data['data_from']=="featured" ?
                                    ucwords(str_replace(['-', '_', '/'], ' ', translate($data['data_from']))) : translate('default') }}
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li class="link-hover-base filter-on-product-filter-change {{ $data['data_from'] == '' ? 'selected':''}}" data-value="">
                                        <label>
                                            <input type="radio" class="real-time-action-update" name="data_from" value="" {{ $data['data_from'] == '' ? 'checked' : '' }}>
                                            {{ translate('default') }}
                                        </label>
                                    </li>
                                    <li class="link-hover-base filter-on-product-filter-change {{$data['data_from']=='latest'? 'selected':''}}" data-value="latest">
                                        <label>
                                            <input type="radio" class="real-time-action-update" name="data_from" value="latest" {{ $data['data_from'] == 'latest' ? 'checked' : '' }}>
                                            {{ translate('Latest') }}
                                        </label>
                                    </li>
                                    <li class="link-hover-base filter-on-product-filter-change {{$data['data_from']=='best-selling'? 'selected':''}}" data-value="best-selling">
                                        <label>
                                            <input type="radio" class="real-time-action-update" name="data_from" value="best-selling" {{ $data['data_from'] == 'best-selling' ? 'checked' : '' }}>
                                            {{ translate('Best_Selling') }}
                                        </label>
                                    </li>
                                    <li class="link-hover-base filter-on-product-filter-change {{$data['data_from']=='top-rated'? 'selected':''}}" data-value="top-rated">
                                        <label>
                                            <input type="radio" class="real-time-action-update" name="data_from" value="top-rated" {{ $data['data_from'] == 'top-rated' ? 'checked' : '' }}>
                                            {{ translate('Top_Rated') }}
                                        </label>
                                    </li>
                                    <li class="link-hover-base filter-on-product-filter-change {{$data['data_from']=='most-favorite'? 'selected':''}}" data-value="most-favorite">
                                        <label>
                                            <input type="radio" class="real-time-action-update" name="data_from" value="most-favorite" {{ $data['data_from'] == 'most-favorite' ? 'checked' : '' }}>
                                            {{ translate('Most_Favorite') }}
                                        </label>
                                    </li>
                                    <li class="link-hover-base filter-on-product-filter-change {{$data['data_from']=='featured'? 'selected':''}}" data-value="featured">
                                        <label>
                                            <input type="radio" class="real-time-action-update" name="data_from" value="featured" {{ $data['data_from'] == 'featured' ? 'checked' : '' }}>
                                            {{ translate('featured') }}
                                        </label>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    @endif

                </div>
            </div>
        </div>
    </div>
</div>
