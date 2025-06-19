<?php

namespace App\Providers;

use Kreait\Firebase\Auth;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging;
use Illuminate\Support\ServiceProvider;

class FirebaseServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register(): void
    {
        $this->app->singleton(Factory::class, function ($app) {
            $firebaseConfig = getWebConfig('push_notification_key');
            return (new Factory)->withServiceAccount($firebaseConfig);
        });

        $this->app->singleton(Auth::class, function ($app) {
            return $app->make(Factory::class)->createAuth();
        });

        $this->app->singleton(Messaging::class, function ($app) {
            return $app->make(Factory::class)->createMessaging();
        });

        // Optionally, you can bind it to a simpler alias
        $this->app->alias(Messaging::class, 'firebase.messaging');
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
