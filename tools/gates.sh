#!/usr/bin/env bash
# The release gate, one step per line, in the order AGENTS.md §9's hand-off sequence names.
#
# Why a shell script and not a `composer` script: Composer runs a script's children with
# whatever stdin its own caller had, and the agent `terminal` tool supplies a *closed* stdin.
# `artisan test` is the only step that opens stdin, so it aborts with `stdin is not a tty` and
# Composer reports the whole script failed, while every step passes when run any other way.
# Redirecting stdin per step makes the gate behave the same for a human and an agent.
#
#   bash tools/gates.sh           every check
#   bash tools/gates.sh --fast    everything except the PHP suite
#
# Exit: 0 green, 1 a check failed, 2 could not enter the repo root.

set -uo pipefail
cd "$(dirname "$0")/.." || exit 2

fast=0
[ "${1:-}" = "--fast" ] && fast=1
failed=()

step() {
    local label="$1"; shift
    printf '\n=== %s ===\n' "$label"
    "$@" < /dev/null
    local code=$?
    [ "$code" -ne 0 ] && failed+=("$label:$code")
    return 0
}

# collect(): the files this checkout has actually touched, as explicit paths.
#
# `pint --test` with no path list is the KI-75 defect: it collects every file that differs from
# HEAD, which on a shared worktree is every peer's in-flight edit as well as your own, and it
# reports their style errors as yours. `AGENTS.md` §9 forbids `pint --dirty` for the same reason.
# The set here is `git diff --name-only HEAD` (tracked changes, staged or not) plus untracked
# files, filtered to the extensions the tools under it read. An empty result is not a failure:
# a clean tree has nothing for pint to check, and `pint --test` with no arguments is exactly what
# this script exists to avoid.
collect() {
    {
        git -c advice.statusHints=false diff --name-only HEAD 2>/dev/null
        git -c advice.statusHints=false ls-files --others --exclude-standard 2>/dev/null
    } | grep -E '\.(php|blade\.php)$' | sort -u
}

step "lore (docs)"     php tools/lore.php
step "lore (code)"     php tools/lore.php code

# pint takes the collected set as arguments, so a peer's dirty file cannot fail this
# checkout's gate.
#
# phpstan must NOT be scoped. Measured 2026-11-18 at HEAD 816e923e on this host:
#   repo-wide                    -> [OK] No errors
#   one UNMODIFIED test file      -> [ERROR] Found 32 errors
#   ...with --level=6             -> [ERROR] Found 32 errors
#   ...with --level=6 + --configuration -> [ERROR] Found 32 errors
# Every error is `Call to an undefined method Pest\PendingCalls\TestCall::get()`: Pest's
# stub types arrive through whole-project discovery, so a scoped run resolves test calls
# against a bare TestCall and invents failures on files nobody touched. `--level` cannot
# reach it, and `phpstan.neon`'s `paths: [app]` does not stop it. A gate that reports
# invented failures gets ignored, which is worse than a slow gate, so phpstan keeps the
# whole-project scope the §9 hand-off sequence names.
# Details: .scratch-uma/hermes-audit-20261118/phpstan-scope-finding.md
php_files=$(collect)
if [ -n "$php_files" ]; then
    # shellcheck disable=SC2086
    step "pint"        php vendor/bin/pint --test --format agent $php_files
else
    printf '\n=== pint ===\nno changed PHP files; nothing to check\n'
fi
step "phpstan"     php vendor/bin/phpstan analyse --no-progress --memory-limit=1G

step "typecheck"       npm run typecheck
step "ui-lint"         node tools/ui-framework-check.mjs
step "markdown fences" python tools/md_fence_check.py

# The two checks the pre-repair script omitted. Each is a gate `AGENTS.md` §9 names and each is
# cheap: the citation ratchet and the untracked-file sweep. The ADR contract check is the
# `adr set` step below, which a peer added with its own G-61 note.
step "citation census" python tools/doc_census.py
step "untracked files" python tools/check_untracked.py
# Gate G-62 (GOVERNANCE.md §GATE-REGISTRY). Registered as automated on 2026-11-19 when the backdrop
# plates landed and absent from this script, so nobody could tell a passing plate from an unmeasured
# one. Deterministic, offline, and it reads the committed assets only, so it costs nothing here.
step "backdrop plates" python tools/verify-backdrop.py
# Gate G-61. `adr-check.py` was absent from this script while being a documented gate,
# so an ADR could land with no index row (or a one-sided authority relation) and every
# gate still reported green. It is deterministic and offline, so it belongs here.
step "adr set"         python tools/adr-check.py docs/adr
# N0.5. The register has three status surfaces (index, body headings, historical snapshots); the
# index at --index-lines 140 is the declared authority. Adding the auditor to gates.sh is what makes
# the parser fix in audit_status_register.py enforceable rather than merely available. The window is
# pinned because a note inserted above line 140 shifts a later `KI-57 CLOSED` row out of scope and
# lets a prose `KI-57 onward.` line masquerade as the status (measured 2026-11-20).
step "status register" python tools/audit_status_register.py KNOWN-ISSUES.md --index-lines 140

# Dependency audits. AGENTS.md §5 makes these a PRE-TAG obligation ("clean of reachable
# critical/high before tagging"), not a per-change one, so they report and name their finding
# without reddening a working slice. `--json` is parsed so the number is stated, not implied.
composer_bin=(composer)
if ! composer --version >/dev/null 2>&1; then
    phar="/c/Users/exatf/AppData/Local/ComposerSetup/bin/composer.phar"
    if [ -f "$phar" ]; then composer_bin=(php "$phar"); fi
fi
printf '\n=== composer audit (pre-tag gate) ===\n'
if "${composer_bin[@]}" --version >/dev/null 2>&1; then
    "${composer_bin[@]}" audit --no-interaction || printf 'composer audit: advisories above are advisory until tag time.\n'
else
    printf 'SKIPPED: no working composer on this host (see KI-4).\n'
fi

printf '\n=== npm audit --omit=dev (pre-tag gate) ===\n'
node -e '
const {execSync}=require("child_process");
let out="{}";
try { out=execSync("npm audit --omit=dev --json",{encoding:"utf8",stdio:["ignore","pipe","ignore"]}); }
catch(e){ out=e.stdout||"{}"; }
// Three states, not two. A scan that never ran must not report as clean: offline, proxy-blocked and
// registry-down runs are the normal case on this host, and JSON.parse("{}") has no metadata, so the
// !v.total branch used to print clean with no data behind it. AGENTS.md section 5 forbids exactly
// that claim, and C-8 makes the audit a pre-tag obligation, so an unmeasured run is named as such.
const d=JSON.parse(out||"{}");
const v=(d.metadata||{}).vulnerabilities||{};
const highs=Object.entries(d.vulnerabilities||{}).filter(([,a])=>a.severity==="high"||a.severity==="critical");
// A completed audit always carries metadata. The failure payload npm returns when the request
// itself dies is {message, error} and carries none, so the presence of metadata is what separates
// a measured zero from a scan that did not measure.
if(!d.metadata){ console.log("npm audit: UNAVAILABLE (scan failed, no data; not a clean result)"); console.log("  " + (d.message || "no diagnostic returned")); process.exit(0); }
if(!v.total){ console.log("npm audit: clean"); process.exit(0); }
console.log("npm audit: " + (v.critical||0) + " critical, " + (v.high||0) + " high, " + (v.moderate||0) + " moderate");
for(const [name,a] of highs) console.log("  " + name + ": " + a.severity + ", direct=" + a.isDirect);
const pkg=require("./package.json");
const prodVite=Object.keys(pkg.dependencies||{}).filter(k=>k.includes("vite"));
console.log("  production deps pulling vite: " + (prodVite.join(", ")||"none"));
console.log("  advisory until tag time (AGENTS.md section 5).");
'


[ "$fast" -eq 0 ] && step "php suite" php artisan test --compact

printf '\n=== summary ===\n'
if [ "${#failed[@]}" -eq 0 ]; then echo "GATES: all green"; exit 0; fi
printf 'GATES: %d failed: %s\n' "${#failed[@]}" "${failed[*]}"
exit 1
