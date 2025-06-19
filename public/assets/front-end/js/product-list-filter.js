"use strict";

let productListFilterForm = $('.product-list-filter');
let productListPageBackup = $('#products-search-data-backup');

let productListPageData = {
    id: productListPageBackup.data('id'),
    name: productListPageBackup.data('name'),
    product_name: productListPageBackup.data('name'),
    brand_id: productListPageBackup.data('brand'),
    category_id: productListPageBackup.data('category'),
    data_from: productListPageBackup.data('from'),
    offer_type: productListPageBackup.data('offer'),
    product_check: productListPageBackup.data('product-check'),
    min_price: productListPageBackup.data('min-price'),
    max_price: productListPageBackup.data('max-price'),
    sort_by: productListPageBackup.data('sort_by'),
    product_type: productListPageBackup.data('product-type'),
    vendor_id: productListPageBackup.data('vendor-id'),
    author_id: productListPageBackup.data('author-id'),
    publishing_house_id: productListPageBackup.data('publishing-house-id'),
    flash_deals_id: productListPageBackup.data('flash-deals-id'),
};

productListFilterForm.find('.product-list-filter-input').on('change keypress keyup', function () {
    const inputName = $(this).attr('name');
    const inputValue = $(this).val();
    if (inputName) {
        productListPageData[inputName] = inputValue;
    }
    getProductListFilterRender();
});

$('.search-page-button').on('click', function () {
    productListPageData.product_name = $('.search-page-button-input').val();
    getProductListFilterRender();
});

$('.action-search-products-by-price').on('click', function () {
    productListPageData.min_price = $('#min_price').val();
    productListPageData.max_price = $('#max_price').val();
    getProductListFilterRender();
})

productListFilterForm.on('submit', function (event) {
    event.preventDefault();
});

function getProductListFilterRender() {
    listPageProductTypeCheck();

    const baseUrl = productListPageBackup.data('url');
    const queryParams = $.param(productListPageData);
    const newUrl = baseUrl + '?' + queryParams;
    history.pushState(null, null, newUrl);
    $.get({
        url: productListPageBackup.data('url'),
        data: productListPageData,
        dataType: 'json',
        beforeSend: function () {
            $('#loading').show();
        },
        success: function (response) {
            $('#ajax-products-view').html(response?.html_products);
            $(".view-page-item-count").html(response.total_product);
            renderQuickViewFunction()
        },
        complete: function () {
            $('#loading').hide();
        },
    });
}

function listPageProductTypeCheck() {
    if (productListPageData?.product_type.toString() === 'digital') {
        $('.product-type-digital-section').show();
        $('.product-type-physical-section').hide();
    } else if (productListPageData?.product_type.toString() === 'physical') {
        $('.product-type-digital-section').hide();
        $('.product-type-physical-section').show();
    } else {
        $('.product-type-physical-section').show();
        $('.product-type-digital-section').show();
    }
}
listPageProductTypeCheck();

$("#search-brand").on("keyup", function () {
    let value = this.value.toLowerCase().trim();
    $("#lista1 div>li").show().filter(function () {
        return $(this).text().toLowerCase().trim().indexOf(value) == -1;
    }).hide();
});

$(".search-product-attribute").on("keyup", function () {
    let value = this.value.toLowerCase().trim();
    let container = $(this).closest('.search-product-attribute-container');
    let listItems = container.find(".attribute-list ul>li");
    let noDataText = container.find(".no-data-found");

    $(this).closest('.search-product-attribute-container').find(".attribute-list ul>li").show().filter(function () {
        return $(this).text().toLowerCase().trim().indexOf(value) == -1;
    }).hide();

    if (listItems.filter(":visible").length === 0) {
        noDataText.show();
    } else {
        noDataText.hide();
    }
});
