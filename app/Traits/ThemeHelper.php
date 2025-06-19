<?php

namespace App\Traits;

trait ThemeHelper
{
    public function getThemeRoutesArray(): array
    {
        $themeRoutes = [];
        try {
            if (DOMAIN_POINTED_DIRECTORY == 'public') {
                if (theme_root_path() != 'default' && is_file(base_path('public/themes/'.theme_root_path().'/public/addon/theme_routes.php'))) {
                    $themeRoutes = include(base_path('public/themes/'.theme_root_path().'/public/addon/theme_routes.php')); // theme_root_path()
                }
            } else {
                if (theme_root_path() != 'default' && is_file(base_path('resources/themes/'.theme_root_path().'/public/addon/theme_routes.php'))) {
                    $themeRoutes = include('resources/themes/'.theme_root_path().'/public/addon/theme_routes.php'); // theme_root_path()
                }
            }
        } catch (\Exception $exception) {
        }

        return $themeRoutes;
    }
}
