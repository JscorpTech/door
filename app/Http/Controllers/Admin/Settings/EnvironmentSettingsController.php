<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Enums\ViewPaths\Admin\EnvironmentSettings;
use App\Http\Controllers\BaseController;
use App\Traits\SettingsTrait;
use App\Traits\UpdateClass;
use Brian2694\Toastr\Facades\Toastr;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class EnvironmentSettingsController extends BaseController
{
    use SettingsTrait, UpdateClass;

    /**
     * @param Request|null $request
     * @param string|null $type
     * @return View Index function is the starting point of a controller
     * Index function is the starting point of a controller
     */
    public function index(Request|null $request, string $type = null): View
    {
        return $this->getView();
    }

    public function getView(): View
    {
        return view(EnvironmentSettings::VIEW[VIEW]);
    }

    public function update(Request $request): RedirectResponse
    {
        if (env('APP_MODE') == 'demo') {
            Toastr::info(translate('you_can_not_update_this_on_demo_mode'));
            return back();
        }

        try {
            $this->setEnvironmentValue(envKey: 'APP_DEBUG', envValue: $request['app_debug'] ?? env('APP_DEBUG'));
            $this->setEnvironmentValue(envKey: 'APP_MODE', envValue: $request['app_mode'] ?? env('APP_MODE'));
            Toastr::success(translate('environment_variables_updated_successfully'));
        } catch (Exception $exception) {
            Toastr::error(translate('environment_variables_updated_failed'));
        }
        return back();
    }

    public function updateForceHttps(Request $request): RedirectResponse
    {
        if (env('APP_MODE') == 'demo') {
            Toastr::info(translate('you_can_not_update_this_on_demo_mode'));
            return back();
        }

        try {
            $this->setEnvironmentValue(envKey: 'FORCE_HTTPS', envValue: $request['force_https'] ?? env('FORCE_HTTPS', false));
            Toastr::success(translate('environment_variables_updated_successfully'));
        } catch (Exception $exception) {
            Toastr::error(translate('environment_variables_updated_failed'));
        }
        return back();
    }

    public function optimizeSystem(Request $request): RedirectResponse
    {
        if (env('APP_MODE') == 'demo') {
            Toastr::info(translate('you_can_not_update_this_on_demo_mode'));
            return back();
        }

        if ($request['optimize_type'] == 'cache') {
            Artisan::call('optimize:clear');
            Artisan::call('view:clear');
            Artisan::call('cache:clear');
            Toastr::success(translate('Cache_clear_successfully'));
        } else if ($request['optimize_type'] == 'migrate') {
            Artisan::call('migrate');
            Toastr::success(translate('Database_migrate_successfully'));
        } else if ($request['optimize_type'] == 'update') {
            $this->getProcessAllVersionsUpdates();
            Toastr::success(translate('Database_update_successfully'));
        }

        return back();
    }

    public function installPassport(Request $request): RedirectResponse
    {
        if (env('APP_MODE') == 'demo') {
            Toastr::info(translate('you_can_not_update_this_on_demo_mode'));
            return back();
        }

        shell_exec('php ../artisan passport:install');
        Toastr::success(translate('Passport_install_successfully'));
        return back();
    }
}
