#!/usr/bin/env node
// Delegation depth cap + spawn logger for Trainer Desk (Claude Code).
// Wired to PreToolUse on Task|Agent. Tracks depth per session in
// .claude/state/subagent-depth.json with TTL eviction so an interrupted
// spawn cannot permanently lock delegation. Denies spawns above maxDepth (2).
// Logs one JSONL spawn record per invocation to .claude/logs/delegation.jsonl.
// Usage: delegate-cap.mjs        -- normal spawn path
//        delegate-cap.mjs --reset -- clears all depth state (manual recovery)
import { readFileSync } from 'node:fs';
import { appendFileSync, mkdirSync, writeFileSync } from 'node:fs';
import { join } from 'node:path';
const RESET = process.argv.includes('--reset');
const projectDir = process.env.CLAUDE_PROJECT_DIR || '.';
const stateDir = join(projectDir, '.claude', 'state');
const logDir = join(projectDir, '.claude', 'logs');
const stateFile = join(stateDir, 'subagent-depth.json');
const maxDepth = 2;
const ttlMs = 30 * 60 * 1000; // 30 minutes
const deny = (msg) => { process.stderr.write(msg + '\n'); process.exit(2); };
let state = {};
try { state = JSON.parse(readFileSync(stateFile, 'utf8')); if (!state || typeof state !== 'object') state = {}; } catch {}
// TTL eviction: drop sessions whose depth entry has expired.
const now = Date.now();
for (const [k, v] of Object.entries(state)) {
  if (v && v.expires_at && now > v.expires_at) delete state[k];
}
if (RESET) {
  try { mkdirSync(stateDir, { recursive: true }); writeFileSync(stateFile, '{}'); } catch {}
  console.log('depth state reset');
  process.exit(0);
}
let input = '';
try { input = readFileSync(0, 'utf8'); } catch {}
let p = {};
try { p = JSON.parse(input); } catch {}
const tool_name = p?.tool_name ?? '';
if (tool_name !== 'Task' && tool_name !== 'Agent') process.exit(0);
// Session-scoped depth: a stale entry from a previous session cannot block this one.
const sessionId = p?.tool_input?.sessionId ?? p?.sessionId ?? 'default';
const entry = state[sessionId] ?? { depth: 0, expires_at: now + ttlMs };
if (entry.depth >= maxDepth) {
  deny(`Blocked: subagent depth cap reached (${maxDepth}) for session ${sessionId}. Do not nest subagents deeper than ${maxDepth}. Use delegate-cap.mjs --reset to recover.`);
}
entry.depth += 1;
entry.expires_at = now + ttlMs;
state[sessionId] = entry;
try { mkdirSync(stateDir, { recursive: true }); writeFileSync(stateFile, JSON.stringify(state) + '\n'); } catch {}
const logEntry = {
  timestamp: new Date().toISOString(),
  event: 'SubagentSpawn',
  tool: tool_name,
  session: sessionId,
  depth: entry.depth,
  prompt: String(p?.tool_input?.prompt ?? p?.tool_input?.description ?? '').slice(0, 200),
};
try { mkdirSync(logDir, { recursive: true }); appendFileSync(join(logDir, 'delegation.jsonl'), JSON.stringify(logEntry) + '\n'); } catch {}
process.exit(0);
