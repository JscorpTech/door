@if(!empty($productTags) && count($productTags) > 0)
    <div class="product-tags-section">
        <h6 class="mb-3">{{translate('Tags')}}</h6>
        <div class="products_aside_tags">
            <ul class="common-nav nav flex-column pe-2">
                @foreach($productTags as $tag)
                    <li class="overflow-hidden w-100">
                        <div class="flex-between-gap-3 align-items-center">
                            <div class="custom-checkbox w-75">
                                <label class="d-flex gap-2 align-items-center">
                                    <input type="checkbox" name="tag_ids[]" value="{{ $tag['id'] }}" class="real-time-action-update tag_class_for_filter_{{ $tag['id'] }}">
                                    <span class="text-truncate">{{ $tag['tag'] }}</span>
                                </label>
                            </div>
                            <span class="badge bg-badge rounded-pill text-dark ms-auto">{{ $tag['items_count'] ?? 0 }}</span>
                        </div>
                    </li>
                @endforeach
            </ul>
        </div>

        @if(count($productTags) > 10)
            <div class="d-flex justify-content-center">
                <button
                    class="btn-link text-primary btn_products_aside_tags text-capitalize">{{translate('more_tags').'...'}}
                </button>
            </div>
        @endif
    </div>
@endif
