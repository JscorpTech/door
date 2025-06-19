"use strict";

var audio = document.getElementById("myAudio");
function playAudio() {
    audio.play();
}
function pauseAudio() {
    audio.pause();
}
toastr.options = {
    closeButton: false,
    debug: false,
    newestOnTop: false,
    progressBar: false,
    positionClass: "toast-bottom-left",
    preventDuplicates: false,
    onclick: null,
    showDuration: "300",
    hideDuration: "1000",
    timeOut: "5000",
    extendedTimeOut: "1000",
    showEasing: "swing",
    hideEasing: "linear",
    showMethod: "fadeIn",
    hideMethod: "fadeOut",
};
$(document).on("ready", function () {
    $(".view--more").each(function () {
        const viewItem = $(this);
        const initialHeight = $(this).height();
        if (viewItem.height() > 130) {
            viewItem.addClass("view-more-collapsable");
            const btn = viewItem.find(".expandable-btn");
            btn.removeClass("d-none");
            btn.on("click", function () {
                if (btn.find(".more").hasClass("d-none")) {
                    viewItem.css("height", "130px");
                    btn.find(".more").removeClass("d-none");
                    btn.find(".less").addClass("d-none");
                } else {
                    viewItem.css("height", initialHeight + 40);
                    btn.find(".less").removeClass("d-none");
                    btn.find(".more").addClass("d-none");
                }
            });
        }
    });

    $("img.svg").each(function () {
        let $img = jQuery(this);
        let imgID = $img.attr("id");
        let imgClass = $img.attr("class");
        let imgURL = $img.attr("src");

        jQuery.get(
            imgURL,
            function (data) {
                let $svg = jQuery(data).find("svg");
                if (typeof imgID !== "undefined") {
                    $svg = $svg.attr("id", imgID);
                }
                if (typeof imgClass !== "undefined") {
                    $svg = $svg.attr("class", imgClass + " replaced-svg");
                }

                $svg = $svg.removeAttr("xmlns:a");
                if (
                    !$svg.attr("viewBox") &&
                    $svg.attr("height") &&
                    $svg.attr("width")
                ) {
                    $svg.attr(
                        "viewBox",
                        "0 0 " + $svg.attr("height") + " " + $svg.attr("width")
                    );
                }

                $img.replaceWith($svg);
            },
            "xml"
        );
    });

    if (window.localStorage.getItem("hs-builder-popover") === null) {
        $("#builderPopover")
            .popover("show")
            .on("shown.bs.popover", function () {
                $(".popover").last().addClass("popover-dark");
            });

        $(document).on("click", "#closeBuilderPopover", function () {
            window.localStorage.setItem("hs-builder-popover", true);
            $("#builderPopover").popover("dispose");
        });
    } else {
        $("#builderPopover").on("show.bs.popover", function () {
            return false;
        });
    }
    $(".js-navbar-vertical-aside-toggle-invoker").click(function () {
        $(".js-navbar-vertical-aside-toggle-invoker i").tooltip("hide");
    });
    let sidebar = $(".js-navbar-vertical-aside").hsSideNav();

    $(".js-nav-tooltip-link").tooltip({ boundary: "window" });

    $(".js-nav-tooltip-link").on("show.bs.tooltip", function () {
        if (!$("body").hasClass("navbar-vertical-aside-mini-mode")) {
            return false;
        }
    });
    $(".js-hs-unfold-invoker").each(function () {
        let unfold = new HSUnfold($(this)).init();
    });

    $(".js-form-search").each(function () {
        new HSFormSearch($(this)).init();
    });

    $(".js-select2-custom").each(function () {
        let select2 = $.HSCore.components.HSSelect2.init($(this));
    });

    $(".js-daterangepicker").daterangepicker();

    $(".js-daterangepicker-times-sec").daterangepicker({
        timePicker: true,
        timePickerSeconds: true,
        timePicker24Hour: false,
        locale: {
            format: "MM/DD/YYYY hh:mm:ss A",
        },
    });

    $(".js-date-range-picker-only-times").daterangepicker({
        timePicker: true,
        timePickerSeconds: true,
        timePicker24Hour: false,
        showDropdowns: false,
        locale: {
            format: "hh:mm:ss A",
        },
    });

    $(".js-daterangepicker-times").daterangepicker({
        timePicker: true,
        startDate: moment().startOf("hour"),
        endDate: moment().startOf("hour").add(32, "hour"),
        locale: {
            format: "M/DD hh:mm A",
        },
    });
    let start = moment();
    let end = moment();
    function cb(start, end) {
        $(
            "#js-daterangepicker-predefined .js-daterangepicker-predefined-preview"
        ).html(start.format("MMM D") + " - " + end.format("MMM D, YYYY"));
    }
    $("#js-daterangepicker-predefined").daterangepicker(
        {
            startDate: start,
            endDate: end,
            ranges: {
                Today: [moment(), moment()],
                Yesterday: [
                    moment().subtract(1, "days"),
                    moment().subtract(1, "days"),
                ],
                "Last 7 Days": [moment().subtract(6, "days"), moment()],
                "Last 30 Days": [moment().subtract(29, "days"), moment()],
                "This Month": [
                    moment().startOf("month"),
                    moment().endOf("month"),
                ],
                "Last Month": [
                    moment().subtract(1, "month").startOf("month"),
                    moment().subtract(1, "month").endOf("month"),
                ],
            },
        },
        cb
    );
    $(".js-daterangepicker-with-range").daterangepicker({
        timePicker: false,
        startDate: start,
        endDate: end,
        autoUpdateInput: false,
        ranges: {
            Today: [moment(), moment()],
            Yesterday: [
                moment().subtract(1, "days"),
                moment().subtract(1, "days"),
            ],
            "Last 7 Days": [moment().subtract(6, "days"), moment()],
            "Last 30 Days": [moment().subtract(29, "days"), moment()],
            "This Month": [moment().startOf("month"), moment().endOf("month")],
            "Last Month": [
                moment().subtract(1, "month").startOf("month"),
                moment().subtract(1, "month").endOf("month"),
            ],
        },
        alwaysShowCalendars: true,
    });

    $(".js-daterangepicker-with-range").on(
        "apply.daterangepicker",
        function (ev, picker) {
            $(this).removeAttr("readonly");
            $(this).removeClass("cursor-pointer");
            $(this).val(
                picker.startDate.format("MM/DD/YYYY") +
                    " - " +
                    picker.endDate.format("MM/DD/YYYY")
            );
        }
    );

    $(".js-clipboard").each(function () {
        let clipboard = $.HSCore.components.HSClipboard.init(this);
    });
    $(".table-responsive .dropdown-toggle").on("click", function (e) {
        e.stopPropagation();
        $(this)
            .closest(".table-responsive")
            .find(".dropdown-menu")
            .removeClass("show");
        $(this).siblings(".dropdown-menu").toggleClass("show");
    });

    $(".js-daterangepicker-time-only").daterangepicker(
        {
            timePicker: true,
            timePickerSeconds: true,
            timePicker24Hour: false,
            locale: {
                format: "hh:mm:ss A",
            },
            opens: "center",
        },
        function (start, end) {
            updateTimeRange(start, end);
        }
    );
    $(".js-daterangepicker-time-only").on("show.daterangepicker", function () {
        const picker = $(this).data("daterangepicker");
        if (picker) {
            picker.container.find(".calendar-table").hide();
        }
    });
    function updateTimeRange(start, end) {
        $(".js-daterangepicker-time-only").val(
            start.format("hh:mm:ss A") + " - " + end.format("hh:mm:ss A")
        );
    }
});

function getRndInteger() {
    return Math.floor(Math.random() * 90000) + 100000;
}
let errorMessages = {
    valueMissing: $(".please_fill_out_this_field").data("text"),
};
$("input").each(function () {
    let $el = $(this);

    $el.on("invalid", function (event) {
        let target = event.target,
            validity = target.validity;
        target.setCustomValidity("");
        if (!validity.valid) {
            if (validity.valueMissing) {
                target.setCustomValidity(
                    $el.data("errorRequired") || errorMessages.valueMissing
                );
            }
        }
    });
});

$(document).ready(function () {
    try {
        let vendorSelect = $(".multiple-tags-with-image .multiple-select2"); // Select2 dropdown
        let placeholderTypeShopNameText = $('#type-shop-name-text').data('text') ?? "Type shop name";

        // Initialize select2
        vendorSelect.select2({
            tags: true,
            maximumSelectionLength: false,
            placeholder: placeholderTypeShopNameText,
            templateResult: formatOption,
            templateSelection: formatOption,
            allowClear: false,
            closeOnSelect: false,
        });

        // Format dropdown options with image
        function formatOption(option) {
            if (!option.id) return option.text;
            const imgSrc = $(option.element).data("image");
            if (imgSrc) {
                return $(`<span><img src="${imgSrc}" class="rounded-circle" style="width: 20px; height: 20px; margin-right: 5px;"> ${option.text}</span>`);
            }
            return option.text;
        }

        // Add selected vendor to `selectedVendorObj`
        vendorSelect.on("select2:select", function (e) {
            const data = e.params.data;
            const imgSrc = $(this).find(`option[value="${data.id}"]`).data("image");

            // Prevent duplicates
            if (!selectedVendorObj.some(vendor => vendor.id == data.id)) {
                selectedVendorObj.push({
                    id: data.id,
                    name: data.text,
                    img_src: imgSrc,
                });
            }

            console.log("Selected Vendor Added:", selectedVendorObj);
            renderSelectionOrderObjHtml(selectedVendorObj)
        });

        // Remove unselected vendor from `selectedVendorObj`
        vendorSelect.on("select2:unselect", function (e) {
            const data = e.params.data;
            selectedVendorObj = selectedVendorObj.filter(vendor => vendor.id != data.id);
            console.log("Selected Vendor Removed:", selectedVendorObj);
            renderSelectionOrderObjHtml(selectedVendorObj)
        });

        $(document).on("click", ".show-tags .close-icon", function (e) {
            e.stopPropagation();
            var itemId = $(this).data("id");
            var selectedValues = vendorSelect.val();
            if (selectedValues) {
                selectedValues = selectedValues.filter((id) => id != itemId);
                vendorSelect.val(selectedValues).trigger("change");

                selectedVendorObj = selectedVendorObj.filter(vendor => vendor.id != itemId);
                console.log("Selected Vendor Removed:", selectedVendorObj);
                renderSelectionOrderObjHtml(selectedVendorObj)
            }
        });

        function renderSelectionOrderObjHtml(selectedVendorObj) {
            var htmlForSelectionOrderObj = "";
            selectedVendorObj.forEach(function (item) {
                console.log(item)
                htmlForSelectionOrderObj += `<li class="name d-flex gap-2">
                        <span><img class="rounded-circle tag-image-20px" src="${item.img_src}" alt="" /> ${item.name}</span>
                        <span class="close-icon" data-id="${item.id}"><i class="tio-clear cursor-pointer"></i></span>
                        <input value="${item.id}" name="vendor_priorities_id[]" class="d-none" />
                     </li>`;
            });
            $(".multiple-tags-with-image .show-tags").html(htmlForSelectionOrderObj);
        }
    } catch (e) {

    }
});
// ----- multiple select2 with image ends
