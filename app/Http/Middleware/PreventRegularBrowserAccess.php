<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PreventRegularBrowserAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $secret = env('NATIVEPHP_SECRET');

        if (blank($secret)) {
            return $next($request);
        }

        $provided = $request->header('X-NativePHP-Secret')
            ?? $request->cookie('_php_native');

        if (! $provided || ! hash_equals($secret, (string) $provided)) {
            return response()->json(['error' => ['code' => 'FORBIDDEN', 'message' => 'Request secret required.']], 403);
        }

        return $next($request);
    }
}
