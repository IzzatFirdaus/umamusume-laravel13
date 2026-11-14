# Agent Controls — Maintenance Runbook

The control layer enforces "use the dedicated tool, not the shell" across eight
runtimes. Runtime updates (Codex upgrades, Hermes schema changes, MiniMax
permission-engine bumps, Kilo config-schema evolution) can silently break a
gate, and **a broken gate looks identical to a working gate from the agent's
point of view**. This runbook is the response procedure.

State of record: `.tooling/REVIEW-LOG.md` (what was applied, at what evidence
level, when). Targets and strategies: `.tooling/host-targets.json`. Applier:
`.tooling/scripts/bootstrap-host.mjs` (idempotent; backs up every file before
writing; never prints secret values).

---

## 1. How to know something broke

Each gate has a specific silent-failure signature. Run the probe in column 3
whenever a runtime updates, or whenever an agent is observed doing something
the gate should have blocked.

| Runtime | Broken-gate symptom | Failure signature to check | Probe |
|---|---|---|---|
| Claude Code | Agent runs `Get-Content`/`cat` in Bash instead of being denied | Hook crashed or import broke (node exit 1 = **allow**); policy table failed to load | Simulated stdin (below) — expect exit 2 |
| Qoder | Same as above | `~/.qoder/settings.json` or in-tree `.qoder/settings.json` hook entry removed by a Qoder update; hook file path unresolvable | Simulated stdin (below) — expect exit 2 |
| Codex | `codex exec "cat composer.json"` returns file contents instead of DENIED | **Unknown field in `~/.codex/hooks.json`** — Codex rejects the WHOLE hooks file on one bad field (warning on stderr: `failed to parse hooks config ... unknown field`), silently disabling PostToolUse/Stop too | Run the live probe (below); watch stderr for the parse warning |
| Codex (2) | Prompt to re-approve hooks, or hooks not firing after an edit | Trust hash invalidated: `config.toml [hooks.state]` rows key the hook file's byte content; any edit to a command invalidates the row | Compare the hooks.json mtime/content against the `[hooks.state]` row; re-approve via Codex's own prompt |
| Hermes | Agent shell-reads instead of being denied | Allowlist entry missing or stale (`script_mtime_at_approval` older than the adapter's mtime); adapter not approved after an edit | Check `~/AppData/Local/hermes/shell-hooks-allowlist.json` for the `pre_tool_call` row; re-approve on next live prompt |
| Kilo CLI | Agent shell-reads instead of being denied | `.kilo/kilo.jsonc` renamed/moved, or Kilo schema changed the `permission` key | `kilo debug config` — the resolved `permission` map must show `"*": "deny"` + the four allow rows |
| DSH/Antigravity | DSH agent shell-reads without denial | `cordis.patch.yml` bridge row removed by a profile update; `dsh-hooks.json` path wrong (configPath is launch-cwd-relative — must stay absolute) | Confirm the `dsh-bridge-hooks-umamusume` row still exists in `~/.dsh/profiles/desktop/cordis.patch.yml` |
| MiniMax | Agent shell-reads without a deny | `~/.minimax/permission.json` reset by an app update (v2 schema rewrite); deny rules dropped | Check `deny` array still has the 14 `command`-matcher rules (list in REVIEW-LOG Slice I) |
| MiniMax (2) | Compound command (`&&`, `;`, pipe) executes instead of asking | **Not a regression** — the separator guard routes compound commands to ask, not deny; this is the documented residual gap | None (behavioral; see REVIEW-LOG Slice I) |
| VS Code | Kilo panel auto-approves tool calls in this repo | `.vscode/settings.json` deleted or `kilo-code.new.autoApprove.enabled` re-enabled | File check; the key must be `false` |
| MCP catalog | Agent reports missing/timeout MCP tools; tool count drifts | A runtime update re-added pruned servers, or a server cold-start regressed | `node .tooling/mcp-count.mjs` — must stay at 3 servers / 45 tools |

Two universal rules:

- **node exit 1 from a hook = ALLOW, not block.** Hook runtimes treat non-2
  exits as pass-through. A crashed adapter is a disabled gate. This is why the
  adapters fail closed on parse errors (exit 2) — preserve that property in
  any edit.
- **Codex rejects the entire hooks.json on one unknown field.** A bad merge
  doesn't just fail to add a hook; it strips the existing PostToolUse/Stop
  hooks too. After ANY hand edit of `~/.codex/hooks.json`, run the Codex live
  probe and read stderr for the parse warning.

---

## 2. How to re-verify each gate

Run from the repo root. Every command below was executed during the
implementation and its evidence is in `.tooling/REVIEW-LOG.md`.

```powershell
# Claude Code adapter (simulated stdin; expect exit 2 + "Blocked" message)
'{"tool_name":"Bash","tool_input":{"command":"Get-Content composer.json"}}' | node .claude/hooks/claude-pretooluse.mjs; echo "exit=$LASTEXITCODE"
# pass case (expect exit 0)
'{"tool_name":"Bash","tool_input":{"command":"git status"}}' | node .claude/hooks/claude-pretooluse.mjs; echo "exit=$LASTEXITCODE"
# payload-shape matrix (object/array/missing must ALL exit 2 - fail-closed)
'{"tool_name":"bash","tool_input":{"args":{"command":"cat composer.json"}}}' | node .claude/hooks/claude-pretooluse.mjs; echo "exit=$LASTEXITCODE"

# Qoder adapter (same shape; expect exit 2 / 0)
'{"tool_name":"Bash","tool_input":{"command":"Get-Content composer.json"}}' | node .claude/hooks/qoder-pretooluse.mjs; echo "exit=$LASTEXITCODE"

# Codex adapter (dual-shape; expect exit 2 / 0)
'{"tool":"Bash","arguments":{"command":"cat package.json"}}' | node .claude/hooks/codex-pretooluse.mjs; echo "exit=$LASTEXITCODE"

# Codex LIVE (the only unambiguous proof; expect DENIED, and NO stderr parse warning)
& "$env:LOCALAPPDATA\OpenAI\Codex\bin\e78bad9887fa50cf\codex.exe" exec --skip-git-repo-check "Run the shell command: cat composer.json. Reply with only DENIED if blocked."

# Hermes adapter (expect exit 2 with JSON block body / 0)
'{"hook_event_name":"pre_tool_call","tool_name":"terminal","tool_input":{"args":"cat composer.json"}}' | node .claude/hooks/hermes-pretooluse.mjs; echo "exit=$LASTEXITCODE"
# Hermes allowlist still current?
node -e "const j=require(process.env.USERPROFILE+'/AppData/Local/hermes/shell-hooks-allowlist.json'); console.log(j.approvals.map(a=>a.event+' -> '+a.command))"

# Kilo (expect the permission map with "*": "deny")
& "$env:USERPROFILE\.antigravity-ide\extensions\.944fdd87-341e-40ab-b469-c960212bf2da\bin\kilo.exe" debug config
# (kilo.exe path changes with extension version - find via the extensions dir)

# DSH bridge row still present? (expect the dsh-bridge-hooks-umamusume row)
Select-String -LiteralPath "$env:USERPROFILE\.dsh\profiles\desktop\cordis.patch.yml" -Pattern "dsh-bridge-hooks-umamusume"

# DSH adapter against DSH-shaped payloads (expect exit 2 / 0)
'{"tool_name":"bash","tool_input":{"command":"cat composer.json"}}' | node .claude/hooks/claude-pretooluse.mjs; echo "exit=$LASTEXITCODE"
# NOTE: this is stdin-level only. DSH runtime-fire remains UNVERIFIED
# (no headless CLI). A live denial inside a DSH session is the only
# promotion path - see REVIEW-LOG "Slice G addendum".

# MiniMax deny rules still present? (expect the 14 command patterns)
node -e "const j=require(process.env.USERPROFILE+'/.minimax/permission.json'); console.log('deny rules:', j.deny.length)"
# NOTE: rule-engine-verified only (1:1 simulation of the shipped matcher);
# a live MiniMax session denial is the promotion path.

# Kilo permission file + VS Code workspace gate
Get-Content .kilo/kilo.jsonc
Get-Content .vscode/settings.json

# MCP catalog (expect 3 servers / 45 tools; more means a runtime re-added pruned servers)
node .tooling/mcp-count.mjs
```

---

## 3. How to re-apply after a runtime update

```powershell
node .tooling/scripts/bootstrap-host.mjs
```

The bootstrap is idempotent: it prints `applied <id>` per changed target (with
a `.bak-<timestamp>` beside the file) or `skip <id> (unchanged)`. A second run
must report **0 targets changed** — if it reports changes again, the merge is
not converging; stop and diff before a third run.

What to inspect when it reports changes it shouldn't:

- **`applied codex-hooks`** on every run means something is churning the file —
  each write invalidates the Codex trust hash in `config.toml [hooks.state]`.
  The marker is `codex-pretooluse.mjs`; if an update rewrote the command with a
  different path or quoting, the marker check can't recognize it. Diff against
  the newest `~/.codex/hooks.json.bak-*` and align the command form.
- **`applied qoder-*`** on every run means Qoder rewrote its settings.json in a
  different key order or formatting. The bootstrap compares parsed objects, so
  this only happens if a VALUE actually changed — inspect what Qoder changed
  before letting the merge win.
- **`skip codex-config-toml (strategy set-key ...)`** is expected: the
  `delegation.max_depth` target was never converted (verify-at-runtime note in
  host-targets.json). Hermes edits were applied once via exact-line surgery and
  recorded as `noop` in the manifest — they are NOT bootstrap-replayable; if
  Hermes resets `config.yaml`, re-apply from the notes in host-targets.json
  (hermes-config entry) rather than expecting the bootstrap to do it.
- Any target reporting `applied` that you did not expect after a runtime update
  means the runtime REMOVED a control — that is the signal, not noise. Re-read
  the REVIEW-LOG entry for that target before accepting the re-apply.

Secrets: the bootstrap never prints values, only names (server names, hook
ids, backup paths). The `copy-servers` strategy (Qoder MCP) reads API keys from
`~/.gemini/config/mcp_config.json` at runtime — if that source file is deleted,
`qoder-mcp-servers` will report `skip ... source has no mcpServers`; restore
the source or re-add keys manually to `~/.qoder/settings.json`.

---

## 4. Known stale-state risks

Each residual risk from REVIEW-LOG, with the condition that turns it into a
real regression:

| Risk | Current state | Becomes a real regression when… |
|---|---|---|
| DSH runtime-fire unverified | Bridge row + committed `dsh-hooks.json` in place; stdin-level denial proven | A DSH agent session is used as a primary working runtime WITHOUT a live-fire check first. Promotion path: run `cat composer.json` in a DSH session, observe denial, update REVIEW-LOG. If DSH's hooks bridge turns out not to fire (e.g. the plugin bundle isn't loaded in the desktop profile), the DSH gate is void — treat DSH as ungateable until a native mechanism ships. |
| MiniMax live-fire unverified | 14 deny rules in `permission.json`; matcher logic copied 1:1 from the shipped binary | A MiniMax app update changes `selectMatchingRule` semantics (deny precedence, prefix match, or the separator guard). Re-run the Slice I probe against the new binary (string-search `selectMatchingRule` in the new asar) and re-verify `verify-deny.mjs` assumptions. Promotion path: a live session denial. |
| MiniMax compound-command → ask | Separator guard routes `&&`/`;`/pipe commands to the interactive ask flow, not deny | Only if MiniMax's ask flow is bypassed or auto-approved (`subagent_auto_approve`-style settings). Ask is still a gate; a hard deny for compound commands would require a matcher kind the current schema doesn't offer. |
| Kilo integrated-terminal bypass | Permission block covers agent-issued commands only | An agent (or user) types shell commands into the integrated terminal — those bypass the permission layer entirely. Structural; no in-repo fix. Compensating control: the shell-level hooks of the hosting IDE (Qoder/Antigravity gates). |
| Codex trust-hash churn | Marker-based idempotency prevents gratuitous hooks.json writes | Any manual or third-party edit to `~/.codex/hooks.json` invalidates `[hooks.state]` rows. If Codex prompts to re-approve hooks after a bootstrap run, that is expected — approve once; do not disable hooks. If Codex silently drops hooks (no prompt), check for a stderr parse warning (section 1, Codex row). |
| DSH/MiniMax deny coverage of compound commands | Both route separators to ask/interactive | If an agent runtime auto-approves ask-flow decisions in batch/cron modes, compound read commands execute un-denied. Condition: enabling any auto-approve for ask decisions in those runtimes. |
| `.tooling/logs/delegation.jsonl` empty | No SubagentStart event has fired since the logging hook landed | The delegation-rate metric (see `measure-behavior.mjs`) has an unknown baseline. Not a gate regression — a measurement gap. Fills automatically as Claude Code/Qoder/DSH sessions delegate. |

---

## 5. When this runbook itself is stale

The evidence levels in REVIEW-LOG are dated 2026-11-14. After any promotion
(DSH live-fire, MiniMax live-fire) or regression fix, append to REVIEW-LOG —
do not rewrite history there; it is append-style. Update the FINAL STATE
scoreboard's Evidence column only alongside a dated note.

Version-specific facts that WILL drift (re-derive, don't trust):
- Codex CLI path: `%LOCALAPPDATA%\OpenAI\Codex\bin\<hash>\codex.exe`
- Kilo CLI path: the Antigravity extensions dir `<hash>\bin\kilo.exe`
- DSH profile path: `~/.dsh/profiles/desktop/`
- Tool counts (45) and server list (laravel-boost, filesystem, playwright)
