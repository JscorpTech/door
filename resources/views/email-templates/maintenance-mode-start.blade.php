<?php
$companyPhone = getWebConfig(name: 'company_phone');
$companyEmail = getWebConfig(name: 'company_email');
$companyName = getWebConfig(name: 'company_name');
$companyLogo = getWebConfig(name: 'company_web_logo');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ translate('Maintenance_mode_start') }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            color: #333;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            border: 2px solid #007bff;
        }
        .header {
            padding: 10px;
            text-align: center;
        }
        .content {
            margin: 20px 0;
        }
        .footer {
            text-align: start;
            font-size: 12px;
            color: #777;
            margin-top: 20px;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <div class="text-center">
            <img height="60" class="mb-4" id="view-mail-icon"
                 src="{{ getStorageImages(path: $companyLogo, type: 'backend-logo') }}"
                 alt="">
        </div>
        <h1>{{ translate('Our_maintenance_mode_is_start') }}</h1>
    </div>
    <div class="content">
        <p>{{ translate('Hi') }} {{ $data['user_name'] }},</p>
        <p>{{ translate('we_are_currently_undergoing_maintenance.') }} {{ translate('please_check_back_later.')}}</p>
        <p>{{translate('please_contact_us_for_any_queries,_we_are_always_happy_to_help')}}. </p>
    </div>
    <div class="footer">
        <p>{{ translate('best_regards') }},</p>
        <p>{{ $companyName }}</p>
        <p><strong>{{ translate('phone') }}:</strong> {{ $companyPhone }}</p>
        <p><strong>{{ translate('Email') }}:</strong> {{ $companyEmail }}</p>
    </div>
</div>
</body>
</html>

