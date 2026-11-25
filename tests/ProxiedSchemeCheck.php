<?php

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;

/*
 * Trusted-proxy / scheme-handling self-check (Trusted-proxy integration, 2026-11-23).
 *
 * Proves the single-line trustProxies contract in bootstrap/app.php:
 *   - loopback proxy hop may assert X-Forwarded-* (Cloudflare Tunnel case)
 *   - LAN clients may NOT forge scheme/host through forwarded headers
 *   - plain localhost requests are untouched
 *
 * Run: php tests/ProxiedSchemeCheck.php
 */

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Kernel::class);

function handle(array $server = []): Request
{
    global $kernel;

    $request = Request::create('http://127.0.0.1:8000/', 'GET', [], [], [], $server);
    $request->headers->set('X-Forwarded-Proto', 'https');
    $request->headers->set('X-Forwarded-Host', 'tunnel.example.trycloudflare.com');

    $kernel->handle($request);

    return $request;
}

$checks = [
    'loopback proxy asserts scheme/host' => (function () {
        $r = handle(['REMOTE_ADDR' => '127.0.0.1']);

        return $r->getScheme() === 'https'
            && $r->getHost() === 'tunnel.example.trycloudflare.com';
    })(),
    'LAN client cannot forge scheme/host' => (function () {
        $r = handle(['REMOTE_ADDR' => '192.168.1.50']);

        return $r->getScheme() === 'http'
            && $r->getHost() === '127.0.0.1';
    })(),
    'plain localhost untouched' => (function () {
        global $kernel;

        $r = Request::create('http://127.0.0.1:8000/', 'GET');
        $kernel->handle($r);
        $url = url('build/assets/app.js');

        return str_starts_with($url, 'http://127.0.0.1:8000/');
    })(),
];

$failures = 0;
foreach ($checks as $name => $ok) {
    echo ($ok ? 'PASS' : 'FAIL').': '.$name.PHP_EOL;
    $ok || $failures++;
}

exit($failures === 0 ? 0 : 1);
