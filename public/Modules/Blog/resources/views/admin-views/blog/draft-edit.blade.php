@extends('layouts.back-end.app')

@section('title', translate('draft_Blog'))

@push('css_or_js')
    <link href="{{ dynamicAsset(path: 'public/assets/back-end/libs/quill-editor/quill-editor.css') }}" rel="stylesheet">
@endpush

@section('content')
    <div class="content container-fluid">
        <div class="d-flex justify-content-between align-items-center gap-3 mb-3">
            <h2 class="h1 mb-0 text-capitalize d-flex align-items-center gap-2">
                <a href="{{ route('admin.blog.view') }}">
                    <i class="tio-arrow-backward"></i>
                </a>
                {{ translate('edit_draft_blog') }}
            </h2>
            <a href="{{ route('frontend.blog.details', ['slug' => $blog['slug'], 'source' => 'draft']) }}" target="_blank" class="btn btn-outline-primary"  data-toggle="tooltip" data-placement="bottom" title=""
            >
                {{ translate('view_preview') }}
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="15" viewBox="0 0 14 15" fill="none">
                    <path
                        d="M1.75 4.14583C1.75 3.64303 1.94974 3.16081 2.30528 2.80528C2.66081 2.44974 3.14303 2.25 3.64583 2.25H10.3542C10.857 2.25 11.3392 2.44974 11.6947 2.80528C12.0503 3.16081 12.25 3.64303 12.25 4.14583V10.8542C12.25 11.357 12.0503 11.8392 11.6947 12.1947C11.3392 12.5503 10.857 12.75 10.3542 12.75H3.64583C3.14303 12.75 2.66081 12.5503 2.30528 12.1947C1.94974 11.8392 1.75 11.357 1.75 10.8542V4.14583ZM3.64583 3.125C3.37509 3.125 3.11544 3.23255 2.924 3.424C2.73255 3.61544 2.625 3.87509 2.625 4.14583V10.8542C2.625 11.4177 3.08233 11.875 3.64583 11.875H10.3542C10.6249 11.875 10.8846 11.7674 11.076 11.576C11.2674 11.3846 11.375 11.1249 11.375 10.8542V4.14583C11.375 3.87509 11.2674 3.61544 11.076 3.424C10.8846 3.23255 10.6249 3.125 10.3542 3.125H3.64583ZM3.5 5.3125C3.5 4.749 3.95733 4.29167 4.52083 4.29167H9.47917C10.0427 4.29167 10.5 4.749 10.5 5.3125V6.1875C10.5 6.45824 10.3924 6.7179 10.201 6.90934C10.0096 7.10078 9.74991 7.20833 9.47917 7.20833H4.52083C4.25009 7.20833 3.99044 7.10078 3.799 6.90934C3.60755 6.7179 3.5 6.45824 3.5 6.1875V5.3125ZM4.52083 5.16667C4.48216 5.16667 4.44506 5.18203 4.41771 5.20938C4.39036 5.23673 4.375 5.27382 4.375 5.3125V6.1875C4.375 6.268 4.44033 6.33333 4.52083 6.33333H9.47917C9.51784 6.33333 9.55494 6.31797 9.58229 6.29062C9.60964 6.26327 9.625 6.22618 9.625 6.1875V5.3125C9.625 5.27382 9.60964 5.23673 9.58229 5.20938C9.55494 5.18203 9.51784 5.16667 9.47917 5.16667H4.52083ZM3.9375 8.08333C3.82147 8.08333 3.71019 8.12943 3.62814 8.21147C3.54609 8.29352 3.5 8.4048 3.5 8.52083C3.5 8.63687 3.54609 8.74815 3.62814 8.83019C3.71019 8.91224 3.82147 8.95833 3.9375 8.95833H6.5625C6.67853 8.95833 6.78981 8.91224 6.87186 8.83019C6.95391 8.74815 7 8.63687 7 8.52083C7 8.4048 6.95391 8.29352 6.87186 8.21147C6.78981 8.12943 6.67853 8.08333 6.5625 8.08333H3.9375ZM3.5 10.2708C3.5 10.1548 3.54609 10.0435 3.62814 9.96147C3.71019 9.87943 3.82147 9.83333 3.9375 9.83333H6.5625C6.67853 9.83333 6.78981 9.87943 6.87186 9.96147C6.95391 10.0435 7 10.1548 7 10.2708C7 10.3869 6.95391 10.4981 6.87186 10.5802C6.78981 10.6622 6.67853 10.7083 6.5625 10.7083H3.9375C3.82147 10.7083 3.71019 10.6622 3.62814 10.5802C3.54609 10.4981 3.5 10.3869 3.5 10.2708ZM8.89583 8.08333C8.62509 8.08333 8.36544 8.19088 8.17399 8.38233C7.98255 8.57377 7.875 8.83342 7.875 9.10417V9.6875C7.875 10.251 8.33233 10.7083 8.89583 10.7083H9.47917C9.74991 10.7083 10.0096 10.6008 10.201 10.4093C10.3924 10.2179 10.5 9.95824 10.5 9.6875V9.10417C10.5 8.83342 10.3924 8.57377 10.201 8.38233C10.0096 8.19088 9.74991 8.08333 9.47917 8.08333H8.89583ZM8.75 9.10417C8.75 9.06549 8.76536 9.0284 8.79271 9.00105C8.82006 8.9737 8.85716 8.95833 8.89583 8.95833H9.47917C9.51784 8.95833 9.55494 8.9737 9.58229 9.00105C9.60964 9.0284 9.625 9.06549 9.625 9.10417V9.6875C9.625 9.72618 9.60964 9.76327 9.58229 9.79062C9.55494 9.81797 9.51784 9.83333 9.47917 9.83333H8.89583C8.85716 9.83333 8.82006 9.81797 8.79271 9.79062C8.76536 9.76327 8.75 9.72618 8.75 9.6875V9.10417Z"
                        fill="currentColor" fill-opacity="0.85"/>
                </svg>
            </a>
        </div>
        <form action="{{ route('admin.blog.update', [$blog['id']]) }}" method="POST" enctype="multipart/form-data" id="blog-ajax-form">
            @csrf
                @php($draftData = json_decode($blog->draft_data, true))
            <div class="card">
                <div class="card-body">
                    <div class="row mb-lg-4 align-items-center">
                        <div class="col-lg-6">
                            <div class="form-group">
                                <div class="d-flex justify-content-between align-items-center gap-3 mb-2">
                                    <label for="name" class="title-color mb-0">
                                        {{ translate('Category') }}
                                        <span class="trx-y-2" data-toggle="tooltip" data-placement="right" title="" data-original-title="{{ translate('select_a_category_from_the_dropdown_menu_to_assign_this_blog.') }} {{ translate('if_no_categories_are_available_or_want_to_add_a_new_category_please_add_it_from_the_manage_category_section') }}">
                                            <img src="{{ dynamicAsset(path: 'public/assets/back-end/img/info-circle.svg') }}" alt="Image">
                                        </span>
                                    </label>
                                    <a href="#" class="font-medium user-select-none category-sidebar-toggle">
                                        {{ translate('Manage_Category') }}
                                    </a>
                                </div>
                                <input type="hidden" name="id" value="{{ $blog->id }}">
                                <select class="js-select2-custom form-control" name="blog_category" id="blog-category-select"
                                        data-text="{{ translate('select') }}"
                                        data-route="{{ route('admin.blog.category.get-list') }}">
                                    <option value="" selected disabled>{{ translate('select') }}</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}" {{ $draftData['category_id'] == $category->id ? 'selected' : '' }}>
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
                                <input type="text" id="" name="writer" value="{{ $draftData['writer'] ?? $blog?->writer }}" class="form-control" placeholder="{{ translate('Ex') }}: {{ 'Jhon Milar' }}">
                            </div>
                            <div class="form-group mb-0">
                                <label for="name" class="title-color d-flex gap-1">
                                    {{ translate('Publish_Date') }}
                                    <span class="trx-y-2" data-toggle="tooltip" data-placement="top" title="" data-original-title="{{ translate('pick_the_date_that_you_want_to_show_for_customers_as_blog_publishing_date') }}">
                                        <img src="{{ dynamicAsset(path: 'public/assets/back-end/img/info-circle.svg') }}" alt="Image">
                                    </span>
                                </label>
                                <div class="position-relative">
                                    <input type="date" name="publish_date" class="form-control cursor-pointer" value="{{ $draftData['publish_date'] ?? $blog->publish_date->format('Y-m-d') }}" placeholder="{{ translate('Select_Date') }}" autocomplete="off">
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="text-center my-4 my-lg-0">
                                <label
                                    class="fz-14 text-title font-weight-bold">{{ translate('Thumbnail') }} <span class="input-required-icon">*</span></label>
                                <p class="mb-20">{{ translate('JPG, JPEG, PNG Less Than 5MB') }} <span
                                        class="font-weight-semibold">({{ translate('Ratio 2:1') }})</span>
                                </p>
                                <div class="upload-file radius-10 border-dashed-1 max-w-330px m-auto aspect-2-1">
                                    <input type="file" name="image" class="upload-file__input single_file_input"
                                           accept=".jpg, .jpeg, .png">
                                    <a href="javascript:;" class="edit-btn opacity-1 z-index-99">
                                        <i class="tio-edit"></i>
                                    </a>
                                    <label class="upload-file-wrapper w-100 h-100 mb-0">
                                        <div class="__bg-F9F9F9 upload-file-textbox h-100 w-100 overflow-hidden">
                                            <div
                                                class="d-flex flex-column justify-content-center align-items-center h-100 w-100">
                                                <img src="{{ $blog->is_draft ? getStorageImages(path: $blog?->draft_thumbnail_full_url, type:'backend-product') : getStorageImages(path: $blog?->thumbnail_full_url, type:'backend-product') }}" alt="">
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
                            <?php
                            $translate = [];
                            if ($blog['translations']) {
                                foreach ($blog['translations'] as $translation) {
                                    if ($translation->is_draft == 1) {
                                        if ($translation->key == "title") {
                                            $translate[$translation->locale]['title'] = $translation->value;
                                        } elseif ($translation->key == "description") {
                                            $translate[$translation->locale]['description'] = $translation->value;
                                        }
                                    }
                                }
                            }
                            ?>
                            @foreach($languages as $lang)
                                <div class="{{$lang != $defaultLanguage ? 'd-none':''}} form-system-language-form" id="{{$lang}}-form">
                                    <div class="form-group">
                                        <label for="name"
                                               class="title-color font-weight-medium text-capitalize">{{ translate('title')}}
                                            ({{strtoupper($lang)}})
                                            <span class="input-required-icon">*</span>
                                        </label>
                                        <input type="text" name="title[{{$lang}}]" class="form-control" id="title" value="{{ $defaultLanguage == $lang ? ($draftData['title'] ?? $blog->title) :  $translate[$lang]['title'] ?? ''}}" placeholder="{{translate('ex').':'.translate('LUX')}}"{{$lang == $defaultLanguage ? 'required':''}}>
                                    </div>
                                </div>
                                <input type="hidden" name="lang[{{$lang}}]" value="{{$lang}}" id="lang-{{$lang}}">
                                <div class="form-group mb-0 {{$lang != $defaultLanguage ? 'd-none':''}} form-system-description-language-form" id="{{ $lang}}-description-form">
                                    <label class="title-color">
                                        {{ translate('Description') }}({{strtoupper($lang)}})
                                        <span class="input-required-icon">*</span>
                                    </label>
                                    <div id="description-{{$lang}}-editor" class="quill-editor">{!! $defaultLanguage == $lang ? ($draftData['description'] ?? $blog->title) :  $translate[$lang]['description'] ?? '' !!}</div>
                                    <textarea name="description[{{$lang}}]" id="description-{{$lang}}" style="display:none;">{!! $defaultLanguage == $lang ? ($draftData['description'] ?? $blog->title) :  $translate[$lang]['description'] ?? '' !!}</textarea>                                </div>
                            @endforeach
                        </div>
                    </div>
                    <input type="hidden" name="status" id="status" value="1">
                    <input type="hidden" name="is_draft" id="is_draft" value="0">
                    <input type="hidden" name="clear_draft" id="clear_draft" value="0">
                    <input type="hidden" name="page" id="page" value="draft">

                    <div class="d-flex flex-wrap gap-3 justify-content-end mt-4">
                        <button type="button" id="reset"
                                class="btn btn-secondary font-weight-semibold w-140 clear-draft">{{ translate('clear_draft') }}</button>
                        <button type="button" class="btn btn-outline-primary font-weight-semibold w-140 save-draft">{{ translate('Save_to_Draft') }}</button>
                        <button type="button" class="btn btn--primary font-weight-semibold w-140" data-toggle="modal" data-target="#toggle-status-publish-modal">{{ translate('Update_Publish') }}</button>
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
