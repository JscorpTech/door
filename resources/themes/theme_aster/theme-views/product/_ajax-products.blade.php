@if (count($products) > 0)
    <div class="auto-col gap-3 mobile_two_items minWidth-12rem {{(session()->get('product_view_style') == 'list-view'?'product-list-view':'')}}" id="filtered-products" style="{{(count($products) > 4?'--maxWidth:1fr':'--maxWidth:14rem')}}">
        @foreach($products as $product)
            @include('theme-views.partials._product-small-card', ['product' => $product])
        @endforeach
    </div>
@else
    <div class="d-flex flex-column justify-content-center align-items-center gap-2 py-5 my-5 w-100">
        <img width="80" class="mb-3" src="{{ theme_asset('assets/img/empty-state/empty-product.svg') }}" alt="">
        <h5 class="text-center text-muted">
            {{ translate('There_is_no_product') }}!
        </h5>
    </div>
@endif


<div class="my-4" id="paginator-ajax">
    @if(count($products) > 0 && ceil(($products->total() / ($singlePageProductCount ?? 20))) != 1)
        <ul class="pagination">

        <li class="page-item {{ $page <= 1 ? 'disabled' : ''}}">
            <label class="m-0">
                <input type="radio" name="page" value="{{ $page - 1 }}" class="real-time-action-update">
                <span class="align-items-center aspect-1 d-flex fs-12 page-link" aria-hidden="true">
                    <i class="bi bi-caret-left-fill"></i>
                </span>
            </label>
        </li>

        @php
            $totalPages = ceil($products->total() / $singlePageProductCount);
            $windowSize = 1;
        @endphp

        @for ($i = 1; $i <= min(2, $totalPages); $i++)
            <li class="page-item {{ $page == $i ? 'active' : '' }}">
                <label class="m-0">
                    <input type="radio" name="page" value="{{ $i }}" {{ $page == $i ? 'checked' : '' }} class="real-time-action-update">
                    <span class="page-link {{ $page == $i ? 'active' : '' }}">{{ $i }}</span>
                </label>
            </li>
        @endfor

        @if ($totalPages > 3 && $page > 2)
            <li class="page-item d-flex align-items-center disabled user-select-none">
                <label class="m-0">
                    <span class="page-link aspect-1">...</span>
                </label>
            </li>
        @endif

        @for ($i = max(3, $page - $windowSize); $i <= min($totalPages - 2, $page + $windowSize); $i++)
            <li class="page-item {{ $page == $i ? 'active' : '' }}">
                <label class="m-0">
                    <input type="radio" name="page" value="{{ $i }}" {{ $page == $i ? 'checked' : '' }} class="real-time-action-update">
                    <span class="page-link {{ $page == $i ? 'active' : '' }}">{{ $i }}</span>
                </label>
            </li>
        @endfor

        @if ($page < $totalPages - 2)
            <li class="page-item d-flex align-items-center disabled user-select-none">
                <label class="m-0">
                    <span class="page-link aspect-1">...</span>
                </label>
            </li>
        @endif

        @for ($i = max($totalPages - 1, 1); $i <= $totalPages; $i++)
            @if ($i > 2)
                <li class="page-item {{ $page == $i ? 'active' : '' }}">
                    <label class="m-0">
                        <input type="radio" name="page" value="{{ $i }}" {{ $page == $i ? 'checked' : '' }} class="real-time-action-update">
                        <span class="page-link {{ $page == $i ? 'active' : '' }}">{{ $i }}</span>
                    </label>
                </li>
            @endif
        @endfor

        <li class="page-item {{ $totalPages <= $page ? 'disabled' : ''}}">
            <label class="m-0">
                <input type="radio" name="page" value="{{ $page + 1 }}" class="real-time-action-update">
                <span class="align-items-center aspect-1 d-flex fs-12 page-link" aria-hidden="true">
                    <i class="bi bi-caret-right-fill"></i>
                </span>
            </label>
        </li>

    </ul>
    @endif
</div>

