<div class="modal fade" id="modalForTikTokPixel" tabindex="-1" aria-labelledby="modalForTikTokPixel"
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
                                             src="{{ dynamicAsset(path: 'public/assets/back-end/img/svg/tiktok.svg') }}"
                                             loading="lazy" alt="">
                                    </div>
                                    <div class="text-start title-color mb-3">
                                        <h4 class="lh-md font-weight-bolder fz-16">
                                            {{ translate('how_to_get_the_tiktok_pixel_id') }}
                                        </h4>
                                        <p class="opacity--80">
                                            {{ translate('from_the_tiktok_business_account_click_on_tools_in_the_menu_and_select_events.') }}
                                            {{ translate('access_the_events_manager_by_clicking_on_connect_data_sources_in_the_top_right_corner.') }}
                                            {{ translate('from_the_popup,_choose_the_web_option_and_click_next.') }}
                                            {{ translate('now,_create_your_pixel_by_selecting_manual_setup.') }}
                                            {{ translate('to_find_your_pixel_id,_go_to_data_sources_in_the_left-hand_menu,_where_you_can_view_and_copy_your_pixel_id.') }}
                                        </p>
                                    </div>

                                    <div class="text-start title-color mb-3">
                                        <h4 class="lh-md font-weight-bolder fz-16">
                                            {{ translate('where_to_use_the_tiktok_pixel_id') }}
                                        </h4>
                                        <p class="opacity--80">
                                            {{ translate('go_to_the_marketing_tools_section_in_your_admin_panel_and_complete_the_steps:') }}
                                        </p>
                                        <ol class="d-flex flex-column gap-2 title-color opacity--80">
                                            <li>
                                                {{ translate('navigate_to_the_tiktok_pixel_id_section_under_marketing_tools.') }}
                                            </li>
                                            <li>
                                                {{ translate('turn_on_the_toggle_button.') }}
                                            </li>
                                            <li>
                                                {{ translate('paste_your_tiktok_pixel_id_into_the_input_box_and_click_submit.') }}
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
