<?php

namespace App\Http\Middleware;

use App\Traits\MaintenanceModeTrait;
use App\Utils\Helpers;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MaintenanceModeMiddleware
{
    use MaintenanceModeTrait;

    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next): mixed
    {
        if ($this->checkMaintenanceMode()) {
            if (request()->is('vendor/*')) {
                return redirect()->route('maintenance-mode', ['maintenance_system' => 'vendor']);
            }
            return redirect()->route('maintenance-mode');
        }
        return $next($request);
    }
}
