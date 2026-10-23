#!/usr/bin/env bash
set -euo pipefail

php -m | grep -qi pdo_sqlite || { echo "FAIL: pdo_sqlite missing"; exit 1; }
grep -q '^APP_KEY=base64:' .env || { echo "FAIL: APP_KEY empty"; exit 1; }
php artisan migrate:status >/dev/null 2>&1 || { echo "FAIL: migrations not applied"; exit 1; }

php artisan serve --host=127.0.0.1 --port=8000 >/tmp/trainer-desk-serve.log 2>&1 &
pid=$!
trap 'kill "$pid" 2>/dev/null || true' EXIT

code=000
for _ in $(seq 1 20); do
  code=$(php -r '$h=@get_headers("http://127.0.0.1:8000/up"); echo is_array($h)?substr($h[0],9,3):"000";')
  [ "$code" = "200" ] && break
  sleep 1
done

[ "$code" = "200" ] || {
  echo "FAIL: GET /up returned $code"
  cat /tmp/trainer-desk-serve.log
  exit 1
}

echo "PASS: container boots and serves 200"
