@extends('layouts.back-end.app')

@section('title', translate('Create_New_Blog'))

@push('css_or_js')
    <link href="{{ dynamicAsset(path: 'public/assets/back-end/plugins/summernote/summernote.min.css') }}" rel="stylesheet">
    <link href="{{ dynamicAsset(path: 'public/assets/back-end/libs/quill-editor/quill-editor.css') }}" rel="stylesheet">
@endpush

@section('content')
    <div class="content container-fluid">
        <form action="{{ route('admin.blog.store') }}" method="POST" enctype="multipart/form-data" id="blog-ajax-form">
            @csrf
            <div class="d-flex justify-content-between align-items-center gap-3 mb-3">
                <h2 class="h1 mb-0 text-capitalize d-flex align-items-center gap-2">
                    <a href="{{ route('admin.blog.view') }}">
                        <i class="tio-arrow-backward"></i>
                    </a>
                    {{ translate('Create_New_Blog') }}
                </h2>
            </div>
           <div class="card">
                <div class="card-body">
                    <div class="row mb-lg-4 align-items-center">
                        <div class="col-lg-6">
                            <div class="form-group">
                                <div class="d-flex justify-content-between align-items-center gap-3 mb-2">
                                    <label for="name" class="title-color mb-0">
                                        {{ translate('Category') }}
                                        <span class="trx-y-2" data-toggle="tooltip" data-placement="right" title=""
                                              data-original-title="{{ translate('select_a_category_from_the_dropdown_menu_to_assign_this_blog') }} {{ translate('if_no_categories_are_available_or_want_to_add_a_new_category_please_add_it_from_the_manage_category_section') }}">
                                            <img src="{{ dynamicAsset(path: 'public/assets/back-end/img/info-circle.svg') }}" alt="Image">
                                        </span>
                                    </label>
                                    <a href="#" class="font-medium user-select-none category-sidebar-toggle">
                                        {{ translate('Manage_Category') }}
                                    </a>
                                </div>
                                <select class="js-select2-custom form-control" name="blog_category" id="blog-category-select"
                                        data-text="{{ translate('select') }}"
                                        data-route="{{ route('admin.blog.category.get-list') }}">
                                    <option value="" selected disabled>{{ translate('select') }}</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}">
                                            @if(getDefaultLanguage() == 'en')
                                                {{ $category->name }}
                                            @else
                                                {{ $category?->translations()->where('key', 'name')->where('locale', getDefaultLanguage())->first()?->value ?? $category?->name }}
                                            @endif
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="name" class="title-color d-flex gap-1">
                                    {{ translate('Writer') }}
                                </label>
                                <input type="text" name="writer" id="" value="{{ old('writer') }}" class="form-control" placeholder="{{ translate('Ex') }}: {{ 'Jhon Milar' }}">
                            </div>
                            <div class="form-group mb-0">
                                <label for="name" class="title-color d-flex gap-1">
                                    {{ translate('Publish_Date') }}
                                    <span class="trx-y-2" data-toggle="tooltip" data-placement="top" title="" data-original-title="{{ translate('pick_the_date_that_you_want_to_show_for_customers_as_blog_publishing_date') }}">
                                        <img src="{{ dynamicAsset(path: 'public/assets/back-end/img/info-circle.svg') }}" alt="Image">
                                    </span>
                                </label>
                                <div class="position-relative">
                                    <input type="date" name="publish_date" class="form-control cursor-pointer"
                                           value="{{ date('Y-m-d') }}" placeholder="{{ translate('Select_Date') }}" autocomplete="off">
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-6">
                            <div class="text-center my-4 my-lg-0">
                                <label class="fz-14 text-title font-weight-bold">
                                    {{ translate('Thumbnail') }}
                                    <span class="input-required-icon">*</span>
                                </label>
                                <p class="mb-20">
                                    {{ translate('JPG,_JPEG,_PNG_Less_Than_5MB') }}
                                    <span class="font-weight-semibold">({{ translate('Ratio') }} 2:1)</span>
                                </p>

                                <div class="upload-file radius-10 border-dashed-1 max-w-330px m-auto aspect-2-1">
                                    <input type="file" name="image" class="upload-file__input single_file_input"
                                        accept=".jpg, .jpeg, .png">
                                    <a href="javascript:;" class="edit-btn opacity-0 z-index-99">
                                        <i class="tio-edit"></i>
                                    </a>
                                    <label class="upload-file-wrapper w-100 h-100 mb-0">
                                        <div class="__bg-F9F9F9 upload-file-textbox h-100 w-100">
                                            <div class="d-flex flex-column justify-content-center align-items-center h-100 w-100">
                                                <img width="34" height="34"
                                                src="{{ dynamicAsset(path: 'public/assets/back-end/img/document-upload.png') }}"
                                                alt="">
                                                <h6 class="mt-2 text-center">
                                                    <span class="text-info">{{ translate('Click_to_upload') }}</span>
                                                    <br>
                                                    {{ translate('or_drag_and_drop') }}
                                                </h6>
                                            </div>
                                        </div>
                                        <img class="upload-file-img radius-10" loading="lazy" style="display: none;"
                                            alt="">
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="__bg-FAFAFA rounded p-4">
                        <ul class="nav nav-tabs w-fit-content mb-4">
                            @foreach($languages as $lang)
                                <li class="nav-item text-capitalize {{$lang == $defaultLanguage ? 'active':''}}">
                                    <a class="nav-link lang-link form-system-language-tab {{$lang == $defaultLanguage ? 'active':''}}" href="javascript:" id="{{$lang}}-link">{{getLanguageName($lang).'('.strtoupper($lang).')'}}</a>
                                </li>
                            @endforeach
                        </ul>
                        <div>
                            @foreach($languages as $lang)
                                <div class="{{$lang != $defaultLanguage ? 'd-none':''}} form-system-language-form" id="{{$lang}}-form">
                                    <div class="form-group">
                                        <label for="name" class="title-color font-weight-medium text-capitalize">{{ translate('title')}}
                                            ({{strtoupper($lang)}})
                                            <span class="input-required-icon">*</span>
                                        </label>
                                        <input type="text" name="title[{{$lang}}]" class="form-control" id="title" placeholder="{{translate('ex').':'.translate('LUX')}}">
                                    </div>
                                </div>
                                <input type="hidden" name="lang[{{$lang}}]" value="{{$lang}}" id="lang-{{$lang}}">
                                <div class="form-group mb-0 {{$lang != $defaultLanguage ? 'd-none':''}} form-system-description-language-form" id="{{ $lang}}-description-form">
                                    <label class="title-color">{{ translate('Description') }}({{strtoupper($lang)}}) <span class="input-required-icon">*</span></label>
                                    <div id="description-{{$lang}}-editor" class="quill-editor"></div>
                                    <textarea name="description[{{$lang}}]" id="description-{{$lang}}" style="display:none;"></textarea>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    @include('blog::admin-views.blog.partials._seo-section')

                    <input type="hidden" name="status" id="status" value="1">
                    <input type="hidden" name="is_draft" id="is_draft" value="0">

                    <div class="d-flex flex-wrap gap-3 justify-content-end mt-4">
                        <button type="reset" id="reset"
                        class="btn btn-secondary font-weight-semibold w-140 reset-form">
                            {{ translate('reset') }}
                        </button>
                        <a class="btn btn-outline-primary font-weight-semibold w-140 save-draft">
                            {{ translate('Save_to_Draft') }}
                        </a>
                        <button type="button" class="btn btn--primary font-weight-semibold w-140" data-toggle="modal" data-target="#toggle-status-publish-modal">
                            {{ translate('Publish') }}
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
    @include('blog::admin-views.blog.partials._publish-modal')
    @include('blog::admin-views.blog.category.index')

@endsection

@push('script')
    <script src="{{ dynamicAsset(path: 'public/assets/back-end/libs/quill-editor/quill-editor.js') }}"></script>
    <script src="{{ dynamicAsset(path: 'public/assets/back-end/libs/quill-editor/quill-editor-init.js') }}"></script>

    @include('blog::admin-views.blog.partials._blog-script')
    @include('blog::admin-views.blog.category.partials._script')
@endpush
