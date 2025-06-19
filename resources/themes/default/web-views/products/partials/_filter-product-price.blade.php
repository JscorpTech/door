<div>
    <h6 class="font-semibold fs-13 mb-1">{{ translate('price') }}</h6>
    <div>
        <div class="d-flex align-items-end gap-2 mt-1">
            <div class="form-group mb-0">
                <label for="min_price" class="mb-1 fs-12 fw-semibold">{{ translate('Min') }}</label>
                <input type="number" id="min_price" name="min_price" class="form-control form-control--sm bg-black-09"
                       placeholder="{{ session('currency_symbol') }}{{ '0' }}">
            </div>
            <div class="mb-2">-</div>
            <div class="form-group mb-0">
                <label for="max_price" class="mb-1 fs-12 fw-semibold">{{ translate('Max') }}</label>
                <input type="number" id="max_price" name="max_price" class="form-control form-control--sm bg-black-09"
                       placeholder="{{ session('currency_symbol') }}{{ getProductMaxUnitPriceRange(type: 'web') }}">
            </div>
            <button class="btn btn--primary py-1 px-2 fs-13 action-search-products-by-price" id="">
                <i class="text-absolute-white czi-arrow-{{ session('direction') === "rtl" ? 'left' : 'right' }}"></i>
            </button>
        </div>

        <div id="price_range_slider" class="my-3 rounded-10"
             data-max-value="{{ getProductMaxUnitPriceRange(type: 'web') }}" data-min-value="0"
        >
            <div class="slider-range"></div>
            <div class="slider-thumb" id="thumb_min"></div>
            <div class="slider-thumb" id="thumb_max"></div>
        </div>
    </div>
</div>

@push('script')
    <script>
        $(function () {
            let slider = $("#price_range_slider");
            let minThumb = $("#thumb_min");
            let maxThumb = $("#thumb_max");
            let range = $(".slider-range");
            let minInput = $("#min_price");
            let maxInput = $("#max_price");

            let sliderMin = slider?.data('min-value') ?? 0;
            let sliderMax = slider?.data('max-value') ?? 100000000;

            let minValue = sliderMin;
            let maxValue = sliderMax;

            let isRtl = $('html').attr('dir') === 'rtl';

            function updateSlider() {
                let sliderWidth = slider.width();

                let minLeft = (((minValue - sliderMin) / (sliderMax - sliderMin)) * sliderWidth);
                let maxLeft = ((maxValue - sliderMin) / (sliderMax - sliderMin)) * sliderWidth;

                if (isRtl) {
                    minLeft = sliderWidth - minLeft;
                    maxLeft = sliderWidth - maxLeft;
                }

                minThumb.css(isRtl ? "insetInlineEnd" : "insetInlineStart", minLeft + "px");
                maxThumb.css(isRtl ? "insetInlineEnd" : "insetInlineStart", maxLeft + "px");

                range.css({
                    [isRtl ? 'insetInlineEnd' : 'insetInlineStart']: Math.min(minLeft, maxLeft) + "px",
                    width: Math.abs(maxLeft - minLeft) + "px",
                });

                minInput.val(minValue !== null ? minValue : minInput.attr('placeholder'));
                maxInput.val(maxValue !== null ? maxValue : maxInput.attr('placeholder'));

                let distance = maxValue - minValue;
                $('#slider_distance').text("$" + distance.toLocaleString());
            }

            function clamp(value, min, max) {
                return Math.min(Math.max(value, min), max);
            }

            function handleDrag(thumb, isMinThumb) {
                thumb.on("mousedown", function (e) {
                    let startX = e.pageX;
                    let startValue = isMinThumb ? minValue : maxValue;
                    let sliderWidth = slider.width();

                    $(document).on("mousemove.slider", function (e) {
                        let deltaX = isRtl ? (startX - e.pageX) : (e.pageX - startX);
                        let valueChange = (deltaX / sliderWidth) * (sliderMax - sliderMin);
                        let newValue = clamp(startValue + valueChange, sliderMin, sliderMax);

                        newValue = Math.round(newValue);

                        if (isMinThumb) {
                            minValue = Math.min(newValue, maxValue || sliderMax);
                        } else {
                            maxValue = Math.max(newValue, minValue || sliderMin);
                        }

                        updateSlider();
                    });

                    $(document).on("mouseup.slider", function () {
                        $(document).off(".slider");
                    });
                });
            }

            minInput.on("input", function () {
                let inputValue = parseInt($(this).val(), 10);
                if (!isNaN(inputValue)) {
                    minValue = clamp(inputValue, sliderMin, maxValue || sliderMax);
                } else {
                    minValue = null;
                }
                updateSlider();
            });

            maxInput.on("input", function () {
                let inputValue = parseInt($(this).val(), 10);
                if (!isNaN(inputValue)) {
                    maxValue = clamp(inputValue, minValue || sliderMin, sliderMax);
                } else {
                    maxValue = null;
                }
                updateSlider();
            });

            handleDrag(minThumb, true);
            handleDrag(maxThumb, false);

            updateSlider();
        });
    </script>

@endpush
