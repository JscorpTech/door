@extends('layouts.back-end.app')

@section('title', translate('Blog'))

@push('css_or_js')

@endpush

@section('content')
    <div class="content container-fluid">
        <div class="d-flex justify-content-between align-items-center gap-3 mb-3">
            <h2 class="h1 mb-0 text-capitalize d-flex align-items-center gap-2">
                <img width="20" src="{{ dynamicAsset(path: 'public/assets/back-end/img/blog-logo.png') }}" alt="">
                {{ translate('Blog') }}
            </h2>
        </div>

        @include('blog::admin-views.blog.partials._blog-tab-menu')

        <div class="card mb-20">
            <div class="card-body">
                <div class="row g-2 align-items-center">
                    <div class="col-md-8 col-xl-9">
                        <h3>{{ translate('Blog_Section') }}</h3>
                        <p class="m-0">
                            {{ translate('enabling_this_option_will_make_the_blog_section_visible_on_the_website_for_viewers') }}
                        </p>
                    </div>
                    <div class="col-md-4 col-xl-3">
                        <div class="d-flex justify-content-between align-items-center border rounded px-3 py-2">
                            <h5 class="mb-0 font-weight-normal">{{ translate('Activate_Blog') }}</h5>
                            <form action="{{ route('admin.blog.status-update') }}" method="post"
                                  id="blog-custom-status-form" data-id="blog-custom-status-form">
                                @csrf
                                <label class="switcher mx-auto">
                                    <input type="checkbox" class="switcher_input toggle-switch-message" value="1"
                                           {{ getWebConfig('blog_feature_active_status') == 1 ? 'checked' : ''  }}
                                           id="blog-custom-status" name="status"
                                           data-modal-id="toggle-status-custom-modal"
                                           data-toggle-id="blog-custom-status"
                                           data-on-image="blog-status-on.png"
                                           data-off-image="blog-status-off.png"
                                           data-on-title="{{ translate('are_you_sure_to_turn_on_the_blog_status') }}"
                                           data-off-title="{{ translate('are_you_sure_to_turn_off_the_blog_status') }}"
                                           data-on-message="<p>{{ translate('once_you_turn_on_this_blog_it_will_be_visible_to_the_blog_list_for_users.') }}</p>"
                                           data-off-message="<p>{{ translate('when_you_turn_off_this_blog_it_will_not_be_visible_to_the_blog_list_for_users') }}</p>"
                                           data-on-button-text="{{ translate('turn_on') }}"
                                           data-off-button-text="{{ translate('turn_off') }}">
                                    <span class="switcher_control"></span>
                                </label>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @include('blog::admin-views.blog.partials._blog-intro-section')

        <div class="card mb-20">
            <div class="card-body">
                    @include('blog::admin-views.blog.partials._blog-filter-section')

                <div class="d-flex align-items-center flex-wrap gap-4 mb-20">
                    <h4 class="m-0">{{ translate('Blog_List') }}
                        @if(count($blogs) > 0)
                            <span class="badge badge-soft-dark radius-50 fz-14 ml-1">
                                {{ $blogs->total() }}
                            </span>
                        @endif
                    </h4>
                    <div class="flex-grow-1 d-flex flex-wrap justify-content-between gap-3">
                        <form action="{{ url()->current() }}" method="GET">
                            <div class="input-group input-group-custom input-group-merge">
                                <div class="input-group-prepend">
                                    <div class="input-group-text">
                                        <i class="tio-search"></i>
                                    </div>
                                </div>
                                <input id="datatableSearch_" type="search" name="searchValue" class="form-control"
                                       placeholder="{{ translate('Search_by_title') }}..." aria-label="Search by Order ID"
                                       value="{{ request('searchValue') }}">
                                <button type="submit" class="btn btn--primary input-group-text">
                                    {{ translate('Search') }}
                                </button>
                            </div>
                        </form>
                        <a href="{{ route('admin.blog.add') }}" class="btn btn--primary font-weight-semibold px-5">
                            + {{ translate('Create_Blog') }}</a>
                    </div>
                </div>

                @if(count($blogs) > 0)
                    @include('blog::admin-views.blog.partials._blog-list-section')
                @else
                    <div class="p-4 bg-chat rounded text-center">
                        <div class="py-5">
                            <img src="{{ dynamicAsset('public/assets/back-end/img/empty-blog.png') }}" width="64"
                                 alt="">
                            <div class="mx-auto my-3 max-w-353px">
                                {{ translate('currently_no_blog_available_in_this_state') }}
                            </div>
                            @if(!request()->has('searchValue'))
                                <a href="{{ route('admin.blog.add') }}" class="text-primary text-underline">
                                    + {{ translate('create_blog') }}
                                </a>
                            @endif
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection

@push('script')

@endpush
