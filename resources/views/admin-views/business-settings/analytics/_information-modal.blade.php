<div class="modal fade" id="getInformationModal" tabindex="-1" aria-labelledby="getInformationModal"
     aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered max-w-655px">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0 pt-2 px-2 d-flex justify-content-end">
                <button type="button" class="btn-close btn-close-danger" data-dismiss="modal" aria-label="Close"><i
                        class="tio-clear"></i></button>
            </div>
            <div class="modal-body px-4 px-sm-5 pt-0">
                <div class="swiper instruction-carousel pb-3">
                    <div class="swiper-wrapper">
                        <div class="swiper-slide">
                            <div class="d-flex flex-column align-items-center gap-2">
                                <img width="80" class="mb-3"
                                     src="{{ dynamicAsset(path: 'public/assets/back-end/img/modal/instruction.png') }}"
                                     loading="lazy" alt="">
                                <div>
                                    <h3 class="lh-md mb-3 text-capitalize text-start">{{ translate('step_by_step_guide') }}
                                    </h3>
                                    <ol class="d-flex flex-column px-4 gap-2 mb-4">
                                        <li> {{ translate('open_the_advertising_manager_or_platform_you_want_to_integrate_(e.g.,_meta_ads,_snapchat_ads,_google_analytics).') }}
                                        </li>
                                        <li> {{ translate('locate_and_copy_the_necessary_tracking_ids_from_their_respective_settings.') }}
                                        </li>
                                        <li> {{ translate('turn_on_the_toggle_for_the_platform_you_want_to_activate.') }}</li>
                                        <li> {{ translate('paste_the_code_into_the_input_box_and_click_submit.') }}
                                        </li>
                                        <li> {{ translate('if_you_no_longer_want_to_track_a_platforms_analytics_turn_the_toggle_off_anytime.') }}</li>
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
