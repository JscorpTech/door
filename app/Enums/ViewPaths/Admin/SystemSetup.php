<?php

namespace App\Enums\ViewPaths\Admin;

enum SystemSetup
{
    const OTP_SETUP = [
        URI => 'otp-setup',
        VIEW => 'admin-views.system-setup.login-settings.otp-setup'
    ];

    const LOGIN_URL_SETUP = [
        URI => 'login-url-setup',
        VIEW => 'admin-views.system-setup.login-settings.login-url-setup'
    ];

    const CUSTOMER_LOGIN_SETUP = [
        URI => 'customer-login-setup',
        VIEW => 'admin-views.system-setup.login-settings.customer-login-setup'
    ];

    const CUSTOMER_CONFIG_VALIDATION = [
        URI => 'customer-config-validation',
        VIEW => 'admin-views.system-setup.login-settings.partials._config-validation'
    ];

}
