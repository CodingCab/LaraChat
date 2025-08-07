<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class BackendLocale
{
    public function handle(Request $request, Closure $next): mixed
    {
        $locale = $request->cookie('backend_locale', config('app.locale', 'en'));
        app()->setLocale($locale);

        return $next($request);
    }
}
