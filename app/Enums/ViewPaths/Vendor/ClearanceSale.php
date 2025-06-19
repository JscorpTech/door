<?php

namespace App\Enums\ViewPaths\Vendor;

enum ClearanceSale
{
    const LIST = [
        URI => '/',
        VIEW => 'vendor-views.promotion.clearance-sale.index'
    ];

    const STATUS = [
        URI => 'status-update',
        VIEW => ''
    ];


    const UPDATE_CONFIG = [
        URI => 'update-config',
        VIEW => ''
    ];

    const VENDOR_OFFERS = [
        URI => 'vendor-offers',
        VIEW => 'vendor-views.promotion.clearance-sale.vendor-offers'
    ];

    const PRIORITY_SETUP = [
        URI => 'priority-setup',
        VIEW => 'vendor-views.promotion.clearance-sale.priority-setup'
    ];

    const PRIORITY_CONFIG = [
        URI => 'priority-setup-config',
        VIEW => ''
    ];

    const SEARCH = [
        URI => 'search',
        VIEW => 'vendor-views.promotion.clearance-sale.partials._search-product'

    ];
    const MULTIPLE_PRODUCT_DETAILS = [
        URI => 'multiple-product-details',
        VIEW => 'vendor-views.promotion.clearance-sale.partials._select-product'

    ];
    const ADD_PRODUCT = [
        URI => 'add-clearance-product',
    ];

    const PRODUCT_STATUS = [
        URI => 'clearance-product-status-update',
    ];
    const CLEARANCE_DELETE = [
        URI => 'clearance-delete',
        VIEW => ''
    ];
    const UPDATE_DISCOUNT = [
        URI => 'update-discount',
        VIEW => ''
    ];

    const CLEARANCE_PRODUCTS_DELETE = [
        URI => 'clearance-products-delete',
        VIEW => ''
    ];
}
