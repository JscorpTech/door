<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class APIGuestMiddleware
{
    /**
     * @param Request $request
     * @param Closure $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next): mixed
    {
        if ($request->header('Authorization') && app('auth')->guard('api')) {
            $request->merge(['user' => auth('api')->user()]);
            return $next($request);
        } elseif ($request->guest_id) {
            return $next($request);
        }

        return response()->json(['Unauthorized', 401]);
    }
}
