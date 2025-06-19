"use strict";

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

$('.search-vendor-product-for-clearance-sale').on('keyup', function () {
    let name = $(this).val();
    $.get($('#get-search-vendor-product-for-clearnace-route').data('action'), {searchValue: name}, (response) => {
        $('.search-result-box').empty().html(response.result);
    })
})
