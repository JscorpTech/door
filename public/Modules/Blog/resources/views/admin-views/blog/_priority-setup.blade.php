@extends('layouts.back-end.app')

@section('title', translate('Priority_Setup').' - '.translate('Blog'))

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

        <div class="card mt-2 brand">
            <div class="card-body">
                <div class="row">
                    <div class="col-lg-6">
                        <div class="">
                            <h3 class="mb-3 text-capitalize">{{ translate('categories') }}</h3>
                            <p class="max-w-400">{{ translate('this_blog_category_lists_groups_similar_blogs_you_can_view_categories_in_the_following_order_latest_alphabetical_or_most_clicked')}}</p>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <form action="{{ route('admin.blog.priority-setup.index') }}" method="post">
                            @csrf

                            <input type="hidden" name="type" value="blog_category_list_priority">

                            <div class="border rounded p-3 d-flex gap-4 flex-column">
                                <div class="d-flex gap-2 justify-content-between pb-3 border-bottom">
                                    <div class="d-flex flex-column">
                                        <h5 class="text-capitalize">{{ translate('use_default_sorting_list') }}</h5>
                                        <div class="d-flex gap-2 align-items-center">
                                            <img width="14" src="{{dynamicAsset(path: 'public/assets/back-end/img/icons/info.svg') }}" alt="">
                                            <span class="text-dark fz-12">{{translate('currently_sorting_this_section_based_on_latest_add')}}</span>
                                        </div>
                                    </div>
                                    <label class="switcher">
                                        <input type="checkbox" class="switcher_input switcher-input-js" name="default_sorting_status" value="1" data-parent-class="brand" data-from="default-sorting" {{ !isset($blogCategoryPriority['default_sorting_status']) || $blogCategoryPriority['default_sorting_status'] == 1 ? 'checked' : '' }}>
                                        <span class="switcher_control"></span>
                                    </label>
                                </div>
                                <div class="">
                                    <div class="d-flex gap-2 justify-content-between">
                                        <div class="d-flex flex-column">
                                            <h5 class="text-capitalize">{{ translate('use_custom_sorting_list') }}</h5>
                                            <div class="d-flex gap-2 align-items-center">
                                                <img width="14" src="{{dynamicAsset(path: 'public/assets/back-end/img/icons/info.svg') }}" alt="">
                                                <span class="text-dark fz-12">{{ translate('you_can_sorting_this_section_by_others_way') }}</span>
                                            </div>
                                        </div>
                                        <label class="switcher">
                                            <input type="checkbox" class="switcher_input switcher-input-js" name="custom_sorting_status" value="1" data-parent-class="brand" data-from="custom-sorting" {{ isset($blogCategoryPriority['custom_sorting_status']) && $blogCategoryPriority['custom_sorting_status'] == 1 ? 'checked' : '' }}>
                                            <span class="switcher_control"></span>
                                        </label>
                                    </div>

                                    <div class="custom-sorting-radio-list {{isset($blogCategoryPriority['custom_sorting_status']) && $blogCategoryPriority['custom_sorting_status'] == 1 ? '' : 'd--none'}}">
                                        <div class="border rounded p-3 d-flex flex-column gap-2 mt-4">
                                            <div class="d-flex gap-2 align-items-center">
                                                <input type="radio" class="show" name="sort_by" value="most_clicked" id="blog-category-sort-by-most-clicked" {{ !isset($blogCategoryPriority['sort_by']) || $blogCategoryPriority['sort_by'] == 'most_clicked' ? 'checked' : '' }}>
                                                <label class="mb-0 cursor-pointer" for="blog-category-sort-by-most-clicked">
                                                    {{ translate('show_most_clicked_first') }}
                                                </label>
                                            </div>

                                            <div class="d-flex gap-2 align-items-center">
                                                <input type="radio" class="show" name="sort_by" value="a_to_z" id="blog-category-alphabetic-order" {{ isset($blogCategoryPriority['sort_by']) && $blogCategoryPriority['sort_by'] == 'a_to_z' ? 'checked' : '' }}>
                                                <label class="mb-0 cursor-pointer text-capitalize" for="blog-category-alphabetic-order">
                                                    {{ translate('sort_by_Alphabetical') }} ({{'A '.translate('to').' Z' }})
                                                </label>
                                            </div>

                                            <div class="d-flex gap-2 align-items-center">
                                                <input type="radio" class="show" name="sort_by" value="z_to_a" id="blog-category-alphabetic-order-reverse" {{ isset($blogCategoryPriority['sort_by']) && $blogCategoryPriority['sort_by'] == 'z_to_a' ? 'checked' : '' }}>
                                                <label class="mb-0 cursor-pointer text-capitalize" for="blog-category-alphabetic-order-reverse">
                                                    {{ translate('sort_by_Alphabetical') }} ({{'Z '.translate('to').' A' }})
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="d-flex justify-content-end mt-4">
                                <button type="submit" class="btn btn-primary px-5">
                                    {{ $blogCategoryPriority ? translate('update') : translate('save') }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mt-2 vendor-list">
            <div class="card-body">
                <div class="row">
                    <div class="col-lg-6">
                        <div class="">
                            <h3 class="mb-3 text-capitalize">{{ translate('blog_list') }}</h3>
                            <p class="max-w-400">{{ translate('in_the_all_blogs_section_or_a_specific_blog_category_can_view_the_blogs_in_the_following_order_latest_alphabetical_or_popularity') }} ({{ translate('most_clicked') }})</p>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <form action="{{ route('admin.blog.priority-setup.index') }}" method="post">
                            @csrf

                            <input type="hidden" name="type" value="blog_list_priority">
                            <div class="border rounded p-3 d-flex gap-4 flex-column">
                                <div class="d-flex gap-2 justify-content-between pb-3 border-bottom">
                                    <div class="d-flex flex-column">
                                        <h5 class="text-capitalize">{{ translate('use_default_sorting_list') }}</h5>
                                        <div class="d-flex gap-2 align-items-center">
                                            <img width="14" src="{{dynamicAsset(path: 'public/assets/back-end/img/icons/info.svg') }}" alt="">
                                            <span class="text-dark fz-12">{{translate('currently_sorting_this_section_based_on_first_created')}}</span>
                                        </div>
                                    </div>
                                    <label class="switcher">
                                        <input type="checkbox" class="switcher_input switcher-input-js" name="default_sorting_status" value="1" data-parent-class="vendor-list" data-from="default-sorting" {{ !isset($blogPriority['default_sorting_status']) || $blogPriority['default_sorting_status'] == 1 ? 'checked' : '' }}>
                                        <span class="switcher_control"></span>
                                    </label>
                                </div>
                                <div class="">
                                    <div class="d-flex gap-2 justify-content-between">
                                        <div class="d-flex flex-column">
                                            <h5 class="text-capitalize">{{ translate('blog_sorting_list') }}</h5>
                                            <div class="d-flex gap-2 align-items-center">
                                                <img width="14" src="{{dynamicAsset(path: 'public/assets/back-end/img/icons/info.svg') }}" alt="">
                                                <span class="text-dark fz-12">{{ translate('you_can_sorting_this_section_by_others_way') }}</span>
                                            </div>
                                        </div>
                                        <label class="switcher">
                                            <input type="checkbox" class="switcher_input switcher-input-js" name="custom_sorting_status" value="1" data-parent-class="vendor-list" data-from="custom-sorting" {{ isset($blogPriority['custom_sorting_status']) && $blogPriority['custom_sorting_status'] == 1 ? 'checked' : '' }}>
                                            <span class="switcher_control"></span>
                                        </label>
                                    </div>

                                    <div class="custom-sorting-radio-list {{isset($blogPriority['custom_sorting_status']) && $blogPriority['custom_sorting_status'] == 1 ? '' : 'd--none'}}">
                                        <div class="border rounded p-3 d-flex flex-column gap-2 mt-4">
                                            <div class="d-flex gap-2 align-items-center">
                                                <input type="radio" class="show" name="sort_by" value="most_clicked" id="blog-list-sort-by-most-clicked" {{ !isset($blogPriority['sort_by']) || $blogPriority['sort_by'] == 'most_clicked' ? 'checked' : '' }}>
                                                <label class="mb-0 cursor-pointer" for="blog-list-sort-by-most-clicked">
                                                    {{ translate('popularity_show_most_clicked_first') }}
                                                </label>
                                            </div>

                                            <div class="d-flex gap-2 align-items-center">
                                                <input type="radio" class="show" name="sort_by" value="a_to_z" id="blog-list-alphabetic-order" {{ isset($blogPriority['sort_by']) && $blogPriority['sort_by'] == 'a_to_z' ? 'checked' : '' }}>
                                                <label class="mb-0 cursor-pointer text-capitalize" for="blog-list-alphabetic-order">
                                                    {{ translate('sort_by_Alphabetical') }} ({{'A '.translate('to').' Z' }})
                                                </label>
                                            </div>

                                            <div class="d-flex gap-2 align-items-center">
                                                <input type="radio" class="show" name="sort_by" value="z_to_a" id="blog-list-alphabetic-order-reverse" {{ isset($blogPriority['sort_by']) && $blogPriority['sort_by'] == 'z_to_a' ? 'checked' : '' }}>
                                                <label class="mb-0 cursor-pointer text-capitalize" for="blog-list-alphabetic-order-reverse">
                                                    {{ translate('sort_by_Alphabetical') }} ({{'Z '.translate('to').' A' }})
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="d-flex justify-content-end mt-4">
                                <button type="submit" class="btn btn-primary px-5">
                                    {{ $blogPriority ? translate('update') : translate('save') }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>


    </div>
@endsection

@push('script')
        <script src="{{ dynamicAsset(path: 'public/assets/back-end/js/admin/deal.js') }}"></script>
@endpush
