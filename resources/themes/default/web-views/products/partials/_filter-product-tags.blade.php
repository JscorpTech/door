@if(!empty($productTags) && count($productTags) > 0)
    <div class="product-tags-section">
        <div class="__p-20px rounded __shadow">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h6 class="font-bold">{{translate('Tags')}}</h6>
            </div>

            <div class="products_aside_tags">
                <ul class="common-nav nav flex-column">
                    @foreach($productTags as $tag)
                        <li>
                            <div>
                                <label class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input real-time-action-update" name="tag_ids[]" value="{{ $tag['id'] }}" id="tag_{{ $tag['id'] }}">
                                    <span class="custom-control-label">
                                        <span class="__text-14px">{{ $tag['tag'] }}</span>
                                    </span>
                                    <span class="__text-14px">({{ $tag['items_count'] ?? 0 }})</span>
                                </label>
                            </div>
                        </li>
                    @endforeach
                </ul>
            </div>

            @if(count($productTags) > 10)
                <div class="d-flex justify-content-center">
                    <button class="btn-link text-primary btn_products_aside_tags text-capitalize">
                        {{translate('more_tags').'...'}}
                    </button>
                </div>
            @endif
        </div>
    </div>
@endif
