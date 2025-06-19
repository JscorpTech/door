@if($web_config['digital_product_setting'] && count($productAuthors) > 0)
    <div class="product-type-digital-section">
        <h6 class="mb-3">
            {{ translate('authors') }}/{{ translate('Creator') }}/{{ translate('Artist') }}
        </h6>
        <div class="products_aside_brands">
            <ul class="common-nav nav flex-column pe-2">
                @foreach($productAuthors as $productAuthor)
                    <li>
                        <div class="flex-between-gap-3 align-items-center">
                            <label class="custom-checkbox">
                                <input type="checkbox" name="author_ids[]" value="{{ $productAuthor['id'] }}" class="real-time-action-update authors_id_class_for_tag_{{ $productAuthor['id'] }}">
                                <span>{{ $productAuthor['name'] }}</span>
                            </label>
                            <span class="badge bg-badge rounded-pill text-dark ms-auto">
                                {{ $productAuthor['digital_product_author_count'] }}
                            </span>
                        </div>
                    </li>
                @endforeach
            </ul>
        </div>

        @if(count($productAuthors) > 10)
            <div class="d-flex justify-content-center">
                <button
                    class="btn-link text-primary btn_products_aside_brands text-capitalize">{{translate('more_authors').'...'}}
                </button>
            </div>
        @endif
    </div>
@endif
