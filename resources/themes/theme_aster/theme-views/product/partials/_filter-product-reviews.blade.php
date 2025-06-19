<div>
    <h6 class="mb-3">{{ translate('Ratings') }}</h6>

    <ul class="common-nav nav flex-column pe-2">
        @foreach([5, 4, 3, 2, 1] as $productRating)
            @if (isset($productRatings['rating_'.$productRating]))
                <li class="overflow-hidden w-100">
                    <div class="flex-between-gap-3 align-items-center">
                        <div class="custom-checkbox w-75">
                            <label class="d-flex gap-2 align-items-center">
                                <input type="checkbox" name="rating[]" value="{{ $productRating }}"
                                       class="real-time-action-update review_class_for_tag_{{ $productRating }}">
                                <span class="star-rating text-gold">
                                    @for($i = 0; $i < 5; $i++)
                                        <i class="bi {{ $i < $productRating ? 'bi-star-fill' : 'bi-star' }}"></i>
                                    @endfor
                                </span>
                            </label>
                        </div>
                        <span class="badge bg-badge rounded-pill text-dark ms-auto">
                            {{ $productRatings['rating_'.$productRating] }}
                        </span>
                    </div>
                </li>
            @endif
        @endforeach
    </ul>
</div>
