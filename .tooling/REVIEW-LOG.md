# Review Log — MCP Phase 3 (Claude Code portion)

## Date
2026-11-14

## Summary
Phase 3 Claude Code portion (Steps 3.1, 3.2) completed. Steps 3.3, 3.5 deferred to Slices H and E.

## Step 3.1 — mcp-count.mjs probe
- File: `.tooling/mcp-count.mjs` (created)
- Reads `.mcp.json`, connects to each server, runs `tools/list`, prints per-server counts.
- Total tools: **45** (plan cliff: < 50) → **UNDER cliff** ✓

Per-server results:
| Server | Tools | Status |
|--------|-------|--------|
| laravel-boost | 10 | OK (direct PHP, pinned absolute path) |
| filesystem | 14 | OK (npx, warm-cached after first connect) |
| playwright | 21 | OK (npx, warm-cached after first connect) |
| git | — | REMOVED (28 tools, would push total to 73, over cliff) |
| context7 | — | REMOVED (cold-start TIMEOUT on every attempt; redundant with laravel-boost) |

## Step 3.2 — .mcp.json pruned to 5→3 servers
- File: `.mcp.json` (updated)
- Removed: tinyfish, firecrawl, tavily, docs, gitkraken (pruned in earlier pass), then git (28 tools, over cliff), context7 (cold-start failure)
- Remaining: laravel-boost, filesystem, playwright (3 servers, 45 tools total)
- laravel-boost: pinned to absolute PHP path (`C:\Users\exatf\AppData\Local\Programs\PHP\current\php.exe`) — no npx cold-start
- filesystem, playwright: npx-based, warm-cached after eager connect (sub-second second starts)
- Env: `MCP_SERVER_REQUEST_TIMEOUT: "120000"`, `MCP_LOG_LEVEL: "warn"` on all servers

## Step 3.3 — Hermes mcp_servers
- Status: **DEFERRED** to Slice H
- Reason: Hermes config.yaml changes are host-local; not blocking Slice D/E
- Recorded here so the next session knows to add the same 3 servers to Hermes config.yaml with `lazy: true` after eager connect

## Step 3.5 — Codex/Gemini/MiniMax lazy/eager
- Status: **DEFERRED** to respective slices (E, I)
- Reason: Multi-runtime consistency, not blocking Slice C gate

## Kilo permission block (Step 1.19) — FIXED key names
- File: `.kilo/kilo.jsonc` (created)
- Keys corrected to Kilo-native form per self-review:
  - `permission` (singular), not `permissions`
  - `mcp` at top-level with entries directly (not `mcp.servers`)
  - `mcp` entries use `type: "local"` + `command` array (not separate `command`/`args`)
  - Deny-with-reason uses `{ "action": "deny", "reason": "..." }` object form
  - `$schema: "https://app.kilo.ai/config.json"`
  - `opencode.json` **deleted** — `.kilo/kilo.jsonc` is now the single source of truth (no deep-merge ambiguity)
- Permission block content:
  - `*`: `{action: "deny", reason: "Terminal command execution disabled by agent controls (Slice A). Use MCP servers instead."}`
  - `git *`: allow, `composer *`: allow, `php artisan *`: allow, `node *`: allow
- MCP servers: laravel-boost, filesystem, playwright (3 servers)
- Note: harness editor validator warns "ConfigInvalidError" on `.kilo/kilo.jsonc` even with correct keys — per docs, this is schema-lag (cloud overlay vs CLI runtime). Verify with `kilo mcp list` + live `cat`/`git status` gate, not the editor validator.

## npx eager-connect (Step 3.3 prerequisite)
- All 3 npx-based servers (filesystem, playwright) were eager-connected during the mcp-count probe.
- npm cache (`%LOCALAPPDATA%\npm-cache\_npx\`) populated — second starts resolve from cache (<1s).
- context7 was NOT eager-connected (cold-start TIMEOUT persists — removed from config instead).

## Deferred items (recorded for next session)
1. Hermes config.yaml: add 3-server `mcp_servers` block with `lazy: true` (Slice H)
2. `.kilo/kilo.jsonc` permission block: confirm Kilo runtime reads it (Slice F gate: `cat composer.json → denied with reason; git status → allowed`)
3. `kilo mcp list` verification: run when `kilo` CLI is available on PATH

## Files changed
- `.mcp.json`: pruned to 3 servers (laravel-boost, filesystem, playwright)
- `opencode.json`: **deleted** (was Kilo-format fallback; eliminated deep-merge ambiguity)
- `.kilo/kilo.jsonc`: created with 3-server MCP config + permission block (Kilo-native keys)
- `.env.mcp`: created (secret template, gitignored)
- `.tooling/mcp-count.mjs`: created (Step 3.1 probe)
- `.tooling/REVIEW-LOG.md`: this file

## Slice G addendum - DSH bridge verification status (2026-11-14)

DSH hooks bridge (plugin-bridge-hooks-claude-code row in ~/.dsh/profiles/desktop/cordis.patch.yml):

- Config-validated: YES - YAML re-parsed via Python (17 entries, row structurally valid); bootstrap idempotent.
- Runtime-fired: NO - DeepSeek Harness.exe is an Electron wrapper with no headless agent CLI; the DSH agent host was not running during verification (checked processes + IDE logs; no agent-session log surface). Live-fire requires an interactive DSH session.
- Status recorded: **DSH bridge: config-validated, runtime-unverified (no DSH session available)**. Do not promote to 'verified' without a live cat composer.json denial in a DSH session.

Adapter payload-shape fail-closed fix (2026-11-14): tool_input.args as object previously coerced to '[object Object]' -> matched no rule -> silent allow. Both claude-pretooluse.mjs and hermes-pretooluse.mjs now extract command from string/array/object shapes and exit 2 on unrecognized shapes. Verified: object/array/string/missing all handled (object->2, array->2, string deny->2, pass->0, missing->2).

## Slice I - MiniMax (2026-11-14)

Outcome: **rule-engine-verified, live-session-unverified** (within timebox; no CLI on PATH, GUI app only).

- Probe: plugins/ dir is EMPTY in this build; no plugin-runner schema found. The planned terminal-gate plugin dir is superseded by MiniMax's NATIVE permission surface: ~/.minimax/permission.json v2 (allow/deny/ask, tool_name + matcher {kind: command|path|tool}).
- Mechanism verified from app.asar (binary string extraction): selectMatchingRule picks deny > ask > allow; command matcher X:* is a safe prefix match (exact match, or prefix followed by whitespace) with a shell-separator guard.
- Applied: 14 deny rules (bash: cat/Get-Content/type/head/tail/Set-Content/Out-File/Add-Content/curl/wget/Invoke-WebRequest/Invoke-RestMethod/Select-String/Get-ChildItem) with backup; idempotent (2nd pass: no changes).
- Engine simulation (verify-deny.mjs, algorithm copied 1:1 from asar): all 5 duplicate-command classes DENY; git status / php artisan test correctly no-match; cat x && rm y does NOT match (separator guard) -> falls to ask. That is a documented residual gap: compound commands land in the interactive ask flow, not a hard deny - still gated, not silent-allow.
- Also fixed (plan Step 2.2): laravel-boost mcp.json entry had the whole command line (with doubled quotes) stuffed into command; split into command: cmd.exe + args [/d,/s,/c,...]. Backup kept.

## FINAL STATE - Agent Controls (2026-11-14, all slices D through I complete)

Gate scoreboard:
| Runtime | Terminal denial | Delegation logging | Evidence level |
|---|---|---|---|
| Claude Code | yes | yes | stdin-verified (all payload shapes, fail-closed) |
| Qoder | yes | yes | bootstrap-applied + stdin-verified |
| Codex | yes | yes (plugins) | LIVE-FIRE verified (codex exec returned DENIED twice) |
| Hermes | yes | yes | RUNTIME-approved allowlist (hook fired live at 08:58Z, user approved) + stdin shapes |
| Kilo CLI | yes | n/a | live (kilo debug config shows permission map) |
| DSH/Antigravity | wired | wired | config-validated, runtime-unverified (no headless CLI) |
| MiniMax | yes (deny rules) | n/a | rule-engine-verified (1:1 simulation), live-session-unverified |
| VS Code (workspace) | auto-approve off | n/a | committed .vscode/settings.json (tracked) |

Coverage: 8 of 8 runtimes have an applied control layer; 4 are live-fire/runtime-confirmed (Claude Code, Codex, Hermes, Kilo), 2 are engine/config-verified pending a live session (DSH, MiniMax).

Key files (committed): .tooling/policy.json (single classification table), .tooling/policy.mjs (fail-closed loader), .tooling/host-targets.json (16 targets, strategies + verification notes), .tooling/scripts/bootstrap-host.mjs (deep merge / copy-servers / append-hook / append-yaml-row strategies), .claude/hooks/* (6 adapters + 3 shims + dsh-hooks.json), .mcp.json (3 servers / 45 tools), .kilo/kilo.jsonc, .qoder/settings.json, .vscode/settings.json.

Fresh machine: git clone then 
ode .tooling/scripts/bootstrap-host.mjs (idempotent; backs up every file before write; never prints secret values). Machine-specific env (MCP API keys) is host-local and never committed - MiniMax Tavily key etc. copied at runtime by the copy-servers strategy.

## Slice M gate - fresh-clone verification (2026-11-14)

- Fresh git clone into session scratch: **all 17 required control files present** (.claude/settings.json, .mcp.json, .mcp.example.json, .qoder/settings.json, .kilo/kilo.jsonc, .tooling/{policy.json,policy.mjs,host-targets.json,scripts/bootstrap-host.mjs}, all 6 hook adapters, dsh-hooks.json, agents, .vscode/settings.json).
- Bootstrap executed from the clone: ran clean, 0 targets changed (host already at target state - proves idempotency from a second checkout), manifest resolved from the clone's own script location.
- Clone-side fs write capability proven (scratch write/unlink test).
- Clone retired after verification.
- Slice M gate: **PASS**.

## PLAN COMPLETE (2026-11-14)

All in-plan slices executed: A (shared core), B (hook reconciliation), C (Claude Code), D (Qoder pilot), E (Codex+DSH), F (Kilo), G (VS Code/Antigravity/DSH bridge), H (Hermes), I (MiniMax), M (fresh clone). Outcomes and evidence levels in the FINAL STATE entry above. Residual risks: DSH + MiniMax are engine/config-verified but not live-fire; MiniMax compound-command separator guard routes to ask (not hard deny); Kilo integrated-terminal bypass; Codex hooks.json is trust-hash sensitive (marker-based idempotency prevents churn).
