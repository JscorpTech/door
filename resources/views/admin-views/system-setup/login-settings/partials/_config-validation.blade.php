<div class="d-flex flex-column align-items-center text-center gap-2 mb-2">
    <div class="d-flex flex-column justify-content-center align-items-center mb-3 position-relative">
        @if($data['type'] == 'google')
            <img src="{{ getStorageImages(path: null, type: 'banner', source: asset('public/assets/back-end/img/google-logo.png')) }}"
                class="status-icon" alt="" width="80"/>
        @elseif($data['type'] == 'facebook')
            <img src="{{ getStorageImages(path: null, type: 'banner', source: asset('public/assets/back-end/img/facebook-circle.svg')) }}"
                class="status-icon" alt="" width="80"/>
        @elseif($data['type'] == 'apple')
            <img src="{{ getStorageImages(path: null, type: 'banner', source: asset('public/assets/back-end/img/apple.png')) }}"
                 class="status-icon" alt="" width="80"/>
        @elseif($data['type'] == 'otp' || $data['type'] == 'otp-login')
            <img src="{{ getStorageImages(path: null, type: 'banner', source: asset('public/assets/back-end/img/sms-config-icon.svg')) }}"
                 class="status-icon" alt="" width="80"/>
        @elseif($data['type'] == 'email')
            <img src="{{ getStorageImages(path: null, type: 'banner', source: asset('public/assets/back-end/img/mail-config.png')) }}"
                 class="status-icon" alt="" width="80"/>
        @endif
    </div>
    <h5 class="modal-title">
        @if($data['type'] == 'google')
            {{ translate('set_up_Google_configuration_first') }}
        @elseif($data['type'] == 'facebook')
            {{ translate('set_up_Facebook_configuration_first') }}
        @elseif($data['type'] == 'apple')
            {{ translate('set_up_Apple_configuration_first') }}
        @elseif($data['type'] == 'otp' || $data['type'] == 'otp-login')
            {{ translate('set_up_otp_configuration_first') }}
        @elseif($data['type'] == 'email')
            {{ translate('set_up_Email_configuration_first') }}
        @endif
    </h5>
    <div class="text-center">
        @if($data['type'] == 'google' || $data['type'] == 'facebook' || $data['type'] == 'apple')
            {{ translate('it_looks_like_your_'.$data['type'].'_login_configuration_is_not_set_up_yet.') }}
            {{ translate('to_enable_the_'.$data['type'].'_login_option_please_set_up_the_'.$data['type'].'_configuration_first.') }}
        @elseif($data['type'] == 'otp' || $data['type'] == 'otp-login')
            {{ translate('it_looks_like_your_sms_configuration_is_not_set_up_yet.') }} {{ translate('to_enable_the_otp_system_please_set_up_the_sms_configuration_first') }}
        @elseif($data['type'] == 'email')
            {{ translate('it_looks_like_your_mail_configuration_is_not_set_up_yet.') }} {{ translate('to_enable_the_mail_service_please_set_up_the_mail_configuration_first') }}
        @endif
    </div>
    <div class="text-center py-3">
        @if($data['type'] == 'google')
            <a class="text-underline font-weight-bold" href="{{ route('admin.social-login.view') }}" target="_blank">
                {{ translate('go_to_Google_configuration') }}
            </a>
        @elseif($data['type'] == 'facebook')
            <a class="text-underline font-weight-bold" href="{{ route('admin.social-login.view') }}" target="_blank">
                {{ translate('go_to_Facebook_configuration') }}
            </a>
        @elseif($data['type'] == 'apple')
            <a class="text-underline font-weight-bold" href="{{ route('admin.social-login.view') }}" target="_blank">
                {{ translate('go_to_Apple_configuration') }}
            </a>
        @elseif($data['type'] == 'otp' || $data['type'] == 'otp-login')
            <a class="text-underline font-weight-bold" href="{{ route('admin.business-settings.sms-module') }}" target="_blank">
                {{ translate('go_to_SMS_configuration') }}
            </a>
        @elseif($data['type'] == 'email')
            <a class="text-underline font-weight-bold" href="{{ route('admin.business-settings.mail.index') }}" target="_blank">
                {{ translate('go_to_Email_configuration') }}
            </a>
        @endif
    </div>
</div>
<div class="d-flex justify-content-center gap-3 mt-3">
    <button type="button" class="btn btn--primary min-w-120" data-dismiss="modal">
        {{ translate('ok') }}
    </button>
    <button type="button" class="btn btn-danger-light min-w-120" data-dismiss="modal">
        {{ translate('cancel') }}
    </button>
</div>
