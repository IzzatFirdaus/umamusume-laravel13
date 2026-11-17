#!/usr/bin/env bash
# Bootstrap agent control surfaces for a fresh Trainer Desk clone.
# Copies .mcp.example.json to .mcp.json if absent, makes hook scripts
# executable, and prints the env vars the user must set. No secret generation.
set -euo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"

# 1. Provision .mcp.json from the redacted example if absent.
if [ ! -f "$ROOT/.mcp.json" ]; then
  cp "$ROOT/.mcp.example.json" "$ROOT/.mcp.json"
  echo "Created .mcp.json from .mcp.example.json"
else
  echo ".mcp.json already present, skipping"
fi

# 2. Make hook scripts executable.
chmod +x "$ROOT/.claude/hooks/"*.mjs 2>/dev/null || true

# 3. Print required env vars (set in your shell or devcontainer containerEnv).
echo
echo "Required env vars (set before using MCP servers that need them):"
echo "  FIRECRAWL_API_KEY   only if re-adding firecrawl to .mcp.json"
echo "  TAVILY_API_KEY      only if re-adding tavily to .mcp.json"
echo
echo "Done. Review .claude/settings.json and .claude/hooks/ before first use."
