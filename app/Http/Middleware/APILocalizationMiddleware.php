<?php

namespace App\Http\Middleware;

use App\Utils\Helpers;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class APILocalizationMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next): mixed
    {
        $local = ($request->hasHeader('lang')) ? (strlen($request->header('lang')) > 0 ? $request->header('lang') : Helpers::default_lang()) : Helpers::default_lang();
        App::setLocale($local);
        return $next($request);
    }
}
