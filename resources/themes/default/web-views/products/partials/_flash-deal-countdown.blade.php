<div class="__flash-deals-bg rounded mt-4" style="background: url({{$deal_banner}}) no-repeat center center / cover">
    <div class="row g-3 justify-content-end align-items-center">
        <div class="col-lg-8 col-md-6 text-primary text-center {{Session::get('direction') === "rtl" ? 'text-md-right' : 'text-md-left'}}">
            <div class="flash_deal_title text-primary">
                {{$web_config['flash_deals']->title}}
            </div>
            <span class="fs-14 font-weight-normal">{{translate('hurry_Up')}} ! {{translate('the_offer_is_limited')}}. {{translate('grab_while_it_lasts')}}</span>
        </div>
        <div class="col-lg-4 col-md-6">
            <div class="countdown-card bg-transparent __inline-59-custom">
                <div class="text-center text-white">
                    <div class="countdown-background rounded p-2">
                                 <span class="cz-countdown d-flex justify-content-center align-items-center"
                                       data-countdown="{{$web_config['flash_deals'] ? date('m/d/Y',strtotime($web_config['flash_deals']['end_date'])):''}} 23:59:00">
                                     <span class="cz-countdown-days">
                                         <span class="cz-countdown-value"></span>
                                         <span class="cz-countdown-text text-nowrap">{{ translate('days')}}</span>
                                     </span>
                                     <span class="cz-countdown-value p-1">:</span>
                                     <span class="cz-countdown-hours">
                                         <span class="cz-countdown-value"></span>
                                         <span class="cz-countdown-text text-nowrap">{{ translate('hours')}}</span>
                                     </span>
                                     <span class="cz-countdown-value p-1">:</span>
                                     <span class="cz-countdown-minutes">
                                         <span class="cz-countdown-value"></span>
                                         <span class="cz-countdown-text text-nowrap">{{ translate('minutes')}}</span>
                                     </span>
                                     <span class="cz-countdown-value p-1">:</span>
                                     <span class="cz-countdown-seconds">
                                         <span class="cz-countdown-value"></span>
                                         <span class="cz-countdown-text text-nowrap">{{ translate('seconds')}}</span>
                                     </span>
                                 </span>

                        <?php
                        $startDate = \Carbon\Carbon::parse($web_config['flash_deals']['start_date']);
                        $endDate = \Carbon\Carbon::parse($web_config['flash_deals']['end_date']);
                        $now = \Carbon\Carbon::now();
                        $totalDuration = $endDate->diffInSeconds($startDate);
                        $elapsedDuration = $now->diffInSeconds($startDate);
                        $flashDealsPercentage = ($elapsedDuration / $totalDuration) * 100;
                        ?>

                        <div class="progress __progress">
                            <div class="progress-bar flash-deal-progress-bar" role="progressbar"
                                 style="width: {{ number_format($flashDealsPercentage, 2) }}%"
                                 aria-valuenow="25" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
