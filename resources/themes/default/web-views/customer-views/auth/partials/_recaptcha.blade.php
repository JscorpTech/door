@php($recaptcha = getWebConfig(name: 'recaptcha'))

@if($web_config['firebase_otp_verification'] && $web_config['firebase_otp_verification']['status'])
    <div id="recaptcha-container-manual-login" class="my-2"></div>
@elseif(isset($recaptcha) && $recaptcha['status'] == 1)
    <div id="recaptcha_element" class="w-100 my-2" data-type="image"></div>
@else
    <div class="d-flex justify-content-between py-2">
        <div class="pr-2">
            <input type="text" class="form-control border __h-40" name="default_recaptcha_id_customer_login" value=""
                   id="customer-login-recaptcha-input"
                   placeholder="{{ translate('enter_captcha_value') }}" autocomplete="off">
        </div>
        <div class="input-icons mb-2 rounded bg-white">
            <a href="javascript:"
               class="d-flex align-items-center align-items-center get-login-recaptcha-verify get-session-recaptcha-auto-fill"
               data-session="{{ 'default_recaptcha_id_customer_login' }}"
               data-input="#customer-login-recaptcha-input"

               data-link="{{ URL('/customer/auth/code/captcha') }}">
                <img
                    src="{{ URL('/customer/auth/code/captcha/1?captcha_session_id=default_recaptcha_id_customer_login') }}"
                    class="input-field rounded __h-40" id="customer_login_recaptcha_id" alt="">
                <i class="tio-refresh icon cursor-pointer p-2"></i>
            </a>
        </div>
    </div>
@endif
