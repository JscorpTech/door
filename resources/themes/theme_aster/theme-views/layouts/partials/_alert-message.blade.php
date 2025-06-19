<audio id="myAudio">
    <source src="{{ theme_asset(path: 'assets/sound/notification.mp3') }}" type="audio/mpeg">
</audio>

<div class="alert--container active">
    @if(env('APP_MODE') == 'demo')
        <div class="alert alert--message-2 alert-dismissible fade show" id="demo-reset-warning" role="alert">
            <img width="28" class="align-self-start" src="{{ theme_asset(path: 'assets/img/info-2.png') }}" alt="">
            <div class="w--0">
                <h6 class="mb-1">{{ translate('warning').'!'}}</h6>
                <span>
                    {{translate('though_it_is_a_demo_site').'.'.translate('_our_system_automatically_reset_after_one_hour_&_that_why_you_logged_out').'.'}}
                </span>
            </div>
            <button type="button" class="close position-relative border-0" data-bs-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <div class="alert alert--message-2 alert-dismissible fade show product-restock-stock-alert">
        <div class="d-flex min-w-60px">
            <img width="50" class="align-self-start aspect-1 border rounded image object-cover"
                 src="" alt=""
            >
        </div>
        <div class="w-100 text-start overflow-hidden">
            <h6 class="title text-truncate mb-1"></h6>
            <span class="message"></span>
            <div class="d-flex justify-content-between gap-3 mt-2">
                <a href="javascript:" class="text-decoration-underline text-capitalize get-view-by-onclick product-link"
                   data-link="">
                    {{translate('click_to_view')}}
                </a>
            </div>
        </div>
        <button type="button" class="close position-relative font-semi-bold p-0 product-restock-stock-close btn fw-bold">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
</div>

