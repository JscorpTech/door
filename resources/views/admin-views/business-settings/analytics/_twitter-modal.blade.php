<div class="modal fade" id="modalForTwitterPixel" tabindex="-1" aria-labelledby="modalForTwitterPixel"
     aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered max-w-655px">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0 pt-2 px-2 d-flex justify-content-end">
                <button type="button" class="bg-transparent border-0 btn-close fz-22 p-0 text-black"
                        data-dismiss="modal" aria-label="Close"><i class="tio-clear"></i>
                </button>
            </div>
            <div class="modal-body px-4 px-sm-5 pt-0">
                <div class="swiper instruction-carousel pb-3">
                    <div class="swiper-wrapper">
                        <div class="swiper-slide">
                            <div class="swiper-slide">
                                <div class="">
                                    <div class="d-flex justify-content-center mb-5">
                                        <img height="60"
                                             src="{{ dynamicAsset(path: 'public/assets/back-end/img/svg/twitter.svg') }}"
                                             loading="lazy" alt="">
                                    </div>
                                    <div class="text-start title-color mb-3">
                                        <h4 class="lh-md font-weight-bolder fz-16">
                                            {{ translate('how_to_get_the_x_(twitter)_pixel_id') }}
                                        </h4>
                                        <p class="opacity--80">
                                            {{ translate('to_get_your_x_(twitter)_pixel_id,_log_in_to_your_twitter_ads_account.') }}
                                            {{ translate('from_the_top_navigation,_click_on_tools_and_select_events_manager.') }}
                                            {{ translate('once_in_the_events_manager,_create_your_pixel_id_by_clicking_on_add_event_source.') }}
                                            {{ translate('choose_the_install_with_pixel_code_option_and_press_save.') }}
                                            {{ translate('your_pixel_id_will_then_be_generated,_and_you_can_copy_it_from_the_interface.') }}
                                        </p>
                                    </div>

                                    <div class="text-start title-color mb-3">
                                        <h4 class="lh-md font-weight-bolder fz-16">
                                            {{ translate('where_to_use_the_x_(twitter)_pixel_id') }}
                                        </h4>
                                        <p class="opacity--80">
                                            {{ translate('go_to_the_marketing_tools_section_in_your_admin_panel_and_complete_the_steps:') }}
                                        </p>
                                        <ol class="d-flex flex-column gap-2 title-color opacity--80">
                                            <li>
                                                {{ translate('navigate_to_the_x_(twitter)_pixel_id_section_under_marketing_tools.') }}
                                            </li>
                                            <li>
                                                {{ translate('turn_on_the_toggle_button.') }}
                                            </li>
                                            <li>
                                                {{ translate('paste_your_x_(twitter)_pixel_id_into_the_input_box_and_click_submit.') }}
                                            </li>
                                        </ol>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
