<div>
    <h6 class="font-semibold fs-13 mb-3">{{ translate('categories') }}</h6>
    <div class="accordion mt-n1 product-categories-list" id="shop-categories">
        @foreach($productCategories as $category)
            <?php
                $dropdownActive = false;
                if (in_array(request('sub_category_id'), $category?->childes?->pluck('id')?->toArray() ?? [])) {
                    $dropdownActive = true;
                }

                foreach($category->childes as $child) {
                    if (in_array(request('sub_sub_category_id'), $child?->childes?->pluck('id')?->toArray() ?? [])) {
                        $dropdownActive = true;
                    }
                }
            ?>

            <div class="menu--caret-accordion {{ $dropdownActive ? 'open' : '' }}">
                <div class="card-header flex-between">
                    <?php
                        if (isset($dataFrom) && $dataFrom == 'shop-view' && isset($shopSlug)) {
                            $categoryRoute = route('shopView', ['id' => $shopSlug, 'category_id' => $category['id'],'data_from'=>'category', 'offer_type' => ($data['offer_type'] ?? ''), 'page' => 1]);
                        } else if (isset($dataFrom) && $dataFrom == 'flash-deals') {
                            $categoryRoute = route('flash-deals', ['id' => ($web_config['flash_deals']['id'] ?? 0), 'category_id' => $category['id'],'data_from'=>'category', 'offer_type' => ($data['offer_type'] ?? ''), 'page' => 1]);
                        } else {
                            $categoryRoute = route('products', ['category_id' => $category['id'],'data_from'=>'category', 'offer_type' => ($data['offer_type'] ?? ''), 'page' => 1]);
                        }
                    ?>
                    <div>
                        <label class="for-hover-label cursor-pointer get-view-by-onclick d-flex gap-10px align-items-center {{ request('category_id') == $category['id'] ? 'text-primary' : '' }}"
                               data-link="{{ $categoryRoute }}">
                            <img width="20" class="aspect-1 rounded-circle object-cover" src="{{ getStorageImages(path: $category->icon_full_url, type: 'category') }}" alt="{{ $category['name'] }}">
                            <span class="line--limit-2">
                                {{$category['name']}}
                            </span>
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
                <div class="card-body p-0 ms-2 {{ $dropdownActive ? '' : 'd--none' }}"
                    id="collapse-{{$category['id']}}">
                    @foreach($category->childes as $child)
                        <?php
                            $dropdownActive = false;
                            if (in_array(request('sub_sub_category_id'), $child?->childes?->pluck('id')?->toArray() ?? [])) {
                                $dropdownActive = true;
                            }
                        ?>
                        <div class="menu--caret-accordion {{ $dropdownActive ? 'open' : '' }}">
                            <div class="for-hover-label card-header flex-between">
                                <?php
                                    if (isset($dataFrom) && $dataFrom == 'shop-view' && isset($shopSlug)) {
                                        $subCategoryRoute = route('shopView', ['id' => $shopSlug, 'sub_category_id' => $child['id'],'data_from'=>'category', 'offer_type' => ($data['offer_type'] ?? ''), 'page' => 1]);
                                    } else if (isset($dataFrom) && $dataFrom == 'flash-deals') {
                                        $subCategoryRoute = route('flash-deals', ['id' => ($web_config['flash_deals']['id'] ?? 0), 'sub_category_id' => $child['id'],'data_from'=>'category', 'offer_type' => ($data['offer_type'] ?? ''), 'page' => 1]);
                                    } else {
                                        $subCategoryRoute = route('products', ['sub_category_id' => $child['id'],'data_from'=>'category', 'offer_type' => ($data['offer_type'] ?? ''), 'page' => 1]);
                                    }
                                ?>

                                <div>
                                    <label class="cursor-pointer get-view-by-onclick {{ request('sub_category_id') == $child['id'] ? 'text-primary' : '' }}"
                                           data-link="{{ $subCategoryRoute }}">
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
                                class="card-body p-0 ms-2 {{ $dropdownActive ? '' : 'd--none' }}"
                                id="collapse-{{$child['id']}}">
                                @foreach($child->childes as $subSubCategory)

                                    <?php
                                        if (isset($dataFrom) && $dataFrom == 'shop-view' && isset($shopSlug)) {
                                            $subSubCategoryRoute = route('shopView', ['id' => $shopSlug, 'sub_sub_category_id' => $subSubCategory['id'], 'data_from' => 'category', 'offer_type' => ($data['offer_type'] ?? ''), 'page' => 1]);
                                        } else if (isset($dataFrom) && $dataFrom == 'flash-deals') {
                                            $subSubCategoryRoute = route('flash-deals', ['id' => ($web_config['flash_deals']['id'] ?? 0), 'sub_sub_category_id' => $subSubCategory['id'], 'data_from' => 'category', 'offer_type' => ($data['offer_type'] ?? ''), 'page' => 1]);
                                        } else {
                                            $subSubCategoryRoute = route('products', ['sub_sub_category_id' => $subSubCategory['id'], 'data_from' => 'category', 'offer_type' => ($data['offer_type'] ?? ''), 'page' => 1]);
                                        }
                                    ?>

                                    <div class="card-header">
                                        <label
                                            class="for-hover-label d-block cursor-pointer text-left get-view-by-onclick {{ request('sub_sub_category_id') == $subSubCategory['id'] ? 'text-primary' : '' }}"
                                            data-link="{{ $subSubCategoryRoute }}">
                                            {{ $subSubCategory['name'] }}
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
