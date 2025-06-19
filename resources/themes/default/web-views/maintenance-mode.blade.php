<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ session('direction') }}">

<head>
    <meta charset="utf-8">
    <title>{{ translate('maintenance_Mode_On') }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ $web_config['fav_icon']['path']}}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ $web_config['fav_icon']['path'] }}">
    <link rel="stylesheet" media="screen" href="{{theme_asset(path: 'public/assets/front-end/css/theme.css')}}">
</head>

<body>

<div class="container">
    <div class="min-vh-100 row justify-content-between align-items-center maintenance-mode-container">
        <div class="col-12">
            <div class="text-center">
                <img class="object-fit-contain height-300px" src="{{ theme_asset(path: 'public/assets/front-end/img/maintenance-mode-icon.png') }}" alt="{{ translate('maintenance') }}">
            </div>
            <div class="text-center mt-2 mt-lg-3">
                @if($maintenanceMessages['maintenance_message'])
                    <h2 class="title">{{ $maintenanceMessages['maintenance_message'] }}</h2>
                @else
                    <h2 class="title">{{ translate('we_are_working_on_something_special') }} !</h2>
                @endif

                @if($maintenanceMessages['message_body'])
                    <p class="info">{{ $maintenanceMessages['message_body'] }}</p>
                @else
                    <p class="info">
                        {{ translate('we_apologize_for_any_inconvenience.') }}
                        {{ translate('for_immediate_assistance_please_contact_with_our_support_team') }}
                    </p>
                @endif
            </div>
            <div class="text-center my-5">

                @if($maintenanceMessages['business_number'] == 1 && $maintenanceMessages['business_email'] == 1)
                    <p class="info">{{ translate('Any_query') }}? {{ translate('Feel_free_to_call_or_mail_Us.') }}</p>
                @elseif($maintenanceMessages['business_number'] == 1)
                    <p class="info">{{ translate('Any_query') }}? {{ translate('Feel_free_to_call_Us.') }}</p>
                @elseif($maintenanceMessages['business_email'] == 1)
                    <p class="info">{{ translate('Any_query') }}? {{ translate('Feel_free_to_mail_Us.') }}</p>
                @endif

                @if($maintenanceMessages['business_number'] == 1)
                    <div>
                        <a href="tel:{{ getWebConfig(name: 'company_phone')}}">{{ getWebConfig(name: 'company_phone') }}</a>
                    </div>
                @endif

                @if($maintenanceMessages['business_email'] == 1)
                    <div>
                        <a href="mailto:{{ getWebConfig(name: 'company_email')}}">{{ getWebConfig(name: 'company_email') }}</a>
                    </div>
                @endif
            </div>
        </div>
    </div>

</div>

<script src="{{ theme_asset(path: 'public/assets/front-end/vendor/jquery/dist/jquery-2.2.4.min.js') }}"></script>
<script src="{{ theme_asset(path: 'public/assets/front-end/js/theme.js')}}"></script>
</body>

</html>
