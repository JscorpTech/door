<?php

if (!function_exists('theme_asset')) {
    function theme_asset($path = null): string
    {
        $themeName = theme_root_path();
        if($themeName == 'default'){
            return dynamicAsset(path: $path);
        }else{
            if (DOMAIN_POINTED_DIRECTORY == 'public') {
                return dynamicAsset(path: 'public/themes/'.$themeName.'/public/'.$path);
            }else{
                return dynamicAsset(path: 'resources/themes/'.$themeName.'/public/'.$path);
            }
        }
    }
}

if (!function_exists('theme_root_path')) {
    function theme_root_path(): string
    {
        // API request bo‘lsa, har doim 'theme_aster' ni qaytaradi
        if (request()->is('api/*')) {
            return 'theme_aster';
        }

        // Aks holda web uchun .env dan oladi
        return env('WEB_THEME', 'default');
    }
}


if (!function_exists('getHexToRGBColorCode')) {
    function getHexToRGBColorCode($hex): ?string
    {
        $result = preg_match('/^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i', $hex, $matches);
        return $result ? hexdec($matches[1]) . ', ' . hexdec($matches[2]) . ', ' . hexdec($matches[3]) : null;
    }
}

if (!function_exists('getSystemDynamicPartials')) {
    function getSystemDynamicPartials($type = null): mixed
    {
        if ($type == 'analytics_script') {
            return view("system-partials._analytics_script");
        }
        return null;
    }
}
