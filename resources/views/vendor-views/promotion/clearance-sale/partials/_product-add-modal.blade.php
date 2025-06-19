<div class="modal fade" id="product-add-modal">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-body">
                <div class="border-bottom">
                    <h4>{{ translate('Add_Product') }}</h4>
                    <p>
                        {{ translate('search_product_and_add_to_your_clearance_list') }}
                    </p>
                </div>
                <form action="{{route('vendor.clearance-sale.add-product')}}" method="post" class="clearance-add-product">
                    @csrf
                    <div class="mt-3">
                        <label class="form-label">{{ translate('Products') }}</label>
                        <div class="dropdown select-clearance-product-search w-100">
                            <div class="search-form" data-toggle="dropdown" aria-expanded="false">
                                <input type="text" class="form-control pl-5 search-vendor-product-for-clearance-sale" placeholder="{{ translate('Search_Product') }}" multiple>
                                <span
                                    class="tio-search position-absolute left-0 top-0 h-42px d-flex align-items-center pl-2"></span>
                            </div>
                            <div class="dropdown-menu w-100 px-2">
                                <div class="d-flex flex-column max-h-300 overflow-y-auto overflow-x-hidden search-result-box">
                                    @include('vendor-views.promotion.clearance-sale.partials._search-product', ['products' => $products])
                                </div>
                            </div>
                        </div>
                        <div class="selected-products d-flex flex-wrap g-3 mt-3 clearance-selected-products" id="selected-products">
                            @include('admin-views.partials._select-product')
                        </div>
                    </div>
                    <div class="p-4 bg-chat rounded text-center mt-3 search-and-add-product">
                        <img src="{{ dynamicAsset('public/assets/back-end/img/empty-product.png') }}" width="64"
                             alt="">
                        <div class="mx-auto my-3 max-w-353px">
                            {{ translate('search_and_add_product_from_the_list') }}
                        </div>
                    </div>
                    <div class="btn--container justify-content-end mt-3">
                        <button class="btn btn-secondary font-weight-semibold" data-dismiss="modal"
                                type="reset">{{ translate('Cancel') }}</button>
                        <button class="btn btn--primary font-weight-semibold clearance-product-add-submit" id="add-products-btn"
                                type="button">{{ translate('Add_Products') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
