<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $supportedLocales = array_keys(config('app.supported_locales'));
        $locale = $request->session()->get('locale', config('app.locale'));

        App::setLocale(in_array($locale, $supportedLocales, true) ? $locale : config('app.locale'));

        return $next($request);
    }
}
