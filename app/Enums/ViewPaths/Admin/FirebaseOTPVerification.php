<?php

namespace App\Enums\ViewPaths\Admin;

enum FirebaseOTPVerification
{
    const INDEX = [
        URI => 'index',
        VIEW => 'admin-views.business-settings.firebase-otp-verification.index'
    ];

    const UPDATE = [
        URI => 'update',
        VIEW => ''
    ];
    const FIREBASE_CONFIG_VALIDATION = [
        URI => 'firebase-config-validation',
        VIEW => 'admin-views.business-settings.firebase-otp-verification.config-validation'
    ];
}
