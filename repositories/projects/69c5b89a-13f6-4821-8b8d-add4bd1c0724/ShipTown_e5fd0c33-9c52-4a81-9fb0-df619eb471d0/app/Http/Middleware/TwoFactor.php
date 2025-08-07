<?php

namespace App\Http\Middleware;

use App\Models\Configuration;
use Closure;
use Illuminate\Http\Request;

class TwoFactor
{
    public function handle(Request $request, Closure $next): mixed
    {
        if ($request->is('verify')) {
            return $next($request);
        }

        if ($request->cookie('device_guid') !== null) {
            return $next($request);
        }

        if (Configuration::first('disable_2fa')->disable_2fa) {
            return $next($request);
        }

        return redirect()->route('verify.index');
    }
}
