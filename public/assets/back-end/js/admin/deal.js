'use strict';
$(".lang-link").click(function (e) {
    e.preventDefault();
    $('.lang-link').removeClass('active');
    $(".lang-form").addClass('d-none');
    $(this).addClass('active');
    let formId = this.id;
    let lang = formId.split("-")[0];
    $("#" + lang + "-form").removeClass('d-none');
});

let clearanceSaleDiscountElement = $('.clearance-sale-discount');

clearanceSaleDiscountElement.on('change', function () {
    if ($(this).val() === 'flat') {
        $('.clearance-sale-discount-flat').show();
    } else {
        $('.clearance-sale-discount-flat').hide();
    }
})

let offerActiveTimeElement = $('.offer-active-time');

offerActiveTimeElement.on('change', function () {
    if ($(this).val() === 'always') {
        $('.offer-active-time-section').hide();
    } else {
        $('.offer-active-time-section').show();
    }
})
