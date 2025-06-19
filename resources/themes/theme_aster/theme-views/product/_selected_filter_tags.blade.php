@if (
    $tags_category != null ||
    $tags_brands != null ||
    $selectedRatings != null ||
    (isset($sort_by) && $sort_by != null && $sort_by != 'latest') ||
    isset($publishingHouse) && count($publishingHouse) > 0 ||
    isset($productAuthors) && count($productAuthors) > 0
    )

<span class="text-nowrap">{{ translate('Applied_Filters') }}:</span>
<div class="position-relative applied-filer_wrapper d-grid ps-4">
        <ul class="applied-filer list-unstyled d-flex gap-3 mb-0 p-0 px-4">

            @isset($sort_by)
                @if ($sort_by != 'latest')
                    <li class="bg-white text--primary border-0 rounded-16px px-3 py-1 text-nowrap fw-semibold remove_tags_sortBy">
                        <span class="fs-12">{{ ucwords(translate($sort_by)) }}</span>
                        <button type="button" class="btn text--primary p-0 m-0 fs-18 border-0 d-inline"><i class="bi bi-x"></i></button>
                    </li>
                @endif
            @endisset

            @isset($tags_category)
                @foreach ($tags_category as $item)
                    <li class="bg-white text--primary border-0 rounded-16px px-3 py-1 text-nowrap fw-semibold remove_tags_Category" data-id="{{ $item->id }}">
                        <span class="fs-12">{{ Str::limit($item->name, 20, '...') }}</span>
                        <button type="button" class="btn text--primary p-0 m-0 fs-18 border-0 d-inline"><i class="bi bi-x"></i></button>
                    </li>
                @endforeach
            @endisset

            @isset($tags_brands)
                @foreach ($tags_brands as $item)
                    <li class="bg-white text--primary border-0 rounded-16px px-3 py-1 text-nowrap fw-semibold remove_tags_Brand" data-id="{{ $item->id }}">
                        <span class="fs-12">{{ Str::limit($item->name, 20, '...') }}</span>
                        <button type="button" class="btn text--primary p-0 m-0 fs-18 border-0 d-inline"><i class="bi bi-x"></i></button>
                    </li>
                @endforeach
            @endisset

            @isset($publishingHouse)
                @foreach ($publishingHouse as $item)
                    <li class="bg-white text--primary border-0 rounded-16px px-3 py-1 text-nowrap fw-semibold remove_tags_publishing_house" data-id="{{ $item->id }}">
                        <span class="fs-12">{{ Str::limit($item->name, 20, '...') }}</span>
                        <button type="button" class="btn text--primary p-0 m-0 fs-18 border-0 d-inline"><i class="bi bi-x"></i></button>
                    </li>
                @endforeach
            @endisset

            @isset($productAuthors)
                @foreach ($productAuthors as $item)
                    <li class="bg-white text--primary border-0 rounded-16px px-3 py-1 text-nowrap fw-semibold remove_tags_author_id" data-id="{{ $item->id }}">
                        <span class="fs-12">{{ Str::limit($item->name, 20, '...') }}</span>
                        <button type="button" class="btn text--primary p-0 m-0 fs-18 border-0 d-inline"><i class="bi bi-x"></i></button>
                    </li>
                @endforeach
            @endisset

            @isset($selectedRatings)
                @foreach ($selectedRatings as $item)
                    <li class="bg-white text--primary border-0 rounded-16px px-3 py-1 text-nowrap fw-semibold remove_tags_review" data-id="{{ $item }}">
                        <span class="fs-12"><i class="bi bi-star-fill"></i> {{ $item }}</span>
                        <button type="button" class="btn text--primary p-0 m-0 fs-18 border-0 d-inline"><i class="bi bi-x"></i></button>
                    </li>
                @endforeach
            @endisset
        </ul>
        <button type="button" class="appliedFilterPrevBtn btn rounded-circle aspect-1 filter-top-nav_prev-btn">
            <i class="absolute-white bi bi-chevron-left"></i>
        </button>
        <button type="button" class="appliedFilterNextBtn btn rounded-circle aspect-1 filter-top-nav_next-btn">
            <i class="absolute-white bi bi-chevron-right"></i>
        </button>
</div>
@endif
