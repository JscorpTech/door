@if(isset($selectedProducts))
    @foreach($selectedProducts as $key=>$product)
        <div class="col-xl-6 remove-selected-clearance-parent">
            <div class="mt-20">
                <div class="right-absolute-btn">
                    <button type="button" class="btn btn-close-circle p-0 remove-selected-clearance-product" data-product-id="{{$product['id']}}">
                        <i class="tio-clear fz-18 lh-1"></i>
                    </button>
                </div>
                <div class="media gap-3 p-3 radius-5 border cursor-pointer justify-content-between align-items-center flex-wrap flex-lg-nowrap"
                     data-id="1">
                    <div class="d-flex align-items-center gap-3">
                        <img width="60" height="60"
                             src="{{ getStorageImages(path:$product->thumbnail_full_url , type: 'backend-basic') }}"
                             class="aspect-1 border rounded border-gray-op" alt="">
                        <div class="media-body d-flex flex-column gap-1">
                            <h6 class="product-id" hidden="">1</h6>
                            <h6 class="title-color fz-13 mb-1 product-name line--limit-1">
                                {{$product['name']}}

                            </h6>
                            <input type="hidden" name="productIds[]" value="{{ $product->id }}">
                            <div class="fz-12 title-color d-flex flex-wrap gx-2 flex-column flex-sm-row">
                                <span class="text-nowrap cat-item border-end">
                                    <span class="opacity--70">
                                        {{ translate('Price') }}:
                                    </span>

                                    <strong>{{setCurrencySymbol(usdToDefaultCurrency(amount: $product['unit_price']))}}</strong>
                                </span>
                                @if($product->product_type == 'physical')
                                    <span class="text-nowrap cat-item border-end">
                                        <span class="opacity--70">
                                            {{ translate('current_stock') }}:
                                        </span>
                                        {{ $product->current_stock }}
                                    </span>
                                @endif
                                <span class="text-nowrap cat-item border-end">
                                    <span class="opacity--70">
                                        {{ translate('Category') }}
                                        :
                                    </span>
                                    {{isset($product->category) ? $product->category->name : translate('category_not_found') }}
                                </span>
                                @if($product->product_type != 'digital')
                                    <span class="text-nowrap cat-item border-end">
                                        <span class="opacity--70"> {{ translate('Brand') }}
                                            :</span>
                                        {{isset($product->brand) ? $product->brand->name : translate('brands_not_found') }}
                                    </span>
                                @endif     
                            </div>
                        </div>
                    </div>
                    @if(isset($clearanceConfig) && $clearanceConfig->discount_type == 'product_wise')
                        <div class="">
                            <label
                                class="form-label title-color font-weight-medium fz-12">{{ translate('Discount Amount') }}
                            </label>
                            <div class="custom-group-btn border max-width-200px">
                                <div class="flex-sm-grow-1">
                                    <input type="number" class="form-control border-0" placeholder="Ex : 10" name="discount_amount[{{ $product->id }}]" required>
                                </div>
                                <div class="flex-shrink-0">
                                    <select name="discount_type[{{ $product->id }}]" id="" class="custom-select ltr border-0">
                                        <option value="percentage">%</option>
                                        <option value="flat">{{ getCurrencySymbol(currencyCode: getCurrencyCode()) }}</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endforeach
@endif
