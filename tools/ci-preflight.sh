#!/usr/bin/env bash
set -euo pipefail

echo "==> actionlint"
actionlint

echo "==> composer check-platform-reqs"
composer check-platform-reqs

echo "==> npm ci --dry-run"
npm ci --dry-run

echo "==> npm run plugin:build --dry-run"
npm run plugin:build --dry-run

echo "==> verify action runs.using = node24"
gh api /repos/:owner/:repo/actions/workflows/release.yml \
  --jq '.jobs.build.steps[] | select(.uses == "actions/setup-node@v5") | .with["node-version"]' \
  | grep -qx '24'

echo "==> pre-flight complete"
