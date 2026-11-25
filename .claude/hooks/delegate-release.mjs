#!/usr/bin/env node
// Delegation depth release (Claude Code + Copilot harness).
// Wired to PostToolUse on Task|Agent. Decrements the per-session depth counter
// in .claude/state/subagent-depth.json so a completed subagent frees its level.
// TTL-evicts expired sessions. Paths are module-relative, never
// ${CLAUDE_PROJECT_DIR} (unset under the Copilot harness).
import { readFileSync, mkdirSync, writeFileSync, renameSync } from 'node:fs';
import { join } from 'node:path';
import { fileURLToPath } from 'node:url';

const REPO = fileURLToPath(new URL('../..', import.meta.url));
const stateDir = join(REPO, '.claude', 'state');
const stateFile = join(stateDir, 'subagent-depth.json');

let input = '';
try { input = readFileSync(0, 'utf8'); } catch {}
let p = {};
try { p = JSON.parse(input); } catch {}
const tool_name = p?.tool_name ?? p?.toolName ?? '';
if (!/task|agent|subagent/i.test(tool_name)) process.exit(0);

let state = {};
try { state = JSON.parse(readFileSync(stateFile, 'utf8')); if (!state || typeof state !== 'object') state = {}; } catch {}

// TTL eviction.
const now = Date.now();
for (const [k, v] of Object.entries(state)) {
  if (v && v.expires_at && now > v.expires_at) delete state[k];
}

const sessionId = p?.session_id ?? p?.sessionId ?? p?.tool_input?.sessionId ?? 'default';
const entry = state[sessionId];
if (entry && entry.depth > 0) {
  entry.depth -= 1;
  entry.expires_at = now + 30 * 60 * 1000;
  state[sessionId] = entry;
  // Atomic replace: a concurrent release cannot leave a half-written file.
  try {
    mkdirSync(stateDir, { recursive: true });
    const tmp = stateFile + '.' + process.pid + '.tmp';
    writeFileSync(tmp, JSON.stringify(state) + '\n');
    renameSync(tmp, stateFile);
  } catch {}
}
process.exit(0);
