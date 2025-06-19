'use strict';

$(document).on('focus', '.search-vendor-for-clearance-sale', function () {
    console.log('focus')
    $('.search-result-box').closest('.dropdown-menu').removeClass('d-none').addClass('d--block');
}).on('click', function (e) {
    console.log('click')
    if (!$(e.target).closest('.search-vendor-for-clearance-sale, .search-result-box').length) {
        $('.search-result-box').closest('.dropdown-menu').addClass('d-none').removeClass('d--block');
    }
});

function ajaxSetupToken() {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
}

$('.search-vendor-for-clearance-sale').on('keyup', function () {
    let name = $(this).val();

    $.get($('#get-search-vendor-for-clearnace-route').data('action'), {searchValue: name}, (response) => {
        $('.search-result-box').empty().html(response.result);
    });
})

$(".select-clearance-vendor-item").on("click", function () {
    const shopId = $(this).find("input[id^='shop-id']").val();
    ajaxSetupToken();
    $.ajax({
        url: $('#get-clearance-vendor-add-route').data('action'),
        type: 'post',
        data: {shopId: shopId},
        beforeSend: function () {
            $("#loading").fadeIn();
        },
        success: function (response) {
            if(response.status) {
                toastr.success(response.message)
                setTimeout(() => {
                    location.reload();
                }, 3000);
            } else {
                toastr.error(response.message)
            }
        },
        complete: function () {
            $("#loading").fadeOut();
        },
    });
});
