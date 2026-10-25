#!/usr/bin/env bash
# Screenshot Capture Wrapper
# Starts the Laravel server, runs the capture script, then stops the server

set -e

REPO_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$REPO_ROOT"

echo "╔══════════════════════════════════════════════════════════════╗"
echo "║  Frontend Screenshot Capture (with server management)        ║"
echo "╚══════════════════════════════════════════════════════════════╝"

# Check if server is already running on port 8000
if curl -s -o /dev/null -w "%{http_code}" http://localhost:8000/up 2>/dev/null | grep -q "200"; then
    echo "✓ Server already running on port 8000"
    SERVER_RUNNING=1
else
    echo "Starting Laravel server on port 8000..."
    php artisan serve --port=8000 > /tmp/laravel-server.log 2>&1 &
    SERVER_PID=$!

    # Wait for server to be ready
    echo "Waiting for server to be ready..."
    for i in {1..30}; do
        if curl -s -o /dev/null -w "%{http_code}" http://localhost:8000/up 2>/dev/null | grep -q "200"; then
            echo "✓ Server started (PID: $SERVER_PID)"
            SERVER_RUNNING=0
            break
        fi
        sleep 1
    done

    if [ "$SERVER_RUNNING" != "0" ]; then
        echo "✗ Server failed to start"
        kill $SERVER_PID 2>/dev/null || true
        exit 1
    fi
fi

# Run the capture script
echo ""
echo "Running screenshot capture..."
node tools/capture-screenshots.mjs
CAPTURE_EXIT=$?

# Stop server if we started it
if [ "$SERVER_RUNNING" = "0" ]; then
    echo ""
    echo "Stopping server (PID: $SERVER_PID)..."
    kill $SERVER_PID 2>/dev/null || true
fi

exit $CAPTURE_EXIT