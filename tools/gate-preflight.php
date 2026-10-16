<?php

declare(strict_types=1);

/**
 * Gate pre-flight: trivial SQLite read against dev database.
 * Times one indexed read of training_runs to detect box contention.
 *
 * - Reads only. No write transaction, WAL checkpoint, or modification.
 * - KI-74: if > a few seconds, box is contended and broad sweeps are void.
 *
 * Threshold is documented as ~a few seconds (>= 2000ms) per dispatch spec.
 * Adjust only with owner approval; alternative (multiple of baseline) noted in register.
 */
$devDb = __DIR__.'/../database/database.sqlite';

if (! file_exists($devDb)) {
    fwrite(STDERR, 'gate-preflight: dev database not found: '.$devDb.PHP_EOL);
    exit(2);
}

try {
    $pdo = new PDO('sqlite:'.$devDb);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_TIMEOUT, 10); // seconds
    // Read-only: ensure no writes
    $start = microtime(true);
    $stmt = $pdo->query('SELECT id FROM training_runs LIMIT 1');
    $stmt->fetch();
    $elapsedMs = (microtime(true) - $start) * 1000;
} catch (Throwable $e) {
    fwrite(STDERR, 'gate-preflight: '.$e->getMessage().PHP_EOL);
    exit(3);
}

$thresholdMs = 2000.0; // ~a few seconds as specified
$elapsedRounded = round($elapsedMs, 2);

fwrite(STDOUT, sprintf('gate-preflight: training_runs LIMIT 1 in %.2f ms (threshold %d ms)', $elapsedRounded, (int) $thresholdMs).PHP_EOL);

if ($elapsedMs >= $thresholdMs) {
    // Contended
    fwrite(STDERR, 'gate-preflight: CONTENDED - elapsed >= threshold; broad sweep is void'.PHP_EOL);
    exit(1);
}

// Quiet
exit(0);
