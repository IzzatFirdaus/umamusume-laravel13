#!/usr/bin/env node
// Delegation depth cap + spawn logger (Claude Code + Copilot harness).
// Wired to PreToolUse on Task|Agent. Tracks depth per session in
// .claude/state/subagent-depth.json with TTL eviction so an interrupted spawn
// cannot permanently lock delegation. Denies spawns above maxDepth (2).
// Logs one JSONL spawn record per invocation to .claude/logs/delegation.jsonl.
//
// Paths are module-relative, never ${CLAUDE_PROJECT_DIR}: that variable is unset
// under the Copilot harness, and an empty value resolves to the drive root.
// Usage: delegate-cap.mjs        -- normal spawn path
//        delegate-cap.mjs --reset -- clears all depth state (manual recovery)
import { readFileSync, appendFileSync, mkdirSync, writeFileSync, renameSync } from 'node:fs';
import { join } from 'node:path';
import { fileURLToPath } from 'node:url';

const REPO = fileURLToPath(new URL('../..', import.meta.url));
const RESET = process.argv.includes('--reset');
const stateDir = join(REPO, '.claude', 'state');
const logDir = join(REPO, '.claude', 'logs');
const stateFile = join(stateDir, 'subagent-depth.json');
const maxDepth = 2;
const ttlMs = 30 * 60 * 1000; // 30 minutes
const deny = (msg) => { process.stderr.write(msg + '\n'); process.exit(2); };

// Atomic replace: a concurrent spawn or a killed process cannot leave a
// half-written state file that the next read parses as garbage.
function writeState(obj) {
  try {
    mkdirSync(stateDir, { recursive: true });
    const tmp = stateFile + '.' + process.pid + '.tmp';
    writeFileSync(tmp, JSON.stringify(obj) + '\n');
    renameSync(tmp, stateFile);
  } catch {}
}

let state = {};
try { state = JSON.parse(readFileSync(stateFile, 'utf8')); if (!state || typeof state !== 'object') state = {}; } catch {}

// TTL eviction: drop sessions whose depth entry has expired.
const now = Date.now();
for (const [k, v] of Object.entries(state)) {
  if (v && v.expires_at && now > v.expires_at) delete state[k];
}

if (RESET) {
  writeState({});
  console.log('depth state reset');
  process.exit(0);
}

let input = '';
try { input = readFileSync(0, 'utf8'); } catch {}
let p = {};
try { p = JSON.parse(input); } catch {}
const tool_name = p?.tool_name ?? p?.toolName ?? '';
// Claude `Task`/`Agent`, Copilot `task`, VS Code Local `runSubagent`,
// Hermes `execution_subagent`.
if (!/task|agent|subagent/i.test(tool_name)) process.exit(0);

// Session-scoped depth: a stale entry from a previous session cannot block this
// one. Copilot sends session_id at the top level; Claude sends sessionId.
const sessionId = p?.session_id ?? p?.sessionId ?? p?.tool_input?.sessionId ?? 'default';
const entry = state[sessionId] ?? { depth: 0, expires_at: now + ttlMs };
if (entry.depth >= maxDepth) {
  deny(`Blocked: subagent depth cap reached (${maxDepth}) for session ${sessionId}. Do not nest subagents deeper than ${maxDepth}. Use delegate-cap.mjs --reset to recover.`);
}
entry.depth += 1;
entry.expires_at = now + ttlMs;
state[sessionId] = entry;
writeState(state);

const logEntry = {
  timestamp: new Date().toISOString(),
  event: 'SubagentSpawn',
  tool: tool_name,
  session: sessionId,
  depth: entry.depth,
  prompt: String(p?.tool_input?.prompt ?? p?.toolArgs?.prompt ?? p?.tool_input?.description ?? '').slice(0, 200),
};
try { mkdirSync(logDir, { recursive: true }); appendFileSync(join(logDir, 'delegation.jsonl'), JSON.stringify(logEntry) + '\n'); } catch {}
process.exit(0);
