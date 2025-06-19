<?php

namespace App\Traits;

use App\Contracts\Repositories\AdminRepositoryInterface;
use App\Models\Admin;
use App\Models\Seller;
use App\Models\Shop;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

trait MaintenanceModeTrait
{
    public function checkMaintenanceMode(): bool
    {
        $maintenanceModeStatus = false;
        if (!auth()->guard('admin')->check()) {
            $maintenance = Cache::get('system_maintenance_mode');
            if ($maintenance) {
                $maintenanceStatus = $maintenance['status'];
                if ($maintenanceStatus && isset($maintenance['selectedSystems']) && $maintenance['selectedSystems']) {
                    if ($maintenance['selectedSystems']['vendor_panel'] == 1 && (request()->is('vendor/*') || request('maintenance_system') == 'vendor')) {
                        $maintenanceModeStatus = $this->checkForMaintenanceMode($maintenance);
                    }
                    if (!request()->is('admin/*') && !request()->is('vendor/*') && $maintenance['selectedSystems']['user_website'] == 1) {
                        $maintenanceModeStatus = $this->checkForMaintenanceMode($maintenance);
                    }
                    if (request()->is('api/*')) {
                        $maintenanceModeStatus = $this->checkForMaintenanceMode($maintenance);
                    }

                }
            }
        }
        return $maintenanceModeStatus;
    }

    public function checkForMaintenanceMode($maintenance): bool
    {
        $status = false;
        $maintenanceDuration = $maintenance['maintenance_duration'] ?? [];
        if ($maintenanceDuration) {
            if (isset($maintenanceDuration['maintenance_duration']) && $maintenanceDuration['maintenance_duration'] == 'until_change') {
                $status = true;
            } else {
                if (isset($maintenance['start_date']) && isset($maintenance['end_date'])) {
                    $start = Carbon::parse($maintenance['start_date']);
                    $end = Carbon::parse($maintenance['end_date']);
                    $today = Carbon::now();
                    if ($today->between($start, $end)) {
                        $status = true;
                    }
                }
            }
        }
        return $status;
    }
}
