<?php

declare(strict_types=1);

/*
 * Harness-only router for the PHP built-in server the browser suite starts.
 *
 * `php artisan serve` is deliberately NOT used here. On Windows, ServeCommand
 * rebuilds the child process environment from `$_ENV` through a passthrough
 * whitelist (`ServeCommand::$passthroughVariables`) that does not contain
 * DB_DATABASE, UMA_DATABASE_ROLE or UMA_EXPECTED_DATABASE. This host runs PHP
 * with `variables_order=GPCS`, so `$_ENV` happens to be empty and the child
 * currently inherits everything; that is an accident of php.ini, not a
 * contract. Starting `php -S` directly gives the serving process the exact
 * environment Playwright passed, with no framework filtering in between.
 *
 * This file lives under tests/ and is never referenced by the application, so
 * the identity probe below does not exist in a production-facing web root. It
 * answers one GET only, to a loopback client, from a process that declared the
 * browser role and carries this run's token. It reports a digest of the
 * resolved database path and two booleans - never a filesystem path, an
 * environment dump or a stack trace. A wrong or unconfigured server returns
 * 404 with no identity at all, which is what the preflight asserts against.
 *
 * Every other request is delegated to Laravel's public front controller with
 * the same static-file behaviour as Illuminate's own development router.
 */

$repoRoot = dirname(__DIR__, 2);
$publicPath = $repoRoot.'/public';
$uri = urldecode(parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?? '');

if ($uri === '/__trainerdesk_browser_identity') {
    $remote = (string) ($_SERVER['REMOTE_ADDR'] ?? '');
    $isLoopback = in_array($remote, ['127.0.0.1', '::1'], true);
    $isGet = strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET')) === 'GET';

    $roleValue = $_SERVER['UMA_DATABASE_ROLE'] ?? $_ENV['UMA_DATABASE_ROLE'] ?? getenv('UMA_DATABASE_ROLE');
    $role = is_string($roleValue) ? strtolower($roleValue) : '';

    $tokenValue = $_SERVER['UMA_BROWSER_IDENTITY_TOKEN'] ?? $_ENV['UMA_BROWSER_IDENTITY_TOKEN'] ?? getenv('UMA_BROWSER_IDENTITY_TOKEN');
    $token = is_string($tokenValue) ? $tokenValue : '';

    header('Content-Type: application/json');
    header('Cache-Control: no-store');

    if (! $isLoopback || ! $isGet || $role !== 'browser' || $token === '') {
        http_response_code(404);
        echo json_encode(['probe' => 'trainerdesk-browser-identity-v1', 'available' => false]);

        return;
    }

    // Boot the application so the digest comes from the same DatabaseGuard the
    // app uses, rather than from a second path-resolution implementation here.
    require_once $repoRoot.'/vendor/autoload.php';

    $app = require $repoRoot.'/bootstrap/app.php';
    $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

    /** @var \App\Services\DatabaseSafety\DatabaseGuard $guard */
    $guard = $app->make(\App\Services\DatabaseSafety\DatabaseGuard::class);
    $resolved = $guard->resolvedPath();

    $expectedValue = $_SERVER['UMA_EXPECTED_DATABASE'] ?? $_ENV['UMA_EXPECTED_DATABASE'] ?? getenv('UMA_EXPECTED_DATABASE');
    $expected = is_string($expectedValue) ? $expectedValue : '';

    http_response_code(200);
    echo json_encode([
        'probe' => 'trainerdesk-browser-identity-v1',
        'available' => true,
        'role' => $role,
        'runToken' => $token,
        'dbDigest' => hash('sha256', $resolved),
        'expectedMatches' => $expected !== '' && $guard->sameFile($resolved, $expected),
        'targetsCanonical' => $guard->targetsCanonical(),
        'serverPid' => getmypid(),
    ]);

    return;
}

if ($uri !== '/' && file_exists($publicPath.$uri)) {
    return false;
}

require_once $publicPath.'/index.php';
