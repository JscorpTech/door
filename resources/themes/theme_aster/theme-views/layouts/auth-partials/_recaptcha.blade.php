@if($web_config['firebase_otp_verification'] && $web_config['firebase_otp_verification']['status'])
    <div id="recaptcha-container-manual-login" class="my-2"></div>
@elseif($web_config['recaptcha']['status'] == 1)
    <div class="d-flex justify-content-center mb-3">
        <div id="recaptcha_element_customer_login" class="w-100 mt-4" data-type="image"></div>
    </div>
@else
    <div style="display:none !important;" class="d-flex align-items-center gap-3 justify-content-between my-3">
        <div>
            <input type="text" class="form-control border __h-40" value="111"
                   id="customer-login-recaptcha-input"
                   name="default_recaptcha_id_customer_login" value=""
                   placeholder="{{ translate('enter_captcha_value') }}" autocomplete="off">
        </div>
        <div class="input-icons rounded bg-white">
            <a id="re-captcha-customer-login"
               data-session="default_recaptcha_id_customer_login"
               data-input="#customer-login-recaptcha-input"
               class="d-flex align-items-center align-items-center get-session-recaptcha-auto-fill">
                <img
                    src="{{ URL('/customer/auth/code/captcha/1?captcha_session_id=default_recaptcha_id_customer_login') }}"
                    alt="" class="input-field rounded __h-40" id="customer_login_recaptcha_id">
                <i class="bi bi-arrow-repeat icon cursor-pointer p-2"></i>
            </a>
        </div>
    </div>
@endif
