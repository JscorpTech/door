"use strict";

let walletDirection = $("[dir]").attr("dir");

$(".add-fund-carousel").owlCarousel({
    loop: true,
    dots: true,
    autoplay: false,
    nav: false,
    margin: 20,
    rtl: walletDirection === "rtl",
    items: 1.3,
});

$(document).ready(function () {
    // ---- Filter Dropdown functionality
    const filterDropdown = $(".transaction-filter_dropdown");
    const filterCloseBtn = $("#filterCloseBtn");
    const filterSubmitBtn = $("#filterSubmitBtn");
    const dropdownToggle = $("#transactionFilterBtn");

    filterDropdown.on("click", function (e) {
        e.stopPropagation();
    });

    filterCloseBtn.add(filterSubmitBtn).on("click", function (e) {
        e.stopPropagation();
        dropdownToggle.dropdown("toggle");
    });

    $(document).on("click", ".daterangepicker .drp-buttons", function (e) {
        e.stopPropagation();
    });

    // filter by
    $(".transaction_filter_by label").on("click", function () {
        $(".transaction_filter_by label")
            .removeClass("btn-outline-primary")
            .addClass("btn-outline-secondary");
        $(this)
            .removeClass("btn-outline-secondary")
            .addClass("btn-outline-primary");
    });

    // Date Range Picker Initialization
    $('input[name="transaction_range"]').daterangepicker({
        autoUpdateInput: false,
        locale: {
            format: "DD/MM/YYYY",
        },
    });

    $('input[name="transaction_range"]').on(
        "apply.daterangepicker",
        function (ev, picker) {
            $(this).val(
                picker.startDate.format("DD/MM/YYYY") +
                    " - " +
                    picker.endDate.format("DD/MM/YYYY")
            );
        }
    );

    $('input[name="transaction_range"]').on(
        "cancel.daterangepicker",
        function (ev, picker) {
            $(this).val("");
        }
    );

    // --- Earn By check box functionality
    $(".transaction_earn_by input[type='checkbox']").on("change", function () {
        const checkbox = $(this);
        const link = checkbox.siblings("a");

        if (checkbox.is(":checked")) {
            checkbox.removeClass("border-dark");
            link.addClass("text--primary");
        } else {
            checkbox.addClass("border-dark");
            link.removeClass("text--primary");
        }
    });
    // ---- Filter Dropdown functionality ends
});
