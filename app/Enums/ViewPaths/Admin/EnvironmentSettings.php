<?php

namespace App\Enums\ViewPaths\Admin;

enum EnvironmentSettings
{
    const VIEW = [
        URI => 'environment-setup',
        VIEW => 'admin-views.business-settings.environment-index'
    ];

    const FORCE_HTTPS = [
        URI => 'environment-update-force-https',
        VIEW => ''
    ];

    const OPTIMIZE_SYSTEM = [
        URI => 'optimize-system',
        VIEW => ''
    ];

    const INSTALL_PASSPORT = [
        URI => 'install-passport',
        VIEW => ''
    ];

}
