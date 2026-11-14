#!/usr/bin/env node
// Delegation depth release for Trainer Desk (Claude Code).
// Wired to PostToolUse on Task|Agent. Decrements the per-session depth
// counter in .claude/state/subagent-depth.json so a completed subagent
// frees its level. TTL-evicts expired sessions.
import { readFileSync } from 'node:fs';
import { mkdirSync, writeFileSync } from 'node:fs';
import { join } from 'node:path';
let input = '';
try { input = readFileSync(0, 'utf8'); } catch {}
let p = {};
try { p = JSON.parse(input); } catch {}
const tool_name = p?.tool_name ?? '';
if (tool_name !== 'Task' && tool_name !== 'Agent') process.exit(0);
const projectDir = process.env.CLAUDE_PROJECT_DIR || '.';
const stateDir = join(projectDir, '.claude', 'state');
const stateFile = join(stateDir, 'subagent-depth.json');
let state = {};
try { state = JSON.parse(readFileSync(stateFile, 'utf8')); if (!state || typeof state !== 'object') state = {}; } catch {}
// TTL eviction.
const now = Date.now();
for (const [k, v] of Object.entries(state)) {
  if (v && v.expires_at && now > v.expires_at) delete state[k];
}
const sessionId = p?.tool_input?.sessionId ?? p?.sessionId ?? 'default';
const entry = state[sessionId];
if (entry && entry.depth > 0) {
  entry.depth -= 1;
  entry.expires_at = now + 30 * 60 * 1000;
  state[sessionId] = entry;
  try { mkdirSync(stateDir, { recursive: true }); writeFileSync(stateFile, JSON.stringify(state) + '\n'); } catch {}
}
process.exit(0);
