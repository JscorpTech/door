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
        // Header orqali til olish yoki default tilga tushish
        $locale = $request->header('lang') ?? Helpers::default_lang();

        // Faqat mavjud tillarga ruxsat beramiz
        $availableLocales = ['uz', 'ru', 'en'];

        if (! in_array($locale, $availableLocales)) {
            $locale = Helpers::default_lang();
        }

        App::setLocale($locale);

        return $next($request);
    }
}
