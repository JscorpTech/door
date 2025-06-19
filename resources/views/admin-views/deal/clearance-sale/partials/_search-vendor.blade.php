@if(count($allVendorList) > 0)
    @foreach($allVendorList as $vendor)
        <div class="col-lg-12 select-clearance-vendor-item">
                <div class="mt-20">
                    <div
                        class="media gap-3 p-3 radius-5 cursor-pointer justify-content-between align-items-center flex-wrap flex-xl-nowrap"
                        data-id="1">
                        <div class="d-flex align-items-center gap-3">
                            <img class="avatar avatar-xl border" width="75"
                                 src="{{ getStorageImages(path:$vendor?->shop?->image_full_url , type: 'backend-basic') }}"
                                 class="rounded border-gray-op" alt="">
                            <div class="media-body d-flex flex-column gap-1">
                                <input type="hidden" id="shop-id{{ $vendor['id'] }}" value="{{ $vendor?->shop?->id }}">
                                <h6 class="title-color fz-13 mb-1 product-name line--limit-1">
                                    {{$vendor?->shop?->name}}
                                </h6>
                                <div class="fz-12 title-color">
                                    <div class="border-between wrap">
                                         <span class="parent">
                                             <span class="opacity--70">({{$vendor->review_count}} {{ translate('review') }})</span>
                                         </span>
                                        <span class="parent">
                                             <span class="opacity--70"><i class="tio-star text-F5A200"></i>{{round($vendor->average_rating,1)}}</span>
                                        </span>
                                    </div>
                                    <div class="mt-2">
                                        <span class="parent">
                                            <span class="opacity--70">{{ translate('total_products_in_clearance_offer') }}:</span>
                                            <strong>{{$vendor->products_count}}</strong>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
        </div>
    @endforeach
@else
    <div class="text-center p-4">
        <img class="mb-3 w-60px" src="{{dynamicAsset(path: 'public/assets/back-end/img/empty-vendor.png')}}"
             alt="{{translate('image_description')}}">
        <p class="mb-0">{{ translate('no_vendor_found')}}</p>
    </div>
@endif
