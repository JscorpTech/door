@extends('layouts.back-end.app')

@section('title', translate('App_Download_Setup'))

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

        <div class="card collapsible-card-body">
            <div class="card-header">
                <div class="d-flex gap-2 align-items-center">
                    <img width="30" src="{{ dynamicAsset(path: 'public/assets/back-end/img/download-app-button.png') }}" alt="">
                    <div>
                        <h3>{{ translate('download_app_button') }}</h3>
                        <p class="m-0">
                            {{ translate('here_you_can_setup_the_necessary_information_related_to_the_app_download_option') }}
                        </p>
                    </div>
                </div>
                <div>
                    <form action="{{ route('admin.blog.app-download-setup-status') }}" method="post"
                          id="blog-custom-status-form" data-id="blog-custom-status-form">
                        @csrf
                        <label class="switcher mx-auto">
                            <input type="checkbox" class="switcher_input toggle-switch-message" value="1"
                                   {{ getWebConfig('blog_feature_download_app_status') == 1 ? 'checked' : ''  }}
                                   id="blog-custom-status" name="status"
                                   data-modal-id="toggle-status-custom-modal"
                                   data-toggle-id="blog-custom-status"
                                   data-on-image="blog-status-on.png"
                                   data-off-image="blog-status-off.png"
                                   data-on-title="{{ translate('are_you_sure_to_turn_on_the_download_app_button_status') }}"
                                   data-off-title="{{ translate('are_you_sure_to_turn_off_the_download_app_button_status') }}"
                                   data-on-message="<p>{{ translate('once_you_turn_on_this_blog_it_will_be_visible_to_the_blog_list_for_users.') }}</p>"
                                   data-off-message="<p>{{ translate('when_you_turn_off_this_blog_it_will_not_be_visible_to_the_blog_list_for_users') }}</p>"
                                   data-on-button-text="{{ translate('turn_on') }}"
                                   data-off-button-text="{{ translate('turn_off') }}">
                            <span class="switcher_control"></span>
                        </label>
                    </form>
                </div>
            </div>
            <div class="card-body collapsible-card-content">
                <form action="{{ route('admin.blog.app-download-setup') }}" method="post" enctype="multipart/form-data">
                    @csrf
                    <div class="row mt-15">
                        <div class="col-lg-6">
                            <div class="__bg-FAFAFA radius-10 p-4 mb-5">
                                <ul class="nav nav-tabs w-fit-content mb-4">
                                    @foreach($languages as $lang)
                                        <li class="nav-item text-capitalize">
                                            <span class="nav-link form-system-language-tab cursor-pointer {{ $lang == $defaultLanguage? 'active':''}}" id="{{ $lang}}-link">{{ucfirst(getLanguageName($lang)).'('.strtoupper($lang).')'}}
                                            </span>
                                        </li>
                                    @endforeach
                                </ul>
                                <div>
                                    @foreach($languages as $lang)
                                        <div class="form-group {{ $lang != $defaultLanguage ? 'd-none':''}} form-system-language-form" id="{{ $lang}}-form">
                                            <label class="title-color">
                                                {{ translate('Title') }}({{strtoupper($lang)}})
                                                <span class="input-required-icon">{{ $lang == 'en' ? '*' : '' }}</span>
                                            </label>
                                            <input type="text" class="form-control" name="title[{{$lang}}]" value="{{ $titleData[$lang] ?? '' }}" placeholder="{{ translate('Enter_Title') }}" {{ $lang == $defaultLanguage? 'required':''}}>
                                            <input type="hidden" name="lang[]" value="{{ $lang}}">
                                        </div>
                                        <div class="form-group mb-0 {{ $lang != $defaultLanguage ? 'd-none':''}} form-system-sub-title-language-form" id="{{ $lang}}-sub-title-form">
                                            <label class="title-color">{{ translate('Subtitle') }}({{strtoupper($lang)}})</label>
                                            <textarea class="form-control" name="sub_title[{{$lang}}]" placeholder="{{ translate('Enter_Subtitle') }}">{{ $subTitleData[$lang] ?? '' }}</textarea>
                                        </div>
                                    @endforeach
                                    <input name="position" value="0" class="d-none">
                                </div>
                            </div>
                            <div class="__bg-FAFAFA radius-10 p-4 mb-5 mb-lg-0">
                                <div class="mb-30">
                                    <h3>{{ translate('Download_button') }}</h3>
                                    <p class="m-0">
                                        {{ translate('Please_check_which_button_you_want_to_show_in_the_blog_section') }}
                                    </p>
                                </div>
                                <div>
                                    <label class="d-flex gap-10 mb-5 user-select-none cursor-pointer">
                                        <input type="checkbox" value="1" name="google_app_status" {{ $businessSetting['google_app_status'] == 1 ? 'checked' : ''  }}>
                                        <img width="22" src="{{ dynamicAsset(path: 'public/assets/back-end/img/icons/play-store.svg') }}" alt="">
                                        <strong class="mt-1">{{ translate('Playstore_Button') }}</strong>
                                    </label>

                                    <label class="d-flex gap-10 mb-5 user-select-none cursor-pointer">
                                        <input type="checkbox" value="1" name="apple_app_status" {{ $businessSetting['apple_app_status'] == 1 ? 'checked' : ''  }}>
                                        <img width="22" src="{{ dynamicAsset(path: 'public/assets/back-end/img/icons/apple-store.svg') }}" alt="">
                                        <strong class="mt-1">{{ translate('app_Store_Button') }}</strong>
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="d-flex flex-column justify-content-between h-100">
                                <div class="d-flex justify-content-center flex-column flex-sm-row gap-4 mb-4">
                                    <div class="">
                                        <label
                                            class="fz-14 text-title font-weight-bold">{{ translate('Icon') }}</label>
                                        <p class="mb-20 fz-12 max-w-165px">{{ translate('JPG, JPEG, PNG Less Than 1MB') }} <span
                                                class="font-weight-semibold">({{ translate('Ratio 1:1') }})</span>
                                        </p>

                                        <div class="upload-file radius-10 border-dashed-1 max-h-165px aspect-1">
                                            <input type="file" name="icon" class="upload-file__input single_file_input"
                                                accept=".jpg, .jpeg, .png">
                                            <a href="javascript:;" class="edit-btn d-none">
                                                <i class="tio-edit"></i>
                                            </a>
                                            <a href="javascript:" class="remove-btn {{ $businessSetting['app_download_icon'] ? '' : 'd-none' }} delete-data-without-form"
                                               data-action="{{ route('admin.blog.delete-image', ['icon' => isset($businessSetting['app_download_icon']['key']) ? $businessSetting['app_download_icon']['key'] : ''] ) }}"
                                               data-id="{{ isset($businessSetting['app_download_icon']['key']) ? $businessSetting['app_download_icon']['key'] : '' }}" title="{{translate('delete')}}">
                                                <i class="tio-delete"></i>
                                            </a>
                                            <label class="upload-file-wrapper w-100 h-100 mb-0 radius-10 overflow-hidden">
                                                <div class="__bg-F9F9F9 upload-file-textbox h-100 w-100">
                                                    <div class="d-flex flex-column justify-content-center align-items-center w-100 h-100">
                                                        <img src="{{ $businessSetting['app_download_icon'] ? getStorageImages(path: $businessSetting['app_download_icon'] , type: 'backend-basic') :  dynamicAsset(path: 'public/assets/back-end/img/download-app-icon.png') }}"
                                                        alt="">
                                                            <h6 class="mt-2 text-center {{ !$businessSetting['app_download_icon'] ? '' : 'd-none' }} image-upload">
                                                                <span class="text-info">{{ translate('Click to upload') }}</span>
                                                                <br>
                                                                {{ translate('or drag and drop') }}
                                                            </h6>
                                                    </div>
                                                </div>
                                                <img class="upload-file-img radius-10" loading="lazy" style="display: none;"
                                                    alt="">
                                            </label>
                                        </div>
                                    </div>
                                    <div class="">
                                        <label
                                        class="fz-14 text-title font-weight-bold">{{ translate('Background') }}</label>
                                        <p class="mb-20 fz-12 max-w-165px">{{ translate('JPG, JPEG, PNG Less Than 1MB') }} <span
                                                class="font-weight-semibold">({{ translate('Ratio 1:1') }})</span>
                                        </p>

                                        <div class="upload-file radius-10 border-dashed-1 max-h-165px aspect-1">
                                            <input type="file" name="image" class="upload-file__input single_file_input"
                                                accept=".jpg, .jpeg, .png">
                                            <a href="javascript:;" class="edit-btn d-none">
                                                <i class="tio-edit"></i>
                                            </a>
                                            <a href="javascript:" class="remove-btn {{ $businessSetting['app_download_background'] ? '' : 'd-none' }} delete-data-without-form"
                                               data-action="{{ route('admin.blog.delete-image', ['image' => isset($businessSetting['app_download_background']['key']) ? $businessSetting['app_download_background']['key'] : ''] ) }}"
                                               data-id="{{ isset($businessSetting['app_download_background']['key']) ? $businessSetting['app_download_background']['key'] : '' }}" title="{{translate('delete')}}">
                                                <i class="tio-delete"></i>
                                            </a>
                                            <label class="upload-file-wrapper w-100 h-100 mb-0 radius-10 overflow-hidden">
                                                <div class="__bg-F9F9F9 upload-file-textbox h-100 w-100">
                                                    <div class="d-flex flex-column justify-content-center align-items-center w-100 h-100">
                                                        <img
                                                        src="{{ $businessSetting['app_download_background'] ? getStorageImages(path: $businessSetting['app_download_background'] , type: 'backend-basic') :  dynamicAsset(path: 'public/assets/back-end/img/download-app-icon.png') }}"
                                                        alt="">
                                                        <h6 class="mt-2 text-center {{ !$businessSetting['app_download_background'] ? '' : 'd-none' }} image-upload">
                                                            <span class="text-info">{{ translate('Click to upload') }}</span>
                                                            <br>
                                                            {{ translate('or drag and drop') }}
                                                        </h6>
                                                    </div>
                                                </div>
                                                <img class="upload-file-img radius-10" loading="lazy" style="display: none;"
                                                    alt="">
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <div class="d-flex flex-wrap gap-3 justify-content-end">
                                    <button type="reset" id="reset"
                                    class="btn btn-secondary font-weight-semibold px-5">{{ translate('Reset') }}</button>
                                    <button type="submit" class="btn btn--primary font-weight-semibold px-5">{{ translate('Save') }}</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('script')
    <script>
        'use strict';
        $(document).ready(function () {
            // ---- single image upload starts
            $('.single_file_input').on('change', function (event) {
                var file = event.target.files[0];
                var $card = $(event.target).closest('.upload-file');
                var $textbox = $card.find('.upload-file-textbox');
                var $imgElement = $card.find('.upload-file-img');
                var $editBtn = $card.find('.edit-btn');
                var $removeBtn = $card.find('.remove-btn');

                if (file) {
                    var reader = new FileReader();
                    reader.onload = function (e) {
                        $textbox.hide();
                        $imgElement.attr('src', e.target.result).show();
                        $editBtn.removeClass('d-none');
                    };
                    reader.readAsDataURL(file);
                }
            });

            $('.edit-btn').on('click', function () {
                var $card = $(this).closest('.upload-file');
                var $fileInput = $card.find('.single_file_input');

                $fileInput.click();
            });

            // Check for a valid src on load to handle pre-existing images
            $('.upload-file').each(function () {
                var $card = $(this);
                var $textbox = $card.find('.upload-file-textbox');
                var $imgElement = $card.find('.upload-file-img');
                var $removeBtn = $card.find('.remove-btn');

                // If there's already a valid image source
                if ($imgElement.attr('src') && $imgElement.attr('src') !== window.location.href) {
                    $textbox.hide();
                    $imgElement.show();
                    $removeBtn.removeClass('d-none');
                }
            });

           $('.remove-btn').click(function () {
                var $card = $(this).closest('.upload-file');
                var $textbox = $card.find('.upload-file-textbox');
                $card.find('.single_file_input').val('');
                $card.find('.upload-file-img').css('display', 'none');
                $textbox.show();
            });
        });
    </script>
@endpush
