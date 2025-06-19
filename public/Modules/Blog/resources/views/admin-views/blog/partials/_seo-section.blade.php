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
                   class="form-control" id="meta_title">
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
                      placeholder="{{ translate('write_a_short_description_of_the_blog') }}"></textarea>
        </div>
    </div>

    <div class="col-md-4">
        <div class="text-center my-4 my-lg-0">
            <label class="fz-14 text-title font-weight-bold">
                {{ translate('meta_Image') }}
            </label>
            <p class="mb-20">
                {{ translate('JPG,_JPEG,_PNG_Less_Than_5MB') }}
                <span class="font-weight-semibold">{{ THEME_RATIO[theme_root_path()]['Meta Thumbnail'] }}</span>
            </p>

            <div class="upload-file radius-10 border-dashed-1 max-w-330px m-auto aspect-2-1">
                <input type="file" name="meta_image" class="upload-file__input single_file_input"
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

    <div class="col-lg-6">
        <div class="robots-meta-checkbox-card d-flex flex-wrap gap-2 justify-content-between h-100">
            <div class="item">
                <label class="checkbox--item">
                    <input type="radio" name="meta_index" value="index" checked>
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
                    <input type="checkbox" name="meta_no_follow" value="1" class="input-no-index-sub-element">
                    <img class="unchecked" src="{{ dynamicAsset('public/assets/back-end/img/uncheck-icon.svg') }}" alt="">
                    <img class="checked" src="{{ dynamicAsset('public/assets/back-end/img/check-icon.svg') }}" alt="">
                    <span class="user-select-none">{{ translate('No_Follow') }}</span>
                    <span data-toggle="tooltip" title="{{ translate('instruct_search_engines_not_to_follow_links_from_this_web_page.') }}">
                        <img src="{{ dynamicAsset('public/assets/back-end/img/query.png') }}" alt="">
                    </span>
                </label>
                <label class="checkbox--item">
                    <input type="checkbox" name="meta_no_image_index" value="1" class="input-no-index-sub-element">
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
                    <input type="radio" name="meta_index" value="noindex" class="action-input-no-index-event">
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
                    <input type="checkbox" name="meta_no_archive" value="1" class="input-no-index-sub-element">
                    <img class="unchecked" src="{{ dynamicAsset('public/assets/back-end/img/uncheck-icon.svg') }}" alt="">
                    <img class="checked" src="{{ dynamicAsset('public/assets/back-end/img/check-icon.svg') }}" alt="">
                    <span class="user-select-none">{{ translate('No_Archive') }}</span>
                    <span data-toggle="tooltip" title="{{ translate('instruct_search_engines_not_to_display_this_webpages_cached_or_saved_version.') }}">
                        <img src="{{ dynamicAsset('public/assets/back-end/img/query.png') }}" alt="">
                    </span>
                </label>
                <label class="checkbox--item">
                    <input type="checkbox" name="meta_no_snippet" value="1" class="input-no-index-sub-element">
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
                        <input type="checkbox" name="meta_max_snippet" value="1">
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
                    <input type="number" placeholder="-1" class="form-control h-30 py-0" name="meta_max_snippet_value" value="-1">
                </div>
            </div>
            <div class="d-flex flex-wrap gap-2 justify-content-between align-items-center">
                <div class="item">
                    <label class="checkbox--item m-0">
                        <input type="checkbox" name="meta_max_video_preview" value="1">
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
                        <input type="checkbox" name="meta_max_image_preview" value="1">
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
                        <option value="large">{{ translate('large') }}</option>
                        <option value="medium">{{ translate('medium') }}</option>
                        <option value="small">{{ translate('small') }}</option>
                    </select>
                </div>
            </div>
        </div>
    </div>
</div>
