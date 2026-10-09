# MCP Setup

Local, IDE-agnostic MCP configuration for this project. No secrets are stored
in either config file. Both files are gitignored (`.gitignore` lines 47 and
72), so nothing here is ever committed.

## Enabled servers

| Name        | Package                              | Purpose                                     | Boundary                                        |
| ----------- | ------------------------------------ | ------------------------------------------- | ----------------------------------------------- |
| `filesystem`| `@modelcontextprotocol/server-filesystem` | Read/write files                      | Server accepts exactly one root argument: the project absolute path. Access outside it is refused by the server. |
| `git`       | `@cyanheads/git-mcp-server`          | Inspect history, status, diffs, blame       | `GIT_BASE_DIR` env var restricts every operation to the project tree. The server also exposes write tools (`git_commit`, `git_push`); the agent is instructed to never invoke them without an explicit user request. |
| `docs`      | `@upstash/context7-mcp`              | Retrieve indexed library/framework docs     | Read-only document retrieval. Optional `CONTEXT7_API_KEY` env var raises the anonymous rate limit; not required. |
| `laravel-boost` | in-repo (`php artisan boost:mcp`)  | Laravel version-specific docs and code search | Project-local stdio server, no network. Installed via `laravel/boost` (require-dev). |
| `gitkraken` | `@gitkraken/gk`                      | Git history, issues, PRs, multi-repo workflows | Project-local `gk mcp` over stdio. Cloud tools (issues, PRs) need `gk auth login` first. |
| `sequentialthinking` | `@modelcontextprotocol/server-sequential-thinking` | Structured step-by-step reasoning | User-global config only, no env. |
| `memory`    | `@modelcontextprotocol/server-memory` | Persistent knowledge graph across sessions | User-global config only. `MEMORY_FILE_PATH` points at `%USERPROFILE%/.memory/memory.json`. |

## Test runner (no MCP server)

No language-agnostic restricted shell MCP exists on the npm registry
(`@modelcontextprotocol/server-shell` and community candidates all return
404). The restricted command executor is implemented natively instead, in
`.opencode/opencode.json` under `permission.bash`: the built-in shell tool
is client-enforced to an allowlist of test commands only.

- `php artisan test*` (Pest suite, any filter)
- `vendor/bin/pest*`
- `composer test`
- `npm run test*`

Everything else stays on `ask`. This is enforced by the opencode client, not
by the agent's prompt, so it cannot be talked around.

## Config locations

- `.mcp.json` (project root): standard `mcpServers` schema. Works with
  opencode and other MCP-capable clients.
- `.opencode/opencode.json`: opencode-native `mcp` block with the same
  servers plus the `permission.bash` allowlist.
- `opencode.json` (project root): `laravel-boost` (already there) plus `gitkraken`.
- `.vscode/mcp.json`: VS Code workspace file (`servers` schema) with the
  same five project servers.
- User-global: `%APPDATA%/Code/User/mcp.json` and
  `%USERPROFILE%/.config/opencode/opencode.json` carry `sequentialthinking`
  and `memory` only.

Keep server names identical in every file. opencode merges by name, so a
duplicate name with different settings means last-write-wins.

## Adding or removing a server

1. Edit both `.mcp.json` and `.opencode/opencode.json` (same name, same
   command).
2. Restart opencode. Config is loaded at startup only; the running session
   keeps the old config until restart.
3. Health-check the server: it must answer a JSON-RPC `initialize` request
   over stdio before any tool call is trusted.

## Reminders

- Review every proposed file change, commit, and push before applying. The
  git server can write; the agent must not do so unprompted.
- The filesystem server is scoped to this project root only. Do not add
  additional roots unless the boundary should widen.
- Already connected globally (not in these files): GitKraken git tools and
  the laravel-boost server (Laravel doc search) via the agent host. This
  project config declares its own `gitkraken` and `laravel-boost` entries on
  top of those, plus user-global `sequentialthinking` and `memory`.

## Deviations from the requested package list

- `@modelcontextprotocol/server-git` and `@modelcontextprotocol/server-fetch`
  are unpublished (404 on npm; the reference servers were archived).
  Replaced with `@cyanheads/git-mcp-server` (maintained, Apache-2.0,
  STDIO + Streamable HTTP) and `@upstash/context7-mcp`.
- Test-runner MCP: see above, native allowlist instead of a shell MCP.
