<?php

namespace App\Enums\ViewPaths\Admin;

enum ClearanceSale
{
    const LIST = [
        URI => '/',
        VIEW => 'admin-views.deal.clearance-sale.index'
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
        VIEW => 'admin-views.deal.clearance-sale.vendor-offers'
    ];

    const PRIORITY_SETUP = [
        URI => 'priority-setup',
        VIEW => 'admin-views.deal.clearance-sale.priority-setup'
    ];

    const PRIORITY_CONFIG = [
        URI => 'priority-setup-config',
        VIEW => ''
    ];

    const SEARCH = [
        URI => 'search',
        VIEW => 'admin-views.deal.clearance-sale.partials._search-product'

    ];
    const MULTIPLE_PRODUCT_DETAILS = [
        URI => 'multiple-product-details',
        VIEW => 'admin-views.deal.clearance-sale.partials._select-product'

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

    const CLEARANCE_PRODUCTS_DELETE = [
        URI => 'clearance-products-delete',
        VIEW => ''
    ];

    const UPDATE_DISCOUNT = [
        URI => 'update-discount',
        VIEW => ''
    ];

    const VENDOR_SEARCH = [
        URI => 'vendor-search',
        VIEW => 'admin-views.deal.clearance-sale.partials._search-vendor'
    ];

    const ADD_VENDOR = [
        URI => 'vendor-add',
    ];

    const UPDATE_STATUS = [
        URI => 'update-status',

    ];
    const UPDATE_OFFER_STATUS = [
        URI => 'update-offer-status',

    ];

    const VENDOR_DELETE = [
        URI => 'delete-vendor',
    ];
}
