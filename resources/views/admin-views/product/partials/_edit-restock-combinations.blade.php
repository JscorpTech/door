@if(count($combinations) > 0)
    <table class="table physical_product_show table-borderless">
        <thead class="text-capitalize">
            <tr>
                <th class="py-2">
                    <label for="" class="control-label">
                        {{ translate('variations') }}
                    </label>
                </th>
                <th class="py-2">
                    <label for="" class="control-label">
                        {{ translate('Stock') }}
                    </label>
                </th>
            </tr>
        </thead>
        <tbody>

        @php
            $serial = 1;
        @endphp

        @foreach ($combinations as $key => $combination)
            <tr>
                <td>
                    <label for="" class="control-label">
                        {{ $combination['type'] }}
                        @if(isset($restockVariants) && in_array($combination['type'], $restockVariants))
                            <span class="input-required-icon">*</span>
                        @endif
                    </label>
                    <input value="{{ $combination['type'] }}" name="type[]" class="d-none">
                </td>
                    <input type="hidden" name="price_{{ $combination['type'] }}"
                           value="{{ usdToDefaultCurrency(amount: $combination['price']) }}" min="0"
                           step="0.01"
                           class="form-control" required placeholder="{{ translate('ex').': 100'}}">
                    <input type="hidden" name="sku_{{ $combination['type'] }}" value="{{ $combination['sku'] }}"
                           class="form-control store-keeping-unit" required>
                <td>
                    <input type="number" name="qty_{{ $combination['type'] }}"
                           value="{{ $combination['qty'] }}" min="0" max="100000" step="1"
                           class="form-control" placeholder="{{ translate('ex') }}: {{ translate('5') }}"
                           required>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
@endif
