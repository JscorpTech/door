<div class="inline-page-menu my-4">
    <ul class="list-unstyled">
        <li class="{{ Request::is('admin/blog/view*') ? 'active' : '' }}">
            <a href="{{ route('admin.blog.view') }}"> {{ translate('Blog_Page') }}</a>
        </li>
        <li class="{{ Request::is('admin/blog/app-download-setup*') ? 'active' : '' }}">
            <a href="{{ route('admin.blog.app-download-setup') }}">{{ translate('App_Download_Setup') }}</a>
        </li>
        <li class="{{ Request::is('admin/blog/priority-setup*') ? 'active' : '' }}">
            <a href="{{ route('admin.blog.priority-setup.index') }}"> {{ translate('Priority_Setup') }}</a>
        </li>
    </ul>
</div>
