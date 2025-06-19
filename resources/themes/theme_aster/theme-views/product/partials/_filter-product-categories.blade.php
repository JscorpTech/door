<div class="">
    <h6 class="mb-3">{{translate('Categories')}}</h6>

    <div class="products_aside_categories products-aside-categories-list">
        <ul class="common-nav flex-column nav flex-nowrap custom_common_nav pe-1">
            @foreach($productCategories as $category)
                <li class="categories-form-check">
                    <div class="d-flex justify-content-between align-items-center categories-form-check-inner">
                        <label class="custom-checkbox d-flex gap-2 align-items-center">
                            <input type="checkbox" name="category_ids[]" value="{{ $category['id'] }}" class="real-time-action-update category_class_for_tag_{{ $category['id'] }}"
                                {{ in_array($category['id'], request('category_ids') ?? []) ? 'checked' : '' }}>
                            <span class="line-clamp-2">{{$category['name']}}</span>
                        </label>
                        <div class="badge bg-badge rounded-pill text-dark ms-auto">
                            {{ $category?->product_count ?? 0 }}
                        </div>
                    </div>

                    @if ($category->childes->count() > 0)
                        <ul class="sub_menu categories-form-subgroup">
                            @foreach($category->childes as $child)
                                <li class="categories-form-check">
                                    <div class="d-flex justify-content-between align-items-center categories-form-check-inner">
                                        <label class="custom-checkbox d-flex gap-2 align-items-center">
                                            <input type="checkbox" name="sub_category_ids[]" value="{{ $child['id'] }}" class="real-time-action-update">
                                            <span class="line-clamp-2">{{$child['name']}}</span>
                                        </label>
                                        <div class="badge bg-badge rounded-pill text-dark ms-auto">
                                            {{ $child?->sub_category_product_count ?? 0 }}
                                        </div>
                                    </div>

                                    @if ($child->childes->count() > 0)
                                        <ul class="sub_menu categories-form-subgroup">
                                            @foreach($child->childes as $ch)
                                                <li class="categories-form-check">
                                                    <div class="d-flex justify-content-between align-items-center categories-form-check-inner">
                                                        <label class="custom-checkbox">
                                                            <input type="checkbox" name="sub_sub_category_ids[]" value="{{ $ch['id'] }}" class="real-time-action-update">
                                                            <span class="line-clamp-2">{{$ch['name']}}</span>
                                                        </label>
                                                        <div class="badge bg-badge rounded-pill text-dark ms-auto">
                                                            {{ $ch?->sub_sub_category_product_count ?? 0 }}
                                                        </div>
                                                    </div>
                                                </li>
                                            @endforeach
                                        </ul>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </li>
            @endforeach
        </ul>
    </div>

    @if ($productCategories->count() > 10)
        <div class="d-flex justify-content-center">
            <button
                class="btn-link text-primary btn_products_aside_categories">{{translate('more_categories').'...'}}
            </button>
        </div>
    @endif
</div>
