<?php

namespace App\Enums\ViewPaths\Admin;

enum BlogList
{
    const LIST = [
        URI => 'view',
        VIEW => 'blog::admin-views.blog.index'
    ];

    const ADD = [
        URI => 'add',
        VIEW => 'blog::admin-views.blog.create'
    ];

    const INDEX = [
        URI => '',
        VIEW => 'blog::admin-views.blog._priority-setup'
    ];

    const DELETE = [
        URI => 'delete',
        VIEW => ''
    ];

    const EDIT = [
        URI => 'edit',
        VIEW => 'blog::admin-views.blog.edit'
    ];

    const UPDATE = [
        URI => 'update',
        VIEW => ''
    ];

    const STATUS_UPDATE = [
        URI => 'status-update',
        VIEW => ''
    ];

    const BLOG_STATUS_UPDATE = [
        URI => 'blog-status-update',
        VIEW => ''
    ];

    const DRAFT_EDIT = [
        URI => 'draft-edit',
        VIEW => 'blog::admin-views.blog.draft-edit'
    ];
}
