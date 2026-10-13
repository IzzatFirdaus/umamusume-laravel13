# MCP Setup

Local, IDE-agnostic MCP configuration for this project. This file records where
each client actually reads its MCP servers, and the two Windows gotchas that
make a correct-looking config silently fail.

> Corrected 2026-10-12. The previous version assumed a single `.mcp.json` was
> read by "opencode and other MCP-capable clients". It is not: each client reads
> a different file in a different schema. See "Config locations" below.

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
| `gitkraken` | `@gitkraken/gk`                      | Git history, issues, PRs, multi-repo workflows | Project-local `gk mcp` over stdio. Cloud tools (issues, PRs) need `gk auth login` first. |
| `sequentialthinking` | `@modelcontextprotocol/server-sequential-thinking` | Structured step-by-step reasoning | No env. |
| `memory`    | `@modelcontextprotocol/server-memory` | Persistent knowledge graph across sessions | `MEMORY_FILE_PATH` points at `%USERPROFILE%/.memory/memory.json`. |
| `playwright`| `@playwright/mcp`                    | Browser automation                          | Local stdio; drives a real Chromium. |
| `firecrawl` | `firecrawl-mcp`                      | Web crawl/extract                           | Needs `FIRECRAWL_API_KEY`. |
| `context7`  | `@upstash/context7-mcp`              | Hosted docs retrieval (same package as `docs`) | Needs `CONTEXT7_API_KEY` for higher limits. |
| `tavily`    | `tavily-mcp`                         | Web search                                  | Needs `TAVILY_API_KEY`. |

Server names must be identical across every file that a single client reads;
clients that merge by name take last-write-wins.

## Config locations (what each client actually reads)

| Client | Project file | Global file | Schema key |
| ------ | ------------ | ----------- | ---------- |
| Kilo (CLI + VS Code extension) | `kilo.json` (root), `opencode.json` (root, legacy) | `~/.config/kilo/kilo.json`, `~/.config/kilo/kilo.jsonc` | `mcp` |
| Claude Code | `.mcp.json` | `~/.claude.json` | `mcpServers` |
| VS Code / GitHub Copilot | `.vscode/mcp.json` | `%APPDATA%\Code\User\mcp.json` | `servers` |
| GitHub Copilot CLI | - | `~/.copilot/mcp-config.json` | `mcpServers` |
| opencode | `opencode.json` (root), `.opencode/opencode.json` | `~/.config/opencode/opencode.json(c)` | `mcp` |
| Cursor | `.cursor/mcp.json` | `~/.cursor/mcp.json` (absent) | `mcpServers` |
| Kiro | `.kiro/settings/mcp.json` | `%APPDATA%\Kiro\User\settings.json` (no MCP) | `mcpServers` |
| Trae | `.trae/mcp.json` | `%APPDATA%\Trae\User\...` | `mcpServers` |
| JetBrains Junie | `.junie/mcp/mcp.json` | - | `mcpServers` |
| Codex | `.codex/config.toml` | `~/.codex/config.toml` | `[mcp_servers.*]` |
| Grok | `.grok/config.toml` | - | `[mcp_servers.*]` |
| Qoder | - | `~/.qoder/settings.json` | `mcpServers` |
| Crush (Charm) | `crush.json` (root) | `%LOCALAPPDATA%\crush\crush.json` (no MCP) | `mcp` |

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
- Gemini CLI: `~/.gemini/config/mcp_config.json` is empty and
  `~/.gemini/settings.json` is absent.
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
