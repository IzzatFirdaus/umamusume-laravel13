#!/usr/bin/env node
// Hermes pre_tool_call hook adapter for Trainer Desk.
// Reads Hermes' payload (hook_event_name, tool_name, tool_input.args) and
// classifies shell commands against the shared .tooling/policy.json table.
// Blocks via exit code 2 with a Hermes-native JSON block body on stderr.
// Fail-closed: classify() returns a deny rule if the policy table cannot load.
import { readFileSync } from 'node:fs';
import { classify } from '../../.tooling/policy.mjs';

let input = '';
try { input = readFileSync(0, 'utf8'); } catch {}
let p = {};
try { p = JSON.parse(input); } catch {
  process.exit(2);
}

if ((p?.hook_event_name ?? '') !== 'pre_tool_call') {
  process.exit(0);
}

const tool = p?.tool_name ?? '';
if (tool !== 'terminal' && tool !== 'process') {
  process.exit(0);
}

const args = p?.tool_input?.args;
// Shape-tolerant extraction: string passes through; arrays join (argv form);
// objects use .command; anything else is unrecognized -> fail closed
// (a String()-coerced "[object Object]" would match no rule and silently allow).
let cmd = null;
if (typeof args === 'string') {
  cmd = args;
} else if (Array.isArray(args)) {
  cmd = args.map((x) => String(x)).join(' ');
} else if (args !== null && typeof args === 'object') {
  const inner = args.command ?? args.args ?? args.cmd;
  if (typeof inner === 'string') cmd = inner;
  else if (Array.isArray(inner)) cmd = inner.map((x) => String(x)).join(' ');
}
if (cmd === null) {
  process.stderr.write(JSON.stringify({
    action: 'block',
    message: 'Blocked: unrecognized terminal/process payload shape; failing closed.'
  }) + '\n');
  process.exit(2);
}

// The shared table matches on Bash|PowerShell transport names; Hermes uses
// terminal/process, so map before classifying.
const result = classify('Bash', cmd);

if (!result || !result.deny) {
  process.exit(0);
}

const first = String(cmd).split(/\s+/)[0] ?? cmd;
const message = (result.message || 'Blocked command.').replace('{cmd}', first);
process.stderr.write(JSON.stringify({ action: 'block', message }) + '\n');
process.exit(2);
