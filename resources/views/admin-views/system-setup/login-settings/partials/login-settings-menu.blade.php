@php
    use App\Enums\ViewPaths\Admin\SystemSetup;
@endphp
<div class="inline-page-menu my-4">
    <ul class="list-unstyled">
        <li class="{{ Request::is('admin/system-setup/login-settings/'.SystemSetup::CUSTOMER_LOGIN_SETUP[URI]) ? 'active':'' }}">
            <a href="{{ route('admin.system-setup.login-settings.customer-login-setup') }}">{{translate('Customer_Login')}}</a>
        </li>
        <li class="{{ Request::is('admin/system-setup/login-settings/'.SystemSetup::OTP_SETUP[URI]) ? 'active':'' }}">
            <a href="{{ route('admin.system-setup.login-settings.otp-setup') }}">{{translate('OTP_&_Login_Attempts')}}</a>
        </li>
        <li class="{{ Request::is('admin/system-setup/login-settings/'.SystemSetup::LOGIN_URL_SETUP[URI]) ?'active':'' }}">
            <a href="{{route('admin.system-setup.login-settings.login-url-setup')}}">{{translate('login_Url')}}</a>
        </li>
    </ul>
</div>
