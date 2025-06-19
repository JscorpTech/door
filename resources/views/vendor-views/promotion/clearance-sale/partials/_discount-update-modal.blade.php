<div class="modal fade" id="discount-update-modal">
    <div class="modal-dialog">
        <div class="modal-content" style="max-height: 80vh; overflow-y: auto;">
            <div class="modal-header">
                <button type="button" class="close p-0 fz-22" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true"><i class="tio-clear"></i></span>
                </button>
            </div>
            <div class="modal-body">
                <form action="{{route('vendor.clearance-sale.update-discount')}}" method="post" class="discount-amount-update">
                    @csrf
                    <input type="hidden" name="product_id">
                    <input type="hidden" name="id">
                    <div class="mb-30">
                        <a href="" class="title-color hover-c1 d-flex align-items-center gap-10">
                            <img src="{{ asset('/public/assets/back-end/img/160x160/img2.jpg') }}"
                                 class="rounded border-gray-op" alt="" width="60">
                            <h6 class="fz-14 font-medium">
                                {{ translate('Family Size Trolley Case Long Lasting and 8 Wheel Waterproof Travel bag') }}

                            </h6>
                        </a>
                        <div class="mt-30">
                            <label class="form-label title-color font-weight-medium fz-14 title-color font-medium">{{ translate('Discount Amount') }}
                                <span id="discount-symbol">(%)</span>
                            </label>
                            <div class="input-group">
                                <input type="number" name="discount_amount" class="form-control" placeholder="Ex : 10" placeholder="">
                                <div class="input-group-append">
                                    <input type="hidden" id="dynamic-currency-symbol" value="{{ getCurrencySymbol(currencyCode: getCurrencyCode()) }}">
                                    <select name="discount_type" id="discount_type" class="form-control js-select2-custom">
                                        <option value="percentage">%</option>
                                        <option value="flat">{{ getCurrencySymbol(currencyCode: getCurrencyCode()) }}</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="btn--container justify-content-end">
                        <button class="btn btn-danger-light font-weight-semibold" data-dismiss="modal"
                                type="reset">{{ translate('Cancel') }}</button>
                        <button class="btn btn--primary font-weight-semibold discount-amount-submit"
                                type="button">{{ translate('Update') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
