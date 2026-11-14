<# 
.SYNOPSIS
Guard: fail fast if ELECTRON_RUN_AS_NODE is set.
When set, Electron runs as a Node.js process instead of spawning the app,
which causes native:build to hang on a locked .asar file.
#>

param()

if ($env:ELECTRON_RUN_AS_NODE) {
    Write-Error "ERROR: ELECTRON_RUN_AS_NODE is set. Unset it before running native:build."
    Write-Error "       This variable causes Electron to run as Node.js, which hangs the build."
    exit 1
}

exit 0