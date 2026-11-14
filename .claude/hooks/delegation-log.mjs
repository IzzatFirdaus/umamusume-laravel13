#!/usr/bin/env node
// Delegation log hook for Trainer Desk (Claude Code).
// Wired to SubagentStart with matcher "*". Appends one JSONL line per
// delegation with parent/child identity, depth, and task. Always exits 0.
import { readFileSync, appendFileSync, mkdirSync } from 'node:fs';
import { join } from 'node:path';

let input = '';
try { input = readFileSync(0, 'utf8'); } catch {}
let p = {};
try { p = JSON.parse(input); } catch { process.exit(0); }

const projectDir = process.env.CLAUDE_PROJECT_DIR || '.';
const logDir = join(projectDir, '.tooling', 'logs');
const logFile = join(logDir, 'delegation.jsonl');

const entry = {
  ts: new Date().toISOString(),
  event: 'SubagentStart',
  parent_id: p?.sessionId ?? p?.parent_id ?? 'unknown',
  child_id: p?.child_id ?? p?.tool_input?.child_id ?? 'unknown',
  depth: p?.depth ?? p?.tool_input?.depth ?? 0,
  task: String(p?.tool_input?.prompt ?? p?.prompt ?? p?.description ?? '').slice(0, 300)
};

try {
  mkdirSync(logDir, { recursive: true });
  appendFileSync(logFile, JSON.stringify(entry) + '\n');
} catch {}
process.exit(0);
