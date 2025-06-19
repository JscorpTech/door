<div id="offcanvas-overlay" class="offcanvas-overlay category-sidebar-toggle"></div>
<div class="custom-offcanvas overflow-x-auto" id="category-off-canvas">
    <div class="custom-offcanvas-header d-flex justify-content-between align-items-baseline px-4 pt-4">
        <h4 class="mb-0">{{ translate('Category_Setup') }}</h4>
        <button type="button" class="close fz-24 p-0 category-sidebar-toggle">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
    <div class="custom-offcanvas-body p-4">
        <div class="mb-4">
            @include("blog::admin-views.blog.category.partials._create-category")
            @include("blog::admin-views.blog.category.partials._edit-category")
        </div>

        <div class="row align-items-center justify-content-between flex-wrap g-3 mb-20">
            <div class="col-12 col-md-4">
                <h4 class="m-0">{{ translate('Category_List') }}
                    @if(count($categories) > 0)
                        <span class="badge badge-soft-dark radius-50 fz-14 ml-1">
                                {{ $categories->total() }}
                        </span>
                    @endif
                </h4>
            </div>
            <div class="col-12 col-md-8">
                <form action="javascript:" method="POST" id="search-form" class="mb-0 px-1">
                    @csrf
                    <div class="input-group input-group-custom input-group-merge">
                        <div class="input-group-prepend">
                            <div class="input-group-text">
                                <i class="tio-search"></i>
                            </div>
                        </div>
                        <input id="datatableSearch"
                               type="text"
                               name="searchValue"
                               class="form-control"
                               placeholder="{{ translate('Search_by_Category_Name') }}"
                               aria-label="{{ translate('Search_here') }}">
                        <div class="input-group-append">
                            <button type="submit" class="btn btn--primary">{{ translate('Search') }}</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <div class="" id="categories-table">
            @include("blog::admin-views.blog.category.partials.table-rows")
        </div>
    </div>
</div>
