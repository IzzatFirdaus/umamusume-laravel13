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
