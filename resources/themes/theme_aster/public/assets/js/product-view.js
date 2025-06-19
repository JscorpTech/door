"use strict";

$(document).on('change', '.product-list-filter .real-time-action-update', function () {
    $('.product-list-filter').submit();
})

$(document).on('click', '.action-search-products-by-price', function () {
    $('.product-list-filter').submit();
})

// Handle popstate event
window.addEventListener("popstate", function () {
    const currentUrl = window.location.href;

    $.ajaxSetup({
        headers: {
            "X-CSRF-TOKEN": $('meta[name="_token"]').attr("content"),
        },
    });

    $.ajax({
        url: currentUrl, // Use the current URL from the browser
        method: "GET",
        beforeSend: function () {
            $('#loading').addClass('d-grid');
        },
        success: function (response) {
            $("#ajax-products-view").empty().html(response?.html_products);
            $(".product-list-selected-tags").empty().html(response?.html_tags);

            renderProductCardIconFunctionality();
            productTagsActionForViewEvents()

            // resetAllInProductList();
        },
        complete: function () {
            $('#loading').removeClass('d-grid');
        },
    });
});

function resetCategoryWiseSearchInput() {
    console.log('1')
    try {
        $(".header-search-dropdown-button").html($(".header-search-dropdown-button").data('default'));
        $('#search_category_value').val('all');
        $('#global-search').val('');
    } catch (e) {

    }
}

$('.product-list-filter').on('submit', function (event) {
    event.preventDefault();

    updateBrowserURL($(this).attr("action"), $(this).serializeArray());
    resetCategoryWiseSearchInput();

    $.ajaxSetup({
        headers: {
            "X-CSRF-TOKEN": $('meta[name="_token"]').attr("content"),
        },
    });

    $.ajax({
        url: $(this).attr("action"),
        method: "GET",
        data: $(this).serialize(),
        beforeSend: function () {
            $('#loading').addClass('d-grid');
        },
        success: function (response) {
            if (response?.status?.toString() === '0' && response?.message) {
                toastr.error(response?.message)
            } else {
                $("#ajax-products-view").empty().html(response?.html_products);
                $(".product-list-selected-tags").empty().html(response?.html_tags);

                renderProductCardIconFunctionality();
                productTagsActionForViewEvents();
            }
        },
        complete: function () {
            $('#loading').removeClass('d-grid');
        },
    });
});

$(document).on('click', '.product-list-filter-on-sort-by', function () {
    $(".product-view-sort-by button").html($(this).text());
    $(".product-list-filter-on-sort-by").removeClass("selected");
    $(this).addClass("selected");
})

$(document).on('click', '.filter-on-product-filter-change', function () {
    $(".filter-on-product-filter button").html($(this).text());
    $(".filter-on-product-filter input").val($(this).data('value'));
    $(".filter-on-product-filter-change").removeClass("selected");
    $(this).addClass("selected");
})

$(document).on('click', '.filter-on-product-type-change', function () {
    $(".filter-on-product-type-button").html($(this).text());
    listPageProductTypeCheck();
})

function listPageProductTypeCheck() {
    if ($('[name="product_type"]').val()?.toString() === 'digital') {
        $('.product-type-digital-section').show();
        $('.product-type-physical-section').hide();
    } else if ($('[name="product_type"]').val()?.toString() === 'physical') {
        $('.product-type-digital-section').hide();
        $('.product-type-physical-section').show();
    } else {
        $('.product-type-physical-section').show();
        $('.product-type-digital-section').show();
    }
}

function productTagsActionForViewEvents() {
    $(".remove_tags_Category").on("click", function () {
        let id = $(this).data("id");
        $(".category_class_for_tag_" + id).click();
    });

    $(".remove_tags_Brand").on("click", function () {
        let id = $(this).data("id");
        $(".brand_class_for_tag_" + id).click();
    });

    $(".remove_tags_publishing_house").on("click", function () {
        let id = $(this).data("id");
        $(".publishing_house_class_for_tag_" + id).click();
    });

    $(".remove_tags_author_id").on("click", function () {
        let id = $(this).data("id");
        $(".authors_id_class_for_tag_" + id).click();
    });

    $(".remove_tags_review").on("click", function () {
        let id = $(this).data("id");
        $(".review_class_for_tag_" + id).click();
    });

    $(".remove_tags_sortBy").on("click", function () {
        $(".product-list-filter-on-sort-by").removeClass("selected");
        $(".product-view-sort-by button").html($('.product-view-sort-by').data('default'));
        $('[name="sort_by"]').each(function (index, element) {
            if ($(element).val() === 'latest') {
                $(element).prop('checked', true);
                $(element).closest('.product-list-filter-on-sort-by').addClass("selected");
            } else {
                $(element).prop('checked', false);
            }
        });
        $(this).remove();
        $('.product-list-filter').submit();
    });

    checkNavOverflow();
}

listPageProductTypeCheck();
productTagsActionForViewEvents();

function checkNavOverflow() {
    try {
        let $nav = $(".applied-filer");
        let $btnNext = $(".appliedFilterNextBtn");
        let $btnPrev = $(".appliedFilterPrevBtn");
        let isRTL = $("html").attr("dir") === "rtl";
        let navScrollWidth = $nav[0].scrollWidth;
        let navClientWidth = $nav[0].clientWidth;
        let scrollLeft = $nav.scrollLeft();

        if (isRTL) {
            $btnNext.find("i").removeClass('bi-chevron-right').addClass('bi-chevron-left');
            $btnPrev.find("i").removeClass('bi-chevron-left').addClass('bi-chevron-right');

            let scrollRight = navScrollWidth - navClientWidth + scrollLeft;

            $btnNext.toggle(scrollRight > 0.5);
            $btnPrev.toggle(scrollLeft < -0.5);
        } else {
            $btnNext.find("i").removeClass('bi-chevron-left').addClass('bi-chevron-right');
            $btnPrev.find("i").removeClass('bi-chevron-right').addClass('bi-chevron-left');

            $btnNext.toggle(navScrollWidth > navClientWidth && scrollLeft + navClientWidth < navScrollWidth);
            $btnPrev.toggle(scrollLeft > 0.5);
        }
    } catch (error) {

    }
}

 $(document).ready(function () {

    checkNavOverflow();
    $(window).on("resize", function () {
        checkNavOverflow();
    });

    $(".applied-filer").on("scroll", function () {
        checkNavOverflow();
    });

    $(document).on('click', '.appliedFilterNextBtn', function () {
        let $nav = $(".applied-filer");
        let scrollWidth = $nav.find("li").outerWidth(true);
        let isRTL = $("html").attr("dir") === "rtl";

        if (isRTL) {
            $nav.animate(
                { scrollLeft: $nav.scrollLeft() - scrollWidth },
                300,
                checkNavOverflow
            );
        } else {
            $nav.animate(
                { scrollLeft: $nav.scrollLeft() + scrollWidth },
                300,
                checkNavOverflow
            );
        }
    });

    $(document).on('click', '.appliedFilterPrevBtn', function () {
        let $nav = $(".applied-filer");
        let scrollWidth = $nav.find("li").outerWidth(true);
        let isRTL = $("html").attr("dir") === "rtl";

        if (isRTL) {
            $nav.animate(
                { scrollLeft: $nav.scrollLeft() + scrollWidth },
                300,
                checkNavOverflow
            );
        } else {
            $nav.animate(
                { scrollLeft: $nav.scrollLeft() - scrollWidth },
                300,
                checkNavOverflow
            );
        }
    });
});

// ---- filter top menu scrollable ends
