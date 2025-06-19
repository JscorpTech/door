<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Cache;

class DatabaseRefreshMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure(Request): (Response) $next
     * @return Response
     */
    public function handle(Request $request, Closure $next): Response
    {
       $dbRefresh = Cache::get('demo_database_refresh');
       if($dbRefresh) {
           if (!$request->expectsJson()) {
               abort(503, 'Service Unavailable');
           }

           return response()->json([
               'code' => 503,
               'message' => 'System database is being refreshed, please keep patience. System will be up in 2 minutes....'
           ], 503);
       }

        return $next($request);
    }
}
