#!/usr/bin/env php
<?php

/**
 * Guard: fail fast if ELECTRON_RUN_AS_NODE is set.
 * When set, Electron runs as a Node.js process instead of spawning the app,
 * which causes native:build to hang on a locked .asar file.
 */

$envVar = 'ELECTRON_RUN_AS_NODE';

if (isset($_SERVER[$envVar]) || getenv($envVar) !== false) {
    fwrite(STDERR, "ERROR: {$envVar} is set. Unset it before running native:build.\n");
    fwrite(STDERR, "       This variable causes Electron to run as Node.js, which hangs the build.\n");
    exit(1);
}

exit(0);