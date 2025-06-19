<?php

namespace App\Providers;

use App\Utils\Helpers;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\ServiceProvider;

class SocialLoginServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        try {
            $socialLoginServices = getWebConfig(name: 'social_login');

            if ($socialLoginServices) {
                foreach ($socialLoginServices as $socialLoginService) {
                    if ($socialLoginService['status'] == true && $socialLoginService['login_medium'] == 'google') {
                        $google_config = array(
                            'client_id' => $socialLoginService['client_id'],
                            'client_secret' => $socialLoginService['client_secret'],
                            'redirect' => url('customer/auth/login/google/callback'),
                        );
                        Config::set('services.google', $google_config);
                    } elseif ($socialLoginService['status'] == true && $socialLoginService['login_medium'] == 'facebook') {
                        $facebook_config = array(
                            'client_id' => $socialLoginService['client_id'],
                            'client_secret' => $socialLoginService['client_secret'],
                            'redirect' => url('customer/auth/login/facebook/callback'),
                        );
                        Config::set('services.facebook', $facebook_config);
                    }
                }
            }
        } catch (\Exception $exception) {
        }
        try {
            if (env('FORCE_HTTPS')) {
                $googleRedirect = Config::get('services.google.redirect');
                $facebookRedirect = Config::get('services.facebook.redirect');

                if ($googleRedirect) {
                    Config::set('services.google.redirect', str_replace('http://', 'https://', $googleRedirect));
                }
                if ($facebookRedirect) {
                    Config::set('services.facebook.redirect', str_replace('http://', 'https://', $facebookRedirect));
                }
            }
        } catch (\Exception $exception) {
        }
    }
}
