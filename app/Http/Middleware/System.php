<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\File;

class System
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {

        if ($request->is('system-config-99*') || $request->is('system-config-un-99*')) {
            return $next($request);
        }

        if (File::exists(storage_path('framework/system_lock.txt'))) {
            abort(503, 'Application Under Maintenance');
        }

        return $next($request);
    }
}
