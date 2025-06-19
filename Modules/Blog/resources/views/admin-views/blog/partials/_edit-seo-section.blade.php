<div class="row">
    <div class="col-md-12">
        <div class="border-bottom d-flex gap-2 my-3 py-1">
            <i class="tio-user-big"></i>
            <h4>
                {{ translate('seo_section') }}
                <span class="input-label-secondary cursor-pointer" data-toggle="tooltip"
                      data-placement="top"
                      title="{{ translate('add_meta_titles_descriptions_and_images_for_blogs').', '.translate('this_will_help_more_people_to_find_them_on_search_engines_and_see_the_right_details_while_sharing_on_other_social_platforms') }}">
                    <img src="{{ dynamicAsset(path: 'public/assets/back-end/img/info-circle.svg') }}" alt="">
                </span>
            </h4>
        </div>
    </div>

    <div class="col-md-8">
        <div class="form-group">
            <label class="title-color">
                {{ translate('meta_Title') }}
                <span class="input-label-secondary cursor-pointer" data-toggle="tooltip"
                      data-placement="top"
                      title="{{ translate('add_the_blogs_title_name_taglines_etc_here').' '.translate('this_title_will_be_seen_on_Search_Engine_Results_Pages_and_while_sharing_the_blogs_link_on_social_platforms') .' [ '. translate('character_Limit') }} : 100 ]">
                    <img src="{{ dynamicAsset(path: 'public/assets/back-end/img/info-circle.svg') }}" alt="">
                </span>
            </label>
            <input type="text" name="meta_title" placeholder="{{ translate('meta_Title') }}"
                   class="form-control" id="meta_title" value="{{ $blog?->seoInfo?->title }}">
        </div>
        <div class="form-group">
            <label class="title-color">
                {{ translate('meta_Description') }}
                <span class="input-label-secondary cursor-pointer" data-toggle="tooltip"
                      data-placement="top"
                      title="{{ translate('write_a_short_description_of_the_blog').' '.translate('this_description_will_be_seen_on_Search_Engine_Results_Pages_and_while_sharing_the_blogs_link_on_social_platforms') .' [ '. translate('character_Limit') }} : 160 ]">
                    <img src="{{ dynamicAsset(path: 'public/assets/back-end/img/info-circle.svg') }}" alt="">
                </span>
            </label>
            <textarea rows="4" type="text" name="meta_description" id="meta_description"
                      class="form-control"
                      placeholder="{{ translate('write_a_short_description_of_the_blog') }}">{{ $blog?->seoInfo?->description }}</textarea>
        </div>
    </div>

    <div class="col-md-4">
        <div class="d-flex justify-content-center">
            <div class="form-group w-100">
                <div class="d-flex align-items-center justify-content-between gap-2">
                    <div>
                        <label class="title-color" for="meta_Image">
                            {{ translate('meta_Image') }}
                        </label>
                        <span
                            class="badge badge-soft-info">{{ THEME_RATIO[theme_root_path()]['Meta Thumbnail'] }}</span>
                        <span class="input-label-secondary cursor-pointer" data-toggle="tooltip"
                              title="{{ translate('add_Meta_Image_in') }} JPG, PNG or JPEG {{ translate('format_within') }} 2MB, {{ translate('which_will_be_shown_in_search_engine_results') }}.">
                            <img src="{{ dynamicAsset(path: 'public/assets/back-end/img/info-circle.svg') }}"
                                 alt="">
                        </span>
                    </div>
                </div>

                <div>
                    <div class="custom_upload_input">
                        <input type="file" name="meta_image"
                               class="custom-upload-input-file meta-img action-upload-color-image"
                               data-imgpreview="pre_meta_image_viewer"
                               id="meta_image_input"
                               accept=".jpg, .webp, .png, .jpeg, .gif, .bmp, .tif, .tiff|image/*">
                        @if($blog?->seoInfo?->image_full_url['path'])
                            <span class="delete_file_input btn btn-outline-danger btn-sm square-btn d-flex">
                                <i class="tio-delete"></i>
                            </span>
                        @else
                            <span class="delete_file_input btn btn-outline-danger btn-sm square-btn d--none">
                                <i class="tio-delete"></i>
                            </span>
                        @endif

                        <div class="img_area_with_preview position-absolute z-index-2 d-flex align-items-center justify-content-center">
                            <img id="pre_meta_image_viewer" class="h-auto bg-white onerror-add-class-d-none pre-meta-image-viewer" alt=""
                                 src="{{ getStorageImages(path: $blog?->seoInfo?->image_full_url['path'] ? $blog?->seoInfo?->image_full_url : $blog?->meta_image_full_url, type: 'backend-banner') }}">
                        </div>
                        <div class="position-absolute h-100 top-0 w-100 d-flex align-content-center justify-content-center overflow-hidden">
                            <div
                                class="d-flex flex-column justify-content-center align-items-center">
                                <img alt="" class="w-75"
                                     src="{{ dynamicAsset(path: 'public/assets/back-end/img/icons/product-upload-icon.svg') }}">
                                <h3 class="text-muted">{{ translate('Upload_Image') }}</h3>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="robots-meta-checkbox-card d-flex flex-wrap gap-2 justify-content-between h-100">
            <div class="item">
                <label class="checkbox--item">
                    <input type="radio" name="meta_index" value="index" {{ $blog?->seoInfo?->index != 'noindex' ? 'checked' : '' }}>
                    <img class="unchecked"
                         src="{{ dynamicAsset('public/assets/back-end/img/uncheck-radio-icon.svg') }}"
                         alt="">
                    <img class="checked"
                         src="{{ dynamicAsset('public/assets/back-end/img/check-radio-icon.svg') }}"
                         alt="">
                    <span class="user-select-none">{{ translate('Index') }}</span>
                    <span data-toggle="tooltip" title="{{ translate('allow_search_engines_to_put_this_web_page_on_their_list_or_index_and_show_it_on_search_results.') }}">
                        <img src="{{ dynamicAsset('public/assets/back-end/img/query.png') }}" alt="">
                    </span>
                </label>

                <label class="checkbox--item">
                    <input type="checkbox" name="meta_no_follow" value="1" class="input-no-index-sub-element" {{ !empty($blog?->seoInfo?->no_follow) ? 'checked' : '' }}>
                    <img class="unchecked" src="{{ dynamicAsset('public/assets/back-end/img/uncheck-icon.svg') }}" alt="">
                    <img class="checked" src="{{ dynamicAsset('public/assets/back-end/img/check-icon.svg') }}" alt="">
                    <span class="user-select-none">{{ translate('No_Follow') }}</span>
                    <span data-toggle="tooltip" title="{{ translate('instruct_search_engines_not_to_follow_links_from_this_web_page.') }}">
                        <img src="{{ dynamicAsset('public/assets/back-end/img/query.png') }}" alt="">
                    </span>
                </label>
                <label class="checkbox--item">
                    <input type="checkbox" name="meta_no_image_index" value="1" class="input-no-index-sub-element" {{ $blog?->seoInfo?->no_image_index ? 'checked' : '' }}>
                    <img class="unchecked" src="{{ dynamicAsset('public/assets/back-end/img/uncheck-icon.svg') }}" alt="">
                    <img class="checked" src="{{ dynamicAsset('public/assets/back-end/img/check-icon.svg') }}" alt="">
                    <span class="user-select-none">{{ translate('No_Image_Index') }}</span>
                    <span data-toggle="tooltip" title="{{ translate('prevents_images_from_being_listed_or_indexed_by_search_engines') }}">
                        <img src="{{ dynamicAsset('public/assets/back-end/img/query.png') }}" alt="">
                    </span>
                </label>
            </div>
            <div class="item">
                <label class="checkbox--item">
                    <input type="radio" name="meta_index" value="noindex" class="action-input-no-index-event" {{ $blog?->seoInfo?->index == 'noindex' ? 'checked' : '' }}>
                    <img class="unchecked"
                         src="{{ dynamicAsset('public/assets/back-end/img/uncheck-radio-icon.svg') }}"
                         alt="">
                    <img class="checked"
                         src="{{ dynamicAsset('public/assets/back-end/img/check-radio-icon.svg') }}"
                         alt="">
                    <span class="user-select-none">{{ translate('no_index') }}</span>
                    <span data-toggle="tooltip" title="{{ translate('disallow_search_engines_to_put_this_web_page_on_their_list_or_index_and_do_not_show_it_on_search_results.') }}">
                        <img src="{{ dynamicAsset('public/assets/back-end/img/query.png') }}" alt="">
                    </span>
                </label>
                <label class="checkbox--item">
                    <input type="checkbox" name="meta_no_archive" value="1" class="input-no-index-sub-element" {{ $blog?->seoInfo?->no_archive ? 'checked' : '' }}>
                    <img class="unchecked" src="{{ dynamicAsset('public/assets/back-end/img/uncheck-icon.svg') }}" alt="">
                    <img class="checked" src="{{ dynamicAsset('public/assets/back-end/img/check-icon.svg') }}" alt="">
                    <span class="user-select-none">{{ translate('No_Archive') }}</span>
                    <span data-toggle="tooltip" title="{{ translate('instruct_search_engines_not_to_display_this_webpages_cached_or_saved_version.') }}">
                        <img src="{{ dynamicAsset('public/assets/back-end/img/query.png') }}" alt="">
                    </span>
                </label>
                <label class="checkbox--item">
                    <input type="checkbox" name="meta_no_snippet" value="1" class="input-no-index-sub-element" {{ $blog?->seoInfo?->no_snippet ? 'checked' : '' }}>
                    <img class="unchecked" src="{{ dynamicAsset('public/assets/back-end/img/uncheck-icon.svg') }}" alt="">
                    <img class="checked" src="{{ dynamicAsset('public/assets/back-end/img/check-icon.svg') }}" alt="">
                    <span class="user-select-none">
                        {{ translate('No_Snippet') }}
                    </span>
                    <span data-toggle="tooltip" title="{{ translate('instruct_search_engines_not_to_show_a_summary_or_snippet_of_this_webpages_content_in_search_results.') }}">
                        <img src="{{ dynamicAsset('public/assets/back-end/img/query.png') }}" alt="">
                    </span>
                </label>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="robots-meta-checkbox-card d-flex flex-column gap-2 h-100">
            <div class="d-flex flex-wrap gap-2 justify-content-between align-items-center">
                <div class="item">
                    <label class="checkbox--item m-0">
                        <input type="checkbox" name="meta_max_snippet" value="1" {{ $blog?->seoInfo?->max_snippet ? 'checked' : '' }}>
                        <img class="unchecked" src="{{ dynamicAsset('public/assets/back-end/img/uncheck-icon.svg') }}" alt="">
                        <img class="checked" src="{{ dynamicAsset('public/assets/back-end/img/check-icon.svg') }}" alt="">
                        <span class="user-select-none">
                            {{ translate('max_Snippet') }}
                        </span>
                        <span data-toggle="tooltip" title="{{ translate('determine_the_maximum_length_of_a_snippet_or_preview_text_of_the_webpage.') }}">
                            <img src="{{ dynamicAsset('public/assets/back-end/img/query.png') }}" alt="">
                        </span>
                    </label>
                </div>
                <div class="item w-120px flex-grow-0">
                    <input type="number" placeholder="-1" class="form-control h-30 py-0" name="meta_max_snippet_value" value="{{ $blog?->seoInfo?->max_snippet_value ?? '-1' }}">
                </div>
            </div>
            <div class="d-flex flex-wrap gap-2 justify-content-between align-items-center">
                <div class="item">
                    <label class="checkbox--item m-0">
                        <input type="checkbox" name="meta_max_video_preview" value="1" {{ $blog?->seoInfo?->max_video_preview ? 'checked' : '' }}>
                        <img class="unchecked" src="{{ dynamicAsset('public/assets/back-end/img/uncheck-icon.svg') }}" alt="">
                        <img class="checked" src="{{ dynamicAsset('public/assets/back-end/img/check-icon.svg') }}" alt="">
                        <span class="user-select-none">
                            {{ translate('max_Video_Preview') }}
                        </span>
                        <span data-toggle="tooltip" title="{{ translate('determine_the_maximum_duration_of_a_video_preview_that_search_engines_will_display') }}">
                            <img src="{{ dynamicAsset('public/assets/back-end/img/query.png') }}" alt="">
                        </span>
                    </label>
                </div>
                <div class="item w-120px flex-grow-0">
                    <input type="number" placeholder="-1" class="form-control h-30 py-0" name="meta_max_video_preview_value" value="-1">
                </div>
            </div>
            <div class="d-flex flex-wrap gap-2 justify-content-between align-items-center">
                <div class="item">
                    <label class="checkbox--item m-0">
                        <input type="checkbox" name="meta_max_image_preview" value="1" {{ $blog?->seoInfo?->max_image_preview ? 'checked' : '' }}>
                        <img class="unchecked" src="{{ dynamicAsset('public/assets/back-end/img/uncheck-icon.svg') }}" alt="">
                        <img class="checked" src="{{ dynamicAsset('public/assets/back-end/img/check-icon.svg') }}" alt="">
                        <span class="user-select-none">{{ translate('max_Image_Preview') }}</span>
                        <span data-toggle="tooltip" title="{{ translate('determine_the_maximum_size_or_dimensions_of_an_image_preview_that_search_engines_will_display.') }}">
                            <img src="{{ dynamicAsset('public/assets/back-end/img/query.png') }}" alt="">
                        </span>
                    </label>
                </div>
                <div class="item w-120px flex-grow-0">
                    <select class="form-control h-30 py-0" name="meta_max_image_preview_value">
                        <option value="large" {{ $blog?->seoInfo?->max_image_preview_value == 'large' ? 'selected' : '' }}>{{ translate('large') }}</option>
                        <option value="medium" {{ $blog?->seoInfo?->max_image_preview_value == 'medium' ? 'selected' : '' }}>{{ translate('medium') }}</option>
                        <option value="small" {{ $blog?->seoInfo?->max_image_preview_value == 'small' ? 'selected' : '' }}>{{ translate('small') }}</option>
                    </select>
                </div>
            </div>
        </div>
    </div>
</div>
