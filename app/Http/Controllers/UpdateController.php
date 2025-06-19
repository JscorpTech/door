<?php

namespace App\Http\Controllers;

use App\Utils\Helpers;
use App\Traits\ActivationClass;
use App\Traits\UpdateClass;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Artisan;
use Mockery\Exception;

class UpdateController extends Controller
{
    use ActivationClass;
    use UpdateClass;

    public function index(): View
    {
        return view('update.update-software');
    }

    public function updateSoftware(Request $request): Redirector|RedirectResponse
    {
        Helpers::setEnvironmentValue('SOFTWARE_ID', 'MzE0NDg1OTc=');
        Helpers::setEnvironmentValue('BUYER_USERNAME', $request['username']);
        Helpers::setEnvironmentValue('PURCHASE_CODE', $request['purchase_key']);
        Helpers::setEnvironmentValue('SOFTWARE_VERSION', SOFTWARE_VERSION);
        Helpers::setEnvironmentValue('APP_MODE', 'live');
        Helpers::setEnvironmentValue('APP_NAME', '6valley' . time());
        Helpers::setEnvironmentValue('SESSION_LIFETIME', '60');

        $data = $this->actch();
        try {
            if (!$data->getData()->active) {
                return redirect(base64_decode('aHR0cHM6Ly82YW10ZWNoLmNvbS9zb2Z0d2FyZS1hY3RpdmF0aW9u'));
            }
        } catch (Exception $exception) {
            Toastr::error(translate('verification_failed_try_again'));
            return back();
        }

        Artisan::call('migrate', ['--force' => true]);
        $previousRouteServiceProvider = base_path('app/Providers/RouteServiceProvider.php');
        $newRouteServiceProvider = base_path('app/Providers/RouteServiceProvider.txt');
        copy($newRouteServiceProvider, $previousRouteServiceProvider);

        //start symlink
        if (DOMAIN_POINTED_DIRECTORY == 'public') {
            shell_exec('ln -s ../resources/themes themes');
            Artisan::call('storage:link');
        }
        //end symlink

        Artisan::call('cache:clear');
        Artisan::call('view:clear');

        Artisan::call('config:cache');
        Artisan::call('config:clear');

        $this->getProcessAllVersionsUpdates();

        return redirect(env('APP_URL'));
    }
}
