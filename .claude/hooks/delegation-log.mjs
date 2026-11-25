#!/usr/bin/env node
// Delegation log hook (Claude Code + Copilot harness).
// Wired to SubagentStart with matcher "*". Appends one JSONL line per
// delegation with parent/child identity, depth, and task. Always exits 0.
// Paths are module-relative, never ${CLAUDE_PROJECT_DIR} (unset under the
// Copilot harness; an empty value resolves to the drive root).
import { readFileSync, appendFileSync, mkdirSync } from 'node:fs';
import { join } from 'node:path';
import { fileURLToPath } from 'node:url';

const REPO = fileURLToPath(new URL('../..', import.meta.url));
const logDir = join(REPO, '.tooling', 'logs');
const logFile = join(logDir, 'delegation.jsonl');

let input = '';
try { input = readFileSync(0, 'utf8'); } catch {}
let p = {};
try { p = JSON.parse(input); } catch { process.exit(0); }

const entry = {
  ts: new Date().toISOString(),
  event: 'SubagentStart',
  parent_id: p?.session_id ?? p?.sessionId ?? p?.parent_id ?? 'unknown',
  child_id: p?.agent_id ?? p?.child_id ?? p?.tool_input?.child_id ?? 'unknown',
  depth: p?.depth ?? p?.tool_input?.depth ?? 0,
  task: String(p?.tool_input?.prompt ?? p?.toolArgs?.prompt ?? p?.prompt ?? p?.agent_name ?? p?.description ?? '').slice(0, 300)
};

try {
  mkdirSync(logDir, { recursive: true });
  appendFileSync(logFile, JSON.stringify(entry) + '\n');
} catch {}
process.exit(0);
