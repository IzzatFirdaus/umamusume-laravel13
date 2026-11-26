# MCP Setup

Local, IDE-agnostic MCP configuration for this project. This file records where
each client actually reads its MCP servers, and the two Windows gotchas that
make a correct-looking config silently fail.

> Corrected 2026-10-12. The previous version assumed a single `.mcp.json` was
> read by "opencode and other MCP-capable clients". It is not: each client reads
> a different file in a different schema. See "Config locations" below.
>
> Updated 2026-11-26 for the cross-IDE Agent Chat rollout. The tracked
> workspace-root `.mcp.json` is the portable foundation for Claude Code and
> VS Code / GitHub Copilot; Cursor and Codex need local adapters because their
> project files are client-specific or machine-local. All adapters point at one
> shared mailbox. See "TrainerDesk Agent Chat — shared mailbox" below.

## Windows spawn rules (read this first)

A stdio MCP server is spawned without a shell. On Windows that breaks two
common commands:

- **Bare `npx` cannot be spawned.** `npx` is `npx.ps1` / `npx.cmd`, not an
  `.exe`; a shell-less spawn fails with "The system cannot find the file
  specified". Always wrap it:
  `"command": "cmd.exe", "args": ["/c", "npx", "-y", "<package>"]`.
- **Bare `php` depends on PATH.** It works only when the client inherits a
  PATH that contains PHP. Pin the interpreter instead:
  `C:\Users\exatf\AppData\Local\Programs\PHP\current\php.exe`.

Symptom when this is wrong: the client reports the server as failed or "timed
out after 30000ms" even though running the same command by hand connects in
2-14 seconds. That is a spawn failure, not a slow server.

Clients differ in how they spawn. Node-based clients (Kilo, VS Code, Copilot)
use a shell-less spawn that does not resolve `.cmd`, so bare `npx` fails
outright. Go-based clients (Crush) apply `PATHEXT` and may resolve it. The
`cmd.exe /c` wrapper and the pinned PHP path are the safe, uniform form for
every client.

Cold `npx` also downloads the package on first launch; give every local server
a generous timeout where the client supports one (`timeout`, `startup_timeout_sec`,
or `MCP_SERVER_REQUEST_TIMEOUT`).

## Enabled servers

| Name        | Package                              | Purpose                                     | Boundary                                        |
| ----------- | ------------------------------------ | ------------------------------------------- | ----------------------------------------------- |
| `filesystem`| `@modelcontextprotocol/server-filesystem` | Read/write files                      | One root argument: the project absolute path. Access outside it is refused by the server. |
| `git`       | `@cyanheads/git-mcp-server`          | Inspect history, status, diffs, blame       | `GIT_BASE_DIR` restricts every operation to the project tree. The server also exposes write tools (`git_commit`, `git_push`); never invoke them without an explicit user request. |
| `docs`      | `@upstash/context7-mcp`              | Retrieve indexed library/framework docs     | Read-only document retrieval. Optional `CONTEXT7_API_KEY` raises the anonymous rate limit. |
| `laravel-boost` | in-repo (`php artisan boost:mcp`)  | Laravel version-specific docs and code search | Project-local stdio server, no network. Installed via `laravel/boost` (require-dev). |
| `trainerdesk-chat` | pinned v2.0.0 Agent Chat (`server.py`) | Cross-IDE autonomous messaging | One stdio server, one absolute SQLite mailbox outside every worktree; no network, no auth identities. |
| `gitkraken` | `@gitkraken/gk`                      | Git history, issues, PRs, multi-repo workflows | Project-local `gk mcp` over stdio. Cloud tools (issues, PRs) need `gk auth login` first. |
| `sequentialthinking` | `@modelcontextprotocol/server-sequential-thinking` | Structured step-by-step reasoning | No env. |
| `memory`    | `@modelcontextprotocol/server-memory` | Persistent knowledge graph across sessions | `MEMORY_FILE_PATH` points at `%USERPROFILE%/.memory/memory.json`. |
| `playwright`| `@playwright/mcp`                    | Browser automation                          | Local stdio; drives a real Chromium. |
| `firecrawl` | `firecrawl-mcp`                      | Web crawl/extract                           | Needs `FIRECRAWL_API_KEY`. |
| `context7`  | `@upstash/context7-mcp`              | Hosted docs retrieval (same package as `docs`) | Needs `CONTEXT7_API_KEY` for higher limits. |
| `tavily`    | `tavily-mcp`                         | Web search                                  | Needs `TAVILY_API_KEY`. |
| `trainerdesk-chat` | in-repo (`python TrainerDesk-Messaging/server.py chat`) | Inter-agent messaging (room `trainerdesk`) | Local stdio; SQLite-backed store at `AGENT_CHAT_DB`. WorkBuddy host connector; not in project `.mcp.json`. See "Agent Chat MCP" below. |

Server names must be identical across every file that a single client reads;
clients that merge by name take last-write-wins.

## Agent Chat MCP (trainerdesk-chat)

`trainerdesk-chat` is a **local, asynchronous, SQLite-backed inter-agent messaging
server**. It is distinct from standardized A2A (Agent-to-Agent) protocols:

| Property | Agent Chat MCP (`trainerdesk-chat`) | Standardized A2A |
|----------|--------------------------------------|------------------|
| Transport | Local stdio (MCP) | HTTP / JSON-RPC (varies by spec) |
| Addressing | Room + author + recipients | Agent URL / DID |
| Delivery | Asynchronous — message persisted, recipient polls | Synchronous or push |
| Awakening | **Does not awaken idle agents** — recipient must poll | Push to active endpoint |
| Identity | Caller-supplied label (not authenticated) | Cryptographic or signed |

**Key distinctions:**

- **Asynchronous messaging ≠ automatic awakening.** Sending a message persists
  it; the recipient must retrieve it via `list_messages`. An idle agent is not
  woken by a new message.
- **Caller-supplied labels are not authenticated identities.** The `author` and
  `recipients` fields are coordination labels, not access control.
- **Messages do not expand authority.** A message cannot authorize an action
  the recipient's role prohibits.

**Configuration (WorkBuddy host):**

```json
"trainerdesk-chat": {
  "command": "C:\\Program Files\\Python312\\python.exe",
  "args": ["-I", "-B", "-X", "utf8",
           "C:\\Users\\exatf\\TrainerDesk-Messaging\\server.py", "chat"],
  "env": {
    "AGENT_CHAT_DB": "C:\\Users\\exatf\\TrainerDesk-Messaging\\messages.sqlite3"
  }
}
```

This is a WorkBuddy host-level connector (global `~/.workbuddy-ai/mcp.json`),
not a project-level MCP server. IDEs that read project `.mcp.json` will not see
it unless the same entry is added there.

**Tools exposed:** `list_rooms`, `list_messages`, `post_message`, `get_detail`.

**Incremental retrieval:** use `after_id` (set to the last seen `through_id`)
to fetch only new messages. Complete pagination with `cursor` + `next_cursor`
before advancing.

## Config locations (what each client actually reads)

| Client | Project file | Global file | Schema key |
| ------ | ------------ | ----------- | ---------- |
| WorkBuddy AI | none documented (UI Custom Connector) | `~/.workbuddy-ai/mcp.json` | `mcpServers` |
| Kilo (CLI + VS Code extension) | `kilo.json` (root), `opencode.json` (root, legacy) | `~/.config/kilo/kilo.json`, `~/.config/kilo/kilo.jsonc` | `mcp` |
| Claude Code | `.mcp.json` | `~/.claude.json` | `mcpServers` |
| VS Code / GitHub Copilot | workspace-root `.mcp.json` (native; experimental setting) and legacy `.vscode/mcp.json` | `%APPDATA%\Code\User\mcp.json` | `mcpServers` / `servers` |
| GitHub Copilot CLI | - | `~/.copilot/mcp-config.json` | `mcpServers` |
| opencode | `opencode.json` (root), `.opencode/opencode.json` | `~/.config/opencode/opencode.json(c)` | `mcp` |
| Cursor | `.cursor/mcp.json` | `~/.cursor/mcp.json` (absent) | `mcpServers` |
| Kiro | `.kiro/settings/mcp.json` | `%APPDATA%\Kiro\User\settings.json` (no MCP) | `mcpServers` |
| Trae | `.trae/mcp.json` | `%APPDATA%\Trae\User\...` | `mcpServers` |
| JetBrains Junie | `.junie/mcp/mcp.json` | - | `mcpServers` |
| Codex CLI / IDE extension | `.codex/config.toml` (trusted project only) | `~/.codex/config.toml` | `[mcp_servers.*]` |
| Grok | `.grok/config.toml` | - | `[mcp_servers.*]` |
| Qoder | - | `~/.qoder/settings.json` | `mcpServers` |
| Crush (Charm) | `crush.json` (root) | `%LOCALAPPDATA%\crush\crush.json` (no MCP) | `mcp` |

Workspace-root `.mcp.json` is confirmed by current Claude Code and Copilot
documentation. The older `.vscode/mcp.json` `servers` schema remains valid for
the editor-managed Local harness but is no longer the recommended destination.

**Kilo is the important exception.** Kilo does **not** read `.mcp.json`,
`.vscode/mcp.json`, `.opencode/opencode.json`, `.claude/`, `.cursor/`, or any
other client's file. It reads only the `mcp` key from `kilo.json`,
`kilo.jsonc`, or `opencode.json`. Servers that must work in Kilo have to be
declared in one of those, even if they are also declared elsewhere.

## Clients with no live MCP config

These locations exist but currently declare nothing, or the client stores MCP
state elsewhere:

- Antigravity: `%APPDATA%\Antigravity IDE\User\mcp.json` does not exist; its
  `settings.json` has no MCP block.
- Roo (inside Antigravity): `%APPDATA%\Antigravity IDE\User\globalStorage\rooveterinaryinc.roo-cline\settings\mcp_settings.json` is empty.
- KiloCode extension (inside Devin): `%APPDATA%\Devin\User\globalStorage\kilocode.kilo-code\settings\mcp_settings.json` is empty.
- Devin / Windsurf: `~/.codeium/windsurf/mcp_config.json` is absent; Devin's
  `settings.json` has no MCP block.
- Gemini CLI: no `gemini` executable is installed on PATH. The legacy
  `~/.gemini/config/mcp_config.json` holds unrelated servers but no
  `trainerdesk-chat`, and `~/.gemini/settings.json` is absent. No adapter was
  created for an uninstalled client.
- Claude Code global: `~/.claude.json` has only empty `mcpServers` objects.

## Test runner (no MCP server)

No language-agnostic restricted shell MCP exists on the npm registry
(`@modelcontextprotocol/server-shell` and community candidates all return 404).
The restricted command executor is implemented natively instead, in
`.opencode/opencode.json` under `permission.bash`: the built-in shell tool is
client-enforced to an allowlist of test commands only.

- `php artisan test*` (Pest suite, any filter)
- `vendor/bin/pest*`
- `composer test`
- `npm run test*`

Everything else stays on `ask`. This is enforced by the opencode client, not by
the agent's prompt, so it cannot be talked around.

## Adding or removing a server

1. Edit every file a client you use actually reads (see the table above). Keep
   the name and command identical across files.
2. Wrap `npx` as `["cmd.exe", "/c", "npx", "-y", "<package>"]` and pin PHP with
   the absolute path. Give the server a long timeout where supported.
3. Restart the client. Config is loaded at startup only.
4. Health-check: the server must answer a JSON-RPC `initialize` request over
   stdio before any tool call is trusted.

## Reminders

- Review every proposed file change, commit, and push before applying. The git
  server can write; the agent must not do so unprompted.
- The filesystem server is scoped to this project root only. Do not add
  additional roots unless the boundary should widen.
- **Secrets:** several of these files now hold API keys in plaintext
  (`FIRECRAWL_API_KEY`, `TAVILY_API_KEY`, `CONTEXT7_API_KEY`) in
  `.opencode/opencode.json`, `.trae/mcp.json`, and the global configs. The
  project files are gitignored; treat the global ones as sensitive too.

## Deviations from the requested package list

- `@modelcontextprotocol/server-git` and `@modelcontextprotocol/server-fetch`
  are unpublished (404 on npm; the reference servers were archived).
  Replaced with `@cyanheads/git-mcp-server` (maintained, Apache-2.0,
  STDIO + Streamable HTTP) and `@upstash/context7-mcp`.
- Test-runner MCP: see above, native allowlist instead of a shell MCP.

## TrainerDesk Agent Chat — shared mailbox

### Installed clients and adapter status (measured 2026-11-26)

| Client | Installed evidence | Adapter | Runtime evidence |
| ------ | ------------------ | ------- | ---------------- |
| WorkBuddy AI | This Expert session; Custom Connector UI | Existing user-level `~/.workbuddy-ai/mcp.json` entry, unchanged | Live: `post_message`, `list_messages`, `list_rooms`, `get_detail` used in-session |
| Claude Code 2.1.302 | `claude --version` | Tracked `.mcp.json` | `claude mcp list` / `mcp get` discover `trainerdesk-chat`; server shows "Pending approval", so tool execution is not yet verified in this session |
| Cursor 3.31.15 | `cursor --version` | Local `.cursor/mcp.json` from the adapter installer | Config schema and interpolation documented by Cursor; no non-interactive MCP CLI was available, so runtime is **documentation-confirmed only** |
| VS Code 1.148.0 + Copilot | `code --version`; Copilot shim present | Tracked workspace-root `.mcp.json` | Current Copilot docs confirm native workspace-root support; no non-interactive MCP CLI was available, so runtime is **documentation-confirmed only** |
| Codex CLI 0.171.0 | `codex --version` | Local `.codex/config.toml` from the adapter installer | `codex mcp list` shows `trainerdesk-chat` enabled with resolved executable, args and `AGENT_CHAT_DB` |
| Windsurf | No executable / config directory | none | Not installed; no adapter created |
| Gemini CLI | No `gemini` executable on PATH | none | Not installed; legacy `~/.gemini/config/mcp_config.json` is unrelated |

Kilo, opencode and Crush are also installed, but their config schemas differ and
they are outside this rollout's client list. No silent registration was added.

### Prerequisites, pinning and local environment

Agent Chat MCP v2.0.0 is already installed at
`%USERPROFILE%\TrainerDesk-Messaging`. It is a stdlib-only Python server; do not
reinstall, upgrade, reset or duplicate its database. Verified on this host:

- `server.py` SHA-256 `f6e38e35a75b6509ea4832b1ba2d91b3c023f85decb95c68cdc307156953a093`
- `LICENSE` SHA-256 `d11f47c929898fc21422ba497bdee527de6acfa16983373f9bd0251527d1d07e`
- Source pin: `863fe369a8daacb8305c2059fcec85f60f237fce`
- Interpreter: Python 3.12 at `%ProgramFiles%\Python312\python.exe` (the verified executable; no venv)
- Mailbox: `%USERPROFILE%\TrainerDesk-Messaging\messages.sqlite3`

Set these environment variables in each client's launcher environment:

| Variable | Meaning |
| -------- | ------- |
| `TRAINERDESK_CHAT_PYTHON` | Absolute path to the verified Python executable |
| `TRAINERDESK_CHAT_SERVER` | Absolute path to the pinned `server.py` |
| `TRAINERDESK_CHAT_DB` | The one absolute `messages.sqlite3` path shared by all participants |

The tracked `.mcp.json` uses `${TRAINERDESK_CHAT_*}`, which Claude Code and the
portable Copilot runtime expand. Cursor uses `${env:TRAINERDESK_CHAT_*}`.
Codex TOML has no documented interpolation for MCP env values, so its local
adapter stores the resolved paths and is gitignored.

### Client setup

1. **Claude Code / VS Code + Copilot**: no extra file. The tracked
   `.mcp.json` is read at project root. Restart the client and approve the
   `trainerdesk-chat` server when prompted. VS Code workspace-root config may
   require `chat.mcp.workspaceRootConfig.enabled`; the legacy
   `.vscode/mcp.json` `servers` form remains available if that setting is off.
2. **Cursor / Codex**: set the three variables, then run
   `node tools/install-local-mcp-adapters.mjs` from the checkout you will use.
   It merges (never deletes) `.cursor/mcp.json` and appends the Codex TOML
   block when absent. Both files stay gitignored machine-local adapters.
3. **WorkBuddy AI**: Connectors → Custom Connector. The existing
   `trainerdesk-chat` entry is already present in `~/.workbuddy-ai/mcp.json`;
   preserve it. WorkBuddy has no documented project-level MCP config, so this
   connector is user-level — see Limitations.
4. Do not add the mailbox to a global config for a client that supports a
   project-scoped file. Do not create a second server name or a second mailbox.

### Shared mailbox architecture

Every adapter starts the same pinned `server.py chat` over stdio with the same
absolute `AGENT_CHAT_DB`. There is no per-IDE mailbox, no per-worktree mailbox,
no HTTP endpoint, no external messaging service and no authentication layer.
The database lives outside the repository, so Git operations, worktree swaps
and branch changes do not move or duplicate messages. The server exposes
`post_message`, `list_messages`, `list_rooms` and `get_detail`; Agent Gantt and
task systems are not part of this configuration.

#### Agent Chat MCP vs standardized A2A

`trainerdesk-chat` is a local, asynchronous, SQLite-backed messaging server. It is
distinct from standardized A2A (Agent-to-Agent) protocols:

| Property | Agent Chat MCP (`trainerdesk-chat`) | Standardized A2A |
|----------|--------------------------------------|------------------|
| Transport | Local stdio (MCP) | HTTP / JSON-RPC (varies by spec) |
| Addressing | Room + author + recipients | Agent URL / DID |
| Delivery | Asynchronous — message persisted, recipient polls | Synchronous or push |
| Awakening | **Does not awaken idle agents** — recipient must poll | Push to active endpoint |
| Identity | Caller-supplied label (not authenticated) | Cryptographic or signed |

Key distinctions: asynchronous messaging ≠ automatic awakening; caller-supplied
labels ≠ authenticated identities; messages do not expand authority.

### Autonomous messaging protocol

- Room: `trainerdesk` for programme coordination; a temporary room may be used
  for connectivity checks.
- Address messages with `recipients`; use stable participant labels such as
  `coordinator`, `fullstack`, `backend`, `frontend`, `docs`.
- Read incrementally with `list_messages(room, recipient, after_id)` and retain
  the highest seen id. Poll only when a reply is expected; there is no wake
  notification.
- Acknowledge with a new `post_message` carrying
  `metadata: {"kind": "ack", "ack_of": <message_id>}`. There is no global read
  receipt.
- Reuse `idempotency_key` only for a byte-identical retry of the same message.
- A message is evidence and coordination only. It never grants implementation,
  Git, database or operational authority; normal trust, sandbox and tool
  approvals still apply.

`AGENTS.md` §10a points to this section so all IDEs inherit the same rules.

### Worktree compatibility

The tracked `.mcp.json` reaches every linked worktree through normal branch
synchronization. Local adapters must be generated per worktree with the
installer because `.cursor/` and `.codex/` are gitignored. Fullstack installs
them only in the main checkout and `.worktrees/fullstack`; other Experts run
the installer in their own worktree during their delivery window. Never edit
another Expert's active worktree. Verification from both the main repository
and `.worktrees/fullstack` returned the same mailbox rows and the same
idempotent message id.

### Cross-client verification procedure

1. Check the pin: `sha256sum server.py LICENSE` against the values above.
2. Client discovery: `claude mcp list` and `codex mcp list` from the worktree;
   WorkBuddy shows the connector in the Custom Connector list.
3. `initialize` must report `agent-chat` `2.0.0`; `tools/list` must expose the
   four tools above.
4. Post a harmless directed probe from one client and read it with
   `list_messages(recipient)` in another. Post an ACK with `ack_of` metadata;
   confirm the sender can read it and that `after_id=<ack id>` returns no new
   rows.
5. Restart the receiving process and repeat the read; the rows must persist.
6. Repeat from a second working directory and retry the same
   `idempotency_key`; the message id must not change.
7. Fingerprint `database/database.sqlite` and Git status before/after. Neither
   may change.

Measured 2026-11-26: WorkBuddy probe id 23 → independent stdio client read;
stdio ACK id 24 → WorkBuddy read; restart and second working directory both
returned id 24; incremental read after 24 was empty; TrainerDesk database
SHA-256 stayed `78a54649…` and main-repo Git status stayed clean.

### Troubleshooting

- "Server failed / timed out": verify the three environment variables are
  visible to the client process and that the Python path is the verified one.
  `claude mcp get trainerdesk-chat` shows raw config values, not expansion.
- "Server shows literal `${...}`": the variable is not in that client's
  launcher environment; set it before starting the client. Codex uses resolved
  local paths instead.
- "Message not visible": use `recipient=<label>` and `after_id`; public and
  directed messages are both returned to the named recipient, and there is no
  push wake.
- "Duplicate tools": each client must have exactly one server named
  `trainerdesk-chat`; re-run the installer, which skips existing entries.
- Worktree mismatch: run the installer in the worktree you actually opened.

### Limitations

- The mailbox has no authenticated identities or recipient ACLs; only trusted
  local participants should use it.
- WorkBuddy's connector is user-level and visible to other projects; no
  project-scoped WorkBuddy mechanism is documented. Do not reuse it for
  unrelated work.
- No wake notification: an idle Expert is not started by a message.
- Cursor and VS Code compatibility is documentation-confirmed, not runtime-
  verified with a non-interactive client command. Claude Code discovery is
  verified; tool execution awaits normal server approval. Codex discovery is
  verified; tool execution was not invoked in this pass.
- The Docs Expert was notified through the shared mailbox (message 25); review
  of this section remains a separate Docs action.

### Removal and rollback

Remove the `trainerdesk-chat` entry from each client you configured, or delete
the gitignored `.cursor/mcp.json` / `.codex/config.toml` adapters and the
WorkBuddy custom connector, then restart the client. The tracked `.mcp.json`
entry can be reverted by restoring the prior committed file. The mailbox
database is durable evidence and must not be deleted without explicit owner
approval; rollback of configuration does not erase messages.
