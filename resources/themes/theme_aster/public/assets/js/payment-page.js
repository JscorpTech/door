"use strict";
$("#digital-payment-btn").on("click", function () {
    $(".digital-payment").slideToggle("slow");
});

$("#pay-offline-method").on("change", function () {
    payOfflineMethodField(this.value);
});
function payOfflineMethodField(methodId) {
    $.get(
        $(".get-payment-method-list").data("action"),
        { method_id: methodId },
        (response) => {
            $("#method-filed-div").html(response.methodHtml);
        }
    );
}

$(".checkout-wallet-payment-form").on("submit", function (event) {
    setTimeout(() => {
        $(".update_wallet_cart_button")
            .attr("type", "button")
            .addClass("disabled");
    }, 100);
});

$(".checkout-payment-form").on("submit", function (event) {
    event.preventDefault();

    $.ajaxSetup({
        headers: {
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
        },
    });
    $.ajax({
        url: $(this).attr("action"),
        method: "GET",
        data: $(this).serialize(),
        beforeSend: function () {
            $("#loading").addClass("d-grid");
        },
        success: function (response) {
            if (response?.status == 0 && response?.message) {
                toastr.error(response.message);
            }
            if (response?.redirect && response?.redirect != "") {
                location.href = response?.redirect;
            }
        },
        complete: function () {
            $("#loading").removeClass("d-grid");
        },
        error: function () {},
    });
});

$("#bring_change_amount").on("shown.bs.collapse", function () {
    $("#bring_change_amount_btn").text($(this).data("less")).addClass("pb-2");
    $(this).closest(".payment-method").addClass("with-transition");
});

$("#bring_change_amount").on("hidden.bs.collapse", function () {
    $("#bring_change_amount_btn")
        .text($(this).data("more"))
        .removeClass("pb-2");
    $(this).closest(".payment-method").removeClass("with-transition");
});

// Payment method selection
$(".payment-method_parent").on("click", function (e) {
    e.preventDefault();

    $(".payment-method-list-page")
        .find("input[type='radio']")
        .prop("checked", false);
    $(this)
        .closest(".payment-method-form")
        .find("input[name='payment_method']")
        .prop("checked", true);

    $(".payment-method_parent").removeClass("border-selected");
    $(this).addClass("border-selected");

    if (
        $("#digital-payment-btn .payment-method_parent").hasClass(
            "border-selected"
        )
    ) {
        $(".digital-payment-card").removeClass("border-selected");
        $(this).addClass("border-selected");
    } else {
        $(".digital-payment-card").removeClass("border-selected");
        $(".digital-payment").hide("slow");
    }
});

$(".digital-payment-card").on("click", function (e) {
    e.preventDefault();

    $(".payment-method-list-page")
        .find("input[type='radio']")
        .prop("checked", false);
    $(this)
        .closest(".payment-method-form")
        .find("input[name='payment_method']")
        .prop("checked", true);

    if (
        $("#digital-payment-btn .payment-method_parent").hasClass(
            "border-selected"
        )
    ) {
        $(".digital-payment-card").removeClass("border-selected");
        $(this).addClass("border-selected");
    } else {
        $(".digital-payment-card").removeClass("border-selected");
    }
});

// proceed to next btn enable
$(".payment-method").on("click", function () {
    if ($(this).hasClass("next-btn-enable")) {
        $("#proceed-to-payment-action")
            .removeClass("custom-disabled")
            .removeAttr("disabled");
    } else {
        $("#proceed-to-payment-action")
            .addClass("custom-disabled")
            .attr("disabled", true);
    }
});

// Attach event listeners to the collapse
$("#bring_change_amount").on("shown.bs.collapse", function () {
    $(document).on("click.outsideCollapse", function (e) {
        if ($(e.target).is("#bring_change_amount_btn") && $("#bring_change_amount").hasClass("show")) {
            $("#bring_change_amount").collapse("hide");
        }
    });
});


$("#bring_change_amount").on("hidden.bs.collapse", function () {
    // Remove the document click listener when the collapse is hidden
    $(document).off("click.outsideCollapse");
});

$("#proceed-to-payment-action").on("click", function () {
    let getType = $(this).data("type");
    if (getType && getType.toString() === "checkout-payment") {
        let formId = $(".payment-method-list-page")
            .find('input[type="radio"]:checked')
            .data("form");
        if (formId !== "") {
            $(this).attr("disabled", true).addClass("custom-disabled");
            $(formId).submit();
        }
    }
});
