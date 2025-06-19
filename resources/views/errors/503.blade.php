<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ session('direction') }}">

<head>
    <meta charset="utf-8">
    <title>{{ translate('database_refreshing') }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ $web_config['fav_icon']['path'] }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ $web_config['fav_icon']['path'] }}">
    <link rel="stylesheet" media="screen" href="{{dynamicAsset(path: 'public/assets/front-end/css/theme.css')}}">
</head>

<body>

<div class="container rtl">
    <div class="row vh-100 align-content-center">
        <div class="col-12">
            <div class="text-center">
                <img class="object-fit-contain height-300px" loading="lazy" src="{{dynamicAsset(path: 'public/assets/front-end/img/maintenance-mode-icon.png')}}" alt="{{ translate('maintenance-mode') }}">
                <h3 class="mt-3">
                    {{ 'System database is being refreshed, please keep patience. System will be up in 2 minutes....' }}
                </h3>
                <br>
            </div>
        </div>
    </div>
</div>

<script src="{{ dynamicAsset(path: 'public/assets/front-end/vendor/jquery/dist/jquery-2.2.4.min.js') }}"></script>
<script src="{{ dynamicAsset(path: 'public/assets/front-end/js/theme.js')}}"></script>
</body>

</html>
