<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnforceNativePHPSecret
{
    public function handle(Request $request, Closure $next): Response
    {
        // `env()` is a `config/`-only call in this repo (AGENTS.md §7); the key is published there as
        // `nativephp.secret`. Reading it through `config()` also keeps it cacheable.
        $secret = config('nativephp.secret');

        if (! $secret) {
            return $next($request);
        }

        $provided = $request->header('X-NativePHP-Secret') ?? $request->cookie('_php_native');

        if (! $provided || ! hash_equals($secret, $provided)) {
            return response('', 403);
        }

        return $next($request);
    }
}
