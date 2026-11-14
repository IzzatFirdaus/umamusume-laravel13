#!/bin/sh
# Guard: fail fast if ELECTRON_RUN_AS_NODE is set.
# When set, Electron runs as a Node.js process instead of spawning the app,
# which causes native:build to hang on a locked .asar file.

if [ -n "${ELECTRON_RUN_AS_NODE:-}" ]; then
    echo "ERROR: ELECTRON_RUN_AS_NODE is set. Unset it before running native:build." >&2
    echo "       This variable causes Electron to run as Node.js, which hangs the build." >&2
    exit 1
fi

exit 0