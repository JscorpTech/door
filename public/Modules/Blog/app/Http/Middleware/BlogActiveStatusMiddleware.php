<?php

namespace Modules\Blog\app\Http\Middleware;

use Brian2694\Toastr\Facades\Toastr;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class BlogActiveStatusMiddleware
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

       if(!getWebConfig(name: 'blog_feature_active_status') ?? 0) {
           if (!$request->expectsJson()) {
               Toastr::error(translate('Page_not_found'));
               return redirect()->route('home');
           }

           return response()->json([
               'code' => 404,
               'message' => translate('Page_not_found')
           ], 404);
       }

        return $next($request);
    }
}
