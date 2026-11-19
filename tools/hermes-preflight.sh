#!/usr/bin/env bash
set -euo pipefail

echo "=== 1. actionlint ==="
actionlint .github/workflows/*.yml || { echo "❌ actionlint failed"; exit 1; }

echo "=== 2. PHP platform requirements ==="
# Some hosts carry a broken composer wrapper phar ("Could not open input file:
# composer.phar"). The check still runs wherever composer is usable — CI runs it
# through setup-php — and warns instead of blocking a local push here.
if composer --version >/dev/null 2>&1; then
  composer check-platform-reqs --no-dev || { echo "❌ Missing PHP extension"; exit 1; }
else
  echo "⚠️  composer CLI unusable on this host — platform check runs in CI (setup-php)"
fi

echo "=== 3. Composer scripts referenced in workflows exist ==="
for script in pre-native-build; do
  if ! grep -q "\\\"$script\\\"" composer.json; then
    echo "❌ composer.json missing script: $script"
    exit 1
  fi
done

echo "=== 4. Workflow files with workflow_dispatch/workflow_run are on origin/master ==="
for wf in .github/workflows/*.yml; do
  if grep -qE 'workflow_dispatch:|workflow_run:' "$wf"; then
    wf_path="${wf#./}"
    if ! git cat-file -e "origin/master:$wf_path"; then
      echo "❌ $wf_path has workflow_dispatch/workflow_run but is NOT on origin/master"
      echo "   It will not be registered by GitHub Actions. Merge to master first."
      exit 1
    fi
  fi
done

echo "=== 5. Node/TypeScript check ==="
npm run typecheck || { echo "❌ TypeScript check failed"; exit 1; }

echo "=== 6. PHP lint ==="
# Never a bare `pint --test` here. With no path list it collects every file that differs from HEAD,
# which on a shared worktree is every peer's in-flight edit as well as the caller's, and it reports
# their style errors as this checkout's (KI-75, AGENTS.md §9). `tools/gates.sh` already owns the
# correct semantics — it collects the changed set explicitly and keeps phpstan whole-project — so
# delegate rather than reimplementing a third opinion.
bash tools/gates.sh --fast || { echo "❌ Gate failed"; exit 1; }

echo "=== 7. npm ci dry-run ==="
npm ci --dry-run || { echo "❌ npm ci dry-run failed"; exit 1; }

echo "=== 8. plugin build script exists ==="
if ! grep -q '"plugin:build"' nativephp/electron/package.json; then
  echo "❌ nativephp/electron/package.json missing script: plugin:build"
  exit 1
fi

echo "=== 9. GitHub Actions node24 in build workflow ==="
if ! git show origin/master:.github/workflows/nativephp-windows.yml | grep -q "node-version: '24'"; then
  echo "❌ node24 not found in nativephp-windows.yml on origin/master"
  exit 1
fi

echo "✅ All pre-flight checks passed."
