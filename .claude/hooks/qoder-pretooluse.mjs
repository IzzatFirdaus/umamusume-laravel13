#!/usr/bin/env node
// Qoder PreToolUse hook adapter for Trainer Desk.
// Reads Qoder's Claude-compatible payload (tool_name / tool_input.command) with
// fallback field names (toolName / input.command) for Qoder-specific shapes.
// Classifies against the shared .tooling/policy.json table; blocks via exit code 2.
// Fail-closed: classify() returns a deny rule if the policy table cannot load.
import { readFileSync } from 'node:fs';
import { classify } from '../../.tooling/policy.mjs';
import { logDenial } from '../../.tooling/denial-log.mjs';

let input = '';
try { input = readFileSync(0, 'utf8'); } catch {}
let p = {};
try { p = JSON.parse(input); } catch (err) {
  process.stderr.write('qoder-pretooluse: failed to parse hook payload: ' + String(err && err.message ? err.message : err) + '\n');
  process.exit(2);
}

const tool = p?.tool_name ?? p?.toolName ?? '';
const cmd = p?.tool_input?.command ?? p?.input?.command ?? '';
const result = classify(tool, cmd);

if (!result || !result.deny) {
  process.exit(0);
}

const first = String(cmd).split(/\s+/)[0] ?? cmd;
process.stderr.write((result.message || 'Blocked command.').replace('{cmd}', first) + '\n');
logDenial({ runtime: 'qoder', tool, command: cmd, rule: result.id, replacement: result.replacement ?? '' });
process.exit(2);
