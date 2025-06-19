'use strict';
$('.search-product').on('keyup',function (){
    let name = $(this).val();
    if (name.length > 0) {
        $.get($('#get-search-product-route').data('action'), {searchValue: name}, (response) => {
            $('.search-result-box').empty().html(response.result);
        })
    }
})
let selectProductSearch = $('.select-product-search');
let productIdsArray = [];
selectProductSearch.on('click', '.select-product-item', function () {
    let productId = $(this).find('.product-id').text();
    if(productIdsArray.indexOf(productId)){
        productIdsArray.push(productId);
        getProductDetails(productIdsArray);
    }


})
function removeSelectedProduct(){
    $('.remove-selected-product').on('click', function () {
        productIdsArray.splice(productIdsArray.indexOf($(this).data('product-id')));
        $(this).closest('.select-product-item').remove();
    });
}
$('.reset-selected-products').on('click',function (){
    productIdsArray = [];
    $('#selected-products').empty();
})

function getProductDetails(productIds){
    $.ajax({
        url: $('#get-multiple-product-details-route').data('action'),
        type: 'GET',
        data: { productIds: productIds },
        beforeSend: function () {
            $("#loading").fadeIn();
        },
        success: function(response) {
            $('#selected-products').empty().html(response.result);
            removeSelectedProduct();
        },
        complete: function () {
            $("#loading").fadeOut();
        },
    });

}

$('.search-product-for-clearance-sale').on('keyup', function () {
    let name = $(this).val();
    $.get($('#get-search-product-for-clearnace-route').data('action'), {searchValue: name}, (response) => {
        $('.search-result-box').empty().html(response.result);
    })
})

let selectClearanceProductSearch = $('.select-clearance-product-search');
let clearanceProductIdsArray = [];
selectClearanceProductSearch.on('click', '.select-clearance-product-item', function () {
    let productId = $(this).find('.product-id').text();
    if (clearanceProductIdsArray.indexOf(productId)) {
        clearanceProductIdsArray.push(productId);
        getClearanceProductDetails(clearanceProductIdsArray);
    }
    checkClearanceProductArray()

})

function removeSelectedClearanceProduct() {
    $('.remove-selected-clearance-product').on('click', function () {
        console.log(clearanceProductIdsArray, $(this).data('product-id'))
        clearanceProductIdsArray.splice(clearanceProductIdsArray.indexOf($(this).data('product-id')));
        $(this).closest('.remove-selected-clearance-parent').remove();
        checkClearanceProductArray()
    });
}

function getClearanceProductDetails(productIds) {
    $.ajax({
        url: $('#get-multiple-clearance-product-details-route').data('action'),
        type: 'GET',
        data: {productIds: productIds},
        beforeSend: function () {
            $("#loading").fadeIn();
        },
        success: function (response) {
            $('#selected-products').empty().html(response.result);
            removeSelectedClearanceProduct();
        },
        complete: function () {
            $("#loading").fadeOut();
        },
    });

}

function checkClearanceProductArray() {
    if (clearanceProductIdsArray?.length > 0) {
        $('.search-and-add-product').hide();
    } else {
        $('.search-and-add-product').show();
    }
}

$(document).ready(function() {
    const $selectedProductsContainer = $('#selected-products');
    const $addProductsBtn = $('#add-products-btn');
    function toggleAddProductsButton() {
        $addProductsBtn.prop('disabled', $selectedProductsContainer.children().length === 0);
    }

    toggleAddProductsButton();

    const observer = new MutationObserver(toggleAddProductsButton);
    observer.observe($selectedProductsContainer[0], { childList: true });
});
