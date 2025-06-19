<?php

return [
    'name' => 'Default',
    'route' => '',
    'url' => 'javascript:',
    'icon' => '<i class="fa-solid fa-screwdriver-wrench"></i>',
    'index' => 0,
    'path' => 'theme_route',
    'comfortable_panel_version' => '15.1',
    'route_list' => [
        [
            'name' => 'Banners',
            'route' => 'admin.banner.list',
            'url' => url('/') . '/admin/banner/list',
            'icon' => '<i class="tio-photo-square-outlined nav-icon"></i>',
            'path' => 'admin/banner/list',
            'route_list' => []
        ],
    ]
];
